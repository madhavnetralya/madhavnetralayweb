<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'description',
        'date',
        'time',
        'location',
        'image',
        'status',
        'gallery',
        'registrations_count',
    ];

    protected $casts = [
        'gallery' => 'array',
        'registrations_count' => 'integer',
        'date' => 'date',
    ];

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'id');
    }
}
