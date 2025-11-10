<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'activity_id',
        'student_id',
        'points_earned',
        'feedback',
        'graded_at',
    ];

    protected $casts = [
        'points_earned' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function getPercentageAttribute()
    {
        if (!$this->activity) return 0;
        return ($this->points_earned / $this->activity->max_points) * 100;
    }
}
