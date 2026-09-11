<?php

namespace Tests\Feature;

use App\Enums\TestDepartment;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TestImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_downloads_a_csv_header(): void
    {
        $response = $this->get(route('tests.import.template'));

        $response->assertOk();
        $response->assertDownload('lab-tests-template.csv');
        $this->assertStringContainsString(
            'name,code,department,price,normal_range,method',
            $response->streamedContent(),
        );
    }

    public function test_uploading_a_csv_creates_and_updates_catalogue_tests(): void
    {
        Test::factory()->create([
            'name' => 'Old CBC',
            'code' => 'CBC',
            'price' => '100.00',
            'normal_range' => 'old',
        ]);

        $csv = UploadedFile::fake()->createWithContent(
            'tests.csv',
            "name,code,department,price,normal_range,method\nCBC,CBC,routine,250,See parameters,Automated\nTFT,TFT,special_chemistry,180,0.4-4.0,ECL\n",
        );

        $response = $this->from(route('tests.index'))
            ->post(route('tests.import'), [
                'file' => $csv,
            ]);

        $response->assertRedirect(route('tests.index'));

        $cbc = Test::query()->firstWhere('code', 'CBC');
        $tft = Test::query()->firstWhere('code', 'TFT');

        $this->assertSame('CBC', $cbc?->name);
        $this->assertSame('250.00', $cbc?->price);
        $this->assertSame('TFT', $tft?->name);
        $this->assertSame(TestDepartment::SpecialChemistry->value, $tft?->department->value);
        $this->assertSame(2, Test::query()->count());
    }

    public function test_import_rejects_a_missing_file(): void
    {
        $response = $this->from(route('tests.index'))
            ->post(route('tests.import'), []);

        $response->assertRedirect(route('tests.index'));
        $response->assertSessionHasErrors(['file']);
    }
}
