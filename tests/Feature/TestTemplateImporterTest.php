<?php

namespace Tests\Feature;

use App\Models\Test;
use App\Models\TestParameter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class TestTemplateImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploading_a_word_file_links_it_to_the_matching_test_and_reads_ranges(): void
    {
        Storage::fake('local');

        $test = Test::factory()->create([
            'name' => 'CBC',
            'code' => 'CBC',
            'normal_range' => 'old',
        ]);

        $file = $this->makeDocx('CBC.docx', [
            'Investigation Result Unit Normal range',
            'WBC 4.0-11.0 x10^9/L',
            'HB 12-16 g/dL',
        ]);

        $response = $this->from(route('settings.index'))
            ->post(route('tests.templates.store'), [
                'files' => [$file],
            ]);

        $response->assertRedirect(route('settings.index'));

        $test->refresh();

        $this->assertSame('CBC.docx', $test->template_filename);
        $this->assertNotNull($test->template_path);
        $this->assertSame(2, $test->parameters()->count());
        $this->assertSame('WBC', $test->parameters()->orderBy('sort_order')->value('name'));
        $this->assertSame('4.0-11.0', TestParameter::query()->where('name', 'WBC')->value('normal_range'));
        $this->assertSame('g/dL', TestParameter::query()->where('name', 'HB')->value('unit'));
    }

    public function test_named_file_creates_a_test_when_none_matches(): void
    {
        Storage::fake('local');

        $file = $this->makeDocx('TFT.docx', [
            'TSH 0.4-4.0 mIU/L',
        ]);

        $this->post(route('tests.templates.store'), [
            'files' => [$file],
        ])->assertRedirect();

        $test = Test::query()->firstWhere('name', 'TFT');

        $this->assertNotNull($test);
        $this->assertSame('TFT.docx', $test->template_filename);
        $this->assertSame('TSH', $test->parameters()->value('name'));
        $this->assertSame('0.4-4.0', $test->parameters()->value('normal_range'));
    }

    public function test_template_upload_rejects_a_missing_file(): void
    {
        $response = $this->from(route('tests.index'))
            ->post(route('tests.templates.store'), []);

        $response->assertRedirect(route('tests.index'));
        $response->assertSessionHasErrors(['files']);
    }

    /**
     * @param  list<string>  $lines
     */
    private function makeDocx(string $filename, array $lines): UploadedFile
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('lab-', true).'.docx';
        $paragraphs = '';

        foreach ($lines as $line) {
            $paragraphs .= '<w:p><w:r><w:t>'.htmlspecialchars($line, ENT_XML1).'</w:t></w:r></w:p>';
        }

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString(
            '[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>',
        );
        $zip->addFromString(
            '_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>',
        );
        $zip->addFromString(
            'word/document.xml',
            '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$paragraphs.'</w:body></w:document>',
        );
        $zip->close();

        return new UploadedFile(
            $path,
            $filename,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true,
        );
    }
}
