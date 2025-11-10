<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $fillable = [
        'student_id',
        'section_id',
        'material_id',
        'relevance_score',
        'reason',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
