<?php

namespace App\Providers;

use App\Contracts\Services\ImportServiceInterface;
use App\Services\InmuebleImportService;
use App\Services\Validation\FileValidationService;
use App\Services\Security\RateLimitService;
use App\Services\Logging\ImportLogService;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(ImportServiceInterface::class, InmuebleImportService::class);
        
        // Bind services as singletons for better performance
        $this->app->singleton(FileValidationService::class);
        $this->app->singleton(RateLimitService::class);
        $this->app->singleton(ImportLogService::class);
        
        // Register import service with dependencies
        $this->app->when(InmuebleImportService::class)
            ->needs('$config')
            ->give(function () {
                return config('import');
            });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/import.php' => config_path('import.php'),
            ], 'import-config');
        }

        // Register commands if needed
        $this->commands([
            // \App\Console\Commands\ImportCommand::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ImportServiceInterface::class,
            InmuebleImportService::class,
            FileValidationService::class,
            RateLimitService::class,
            ImportLogService::class,
        ];
    }
} 