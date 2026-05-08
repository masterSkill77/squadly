<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Laravel\Sentinel\Drivers\Driver;
use Laravel\Sentinel\Sentinel;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        Sentinel::extend('horizon', fn () => new class(fn () => app()) extends Driver {
            public function authorize(Request $request): bool
            {
                return true;
            }
        });

        parent::boot();
    }

    protected function authorization(): void
    {
        Horizon::auth(fn () => true);
    }

    protected function gate(): void {}
}
