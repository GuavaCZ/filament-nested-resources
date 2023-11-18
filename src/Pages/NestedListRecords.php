<?php

namespace Guava\Filament\NestedResources\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NestedListRecords extends ListRecords
{
    use NestedPage;

    protected function makeTable(): Table
    {
        return parent::makeTable()
            ->when(
                static::getResource()::hasPage('edit'),
                fn (Table $table) => $table->recordUrl(fn (Model $record) => static::getResource()::getUrl('edit', [
                    ...static::getResource()::getAncestor()->getNormalizedRouteParameters($record),
                    'record' => $record,
                ])),
                fn (Table $table) => $table->recordUrl(null)
            )
        ;
    }

    protected function configureViewAction(Tables\Actions\ViewAction | EditAction $action): void
    {
        parent::configureViewAction($action);
        $action
            ->when(
                static::getResource()::hasPage('view'),
                fn (Tables\Actions\ViewAction $action) => $action
                    ->url(fn ($record) => static::getResource()::getUrl('view', [
                        ...static::getResource()::getAncestor()->getNormalizedRouteParameters($record),
                        'record' => $record,
                    ]))
            )
        ;
    }

    protected function configureCreateAction(Tables\Actions\CreateAction | CreateAction $action): void
    {
        parent::configureCreateAction($action);
        $action
            ->when(
                static::getResource()::hasPage('create'),
                fn (CreateAction $action) => $action
                    ->url(static::getResource()::getUrl('create', $this->getRouteParameterIds())),
                fn (CreateAction $action) => $action->hidden()
            )
        ;
    }

    protected function configureEditAction(Tables\Actions\EditAction | EditAction $action): void
    {
        parent::configureEditAction($action);
        $action
            ->when(
                static::getResource()::hasPage('edit'),
                fn (Tables\Actions\EditAction $action) => $action
                    ->url(fn ($record) => static::getResource()::getUrl('edit', [
                        ...static::getResource()::getAncestor()->getNormalizedRouteParameters($record),
                        'record' => $record,
                    ]))
            );
    }
}
