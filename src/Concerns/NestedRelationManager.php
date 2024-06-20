<?php

namespace Guava\FilamentNestedResources\Concerns;

use Filament\Facades\Filament;
use Guava\FilamentNestedResources\Actions\NestedCreateAction;
use Guava\FilamentNestedResources\Actions\NestedEditAction;
use Guava\FilamentNestedResources\Actions\NestedViewAction;
use Illuminate\Database\Eloquent\Model;

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
