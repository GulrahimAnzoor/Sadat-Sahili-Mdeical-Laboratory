<?php

namespace Tests;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected bool $authenticateAsManager = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        if ($this->authenticateAsManager && in_array(RefreshDatabase::class, class_uses_recursive($this), true)) {
            $this->actingAs($this->manager());
        }
    }

    protected function manager(): User
    {
        return User::factory()->manager()->create([
            'name' => 'Lab Manager',
            'email' => 'manager@ssml.test',
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function staffUser(array $permissions): User
    {
        $role = Role::factory()->create([
            'permissions' => $permissions,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        Staff::factory()->for($role)->create([
            'user_id' => $user->id,
            'name' => $user->name,
        ]);

        return $user->fresh(['staff.role']);
    }
}
