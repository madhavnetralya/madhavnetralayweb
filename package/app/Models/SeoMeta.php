<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    use HasFactory;

    protected $table = 'seo_meta';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'page_key',
        'title',
        'description',
        'keywords',
        'canonical_url',
        'og_type',
        'og_image',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];
}
