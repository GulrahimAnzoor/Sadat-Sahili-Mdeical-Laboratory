<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use App\Models\Test;
use App\Models\TestParameter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestReportContentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_lists_tests_and_asks_to_select_one(): void
    {
        Test::factory()->create(['name' => 'TB (ICT)']);

        $this->get(route('settings.test-reports.edit'))
            ->assertOk()
            ->assertSee('TB (ICT)')
            ->assertSee('Choose a test from the list to add its reference range and summary.');
    }

    public function test_selected_test_shows_range_and_summary_fields(): void
    {
        $test = Test::factory()->create([
            'name' => 'B. Sugar (F)',
            'normal_range' => "Adult Fasting: 70-126 mg/dl\nChildren: 60-110 mg/dl",
            'interpretation' => 'Glucose is the major energy source.',
        ]);

        $this->get(route('settings.test-reports.edit', $test))
            ->assertOk()
            ->assertSee('B. Sugar (F)')
            ->assertSee('Adult Fasting: 70-126 mg/dl', false)
            ->assertSee('Glucose is the major energy source.')
            ->assertSee('Print preview')
            ->assertSee('Has summary');
    }

    public function test_saving_long_range_and_summary_updates_the_test(): void
    {
        $test = Test::factory()->create([
            'name' => 'B. Sugar (F)',
            'normal_range' => '70-100',
        ]);

        $range = "Serum/Plasma:\nAdult Fasting: 70-126 mg/dl\nChildren: 60-110 mg/dl\nNewborns: 40-60 mg/dl";
        $summary = trim(str_repeat('Oxidation of glucose in the blood provides energy. ', 20));

        $this->from(route('settings.test-reports.edit', $test))
            ->put(route('settings.test-reports.update', $test), [
                'normal_range' => $range,
                'interpretation' => $summary,
            ])
            ->assertRedirect(route('settings.test-reports.edit', $test));

        $test->refresh();

        $this->assertSame($range, $test->normal_range);
        $this->assertSame($summary, $test->interpretation);
        $this->assertGreaterThan(255, strlen($test->normal_range.$test->interpretation));
    }

    public function test_saving_updates_parameter_ranges(): void
    {
        $test = Test::factory()->create(['name' => 'TB (ICT)']);
        $igg = TestParameter::factory()->for($test)->create([
            'name' => 'IgG',
            'normal_range' => 'Negative',
            'sort_order' => 1,
        ]);
        $igm = TestParameter::factory()->for($test)->create([
            'name' => 'IGM',
            'normal_range' => 'Negative',
            'sort_order' => 2,
        ]);

        $this->put(route('settings.test-reports.update', $test), [
            'normal_range' => 'See parameters',
            'interpretation' => 'IgG indicates established infection.',
            'parameters' => [
                ['id' => $igg->id, 'normal_range' => 'Negative (-ive)'],
                ['id' => $igm->id, 'normal_range' => 'Negative (-ive)'],
            ],
        ])->assertRedirect(route('settings.test-reports.edit', $test));

        $this->assertSame('Negative (-ive)', $igg->fresh()->normal_range);
        $this->assertSame('Negative (-ive)', $igm->fresh()->normal_range);
        $this->assertSame('IgG indicates established infection.', $test->fresh()->interpretation);
    }

    public function test_update_rejects_a_parameter_from_another_test(): void
    {
        $test = Test::factory()->create();
        $other = TestParameter::factory()->create();

        $this->from(route('settings.test-reports.edit', $test))
            ->put(route('settings.test-reports.update', $test), [
                'normal_range' => '70-100',
                'parameters' => [
                    ['id' => $other->id, 'normal_range' => 'hacked'],
                ],
            ])
            ->assertRedirect(route('settings.test-reports.edit', $test))
            ->assertSessionHasErrors('parameters.0.id');

        $this->assertSame($other->normal_range, $other->fresh()->normal_range);
    }

    public function test_update_rejects_missing_range(): void
    {
        $test = Test::factory()->create();

        $this->from(route('settings.test-reports.edit', $test))
            ->put(route('settings.test-reports.update', $test), [
                'normal_range' => '',
            ])
            ->assertRedirect(route('settings.test-reports.edit', $test))
            ->assertSessionHasErrors(['normal_range']);
    }

    public function test_staff_without_tests_permission_cannot_open_report_content(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Settings->value,
        ]);

        $this->actingAs($user)
            ->get(route('settings.test-reports.edit'))
            ->assertForbidden();
    }
}
