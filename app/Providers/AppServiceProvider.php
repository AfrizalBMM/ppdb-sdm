<?php

namespace App\Providers;

use App\Http\Middleware\AksesPembayaran;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Livewire\Livewire;

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
        Paginator::useTailwind();

        // Keep public panitia gate active for subsequent Livewire update requests.
        Livewire::addPersistentMiddleware([
            AksesPembayaran::class,
        ]);
    }
}
