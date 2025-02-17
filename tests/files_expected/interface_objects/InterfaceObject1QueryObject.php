<?php

namespace GraphQL\Tests\SchemaObject;

use GraphQL\SchemaObject\QueryObject;

class InterfaceObject1QueryObject extends QueryObject
{
    public const OBJECT_NAME = "InterfaceObject1";

    public function selectValue(): self
    {
        $this->selectField("value");

        return $this;
    }
}
