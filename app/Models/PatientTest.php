<?php

namespace App\Models;

use App\Enums\VisitStatus;
use Database\Factories\PatientTestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

#[Fillable(['visit_id', 'patient_id', 'test_id', 'total_price', 'paid', 'token_code', 'queue_number', 'status'])]
class PatientTest extends Model
{
    /** @use HasFactory<PatientTestFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'registered',
        'paid' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'paid' => 'boolean',
            'status' => VisitStatus::class,
            'queue_number' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PatientTest $patientTest): void {
            if ($patientTest->status === null) {
                $patientTest->status = $patientTest->paid ? VisitStatus::Paid : VisitStatus::Registered;
            }

            if ($patientTest->paid && $patientTest->status === VisitStatus::Registered) {
                $patientTest->status = VisitStatus::Paid;
            }
        });

        static::created(function (PatientTest $patientTest): void {
            if ($patientTest->visit_id !== null || ($patientTest->token_code !== null && $patientTest->queue_number !== null)) {
                return;
            }

            $queueNumber = static::query()
                ->whereDate('created_at', $patientTest->created_at?->toDateString() ?? today())
                ->count();

            $patientTest->updateQuietly([
                'token_code' => $patientTest->token_code ?: 'SSML-'.now()->format('Ymd').'-'.str_pad((string) $patientTest->id, 4, '0', STR_PAD_LEFT),
                'queue_number' => $patientTest->queue_number ?: $queueNumber,
            ]);
        });
    }

    #[Scope]
    protected function unpaid(Builder $query): Builder
    {
        return $query->where('paid', false);
    }

    #[Scope]
    protected function awaitingResult(Builder $query): Builder
    {
        return $query->whereNotExists(function (QueryBuilder $subquery): void {
            $subquery->selectRaw('1')
                ->from('test_results')
                ->whereColumn('test_results.test_id', 'patient_tests.test_id')
                ->where(function (QueryBuilder $match): void {
                    $match->whereColumn('test_results.visit_id', 'patient_tests.visit_id')
                        ->orWhere(function (QueryBuilder $legacy): void {
                            $legacy->whereNull('test_results.visit_id')
                                ->whereColumn('test_results.patient_id', 'patient_tests.patient_id');
                        });
                });
        });
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
