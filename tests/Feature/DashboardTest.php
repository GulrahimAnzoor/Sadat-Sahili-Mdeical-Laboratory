<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_lab_summary(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Ahmad Sadat']);
        Patient::factory()->for($doctor)->create(['name' => 'Hassan']);

        $response = $this->get(route('dashboard'));

        $response->assertSee('Dashboard');
        $response->assertSee('Welcome');
        $response->assertSee('New today: 1');
        $response->assertSee('Patients');
        $response->assertSee('visits-trend');
        $response->assertSee('workflow-chart');
        $response->assertSee('top-tests-chart');
        $response->assertSee('departments-chart');
        $response->assertSee('Enter results');
        $response->assertSee('Reports');
        $response->assertSee('Catalogue');
        $response->assertSee('/worklist');
        $response->assertSee('/reports');
        $response->assertSee('/tests');
        $response->assertDontSee('New patient');
        $response->assertDontSee('New visit');
        $response->assertDontSee('Worklist');
    }

    public function test_welcome_section_hides_lab_actions_without_permission(): void
    {
        $this->actingAs($this->staffUser([
            LabPermission::Dashboard->value,
        ]));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Enter results')
            ->assertDontSee('Catalogue')
            ->assertDontSee('Reports');
    }

    public function test_dashboard_lists_unpaid_tests_and_awaiting_results(): void
    {
        $patient = Patient::factory()->create(['name' => 'Zahra']);
        $test = Test::factory()->create(['name' => 'HbA1c']);
        PatientTest::factory()->for($patient)->for($test)->create([
            'paid' => false,
            'total_price' => '200.00',
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Zahra');
        $response->assertSee('HbA1c');
        $response->assertSee('Unpaid');
        $response->assertSee('Pending results');
        $response->assertSee('Tests');
        $response->assertSee('Settings');
    }

    public function test_dashboard_shows_lab_income_from_paid_reception_visit(): void
    {
        $patient = Patient::factory()->create(['name' => 'Omar']);
        $test = Test::factory()->create(['name' => 'CBC', 'price' => '150.00']);

        $this->post(route('reception.visits.store', $patient), [
            'test_ids' => [$test->id],
            'paid' => '1',
        ])->assertRedirect();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('150.00')
            ->assertSee('/finance?period=today');

        $this->get(route('finance.index', ['period' => 'today']))
            ->assertOk()
            ->assertSee('150.00')
            ->assertSee('Cash book')
            ->assertSee('Profit and loss');
    }

    public function test_switching_to_pashto_shows_translated_dashboard(): void
    {
        $this->from(route('dashboard'))
            ->get(route('locale.switch', 'ps'))
            ->assertRedirect();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ډشبورډ')
            ->assertSee('ښه راغلاست');
    }

    public function test_topbar_shows_language_theme_and_account_menu(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Lab Manager')
            ->assertSee('English')
            ->assertSee('Password & security')
            ->assertSee('Log out')
            ->assertSee('Theme')
            ->assertSee('System')
            ->assertSee('Notifications')
            ->assertSee('data-theme="system"', false);
    }

    public function test_layout_renders_sidebar_menu_without_extra_links(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertSee('Open menu');
        $response->assertSee('Front desk');
        $response->assertSee('Settings');
        $response->assertSee('/reception');
        $response->assertSee('/worklist');
        $response->assertSee('id="lab-progress"', false);
        $response->assertSee('lab-page', false);
        $response->assertSee('lab-topbar', false);
        $response->assertSee('/finance');
        $response->assertSee('/settings');
        $response->assertSee('Reception');
        $response->assertSee('Lab');
        $response->assertSee('Store');
        $response->assertSee('Suppliers');
        $response->assertSee('/suppliers');
        $response->assertSee('/purchases');
        $response->assertDontSee('Commerce');
    }

    public function test_welcome_path_redirects_to_dashboard(): void
    {
        $this->get('/welcome')->assertRedirect('/');
        $this->get('/wellcome')->assertRedirect('/');
    }

    public function test_misspelled_doctor_paths_redirect_to_doctors_index(): void
    {
        $this->get('/doctor')->assertRedirect('/doctors');
        $this->get('/doctore')->assertRedirect('/doctors');
        $this->get('/Doctor')->assertRedirect('/doctors');
    }
}
