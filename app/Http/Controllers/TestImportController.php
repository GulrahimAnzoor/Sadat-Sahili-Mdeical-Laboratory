<?php

namespace App\Http\Controllers;

use App\Enums\TestDepartment;
use App\Http\Requests\ImportTestsRequest;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestImportController extends Controller
{
    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['name', 'code', 'department', 'price', 'normal_range', 'method']);
            fputcsv($handle, ['CBC', 'CBC', 'routine', '250', 'See parameters', 'Automated']);
            fclose($handle);
        }, 'lab-tests-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(ImportTestsRequest $request): RedirectResponse
    {
        $path = $request->file('file')?->getRealPath();

        if ($path === false || $path === null) {
            return back()->withErrors(['file' => __('The uploaded file could not be read.')]);
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->withErrors(['file' => __('The uploaded file could not be read.')]);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return back()->withErrors(['file' => __('The CSV file is empty.')]);
        }

        $header = array_map(fn (mixed $column): string => Str::of((string) $column)->trim()->lower()->replace("\ufeff", '')->toString(), $header);
        $created = 0;
        $updated = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $record = [];

            foreach ($header as $index => $column) {
                $record[$column] = trim((string) ($row[$index] ?? ''));
            }

            $name = $record['name'] ?? '';

            if ($name === '') {
                continue;
            }

            $department = TestDepartment::tryFrom($record['department'] ?? '') ?? TestDepartment::Routine;
            $attributes = [
                'code' => ($record['code'] ?? '') !== '' ? $record['code'] : null,
                'department' => $department,
                'price' => $record['price'] !== '' ? $record['price'] : '0',
                'normal_range' => ($record['normal_range'] ?? '') !== '' ? $record['normal_range'] : '—',
                'method' => ($record['method'] ?? '') !== '' ? $record['method'] : null,
                'is_active' => true,
            ];

            $test = Test::query()
                ->when(
                    filled($attributes['code']),
                    fn ($query) => $query->where('code', $attributes['code']),
                    fn ($query) => $query->where('name', $name),
                )
                ->first();

            if ($test !== null) {
                $test->update(['name' => $name, ...$attributes]);
                $updated++;

                continue;
            }

            Test::query()->create(['name' => $name, ...$attributes]);
            $created++;
        }

        fclose($handle);

        return redirect()
            ->route('tests.index')
            ->with('success', __('Imported :created tests and updated :updated.', [
                'created' => $created,
                'updated' => $updated,
            ]));
    }
}
