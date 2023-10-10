<?php

namespace Guava\Filament\NestedResources;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NestedResourcesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-nested-resources')
        ;
    }

    public function packageBooted(): void
    {
    }
}
