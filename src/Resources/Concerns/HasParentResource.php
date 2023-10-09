<?php

namespace Guava\Filament\NestedResources\Resources\Concerns;

use Filament\Resources\Resource;

trait HasParentResource
{
    protected static null | string | Resource $parentResource = null;

    //    public function setParentResource(string | Resource $parentResource)
    //    {
    //        $this->parentResource = $parentResource;
    //    }

    public static function getParentResource(): string | Resource | null
    {
        if ($resource = static::$parentResource) {
            return $resource;
        }

        throw new \InvalidArgumentException('Parent resource needs to be set on nested resource: ' . static::class);
    }
}
