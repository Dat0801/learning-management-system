<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Interfaces\CourseRepositoryInterface::class,
            \App\Repositories\CourseRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\EnrollmentRepositoryInterface::class,
            \App\Repositories\EnrollmentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
