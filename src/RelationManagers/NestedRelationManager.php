<?php

namespace Guava\Filament\NestedResources\RelationManagers;

use Filament\Facades\Filament;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class NestedRelationManager extends RelationManager
{
    protected function getResource(): string
    {
        return Filament::getModelResource($this->getRelationship()->getRelated());
    }

    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        parent::configureViewAction(
            $action
                ->when(
                    static::getResource()::hasPage('view'),
                    fn (Tables\Actions\ViewAction $action) => $action->url(fn (Model $record) => static::getResource()::getUrl('view', [
                        ...static::getResource()::getAncestor()->getNormalizedRouteParameters($this->getOwnerRecord()),
                        'record' => $record,
                    ]))
                )
        );
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        parent::configureCreateAction(
            $action
                ->when(
                    static::getResource()::hasPage('create'),
                    fn (Tables\Actions\CreateAction $action) => $action->url(static::getResource()::getUrl('create', [
                        ...static::getResource()::getAncestor()->getNormalizedRouteParameters($this->getOwnerRecord()),
                    ]))
                )
        );
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction(
            $action
                ->when(
                    static::getResource()::hasPage('edit'),
                    fn (Tables\Actions\EditAction $action) => $action->url(fn (Model $record) => static::getResource()::getUrl('edit', [
                        ...static::getResource()::getAncestor()->getNormalizedRouteParameters($record),
                        'record' => $record,
                    ]))
                )
        );
    }
}
