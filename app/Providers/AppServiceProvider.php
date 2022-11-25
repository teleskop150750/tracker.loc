<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UrlGenerator::class, function () {
            $urlGeneratorWithSignedRoutes = new UrlGenerator($this->app);
            $urlGeneratorWithSignedRoutes->setKeyResolver(function () {
                return $this->app->make('config')->get('app.key');
            });

            return $urlGeneratorWithSignedRoutes;
        });

        $this->registerRequestSignatureValidation();
    }

    /**
     * Register the "hasValidSignature" macro on the request.
     */
    public function registerRequestSignatureValidation(): void
    {
        $urlGeneratorWithSignedRoutes = app(UrlGenerator::class);
        Request::macro('hasValidSignature', function ($absolute = true) use ($urlGeneratorWithSignedRoutes) {
            return $urlGeneratorWithSignedRoutes->hasValidSignature($this, $absolute);
        });

        Request::macro('hasValidRelativeSignature', function () use ($urlGeneratorWithSignedRoutes) {
            return $urlGeneratorWithSignedRoutes->hasValidSignature($this, $absolute = false);
        });

        Request::macro('hasValidSignatureWhileIgnoring', function ($ignoreQuery = [], $absolute = true) use ($urlGeneratorWithSignedRoutes) {
            return $urlGeneratorWithSignedRoutes->hasValidSignature($this, $absolute, $ignoreQuery);
        });
    }
}
