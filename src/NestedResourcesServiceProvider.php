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
            ->hasTranslations()
        ;
    }

    public function packageBooted(): void
    {
//        FilamentAsset::register([
//            AlpineComponent::make('tutorial', __DIR__.'/../resources/js/dist/components/tutorial.js'),
//            AlpineComponent::make('step', __DIR__.'/../resources/js/dist/components/step.js'),
//        ], package: 'guava/tutorials');
    }
}
