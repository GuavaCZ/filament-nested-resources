<?php

namespace Guava\Filament\NestedResources\Concerns;

trait HasBreadcrumbTitleAttribute
{
    protected static ?string $breadcrumbTitleAttribute;

    public static function getBreadcrumbTitleAttribute(): string
    {
        return static::$breadcrumbTitleAttribute ?? static::getRecordTitleAttribute() ?? 'id';
    }
}
