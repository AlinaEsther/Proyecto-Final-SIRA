<?php

namespace App\Observers;

use App\Models\Recommendation;
use Illuminate\Support\Facades\Cache;

class RecommendationObserver
{
    public function created(Recommendation $recommendation)
    {
        // Invalidar cache del profesor cuando se crea una recomendación
        if ($recommendation->section && $recommendation->section->professor_id) {
            Cache::forget('sections_professor_' . $recommendation->section->professor_id);
        }

        // Invalidar cache de admin
        Cache::forget('all_sections_admin');
    }

    public function updated(Recommendation $recommendation)
    {
        // Invalidar cache cuando se actualiza
        if ($recommendation->section && $recommendation->section->professor_id) {
            Cache::forget('sections_professor_' . $recommendation->section->professor_id);
        }
    }

    public function deleted(Recommendation $recommendation)
    {
        // Invalidar cache cuando se elimina
        if ($recommendation->section && $recommendation->section->professor_id) {
            Cache::forget('sections_professor_' . $recommendation->section->professor_id);
        }
        Cache::forget('all_sections_admin');
    }
}

