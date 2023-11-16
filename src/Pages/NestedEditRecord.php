<?php

namespace Guava\Filament\NestedResources\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\EditRecord;

class NestedEditRecord extends EditRecord
{
    use NestedPage;

    protected function configureDeleteAction(DeleteAction $action): void
    {
        $resource = static::getResource();
        $ancestor = $resource::getAncestor();

        if (! $ancestor) {
            parent::configureDeleteAction($action);

            return;
        }

        $ancestorResource = $ancestor->getResource();

        $action
            ->authorize($resource::canDelete($this->getRecord()))
            ->successRedirectUrl(
                $resource::hasPage('index')
                    ? $resource::getUrl('index', [
                        ...$ancestor->getNormalizedRouteParameters($this->getRecord()),
                    ])
                    : $ancestorResource::getUrl('edit', [
                        ...$ancestor->getNormalizedRouteParameters($this->getRecord()),
                    ])
            )
        ;
    }

    protected function configureViewAction(ViewAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canView($this->getRecord()))
            ->infolist(fn (Infolist $infolist): Infolist => static::getResource()::infolist($infolist->columns(2)))
            ->form(fn (Form $form): Form => static::getResource()::form($form))
        ;

        if ($resource::hasPage('view')) {
            $action->url(fn (): string => static::getResource()::getUrl('view', [
                ...static::getResource()::getAncestor()->getNormalizedRouteParameters($this->getRecord()),
                'record' => $this->getRecord(),
            ]));
        }
    }
}
