<?php

namespace App\Providers;

use App\Interfaces\FileBatchServiceInterface;
use App\Interfaces\FileItemServiceInterface;
use App\Interfaces\FileNamingServiceInterface;
use App\Interfaces\OrderServiceInterface;
use App\Interfaces\RoleServiceInterface;
use App\Interfaces\UserServiceInterface;
use App\Services\FileBatchService;
use App\Services\FileItemService;
use App\Services\FileNamingService;
use App\Services\OrderService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(FileItemServiceInterface::class, FileItemService::class);

        // Register our new services
        $this->app->bind(FileNamingServiceInterface::class, FileNamingService::class);
        $this->app->bind(FileBatchServiceInterface::class, FileBatchService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
