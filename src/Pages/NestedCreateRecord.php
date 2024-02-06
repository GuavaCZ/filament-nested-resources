<?php

namespace Guava\Filament\NestedResources\Pages;

use Illuminate\Support\Arr;
use Filament\Resources\Pages\CreateRecord;
use Guava\Filament\NestedResources\Ancestor;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NestedCreateRecord extends CreateRecord
{
    use NestedPage;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $ancestor = $this->getAncestorRecord();
        dd($ancestor);

        return parent::mutateFormDataBeforeCreate([
            ...$data,
            ...$ancestor,
        ]);
    }

    protected function getAncestorRecord(): array
    {
        $id = Arr::last($this->getRouteParameterIds());
        $ancestor = static::getResource()::getAncestor();
        $recordRouteKeyName = $ancestor->getResource()::getRecordRouteKeyName() ?? 'id';
        $record = $ancestor->getResource()::getModel()::firstWhere($recordRouteKeyName, $id);
        $fake = new (static::getModel())();
        /** @var BelongsTo $relation */
        $relation = $fake->{$ancestor->getRelationship()}();

        if ($relation instanceof MorphTo) {
            return [$relation->getForeignKeyName() => $record->id, $relation->getMorphType() => get_class($record)];
        }

        return [$relation->getForeignKeyName() => $record->id];
    }

    protected function getRedirectUrlParameters(): array
    {
        /** @var Ancestor $ancestor */
        $ancestor = $this::getResource()::getAncestor();

        return $ancestor->getNormalizedRouteParameters($this->getRecord());
    }
}
