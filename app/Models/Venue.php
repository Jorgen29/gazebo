<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'capacity',
        'price_per_hour',
        'image',
        'showcase_images',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price_per_hour'  => 'decimal:2',
        'capacity'        => 'integer',
        'is_active'       => 'boolean',
        'showcase_images' => 'array',
        'features'        => 'array',
    ];
}
