<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnostic extends Model
{
    use HasFactory;

    protected $table = 'diagnostics';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'indications',
        'procedure',
        'benefits',
        'image',
    ];

    protected $casts = [
        'indications' => 'array',
        'benefits' => 'array',
    ];
}
