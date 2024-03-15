<?php

namespace Guava\FilamentNestedResources\Actions;

use Filament\Actions\ForceDeleteAction;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

trait NestedForceDeleteAction
{
    protected function configureForceDeleteAction(ForceDeleteAction | \Filament\Tables\Actions\ForceDeleteAction $action): void
    {
        try {
            parent::configureForceDeleteAction($action);
        } catch (RouteNotFoundException) {
            if ($ancestor = static::getResource()::getAncestor()) {
                $related = $ancestor->getRelatedRecord($this->record);
                $ancestorResource = $ancestor->getResource($related);
                $operation = $this->getResourcePageName();

                $action->successRedirectUrl(
                    match (true) {
                        $ancestorResource::hasPage($ancestor->getRelationshipName()) => $ancestorResource::getUrl($ancestor->getRelationshipName(), ['record' => $related]),
                        $ancestorResource::hasPage($operation) => $ancestorResource::getUrl($operation, [
                            'record' => $related,
                        ]),
                        $ancestorResource::hasPage('view') => $ancestorResource::getUrl('view', [
                            'record' => $related,
                        ]),
                        $ancestorResource::hasPage('edit') => $ancestorResource::getUrl('edit', [
                            'record' => $related,
                        ]),
                    },
                );
            }
        }
    }
}
