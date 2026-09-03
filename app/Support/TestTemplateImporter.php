<?php

namespace App\Support;

use App\Enums\TestDepartment;
use App\Models\Test;
use App\Models\TestParameter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class TestTemplateImporter
{
    /**
     * @param  list<UploadedFile>  $files
     * @return array{attached: int, created: int, parameters: int, unmatched: list<string>}
     */
    public function importMany(array $files, ?Test $forcedTest = null): array
    {
        $attached = 0;
        $created = 0;
        $parameters = 0;
        $unmatched = [];

        foreach ($files as $file) {
            $result = $this->import($file, $forcedTest);

            if ($result['test'] === null) {
                $unmatched[] = $file->getClientOriginalName();

                continue;
            }

            $attached++;
            $created += $result['created'] ? 1 : 0;
            $parameters += $result['parameters'];
        }

        return [
            'attached' => $attached,
            'created' => $created,
            'parameters' => $parameters,
            'unmatched' => $unmatched,
        ];
    }

    /**
     * @return array{test: Test|null, created: bool, parameters: int}
     */
    public function import(UploadedFile $file, ?Test $forcedTest = null): array
    {
        $originalName = $file->getClientOriginalName();
        $test = $forcedTest ?? $this->matchTest($originalName);
        $created = false;

        if ($test === null) {
            $label = $this->stem($originalName);

            if ($label === '') {
                return ['test' => null, 'created' => false, 'parameters' => 0];
            }

            $test = Test::query()->create([
                'name' => $label,
                'code' => Str::upper(Str::limit(preg_replace('/\s+/', '', $label) ?? $label, 20, '')),
                'price' => 0,
                'normal_range' => __('See parameters'),
                'department' => TestDepartment::Routine,
                'is_active' => true,
            ]);
            $created = true;
        }

        $text = $this->extractText($file);
        $rows = $this->parseParameters($text);

        if ($test->template_path) {
            Storage::disk('local')->delete($test->template_path);
        }

        $path = $file->store('test-templates/'.$test->id, 'local');

        $test->update([
            'template_path' => $path,
            'template_filename' => $originalName,
            'interpretation' => $this->notes($text, $rows) ?: $test->interpretation,
            'normal_range' => $rows[0]['normal_range'] ?? $test->normal_range,
        ]);

        $parameterCount = 0;

        if ($rows !== []) {
            $test->parameters()->delete();

            foreach ($rows as $index => $row) {
                TestParameter::query()->create([
                    'test_id' => $test->id,
                    'name' => $row['name'],
                    'unit' => $row['unit'],
                    'normal_range' => $row['normal_range'],
                    'group_name' => $row['group_name'],
                    'sort_order' => $index + 1,
                ]);
                $parameterCount++;
            }
        }

        return [
            'test' => $test->fresh('parameters'),
            'created' => $created,
            'parameters' => $parameterCount,
        ];
    }

    public function matchTest(string $filename): ?Test
    {
        $stem = $this->stem($filename);

        if ($stem === '') {
            return null;
        }

        $normalized = $this->normalize($stem);

        $tests = Test::query()->orderBy('name')->get();

        $exact = $tests->first(function (Test $test) use ($normalized): bool {
            return $this->normalize($test->name) === $normalized
                || $this->normalize((string) $test->code) === $normalized;
        });

        if ($exact !== null) {
            return $exact;
        }

        return $tests->first(function (Test $test) use ($normalized): bool {
            $name = $this->normalize($test->name);
            $code = $this->normalize((string) $test->code);

            return ($name !== '' && (str_contains($name, $normalized) || str_contains($normalized, $name)))
                || ($code !== '' && (str_contains($code, $normalized) || str_contains($normalized, $code)));
        });
    }

    /**
     * @return list<array{name: string, unit: string, normal_range: string, group_name: string|null}>
     */
    public function parseParameters(string $text): array
    {
        $rows = [];
        $group = null;
        $skip = ['investigation', 'result', 'results', 'unit', 'units', 'normal range', 'reference', 'component', 'parameter', 'test', 'name', 'range'];

        foreach (preg_split("/\r\n|\n|\r/", $text) ?: [] as $line) {
            $line = trim(preg_replace('/[ \t]+/', ' ', $line) ?? '');

            if ($line === '') {
                continue;
            }

            if (in_array(Str::lower($line), $skip, true)) {
                continue;
            }

            if (! preg_match('/\d/', $line) && mb_strlen($line) <= 40) {
                $group = $line;

                continue;
            }

            $parsed = $this->parseLine($line);

            if ($parsed === null) {
                continue;
            }

            $parsed['group_name'] = $group;
            $rows[] = $parsed;
        }

        return $rows;
    }

    public function extractText(UploadedFile $file): string
    {
        $extension = Str::lower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        if ($path === false) {
            return '';
        }

        if (in_array($extension, ['docx', 'doc'], true)) {
            return $this->extractDocx($path);
        }

        if ($extension === 'pdf') {
            return $this->extractPdf($path);
        }

        return '';
    }

    private function extractDocx(string $path): string
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if ($xml === '') {
            return '';
        }

        $xml = str_replace(['</w:p>', '</w:tr>', '</w:br>'], "\n", $xml);
        $xml = str_replace(['</w:tc>', '<w:tab/>'], "\t", $xml);

        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractPdf(string $path): string
    {
        $raw = file_get_contents($path);

        if ($raw === false) {
            return '';
        }

        $chunks = [];

        if (preg_match_all('/stream\s*(.*?)\s*endstream/s', $raw, $streams) > 0) {
            foreach ($streams[1] as $stream) {
                $stream = ltrim($stream, "\r\n");
                $decoded = @gzuncompress($stream) ?: @gzinflate(substr($stream, 2));
                $chunks[] = is_string($decoded) ? $decoded : $stream;
            }
        }

        $text = implode("\n", $chunks);

        if (preg_match_all('/\((.*?)\)/', $text !== '' ? $text : $raw, $literals) > 0) {
            return implode("\n", $literals[1]);
        }

        return $text;
    }

    /**
     * @return array{name: string, unit: string, normal_range: string, group_name: string|null}|null
     */
    private function parseLine(string $line): ?array
    {
        if (! preg_match('/(\d+(?:[.,]\d+)?\s*(?:[-–]|to)\s*\d+(?:[.,]\d+)?|[<≤>≥]\s*\d+(?:[.,]\d+)?)/iu', $line, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $range = trim(str_replace(['–', 'to', 'TO'], '-', $match[1][0]));
        $before = trim(substr($line, 0, (int) $match[0][1]));
        $after = trim(substr($line, (int) $match[0][1] + strlen($match[0][0])));

        if ($before === '') {
            return null;
        }

        $parts = preg_split('/\s{2,}|\t/', $before) ?: [$before];
        $name = trim((string) $parts[0]);
        $unitFromBefore = trim(implode(' ', array_slice($parts, 1)));
        $unit = $unitFromBefore !== '' ? $unitFromBefore : $after;

        if ($name === '' || is_numeric($name)) {
            return null;
        }

        return [
            'name' => $name,
            'unit' => trim($unit),
            'normal_range' => $range,
            'group_name' => null,
        ];
    }

    /**
     * @param  list<array{name: string, unit: string, normal_range: string, group_name: string|null}>  $rows
     */
    private function notes(string $text, array $rows): ?string
    {
        $used = collect($rows)->pluck('name')->filter()->all();
        $kept = [];

        foreach (preg_split("/\r\n|\n|\r/", $text) ?: [] as $line) {
            $line = trim($line);

            if (mb_strlen($line) < 20) {
                continue;
            }

            foreach ($used as $name) {
                if (str_contains($line, $name)) {
                    continue 2;
                }
            }

            $kept[] = $line;
        }

        $notes = trim(implode("\n", array_slice($kept, 0, 4)));

        return $notes !== '' ? Str::limit($notes, 1000, '') : null;
    }

    private function stem(string $filename): string
    {
        return trim((string) preg_replace(
            '/\b(template|report|form|range|ranges|result|results)\b/i',
            '',
            str_replace(['_', '-'], ' ', pathinfo($filename, PATHINFO_FILENAME)),
        ));
    }

    private function normalize(string $value): string
    {
        return Str::lower((string) preg_replace('/[^a-z0-9]+/i', '', $value));
    }
}
