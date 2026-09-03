<?php

namespace Tests\Feature;

use App\Enums\TestDepartment;
use App\Mail\VisitReportMail;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\TestParameter;
use App\Models\TestResultValue;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VisitResultControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_parameter_results_fills_visit_report(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create([
            'name' => 'CBC',
            'interpretation' => 'Correlate with clinical findings and peripheral smear.',
        ]);
        $wbc = TestParameter::factory()->for($test)->create([
            'name' => 'WBC',
            'unit' => 'x10^3/µL',
            'normal_range' => '4.0-11.0',
            'sort_order' => 1,
        ]);
        $visit = Visit::factory()->for($patient)->create();
        $patientTest = PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        $response = $this->post(route('visits.results.store', $visit), [
            'results' => [
                [
                    'patient_test_id' => $patientTest->id,
                    'values' => [
                        ['test_parameter_id' => $wbc->id, 'value' => '7.2'],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('visits.results.edit', [
            'visit' => $visit,
            'test' => $patientTest->id,
        ]));
        $this->assertSame(1, TestResultValue::query()->count());
        $this->assertSame('7.2', TestResultValue::query()->value('value'));

        $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee('Hassan')
            ->assertSee('CBC')
            ->assertSee('WBC')
            ->assertSee('7.2')
            ->assertSee('Normal range')
            ->assertSee('Units')
            ->assertSee('Summary')
            ->assertSee('report-summary')
            ->assertSee('Correlate with clinical findings and peripheral smear.')
            ->assertSee('report-page')
            ->assertSee('report-print-chrome')
            ->assertSee('report-watermark')
            ->assertSee('ssml-logo.png', false)
            ->assertSee('سعادت صالحي طبي', false)
            ->assertSee('لابراتوار', false)
            ->assertSee('0789462768');
    }

    public function test_results_page_lists_the_visit_tests_and_parameter_fields(): void
    {
        $patient = Patient::factory()->create([
            'name' => 'Hassan',
            'file_number' => 'P-00088',
        ]);
        $test = Test::factory()->create(['name' => 'CBC']);
        TestParameter::factory()->for($test)->create([
            'name' => 'WBC',
            'unit' => 'x10^3/µL',
            'normal_range' => '4.0-11.0',
        ]);
        $visit = Visit::factory()->for($patient)->create();
        PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        $this->get(route('visits.results.edit', $visit))
            ->assertOk()
            ->assertSee('Hassan')
            ->assertSee('P-00088')
            ->assertSee('CBC')
            ->assertSee('WBC')
            ->assertSee('4.0-11.0')
            ->assertSee('x10^3/µL')
            ->assertSee('Save result')
            ->assertSee('Add row')
            ->assertSee('Units')
            ->assertSee('Normal range');
    }

    public function test_thyroid_style_rows_print_without_a_parent_result_row(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create([
            'name' => 'Thyroid Studies',
            'department' => TestDepartment::SpecialChemistry,
        ]);
        $visit = Visit::factory()->for($patient)->create();
        $patientTest = PatientTest::factory()->for($visit)->for($patient)->for($test)->create();
        $tshRange = "Thyroid.... 0.3-----4.2 mIU /L\nHyperthyroid.... <0.3 mIU /L\nHypothyroid.... > 4.2 mIU /L";

        $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.results.store', $visit), [
                'results' => [
                    [
                        'patient_test_id' => $patientTest->id,
                        'extra_rows' => [
                            ['name' => 'T3', 'value' => '1.8', 'unit' => 'nmol/L', 'normal_range' => '1.23----3.07'],
                            ['name' => 'T4', 'value' => '121', 'unit' => 'nmol/L', 'normal_range' => '66----181'],
                            ['name' => 'TSH', 'value' => '3.3', 'unit' => 'mIU /L', 'normal_range' => $tshRange],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('visits.results.edit', [
                'visit' => $visit,
                'test' => $patientTest->id,
            ]));

        $this->assertSame(3, TestParameter::query()->where('test_id', $test->id)->count());
        $this->assertSame($tshRange, TestParameter::query()->where('test_id', $test->id)->where('name', 'TSH')->value('normal_range'));

        $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee('Special chemistry Thyroid Studies')
            ->assertSee('T3')
            ->assertSee('1.8')
            ->assertSee('nmol/L')
            ->assertSee('1.23----3.07')
            ->assertSee('T4')
            ->assertSee('121')
            ->assertSee('TSH')
            ->assertSee('3.3')
            ->assertSee('Hyperthyroid')
            ->assertSee('Hypothyroid');
    }

    public function test_extra_result_rows_print_on_the_visit_report(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create(['name' => 'ABG']);
        $visit = Visit::factory()->for($patient)->create();
        $patientTest = PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.results.store', $visit), [
                'results' => [
                    [
                        'patient_test_id' => $patientTest->id,
                        'result' => '32',
                        'unit' => 'mmHg',
                        'extra_rows' => [
                            ['name' => 'pH', 'value' => '7.4', 'unit' => '', 'normal_range' => '7.35-7.45'],
                            ['name' => 'pCO2', 'value' => '40', 'unit' => 'mmHg', 'normal_range' => '35-45'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('visits.results.edit', [
                'visit' => $visit,
                'test' => $patientTest->id,
            ]));

        $this->assertSame(3, TestParameter::query()->where('test_id', $test->id)->count());
        $this->assertTrue(TestParameter::query()->where('test_id', $test->id)->where('name', 'pH')->exists());
        $this->assertTrue(TestParameter::query()->where('test_id', $test->id)->where('name', 'pCO2')->exists());

        $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee('pH')
            ->assertSee('7.4')
            ->assertSee('pCO2')
            ->assertSee('40')
            ->assertSee('7.35-7.45')
            ->assertSee('35-45');
    }

    public function test_emailing_report_sends_mailable(): void
    {
        Mail::fake();

        $patient = Patient::factory()->create();
        $visit = Visit::factory()->for($patient)->create();
        PatientTest::factory()->for($visit)->for($patient)->create();

        $response = $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.email', $visit), [
                'email' => 'clinic@example.com',
            ]);

        $response->assertRedirect(route('visits.results.edit', $visit));
        Mail::assertSent(VisitReportMail::class, function (VisitReportMail $mail): bool {
            return $mail->hasTo('clinic@example.com');
        });
    }

    public function test_whatsapp_redirects_when_patient_has_phone(): void
    {
        $patient = Patient::factory()->create(['phone' => '0701234567']);
        $visit = Visit::factory()->for($patient)->create();

        $response = $this->get(route('visits.whatsapp', $visit));

        $response->assertRedirect();
        $this->assertStringContainsString('https://wa.me/93701234567', (string) $response->headers->get('Location'));
    }
}
