<?php

namespace Guava\FilamentNestedResources\Actions;

use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

trait NestedViewAction
{
    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        parent::configureViewAction($action);

        $action->url(
            fn (Model $record) => $this->getNestedResource($record)::getUrl(
                'view',
                ['record' => $record],
            )
        );
    }
}
