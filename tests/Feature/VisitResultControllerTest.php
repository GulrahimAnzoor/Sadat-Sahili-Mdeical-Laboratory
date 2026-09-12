<?php

namespace Tests\Feature;

use App\Enums\ReportLayout;
use App\Enums\TestDepartment;
use App\Mail\VisitReportMail;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\TestParameter;
use App\Models\TestResult;
use App\Models\TestResultValue;
use App\Models\Visit;
use App\Support\LabPanelTemplates;
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
            ->assertSee('report-top')
            ->assertSee('report-main')
            ->assertSee('report-footer')
            ->assertSee('report-watermark')
            ->assertSee('ssml-logo.png', false)
            ->assertSee('سعادت صالحي', false)
            ->assertSee('report-brand-accent-ps')
            ->assertSee('طبي', false)
            ->assertSee('لابراتوار', false)
            ->assertSee('0789462768')
            ->assertSeeInOrder([
                'Saadat Salihi',
                'Hassan',
                'CBC',
                '0789462768',
            ]);
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
            ->assertSee('Materials used')
            ->assertSee('Add from stock')
            ->assertDontSee('Share')
            ->assertDontSee('Handover')
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

    public function test_visit_report_prints_each_ordered_test_on_its_own_page(): void
    {
        $patient = Patient::factory()->create();
        $visit = Visit::factory()->for($patient)->create();
        $panel = Test::factory()->create(['name' => 'CBC Panel']);
        $children = [
            Test::factory()->create(['name' => 'WBC Count']),
            Test::factory()->create(['name' => 'RBC Count']),
            Test::factory()->create(['name' => 'Platelet Count']),
        ];

        PatientTest::factory()->for($visit)->for($patient)->for($panel)->create();

        foreach ($children as $child) {
            PatientTest::factory()->for($visit)->for($patient)->for($child)->create();
        }

        TestResult::factory()->for($patient)->for($panel)->create([
            'visit_id' => $visit->id,
            'result' => 'Complete',
        ]);

        $html = $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee('CBC Panel')
            ->assertSee('WBC Count')
            ->assertSee('RBC Count')
            ->assertSee('Platelet Count')
            ->assertSee('Complete')
            ->assertDontSee('Urine R/E')
            ->getContent();

        $this->assertSame(4, substr_count($html, 'class="report-page"'));
        $this->assertSame(4, substr_count($html, 'report-test-block'));
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

    public function test_urine_exam_entry_shows_grouped_template_rows(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create([
            'name' => 'Urine R/E',
            'report_layout' => ReportLayout::UrineExam,
            'interpretation' => LabPanelTemplates::urineSummary(),
        ]);

        foreach (LabPanelTemplates::urineParameters() as $parameter) {
            $test->parameters()->create($parameter);
        }

        $test->parameters()->create([
            'name' => 'Protein',
            'group_name' => LabPanelTemplates::Chemical,
            'sort_order' => 99,
        ]);

        $visit = Visit::factory()->for($patient)->create();
        PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        $this->get(route('visits.results.edit', $visit))
            ->assertOk()
            ->assertSee('PHYSICAL EXAMINATION')
            ->assertSee('CHEMICAL EXAMINATION')
            ->assertSee('MICROSCOPIC EXAMINATION')
            ->assertSee('Color')
            ->assertSee('Pus cells')
            ->assertSee('0-10 /HPF')
            ->assertSee('Nutritional status')
            ->assertSee('Add row')
            ->assertSee('Remove')
            ->assertDontSee('value="Protein"', false);
    }

    public function test_urine_exam_results_print_in_three_columns(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create([
            'name' => 'Urine R/E',
            'report_layout' => ReportLayout::UrineExam,
            'interpretation' => LabPanelTemplates::urineSummary(),
        ]);

        foreach (LabPanelTemplates::urineParameters() as $parameter) {
            $test->parameters()->create($parameter);
        }

        $visit = Visit::factory()->for($patient)->create();
        $patientTest = PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.results.store', $visit), [
                'results' => [
                    [
                        'patient_test_id' => $patientTest->id,
                        'extra_rows' => [
                            ['name' => 'Color', 'value' => 'Yellow', 'group_name' => LabPanelTemplates::Physical],
                            ['name' => 'Glucose', 'value' => 'Nil', 'group_name' => LabPanelTemplates::Chemical, 'normal_range' => 'Nil'],
                            ['name' => 'Pus cells', 'value' => '8-10', 'unit' => '/HPF', 'group_name' => LabPanelTemplates::Microscopic, 'normal_range' => '0-10 /HPF'],
                            ['name' => 'Casts', 'value' => 'Not seen', 'group_name' => LabPanelTemplates::Microscopic],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('visits.results.edit', [
                'visit' => $visit,
                'test' => $patientTest->id,
            ]));

        $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee('report-urine')
            ->assertSee('Urine Exam Report')
            ->assertSee('PHYSICAL EXAMINATION')
            ->assertSee('CHEMICAL EXAMINATION')
            ->assertSee('MICROSCOPIC EXAMINATION')
            ->assertSee('Yellow')
            ->assertSee('Nil')
            ->assertSee('8-10')
            ->assertSee('Casts')
            ->assertSee('Not seen')
            ->assertSee('report-summary')
            ->assertSee('Nutritional status')
            ->assertDontSee('report-torch');
    }

    public function test_torch_panel_prints_nested_igm_rows_and_summary(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create([
            'name' => 'TORCH Quantitative',
            'report_layout' => ReportLayout::TorchPanel,
            'interpretation' => LabPanelTemplates::torchSummary(),
        ]);

        foreach (LabPanelTemplates::torchQuantitativeParameters() as $parameter) {
            $test->parameters()->create($parameter);
        }

        $visit = Visit::factory()->for($patient)->create();
        $patientTest = PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        $this->from(route('visits.results.edit', $visit))
            ->get(route('visits.results.edit', $visit))
            ->assertOk()
            ->assertSee('Rubella')
            ->assertSee('TOXO')
            ->assertSee('IgM')
            ->assertSee('Au/ml')
            ->assertSee('Toxoplasma gondii');

        $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.results.store', $visit), [
                'results' => [
                    [
                        'patient_test_id' => $patientTest->id,
                        'extra_rows' => [
                            ['name' => 'Rubella', 'value' => '0.168', 'unit' => 'Au/ml', 'group_name' => 'IgM', 'normal_range' => "Non-Reactive: <2.0\nReactive: >2.6"],
                            ['name' => 'TOXO', 'value' => '0.213', 'unit' => 'miu/ml', 'group_name' => 'IgM'],
                            ['name' => 'CMV', 'value' => '0.754', 'unit' => 'Au/ml', 'group_name' => 'IgM'],
                            ['name' => 'HSV', 'value' => '0.323', 'unit' => 'Au/ml', 'group_name' => 'IgM'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee('report-torch')
            ->assertSee('TORCH Profile')
            ->assertSee('Rubella')
            ->assertSee('0.168')
            ->assertSee('IgM')
            ->assertSee('Non-Reactive')
            ->assertSee('Toxoplasma gondii')
            ->assertDontSee('report-urine')
            ->assertDontSee('Urine Exam Report');
    }

    public function test_cbc_panel_shows_editable_nested_names(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create([
            'name' => 'CBC',
            'report_layout' => ReportLayout::Panel,
            'interpretation' => LabPanelTemplates::cbcSummary(),
        ]);

        foreach (LabPanelTemplates::cbcParameters() as $parameter) {
            $test->parameters()->create($parameter);
        }

        $visit = Visit::factory()->for($patient)->create();
        $patientTest = PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        $this->get(route('visits.results.edit', $visit))
            ->assertOk()
            ->assertSee('HAEMATOLOGY')
            ->assertSee('DIFFERENTIAL COUNT')
            ->assertSee('name="results[0][extra_rows][0][name]"', false)
            ->assertSee('HB')
            ->assertSee('Neutrophils')
            ->assertSee('Remove');

        $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.results.store', $visit), [
                'results' => [
                    [
                        'patient_test_id' => $patientTest->id,
                        'extra_rows' => [
                            ['name' => 'HB', 'value' => '13.4', 'unit' => 'g/dL', 'group_name' => 'HAEMATOLOGY'],
                            ['name' => 'TLC', 'value' => '7.1', 'unit' => 'x10^3/µL', 'group_name' => 'HAEMATOLOGY'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee('CBC')
            ->assertSee('HB')
            ->assertSee('13.4')
            ->assertSee('TLC')
            ->assertSee('peripheral smear');
    }

    public function test_urine_culture_saves_editable_antibiotic_list(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create([
            'name' => 'Urine C/S',
            'report_layout' => ReportLayout::Culture,
            'interpretation' => LabPanelTemplates::cultureSummary(),
        ]);

        foreach (LabPanelTemplates::cultureParameters() as $parameter) {
            $test->parameters()->create($parameter);
        }

        $visit = Visit::factory()->for($patient)->create();
        $patientTest = PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        $this->get(route('visits.results.edit', $visit))
            ->assertOk()
            ->assertSee('Ampicillin')
            ->assertSee('Nitrofurantoin')
            ->assertSee('Antibiotic sensitivity')
            ->assertSee('Organism')
            ->assertDontSee('Culture method');

        $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.results.store', $visit), [
                'results' => [
                    [
                        'patient_test_id' => $patientTest->id,
                        'extra_rows' => [
                            ['name' => 'Growth', 'value' => 'Heavy growth', 'group_name' => 'CULTURE'],
                            ['name' => 'Organism', 'value' => 'E. coli', 'group_name' => 'CULTURE'],
                        ],
                        'sensitivities' => [
                            ['antibiotic' => 'Ciprofloxacin', 'sensitivity' => 'S'],
                            ['antibiotic' => 'Ampicillin', 'sensitivity' => 'R'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertDontSee('Culture report')
            ->assertSee('E. coli')
            ->assertSee('Ciprofloxacin')
            ->assertSee('Sensitive')
            ->assertSee('Ampicillin')
            ->assertSee('Resistant')
            ->assertSee('Heavy growth');
    }

    public function test_standard_tests_keep_the_four_column_report_table(): void
    {
        $patient = Patient::factory()->create(['name' => 'Hassan']);
        $test = Test::factory()->create(['name' => 'CBC']);
        $visit = Visit::factory()->for($patient)->create();
        PatientTest::factory()->for($visit)->for($patient)->for($test)->create();
        TestResult::factory()->for($patient)->for($test)->create([
            'visit_id' => $visit->id,
            'result' => '12.4',
            'unit' => 'g/dL',
        ]);

        $this->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee('CBC')
            ->assertSee('12.4')
            ->assertSee('Units')
            ->assertSee('Normal range')
            ->assertDontSee('report-urine')
            ->assertDontSee('report-torch')
            ->assertDontSee('Urine Exam Report');
    }
}
