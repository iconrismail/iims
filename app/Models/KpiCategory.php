<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
