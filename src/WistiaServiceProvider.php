<?php

namespace Jeffersongoncalves\Wistia;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WistiaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-wistia')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
