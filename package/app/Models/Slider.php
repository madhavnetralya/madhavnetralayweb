<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $table = 'sliders';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'subtitle',
        'background_image',
        'cta_text',
        'cta_link',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];
}
