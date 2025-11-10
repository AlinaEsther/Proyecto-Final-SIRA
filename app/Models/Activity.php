<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'description',
        'type',
        'period',
        'max_points',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'max_points' => 'decimal:2',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }
}
