<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_page_shows_paid_income(): void
    {
        $patient = Patient::factory()->create();
        $test = Test::factory()->create();
        PatientTest::factory()->for($patient)->for($test)->create([
            'paid' => true,
            'total_price' => '200.00',
        ]);

        $response = $this->get(route('finance.index'));

        $response->assertOk();
        $response->assertSee('200.00');
        $response->assertSee('Finance');
        $response->assertSee('Opening');
        $response->assertSee('Closing');
    }

    public function test_finance_export_downloads_csv(): void
    {
        $response = $this->get(route('finance.export', ['period' => 'month']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Lab income', $response->streamedContent());
    }
}
