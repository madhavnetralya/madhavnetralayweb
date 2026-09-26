<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'patient_name',
        'patient_age',
        'patient_gender',
        'patient_phone',
        'patient_email',
        'department_id',
        'doctor_id',
        'date',
        'time_slot',
        'reason',
        'status',
        'created_at',
    ];

    protected $casts = [
        'patient_age' => 'integer',
        'date' => 'date',
        'created_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
}
