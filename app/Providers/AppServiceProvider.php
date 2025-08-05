<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentUserRepository;
use App\Domain\Contracts\ReportRepositoryInterface;
use App\Infrastructure\Repositories\EloquentReportRepository;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->bind(
            ReportRepositoryInterface::class,
            EloquentReportRepository::class
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
