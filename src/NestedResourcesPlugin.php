<?php

namespace Guava\FilamentNestedResources;

use Filament;
use Filament\Contracts\Plugin;

class NestedResourcesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'guava::filament-nested-resources';
    }

    public function register(Filament\Panel $panel): void
    {
    }

    public function boot(Filament\Panel $panel): void
    {
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
