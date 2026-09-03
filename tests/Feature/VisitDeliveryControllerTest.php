<?php

namespace Tests\Feature;

use App\Enums\VisitStatus;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitDeliveryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_handover_marks_visit_delivered_and_opens_print_page(): void
    {
        $patient = Patient::factory()->create(['name' => 'Nadia']);
        $visit = Visit::factory()->for($patient)->create([
            'status' => VisitStatus::Completed,
        ]);
        PatientTest::factory()->for($visit)->for($patient)->create();

        $response = $this->post(route('visits.deliver', $visit), [
            'delivered_to' => 'Karim',
            'delivery_box' => 'Fridge 2',
        ]);

        $response->assertRedirect(route('visits.handover', $visit));
        $visit->refresh();
        $this->assertSame(VisitStatus::Delivered, $visit->status);
        $this->assertSame('Karim', $visit->delivered_to);
        $this->assertSame('Fridge 2', $visit->delivery_box);
        $this->assertNotNull($visit->delivered_at);

        $this->get(route('visits.handover', $visit))
            ->assertOk()
            ->assertSee('Karim')
            ->assertSee('Fridge 2')
            ->assertSee('Nadia');
    }

    public function test_handover_rejects_missing_recipient_name(): void
    {
        $visit = Visit::factory()->create();

        $response = $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.deliver', $visit), [
                'delivery_box' => 'Box A',
            ]);

        $response->assertRedirect(route('visits.results.edit', $visit));
        $response->assertSessionHasErrors(['delivered_to']);
        $this->assertNotSame(VisitStatus::Delivered, $visit->fresh()->status);
    }
}
