<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use App\Observers\RecommendationObserver;
// use Illuminate\Database\Eloquent\Attributes\ObservedBy;

// #[ObservedBy([RecommendationObserver::class])]
class Recommendation extends Model
{
    protected $fillable = [
        'student_id',
        'section_id',
        'material_id',
        'generated_by',
        'course_name',
        'book_title',
        'book_author',
        'relevance_score',
        'average_grade',
        'reason',
        'professor_notes',
        'activities_data',
        'status',
    ];

    protected $casts = [
        'activities_data' => 'array',
        'average_grade' => 'decimal:2',
        'relevance_score' => 'integer',
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

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRecent($query)
    {
        return $query->latest('created_at');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

