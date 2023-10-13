<?php

namespace Guava\Filament\NestedResources\Pages;

use Filament\Actions\DeleteAction;
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
                    ? $resource::getUrl('index')
                    : $ancestorResource::getUrl('edit', [
                        ...$ancestor->getNormalizedRouteParameters($this->getRecord()),
                    ])
            )
        ;
    }
}
