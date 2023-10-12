<?php

namespace Guava\Filament\NestedResources;

use Filament\Resources\Resource;
use function Filament\Support\get_model_label;
use Illuminate\Database\Eloquent\Model;

if (! function_exists('Guava\Filament\NestedResources\get_model_route_parameter')) {
    function get_model_route_parameter(string | Model $model): string
    {
        return get_model_label(is_string($model) ? $model : $model::class) . 'Record';
    }
}

if (! function_exists('Guava\Filament\NestedResources\get_resource_route_parameter')) {
    function get_resource_route_parameter(string | Resource $resource): string
    {
        return get_model_route_parameter($resource::getModel());
    }
}
