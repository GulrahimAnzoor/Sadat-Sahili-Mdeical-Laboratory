<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_page_renders_patient_and_assigned_tests(): void
    {
        $patient = Patient::factory()->create(['name' => 'Karim']);
        $test = Test::factory()->create(['name' => 'CBC']);
        PatientTest::factory()->for($patient)->for($test)->create([
            'total_price' => '150.00',
            'paid' => true,
        ]);

        $response = $this->followingRedirects()->get(route('tokens.show', $patient));

        $response->assertOk();
        $response->assertSee('Karim');
        $response->assertSee('CBC');
        $response->assertSee('150.00');
        $response->assertSee('Laboratory copy');
        $response->assertSee('Patient copy');
    }
}
