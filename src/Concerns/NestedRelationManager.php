<?php

namespace Guava\FilamentNestedResources\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Guava\FilamentNestedResources\Actions\NestedEditAction;
use Guava\FilamentNestedResources\Actions\NestedViewAction;
use Guava\FilamentNestedResources\Actions\NestedCreateAction;

/**
 * @property string $nestedResource
 */
trait NestedRelationManager
{
    use NestedCreateAction;
    use NestedEditAction;
    use NestedViewAction;

    public function getNestedResource(?Model $record = null): string
    {
        if (property_exists(static::class, 'nestedResource')) {
            return static::$nestedResource;
        }

        return Filament::getModelResource($record ?? $this->getRelationship()->getRelated());
    }
}
