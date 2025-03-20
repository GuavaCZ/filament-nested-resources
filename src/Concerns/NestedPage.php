<?php

namespace Guava\FilamentNestedResources\Concerns;

use Filament\Resources\Pages\ManageRelatedRecords;
use Guava\FilamentNestedResources\Actions\NestedDeleteAction;
use Guava\FilamentNestedResources\Actions\NestedForceDeleteAction;
use Guava\FilamentNestedResources\Pages\CreateRelatedRecord;

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
        /** @var \Guava\FilamentNestedResources\Pages\CreateRelatedRecord|\Guava\FilamentNestedResources\Concerns\NestedPage $this */
        if (in_array(static::getResourcePageName(), ['index', 'create'])) {
            return parent::getBreadcrumbs();
        }

        /** @var \Illuminate\Database\Eloquent\Model */
        $record = $this->record ?? $this->getOwnerRecord();
        /** @var class-string<\Guava\FilamentNestedResources\Concerns\NestedResource> */
        $resource = static::getResource();

        $breadcrumbs = $resource::getBreadcrumbs($record, static::getResourcePageName());

        while ($ancestor = $resource::getAncestor()) {

            $record = $ancestor->getRelatedRecord($record);
            if (!$record) {
                break;
            }

            $resource = $resource::getAncestor()->getResource($record);
            $breadcrumbs = $resource::getBreadcrumbs($record, static::getResourcePageName()) + $breadcrumbs;
        }

        return $breadcrumbs + ['' => $this->getBreadcrumb()];
    }

    public function getTitle(): string
    {
        return static::$title ?? match (true) {
            $this instanceof CreateRelatedRecord => __('filament-panels::resources/pages/create-record.title', [
                'label' => static::getNestedResource()::getTitleCaseModelLabel(),
            ]),
            $this instanceof ManageRelatedRecords && in_array(NestedPage::class, class_uses_recursive($this)) => static::getNestedResource()::getTitleCasePluralModelLabel(),
            default => parent::getTitle(),
        };
    }
}
