<?php

namespace Guava\FilamentNestedResources\Concerns;

use Guava\FilamentNestedResources\Actions\NestedDeleteAction;
use Guava\FilamentNestedResources\Actions\NestedForceDeleteAction;

trait NestedPage
{
    use NestedDeleteAction;
    use NestedForceDeleteAction;

    public function mountNestedPage()
    {
        //        $resource = static::getResource();
        //        $ancestor = $resource::getAncestor();
    }

    public function getBreadcrumbs(): array
    {
        if (in_array(static::getResourcePageName(), ['index', 'create'])) {
            return parent::getBreadcrumbs();
        }

        $record = $this->record ?? $this->getOwnerRecord();
        $resource = static::getResource();

        if (! $record) {
            $record = $this->getOwnerRecord();
        }

        $breadcrumbs = $resource::getBreadcrumbs($record, static::getResourcePageName());

        while ($ancestor = $resource::getAncestor()) {

            $record = $ancestor->getRelatedRecord($record);
            if (! $record) {
                break;
            }

            $resource = $resource::getAncestor()->getResource($record);
            $breadcrumbs = $resource::getBreadcrumbs($record, static::getResourcePageName()) + $breadcrumbs;
        }

        return $breadcrumbs + ['#' => $this->getBreadcrumb()];
    }
}
