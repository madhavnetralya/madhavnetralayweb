<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $table = 'testimonials';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'patient_name',
        'age',
        'treatment',
        'rating',
        'comment',
        'photo',
        'video_url',
        'approved',
        'created_at',
    ];

    protected $casts = [
        'age' => 'integer',
        'rating' => 'integer',
        'approved' => 'boolean',
        'created_at' => 'datetime',
    ];
}
