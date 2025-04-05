<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'notes',
        'price',
        'treatment_date',
        'patient_id',
    ];

    protected $casts = [
        'treatment_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    // Relasi many-to-one dengan Patient
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}