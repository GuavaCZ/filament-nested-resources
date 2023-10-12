<?php

namespace Guava\Filament\NestedResources\Pages;

use Filament\Actions\CreateAction;
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
            ->recordUrl(fn (Model $record) => static::getResource()::getUrl('edit', [
                ...static::getResource()::getAncestor()->getNormalizedRouteParameters($record),
                'record' => $record,
            ]))
        ;
    }

    protected function configureCreateAction(Tables\Actions\CreateAction | CreateAction $action): void
    {
        parent::configureCreateAction($action);
        $action->url(static::getResource()::getUrl('create', $this->getRouteParameterIds()));
    }
}
