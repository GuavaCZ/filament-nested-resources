<?php

namespace Guava\Filament\NestedResources;

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

//        $panel->renderHook(
//            'panels::user-menu.after',
//            fn (): string | View => request()->route()->controller instanceof HasTutorials
//                ? view('tutorials::components.help', [
//                    'livewire' => request()->route()->controller,
//                ])
//                : '',
//        );
//        Livewire::component('tutorials::tutorial-container', TutorialContainer::class);
//        Livewire::component('tutorials::step-container', StepContainer::class);

//        $panel->renderHook(
//            'panels::page.end',
//            fn (): View => view('tutorials::render-hook', [
//                'livewire' => request()->route()->controller instanceof HasTutorials
//                    ? request()->route()->controller
//                    : null,
//            ]),
//            //            fn (): string => Blade::renderComponent(new Tutorials()),
//        );
    }

    public function boot(Filament\Panel $panel): void
    {
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
