<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Observer para invalidar cache automáticamente cuando se crean/actualizan/eliminan datos
 * Se aplica a modelos que son cacheados en los controladores
 */
class CacheInvalidationObserver
{
    /**
     * Mapa de modelos a claves de cache que deben invalidarse
     */
    protected array $cacheKeys = [
        // Academic entities
        'Course' => ['courses'],
        'Section' => ['sections'],
        'AcademicProgram' => ['academic_programs'],
        'Activity' => ['activities'],
        'Material' => ['materials'],

        // User entities
        'User' => ['users', 'professors', 'students'],
        'Person' => ['users', 'professors', 'students'],

        // Student enrollment
        'SectionStudent' => ['sections'],

        // Grades
        'Grade' => ['sections'],

        // Agregar más según sea necesario
    ];

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the Model "restored" event (para soft deletes).
     */
    public function restored(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Invalida las claves de cache asociadas al modelo
     */
    protected function invalidateCache(Model $model): void
    {
        $modelName = class_basename($model);

        if (isset($this->cacheKeys[$modelName])) {
            foreach ($this->cacheKeys[$modelName] as $cacheKey) {
                Cache::forget($cacheKey);
            }

            Log::info("Cache invalidated for {$modelName}", [
                'keys' => $this->cacheKeys[$modelName],
                'model_id' => $model->id ?? 'N/A'
            ]);
        }
    }
}
