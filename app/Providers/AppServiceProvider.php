<?php

namespace App\Providers;

use App\Models\AcademicProgram;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Person;
use App\Models\Section;
use App\Models\SectionStudent;
use App\Models\User;
use App\Observers\CacheInvalidationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar el observer de invalidación de cache para modelos principales
        $observer = new CacheInvalidationObserver();

        Course::observe($observer);
        Section::observe($observer);
        AcademicProgram::observe($observer);
        User::observe($observer);
        Person::observe($observer);
        Activity::observe($observer);
        Material::observe($observer);
        SectionStudent::observe($observer);
        Grade::observe($observer);

        // Agregar más modelos según sea necesario
    }
}
