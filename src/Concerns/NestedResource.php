<?php

namespace Guava\FilamentNestedResources\Concerns;

use Guava\FilamentNestedResources\Ancestor;
use Illuminate\Database\Eloquent\Model;

trait NestedResource
{
    abstract public static function getAncestor(): ?Ancestor;

    public static function getBreadcrumbs(Model $record, string $operation): array
    {
        $resource = static::class;

        $indexUrl = match (true) {
            $resource::hasPage('index') => $resource::getUrl('index'),
            default => null,
        };

        $detailUrl = match (true) {
            $resource::hasPage($operation) => $resource::getUrl($operation, [
                'record' => $record,
            ]),

            $resource::hasPage('view') => $resource::getUrl('view', [
                'record' => $record,
            ]),

            $resource::hasPage('edit') => $resource::getUrl('edit', [
                'record' => $record,
            ]),
        };

        $indexUrl ??= "$detailUrl#list";

        return [
            $indexUrl => $resource::getBreadcrumb(),
            $detailUrl => static::getBreadcrumbRecordLabel($record),
        ];
    }

    public static function getBreadcrumbRecordLabel(Model $record)
    {
        return $record->getRouteKey();
    }

    public static function shouldRegisterNavigation(): bool
    {
        if ($ancestor = static::getAncestor()) {
            return $ancestor->getResource() === static::class;
        }

        return parent::shouldRegisterNavigation();
    }
}
