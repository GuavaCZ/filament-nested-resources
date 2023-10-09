<?php

namespace Guava\Filament\NestedResources\Resources\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait HasRelationship
{
    protected static string $relationship;

    public function getRelationship(Model $record): Relation | Builder
    {
        return $this->getOwnerRecord()->{static::getRelationshipName()}();
    }

    public static function getRelationshipName(): string
    {
        return static::$relationship;
    }
}
