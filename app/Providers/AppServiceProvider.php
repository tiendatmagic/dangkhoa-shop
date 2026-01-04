<?php

namespace App\Providers;

use App\Services\EccGmpMathAdapter;
use Illuminate\Support\ServiceProvider;
use Mdanter\Ecc\Math\MathAdapterFactory;

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
        // Force a safer math adapter for ECDSA signing to avoid
        // ValueError: base and exponent overflow in some PHP GMP builds.
        if (extension_loaded('gmp') && class_exists(MathAdapterFactory::class)) {
            MathAdapterFactory::forceAdapter(new EccGmpMathAdapter());
        }
    }
}
