<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
    'venue_id',
    'venue_title',
    'booking_date',
    'start_time',
    'end_time',
    'full_name',
    'email',
    'email_contact',
    'status',
    'payment_proof',
    'payment_requested_at',
];

protected $casts = [
        'payment_requested_at' => 'datetime',
        'booking_date' => 'date',
    ];
}