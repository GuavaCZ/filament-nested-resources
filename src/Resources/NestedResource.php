<?php

namespace Guava\Filament\NestedResources\Resources;

use Filament\Panel;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Guava\Filament\NestedResources\Ancestor;
use Guava\Filament\NestedResources\Concerns\HasAncestor;
use Guava\Filament\NestedResources\Concerns\HasBreadcrumbTitleAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

abstract class NestedResource extends Resource
{
    use HasAncestor;
    use HasBreadcrumbTitleAttribute;

    public static function getSlug(): string
    {
        if ($ancestor = static::getAncestor()) {
            $resource = $ancestor->getResource();
            $parameter = $ancestor->getRouteParameterName();

            return $resource::getSlug() . '/{' . $parameter . '?}/' . parent::getSlug();
        }

        return parent::getSlug();
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (static::hasAncestor()) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }

    public static function getBreadcrumbs(Page $page, Model $record = null): array
    {
        $resource = static::class;

        $breadcrumb = $page->getBreadcrumb();
        /** @var Ancestor $ancestor */
        $ancestor = $resource::getAncestor();

        // If no record passed
        if (! ($page instanceof ListRecords)) {
            $record ??= $page->getRecord();
        }

        // If page has no record (such as create pages)
        $id = Arr::last($page->getRouteParameterIds());
        if ($ancestor) {
            $recordRouteKeyName = $ancestor->getResource()::getRecordRouteKeyName() ?? 'id';
            $relatedRecord = $record ? $ancestor->getRelatedModel($record) : $ancestor->getResource()::getModel()::firstWhere($recordRouteKeyName, $id);
        }

        if ($ancestor) {
            $index = $resource::hasPage('index')
                ? [
                    $resource::getUrl('index', [
                        ...$ancestor->getNormalizedRouteParameters($record ?? $relatedRecord),
                    ]) => $resource::getBreadcrumb(),
                ]
                : [
                    $ancestor->getResource()::getUrl('edit', [
                        ...$ancestor->getNormalizedRouteParameters($record ?? $relatedRecord),
                    ]) . '#relation-manager' => $resource::getBreadcrumb(),
                ];

        } else {
            $index = [$resource::getUrl('index') => $resource::getBreadcrumb()];
        }

        $breadcrumbs = [];

        if ($ancestor) {
            $breadcrumbs = [
                ...$ancestor->getResource()::getBreadcrumbs($page, $relatedRecord),
                ...$breadcrumbs,
            ];
        }

        $breadcrumbs = [
            ...$breadcrumbs,
            ...$index,
        ];

        if ($page::getResource() === $resource) {
            $breadcrumbs = [
                ...$breadcrumbs,
                ...(filled($breadcrumb) ? [$breadcrumb] : []),
            ];
        } else {

            $pageTypes = match (true) {
                $page instanceof ViewRecord => ['view', 'edit'],
                default => ['edit', 'view'],
            };

            foreach ($pageTypes as $pageType) {
                if ($resource::hasPage($pageType) && $resource::can($pageType, $record)) {
                    $recordBreadcrumb = [$resource::getUrl($pageType, [
                        ...$ancestor ? $ancestor->getNormalizedRouteParameters($record) : [],
                        'record' => $record,
                    ]) => $record->{$resource::getBreadcrumbTitleAttribute()}];

                    break;
                }
            }

            $breadcrumbs = [
                ...$breadcrumbs,
                ...$recordBreadcrumb,
            ];
        }

        return $breadcrumbs;
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        if (static::hasPage('edit') && static::canEdit($record)) {
            return static::getUrl(
                'edit',
                collect([
                    'record' => $record->id,
                ])
                    ->when(
                        $ancestor = static::getAncestor(),
                        fn (Collection $collection) => $collection->mergeRecursive(...$ancestor->getNormalizedRouteParameters($record))
                    )
                    ->toArray()
            );
        }

        if (static::hasPage('view') && static::canView($record)) {
            return static::getUrl(
                'view',
                collect([
                    'record' => $record->id,
                ])
                    ->when(
                        $ancestor = static::getAncestor(),
                        fn (Collection $collection) => $collection->merge($ancestor->getNormalizedRouteParameters($record))
                    )
                    ->toArray()
            );
        }

        return parent::getGlobalSearchResultUrl($record);
    }

    public static function getRouteBaseName(string $panel = null): string
    {
        return preg_replace('/.\{[^}]*\}/', '', parent::getRouteBaseName($panel));
    }

    public static function routes(Panel $panel): void
    {
        $slug = static::getSlug();

        Route::name(
            (string) str($slug)
                ->replace('/', '.')
                ->replaceMatches('/.\{[^}]*\}/', '')
                ->append('.'),
        )
            ->prefix($slug)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->group(function () use ($panel) {
                foreach (static::getPages() as $name => $page) {
                    $page->registerRoute($panel)?->name($name);
                }
            })
        ;
    }
}
