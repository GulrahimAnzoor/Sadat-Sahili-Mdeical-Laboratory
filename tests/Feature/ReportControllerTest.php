<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Test;
use App\Models\TestResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_renders_patient_results(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Ahmad Sadat']);
        $patient = Patient::factory()->for($doctor)->create(['name' => 'Hassan']);
        $test = Test::factory()->create([
            'name' => 'Fasting Blood Sugar',
            'normal_range' => '70-100 mg/dL',
            'interpretation' => 'Elevated values may indicate diabetes mellitus.',
            'method' => 'Hexokinase',
        ]);
        TestResult::factory()->for($patient)->for($test)->create([
            'result' => '92',
            'unit' => 'mg/dL',
        ]);

        $response = $this->get(route('reports.show', $patient));

        $response->assertOk();
        $response->assertSee('Hassan');
        $response->assertSee('Dr. Ahmad Sadat');
        $response->assertSee('Fasting Blood Sugar');
        $response->assertSee('92');
        $response->assertSee('70-100 mg/dL');
        $response->assertSee('Normal range');
        $response->assertSee('Units');
        $response->assertSee('Summary');
        $response->assertSee('Elevated values may indicate diabetes mellitus.');
        $response->assertSee('Hexokinase');
        $response->assertSee('Saadat Salihi');
        $response->assertSee('سعادت صالحي طبي', false);
        $response->assertSee('لابراتوار', false);
        $response->assertSee('0787698996');
        $response->assertSee(config('lab.address'));
        $response->assertSee('report-print-chrome');
        $response->assertSee('report-watermark');
        $response->assertSee('ssml-logo.png', false);
    }

    public function test_index_lists_patients_for_reports(): void
    {
        $patient = Patient::factory()->create(['name' => 'Nadia']);

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Nadia');
        $response->assertSee('Reports');
    }
}
