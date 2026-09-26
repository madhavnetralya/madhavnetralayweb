<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'logo',
        'favicon',
        'hospital_name',
        'tagline',
        'primary_color',
        'secondary_color',
        'address',
        'phone_numbers',
        'emails',
        'working_hours',
        'emergency_contacts',
        'google_map_embed_url',
        'whatsapp_number',
        'social_media',
        'google_analytics_id',
        'google_search_console_verification',
        'email_smtp',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'emails' => 'array',
        'working_hours' => 'array',
        'emergency_contacts' => 'array',
        'social_media' => 'array',
        'email_smtp' => 'array',
    ];
}
