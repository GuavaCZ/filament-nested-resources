<?php

namespace Guava\Filament\NestedResources\RelationManagers;

use Filament\Facades\Filament;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Guava\Filament\NestedResources\Resources\NestedResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class NestedRelationManager extends RelationManager
{
    //    public static function resource(): string | Resource;

    protected static ?string $resource = null;

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        parent::configureCreateAction(
            $action->url($this->getResource()::getUrl('create', [
                ...array_reverse(array_values($this->getRouteParameters())),
            ]))
        );
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction(
            $action->url(fn (Model $record) => $this->getResource()::getUrl('edit', [
                ...$this->getRouteParameters(),
                'record' => $record,
            ]))
        );
    }

    protected function getRouteParameters(): array
    {
        $record = $this->getOwnerRecord();

        return Arr::mapWithKeys(
            $this->getResource()::getRouteParameterNames(),
            function (string $parameter) use (&$record) {
                $result = [$parameter => $record->id];
                $resource = $this->getResource()::getParentResource();

                if (in_array(NestedResource::class, class_parents($resource))) {
                    $relationship = $resource::getRelationshipName();
                    $record = $record->$relationship;
                }

                return $result;
            }
        );
    }

    protected function getResource(): string | Resource | NestedResource
    {
        return static::$resource ?? Filament::getModelResource($this->getRelationship()->getRelated());
    }
}
