<?php

namespace Guava\Filament\NestedResources\Resources;

use Filament\Resources\Resource;
use function Guava\Filament\NestedResources\get_resource_route_parameter;
use Guava\Filament\NestedResources\Resources\Concerns\HasParentResource;
use Guava\Filament\NestedResources\Resources\Concerns\HasRelationship;

class NestedResource extends Resource
{
    use HasParentResource;
    use HasRelationship;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getRouteParameterNames(): array
    {
        $parameters = [];
        $resource = static::class;

        do {
            $resource = $resource::getParentResource();
            $parameters[] = get_resource_route_parameter($resource);
        } while (in_array(NestedResource::class, class_parents($resource)));

        return $parameters;
    }

    public static function getSlug(): string
    {
        $resource = static::getParentResource();
        $parameter = get_resource_route_parameter($resource);

        return $resource::getSlug().'/{'.$parameter.'?}/'.parent::getSlug();
    }
}
