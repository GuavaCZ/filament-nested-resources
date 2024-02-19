<?php

namespace Guava\FilamentNestedResources\Actions;

use Filament\Facades\Filament;
use Filament\Tables\Actions\CreateAction;
use Guava\FilamentNestedResources\Ancestor;

trait NestedCreateAction
{
    protected function configureCreateAction(CreateAction $action): void
    {
        $resource = Filament::getModelResource($this->getRelationship()->getRelated());
        /** @var Ancestor $ancestor */
        $ancestor = $resource::getAncestor();

        if (! $ancestor->getResource()::hasPage("{$ancestor->getRelationshipName()}.create")) {
            throw new \Exception("{$ancestor->getResource()} does not have a nested create page. Please make sure to create it and that it is called '{$ancestor->getRelationshipName()}.create'. Check the documentation for more information.");
        }

        parent::configureCreateAction($action->url(
            fn () => $ancestor->getResource()::getUrl("{$ancestor->getRelationshipName()}.create", [
                'record' => $this->getOwnerRecord(),
            ])
        ));
    }
}
