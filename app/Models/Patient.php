<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'species',
        'date_of_birth',
        'owner_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // Relasi many-to-one dengan Owner
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    // Relasi one-to-many dengan Treatment
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }
}