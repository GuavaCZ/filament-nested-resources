<?php

namespace Guava\Filament\NestedResources\Concerns;

use Guava\Filament\NestedResources\Ancestor;

trait HasAncestor
{
    public static function hasAncestor(): bool
    {
        return static::getAncestor() !== null;
    }

    public static function getAncestor(): ?Ancestor
    {
        return null;
    }
}
