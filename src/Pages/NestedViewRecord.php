<?php

namespace Guava\Filament\NestedResources\Pages;

use Filament\Forms\Form;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class NestedViewRecord extends ViewRecord
{
    use NestedPage;

    protected function configureEditAction(EditAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canEdit($this->getRecord()))
            ->form(fn (Form $form): Form => static::getResource()::form($form));

        if ($resource::hasPage('edit')) {
            $action->url(fn (): string => static::getResource()::getUrl('edit', [
                ...static::getResource()::getAncestor()->getNormalizedRouteParameters($this->getRecord()),
                'record' => $this->getRecord()
            ]));
        }
    }
}
