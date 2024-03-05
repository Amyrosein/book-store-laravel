<?php

namespace App\Providers;

use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
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
        BookResource::withoutWrapping();
    }
}
