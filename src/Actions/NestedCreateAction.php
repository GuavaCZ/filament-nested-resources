<?php

namespace Guava\FilamentNestedResources\Actions;

use Filament\Tables\Actions\CreateAction;
use Guava\FilamentNestedResources\Ancestor;

trait NestedCreateAction
{
    protected function configureCreateAction(CreateAction $action): void
    {
        $resource = static::getNestedResource();

        /** @var Ancestor $ancestor */
        $ancestor = $resource::getAncestor();

        $ancestorResource = $ancestor->getResource($this->getOwnerRecord());

        if (! $ancestorResource::hasPage("{$ancestor->getRelationshipName()}.create")) {
            throw new \Exception("{$ancestorResource} does not have a nested create page. Please make sure to create it and that it is called '{$ancestor->getRelationshipName()}.create'. Check the documentation for more information.");
        }

        parent::configureCreateAction($action->url(
            fn () => $ancestorResource::getUrl("{$ancestor->getRelationshipName()}.create", [
                'record' => $this->getOwnerRecord(),
            ])
        ));
    }
}
