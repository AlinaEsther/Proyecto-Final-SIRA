<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'recommendation_id',
        'title',
        'author',
        'type_requested',
        'description',
        'url',
        'status',
        'reviewed_by',
        'admin_notes',
        'material_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Relaciones
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function recommendation()
    {
        return $this->belongsTo(Recommendation::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRecent($query)
    {
        return $query->latest('created_at');
    }
}
