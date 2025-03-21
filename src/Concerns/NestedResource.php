<?php

namespace Guava\FilamentNestedResources\Concerns;

use Filament\Facades\Filament;
use Guava\FilamentNestedResources\Ancestor;
use Guava\FilamentNestedResources\Pages\CreateRelatedRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait NestedResource
{
    abstract public static function getAncestor(): ?Ancestor;

    public static function getBreadcrumbs(Model $record, string $operation): array
    {
        $resource = static::class;

        /** @var Ancestor $ancestor */
        $ancestor = $resource::getAncestor();
        $recordParent = null;
        $relationNameParent = null;
        $resourceParent = null;
        if ($ancestor) {
            $recordParent = $ancestor->getRelatedRecord($record);
            $relationNameParent = $ancestor->getRelationshipName();
            $resourceParent = Filament::getModelResource($recordParent);
        }

        $relationUrl = null;
        $relationLabel = null;
        $operationDetail = $operation;
        if (!in_array($operation, ['view', 'edit']) && $resource::hasPage($operation)) {
            $operationPage = Arr::get($resource::getPages(), $operation)?->getPage();
            if (
                $operationPage &&
                (
                    in_array(NestedRelationManager::class, class_uses_recursive($operationPage)) ||
                    is_a($operationPage, CreateRelatedRecord::class, true)
                )
            ) {
                $relationUrl = $resource::getUrl($operation, [
                    'record' => $record,
                ]);
                $relationLabel = $operationPage::getNavigationLabel();
                if (is_a($operationPage, CreateRelatedRecord::class, true)) {
                    $relationName = $operationPage::getRelationship();
                    $operationBasePage = Arr::get($resource::getPages(), $relationName)?->getPage();
                    if (
                        $operationBasePage &&
                        in_array(NestedRelationManager::class, class_uses_recursive($operationBasePage))
                    ) {
                        $relationUrl = $resource::getUrl($relationName, [
                            'record' => $record,
                        ]);
                        $relationLabel = $operationBasePage::getNavigationLabel();
                    }
                }
                $operationDetail = '';
            }
        }

        $detailUrl = match (true) {
            $resource::hasPage($operationDetail) => $resource::getUrl($operationDetail, [
                'record' => $record,
            ]),

            $resource::hasPage('view') => $resource::getUrl('view', [
                'record' => $record,
            ]),

            $resource::hasPage('edit') => $resource::getUrl('edit', [
                'record' => $record,
            ]),
        };

        $indexUrl = match (true) {
            $resourceParent && $resourceParent::hasPage($relationNameParent) => $resourceParent::getUrl($relationNameParent, [
                'record' => $recordParent,
            ]),
            $resource::hasPage('index') => $resource::getUrl('index'),
            default => null,
        };

        $indexUrl ??= "$detailUrl#list";

        return [
            $indexUrl => $resource::getBreadcrumb(),
            $detailUrl => static::getBreadcrumbRecordLabel($record),
            ...($relationUrl ? [$relationUrl => $relationLabel] : []),
        ];
    }

    public static function getBreadcrumbRecordLabel(Model $record)
    {
        return $record->getRouteKey();
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (static::getAncestor()) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }
}
