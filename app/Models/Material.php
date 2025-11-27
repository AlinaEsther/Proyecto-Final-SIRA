<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'title',
        'type',
        'file_path',
        'original_filename',
        'url',
        'description',
    ];

    public function courses()
    {
        return $this->morphedByMany(Course::class, 'materialable');
    }

    public function sections()
    {
        return $this->morphedByMany(Section::class, 'materialable')
            ->withPivot(['is_required', 'order'])
            ->orderByPivot('order');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }
}
