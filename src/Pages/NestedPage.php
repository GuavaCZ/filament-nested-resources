<?php

namespace Guava\Filament\NestedResources\Pages;

trait NestedPage
{
    public array $routeParameterIds = [];

    public function mountNestedPage(): void
    {
        $this->routeParameterIds = func_get_args();
    }

    public function getRouteParameterIds(): array
    {
        return $this->routeParameterIds;
    }

    public function getBreadcrumbs(): array
    {
        return static::getResource()::getBreadcrumbs($this);
    }

    //    protected function resolveRoutePathRecords(array $arguments) {
    //
    //    }

}
