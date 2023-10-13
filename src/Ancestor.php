<?php

namespace Guava\Filament\NestedResources;

use Illuminate\Database\Eloquent\Model;

class Ancestor
{
    public function __construct(
        protected string $resource,
        protected ?string $relationship = null,
    ) {
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getRelationship(): string
    {
        if (! $this->relationship) {
            return $this->getResource()::getModelLabel();
        }

        return $this->relationship;
    }

    public function getRouteParameterName(): string
    {
        return get_resource_route_parameter($this->getResource());
    }

    public function getRouteParameters(Model $record): array
    {
        $ancestor = $this;
        $related = $record;

        $parameters = [];
        do {
            // For 'create' actions, a model is not yet created, so the owner record is being sent.
            // In this case, the ancestor relation and received model are offset by one level and this
            // will re-sync the depth again.
            if ($ancestor->getResource()::getModel() === $related::class) {
                $parameters[$ancestor->getRouteParameterName()] = $related->id;

                continue;
            }

            $related = $ancestor->getRelatedModel($related);
            $parameters[$ancestor->getRouteParameterName()] = $related->id;
        } while ($ancestor = $ancestor->getResource()::getAncestor());

        return $parameters;
    }

    public function getNormalizedRouteParameters(Model $record): array
    {
        return array_values(
            array_reverse(
                $this->getRouteParameters($record)
            )
        );
    }

    public function getRelatedModel(Model $record): Model
    {
        return $record->{$this->getRelationship()};
    }

    public static function make(string $resource, string $relationship = null)
    {
        return app(static::class, [
            'resource' => $resource,
            'relationship' => $relationship,
        ]);
    }
}
