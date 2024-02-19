<?php

namespace Guava\FilamentNestedResources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class Ancestor
{
    public function __construct(
        protected string $resource,
        protected string $relationshipName,
        protected string $inverseRelationshipName,
    ) {
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getRelationshipName(): string
    {
        return $this->relationshipName;
    }

    public function getInverseRelationshipName(): ?string
    {
        return $this->inverseRelationshipName;
    }

    public function getRelationship(Model $record)
    {
        if (method_exists($record, $this->getRelationshipName())) {
            return $record->{$this->getRelationshipName()}();
        }

        if (method_exists($record, Str::plural($this->getRelationshipName()))) {
            return $record->{Str::plural($this->getRelationshipName())}();
        }

        return null;
    }

    public function getInverseRelationship(Model $record): ?Relation
    {
        if (method_exists($record, $this->getInverseRelationshipName())) {
            return $record->{$this->getInverseRelationshipName()}();
        }

        return null;
    }

    public function getRelatedRecord(Model $record): ?Model
    {
        $relationship = $this->getInverseRelationship($record);

        if ($relationship->exists()) {
            return $relationship->first();
        }

        return null;
    }

    public static function make(string $resource, string $relationshipName, string $inverseRelationshipName)
    {
        return app(static::class, [
            'resource' => $resource,
            'relationshipName' => $relationshipName,
            'inverseRelationshipName' => $inverseRelationshipName,
        ]);
    }
}
