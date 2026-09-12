<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use App\Models\Account;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_page_lists_default_roles(): void
    {
        $response = $this->get(route('settings.staff.index'));

        $response->assertOk();
        $response->assertSee('Reception');
        $response->assertSee('Laboratory');
        $response->assertSee('Finance');
        $response->assertSee('Manager');
        $response->assertSee('Automatic login');
    }

    public function test_storing_a_role_and_staff_member_assigns_a_cash_account(): void
    {
        $account = Account::factory()->create(['name' => 'Petty cash']);

        $this->post(route('settings.roles.store'), [
            'name' => 'Phlebotomy',
            'description' => 'Sample collection',
        ])->assertRedirect(route('settings.staff.index'));

        $role = Role::query()->firstWhere('name', 'Phlebotomy');

        $this->assertNotNull($role);
        $this->assertSame([], $role->permissions);

        $response = $this->post(route('settings.staff.store'), [
            'name' => 'Farid',
            'phone' => '0701112233',
            'role_id' => $role->id,
            'account_id' => $account->id,
        ]);

        $response->assertRedirect(route('settings.staff.index'));
        $response->assertSessionHas('generated_login.email', 'farid@ssml.af');
        $response->assertSessionHas('generated_login.password');

        $this->get(route('settings.staff.index'))
            ->assertOk()
            ->assertSee('farid@ssml.af')
            ->assertSee('Login created. Copy these details now.');

        $staff = Staff::query()->with('user')->firstWhere('name', 'Farid');

        $this->assertNotNull($staff);
        $this->assertSame($role->id, $staff->role_id);
        $this->assertSame($account->id, $staff->account_id);
        $this->assertSame('farid@ssml.af', $staff->user?->email);
    }

    public function test_storing_a_role_saves_selected_permissions(): void
    {
        $this->post(route('settings.roles.store'), [
            'name' => 'Night shift',
            'permissions' => [
                LabPermission::Dashboard->value,
                LabPermission::Lab->value,
            ],
        ])->assertRedirect(route('settings.staff.index'));

        $role = Role::query()->firstWhere('name', 'Night shift');

        $this->assertNotNull($role);
        $this->assertSame([
            LabPermission::Dashboard->value,
            LabPermission::Lab->value,
        ], $role->permissions);
    }

    public function test_created_staff_can_sign_in_with_the_generated_email_and_password(): void
    {
        $role = Role::query()->firstWhere('slug', 'reception');

        $this->post(route('settings.staff.store'), [
            'name' => 'Laila',
            'role_id' => $role->id,
        ])->assertRedirect(route('settings.staff.index'));

        $email = session('generated_login.email');
        $password = session('generated_login.password');

        $this->assertSame('laila@ssml.af', $email);
        $this->assertNotEmpty($password);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $email,
                'password' => $password,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertSame('laila@ssml.af', auth()->user()?->email);
    }

    public function test_storing_staff_rejects_a_missing_name(): void
    {
        $response = $this->from(route('settings.staff.index'))
            ->post(route('settings.staff.store'), [
                'phone' => '0700000000',
            ]);

        $response->assertRedirect(route('settings.staff.index'));
        $response->assertSessionHasErrors(['name']);
        $this->assertSame(0, Staff::query()->count());
    }

    public function test_storing_staff_rejects_a_missing_role(): void
    {
        $this->from(route('settings.staff.index'))
            ->post(route('settings.staff.store'), [
                'name' => 'Farid',
            ])
            ->assertRedirect(route('settings.staff.index'))
            ->assertSessionHasErrors(['role_id']);

        $this->assertSame(0, Staff::query()->count());
    }

    public function test_second_staff_member_with_the_same_name_gets_a_unique_email(): void
    {
        $role = Role::query()->firstWhere('slug', 'reception');

        $this->post(route('settings.staff.store'), [
            'name' => 'Farid',
            'role_id' => $role->id,
        ])->assertRedirect(route('settings.staff.index'));

        $this->post(route('settings.staff.store'), [
            'name' => 'Farid',
            'role_id' => $role->id,
        ])->assertRedirect(route('settings.staff.index'));

        $this->assertTrue(User::query()->where('email', 'farid@ssml.af')->exists());
        $this->assertTrue(User::query()->where('email', 'farid1@ssml.af')->exists());
    }

    public function test_destroying_staff_removes_the_record(): void
    {
        $staff = Staff::factory()->create(['name' => 'Temporary']);

        $this->delete(route('settings.staff.destroy', $staff))
            ->assertRedirect(route('settings.staff.index'));

        $this->assertModelMissing($staff);
    }

    public function test_updating_staff_changes_name_and_role(): void
    {
        $role = Role::query()->firstWhere('slug', 'laboratory');
        $staff = Staff::factory()->create(['name' => 'Old Name']);

        $this->put(route('settings.staff.update', $staff), [
            'name' => 'New Name',
            'phone' => '0700000001',
            'role_id' => $role->id,
        ])->assertRedirect(route('settings.staff.index'));

        $staff->refresh();

        $this->assertSame('New Name', $staff->name);
        $this->assertSame($role->id, $staff->role_id);
        $this->assertSame('0700000001', $staff->phone);
    }

    public function test_staff_without_edit_cannot_update_a_member(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Staff->value,
        ]);
        $staff = Staff::factory()->create(['name' => 'Locked']);

        $this->actingAs($user)
            ->put(route('settings.staff.update', $staff), [
                'name' => 'Changed',
                'role_id' => $staff->role_id,
            ])
            ->assertForbidden();

        $this->assertSame('Locked', $staff->fresh()->name);
    }

    public function test_user_cannot_delete_their_own_staff_record(): void
    {
        $actor = User::query()->firstWhere('email', 'manager@ssml.test');
        $staff = Staff::factory()->create([
            'user_id' => $actor->id,
            'name' => $actor->name,
        ]);

        $this->from(route('settings.staff.index'))
            ->delete(route('settings.staff.destroy', $staff))
            ->assertRedirect(route('settings.staff.index'))
            ->assertSessionHasErrors('staff');

        $this->assertModelExists($staff);
    }
}
