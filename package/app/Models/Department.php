<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'overview',
        'symptoms',
        'diagnosis',
        'treatments',
        'technology',
        'faqs',
        'banner_image',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'symptoms' => 'array',
        'diagnosis' => 'array',
        'treatments' => 'array',
        'technology' => 'array',
        'faqs' => 'array',
    ];

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'department_id', 'id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'department_id', 'id');
    }
}
