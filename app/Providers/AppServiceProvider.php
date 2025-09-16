<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentUserRepository;
use App\Domain\Contracts\ReportRepositoryInterface;
use App\Infrastructure\Repositories\EloquentReportRepository;
use App\Models\ProjectFile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Gate;



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
        // Gate para archivos de proyecto
        Gate::define('view-project-file', function (User $user, ProjectFile $projectFile) {
            return $user->id === $projectFile->user_id;
        });

        // Gate para reportes
        Gate::define('view-report', function (User $user, Report $report) {
            return $user->id === $report->user_id;
        });
    }
}
