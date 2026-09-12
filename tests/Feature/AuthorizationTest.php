<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use App\Models\Patient;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsManager = false;

    public function test_guests_cannot_open_staff_settings(): void
    {
        $this->get(route('settings.staff.index'))
            ->assertRedirect(route('login'));
    }

    public function test_reception_staff_can_open_reception_and_cannot_open_finance(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Reception->value,
        ]);

        $this->actingAs($user)
            ->get(route('reception.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('finance.index'))
            ->assertForbidden();
    }

    public function test_staff_without_staff_permission_cannot_create_roles(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
        ]);

        $this->actingAs($user)
            ->post(route('settings.roles.store'), [
                'name' => 'Night shift',
            ])
            ->assertForbidden();
    }

    public function test_sidebar_hides_links_the_role_cannot_open(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Reception->value,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Reception')
            ->assertDontSee('/finance')
            ->assertDontSee('/settings');
    }

    public function test_staff_without_delete_permission_cannot_delete_a_patient(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Patients->value,
        ]);
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->delete(route('patients.destroy', $patient))
            ->assertForbidden();

        $this->assertModelExists($patient);
    }

    public function test_staff_with_delete_permission_can_delete_a_patient(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Patients->value,
            LabPermission::Delete->value,
        ]);
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->delete(route('patients.destroy', $patient))
            ->assertRedirect(route('patients.index'));

        $this->assertModelMissing($patient);
    }

    public function test_staff_without_edit_permission_cannot_open_patient_edit(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Patients->value,
        ]);
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->get(route('patients.edit', $patient))
            ->assertForbidden();
    }

    public function test_staff_page_hides_delete_without_delete_ability(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Staff->value,
        ]);
        $this->actingAs($user)
            ->get(route('settings.staff.index'))
            ->assertOk()
            ->assertSee('Save permissions')
            ->assertDontSee('name="_method" value="DELETE"', false);
    }

    public function test_staff_can_save_role_permissions_without_edit_ability(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Staff->value,
        ]);
        $role = Role::factory()->create([
            'permissions' => [],
        ]);

        $this->actingAs($user)
            ->put(route('settings.roles.update', $role), [
                'permissions' => [
                    LabPermission::Dashboard->value,
                    LabPermission::Delete->value,
                ],
            ])
            ->assertRedirect(route('settings.staff.index'));

        $this->assertEqualsCanonicalizing([
            LabPermission::Dashboard->value,
            LabPermission::Delete->value,
        ], $role->fresh()->permissions);
    }

    public function test_staff_with_edit_permission_can_update_a_patient(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Patients->value,
            LabPermission::Edit->value,
        ]);
        $patient = Patient::factory()->create(['name' => 'Old Patient']);

        $this->actingAs($user)
            ->put(route('patients.update', $patient), [
                'name' => 'Updated Patient',
                'father_name' => $patient->father_name,
                'gender' => $patient->gender,
            ])
            ->assertRedirect(route('patients.show', $patient));

        $this->assertSame('Updated Patient', $patient->fresh()->name);
    }

    public function test_administrator_can_update_and_delete_an_unused_patient(): void
    {
        $this->actingAs($this->manager());
        $patient = Patient::factory()->create(['name' => 'Temporary']);

        $this->put(route('patients.update', $patient), [
            'name' => 'Renamed Patient',
            'father_name' => $patient->father_name,
            'gender' => $patient->gender,
        ])->assertRedirect(route('patients.show', $patient));

        $this->assertSame('Renamed Patient', $patient->fresh()->name);

        $this->delete(route('patients.destroy', $patient))
            ->assertRedirect(route('patients.index'));

        $this->assertModelMissing($patient);
    }
}
