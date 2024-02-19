<?php

namespace Guava\FilamentNestedResources\Concerns;

use Guava\FilamentNestedResources\Actions\NestedCreateAction;
use Guava\FilamentNestedResources\Actions\NestedEditAction;
use Guava\FilamentNestedResources\Actions\NestedViewAction;

trait NestedRelationManager
{
    use NestedCreateAction;
    use NestedViewAction;
    use NestedEditAction;
}
