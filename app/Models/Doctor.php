<?php

namespace App\Models;

use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Doctor extends Model
{
    /** @use HasFactory<DoctorFactory> */
    use HasFactory;

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
}
