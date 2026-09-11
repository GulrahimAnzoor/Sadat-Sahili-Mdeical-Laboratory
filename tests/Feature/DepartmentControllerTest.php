<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use App\Models\Department;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_tests_page_shows_add_department_controls(): void
    {
        $this->get(route('tests.index'))
            ->assertOk()
            ->assertSee('Add department')
            ->assertSee('New department');
    }

    public function test_valid_payload_creates_department_and_shows_it_in_the_filter(): void
    {
        $response = $this->from(route('tests.index'))
            ->post(route('departments.store'), [
                'name' => 'Hematology',
            ]);

        $response->assertRedirect(route('tests.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('departments', [
            'name' => 'Hematology',
            'slug' => 'hematology',
        ]);

        $this->get(route('tests.index'))
            ->assertOk()
            ->assertSee('Hematology');
    }

    public function test_store_rejects_a_missing_name(): void
    {
        $count = Department::query()->count();

        $response = $this->from(route('tests.index'))
            ->post(route('departments.store'), [
                'name' => '',
            ]);

        $response->assertRedirect(route('tests.index'));
        $response->assertSessionHasErrors('name');
        $this->assertSame($count, Department::query()->count());
    }

    public function test_store_rejects_a_duplicate_department_name(): void
    {
        $count = Department::query()->count();

        $this->from(route('tests.index'))
            ->post(route('departments.store'), [
                'name' => 'Routine',
            ])
            ->assertRedirect(route('tests.index'))
            ->assertSessionHasErrors('name');

        $this->assertSame($count, Department::query()->count());
    }

    public function test_new_department_can_be_assigned_to_a_test(): void
    {
        $this->post(route('departments.store'), [
            'name' => 'Hematology',
        ]);

        $response = $this->post(route('tests.store'), [
            'name' => 'CBC',
            'department' => 'hematology',
            'price' => '250.00',
            'normal_range' => 'See parameters',
        ]);

        $test = Test::query()->firstWhere('name', 'CBC');

        $this->assertNotNull($test);
        $this->assertSame('hematology', $test->department->value);
        $response->assertRedirect(route('tests.show', $test));

        $this->get(route('tests.show', $test))
            ->assertOk()
            ->assertSee('Hematology');
    }

    public function test_tests_page_escapes_department_names(): void
    {
        Department::factory()->create([
            'name' => '<script>alert(1)</script>',
            'slug' => 'xss-dept',
        ]);

        $this->get(route('tests.index'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_staff_without_tests_permission_cannot_create_departments(): void
    {
        $this->actingAs($this->staffUser([
            LabPermission::Dashboard->value,
        ]));

        $this->post(route('departments.store'), [
            'name' => 'Hematology',
        ])->assertForbidden();

        $this->assertDatabaseMissing('departments', ['name' => 'Hematology']);
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->post(route('logout'));

        $this->post(route('departments.store'), [
            'name' => 'Hematology',
        ])->assertRedirect(route('login'));
    }
}
