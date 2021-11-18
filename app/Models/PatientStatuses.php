<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientStatuses extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'status_id', 'date_in', 'date_out'];

    function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    function status()
    {
        return $this->belongsTo(Status::class);
    }
}
