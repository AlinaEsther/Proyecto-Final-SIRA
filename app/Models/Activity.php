<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    // Tipos de actividades:
    // - assignment: Asignaciones teóricas (tareas)
    // - practice: Prácticas/ejercicios técnicos
    // - project: Proyectos integradores
    // - exam: Exámenes/evaluaciones formales
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_PRACTICE = 'practice';
    public const TYPE_PROJECT = 'project';
    public const TYPE_EXAM = 'exam';

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

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class, 'period', 'code');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public static function getTypes()
    {
        return [
            self::TYPE_ASSIGNMENT => 'Asignación',
            self::TYPE_PRACTICE => 'Práctica',
            self::TYPE_PROJECT => 'Proyecto',
            self::TYPE_EXAM => 'Examen',
        ];
    }

    public function getTypeNameAttribute()
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }
}
