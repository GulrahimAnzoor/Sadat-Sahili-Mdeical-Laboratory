<?php

namespace App\Models;

use App\Enums\VisitStatus;
use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'patient_id',
    'doctor_id',
    'is_self_request',
    'subtotal',
    'discount_percent',
    'discount_amount',
    'total',
    'paid_amount',
    'paid',
    'queue_number',
    'token_code',
    'status',
    'delivered_to',
    'delivery_box',
    'delivered_at',
])]
class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'registered',
        'paid' => false,
        'is_self_request' => false,
        'subtotal' => 0,
        'discount_percent' => 0,
        'discount_amount' => 0,
        'total' => 0,
        'paid_amount' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_self_request' => 'boolean',
            'subtotal' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid' => 'boolean',
            'queue_number' => 'integer',
            'status' => VisitStatus::class,
            'delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Visit $visit): void {
            if ($visit->status === null) {
                $visit->status = $visit->paid ? VisitStatus::Paid : VisitStatus::Registered;
            }

            if ($visit->paid && $visit->status === VisitStatus::Registered) {
                $visit->status = VisitStatus::Paid;
            }

            $visit->is_self_request = $visit->doctor_id === null;
        });

        static::created(function (Visit $visit): void {
            if ($visit->token_code !== null && $visit->queue_number !== null) {
                return;
            }

            $queueNumber = static::query()
                ->whereDate('created_at', $visit->created_at?->toDateString() ?? today()->toDateString())
                ->count();

            $visit->updateQuietly([
                'token_code' => $visit->token_code ?: 'SSML-'.now()->format('Ymd').'-'.str_pad((string) $visit->id, 4, '0', STR_PAD_LEFT),
                'queue_number' => $visit->queue_number ?: $queueNumber,
            ]);
        });
    }

    #[Scope]
    protected function today(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    #[Scope]
    protected function unpaid(Builder $query): Builder
    {
        return $query->where('paid', false);
    }

    #[Scope]
    protected function awaitingResult(Builder $query): Builder
    {
        return $query
            ->whereNot('status', VisitStatus::Delivered)
            ->whereHas('patientTests', function (Builder $tests): void {
                $tests->awaitingResult();
            });
    }

    public function remainingAmount(): float
    {
        return max(0, round((float) $this->total - (float) $this->paid_amount, 2));
    }

    public function referrerLabel(): string
    {
        if ($this->is_self_request || $this->doctor_id === null) {
            return __('Self request');
        }

        return $this->doctor?->name ?? __('Self request');
    }

    public function isComplete(): bool
    {
        if ($this->patientTests->isEmpty()) {
            return false;
        }

        $resultKeys = $this->testResults
            ->map(fn (TestResult $result): string => $result->patient_id.'-'.$result->test_id)
            ->unique();

        return $this->patientTests->every(
            fn (PatientTest $patientTest): bool => $resultKeys->contains($patientTest->patient_id.'-'.$patientTest->test_id),
        );
    }

    public function whatsappUrl(?string $message = null): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->patient?->phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '93'.substr($digits, 1);
        }

        $text = $message ?? __('Lab report for :name — :url', [
            'name' => $this->patient?->name ?? '',
            'url' => route('visits.report', $this),
        ]);

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($text);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patientTests(): HasMany
    {
        return $this->hasMany(PatientTest::class);
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function cashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }
}
