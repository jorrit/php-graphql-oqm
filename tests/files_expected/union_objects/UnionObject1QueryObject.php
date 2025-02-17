<?php

namespace GraphQL\Tests\SchemaObject;

use GraphQL\SchemaObject\QueryObject;

class UnionObject1QueryObject extends QueryObject
{
    public const OBJECT_NAME = "UnionObject1";

    public function selectUnion(): UnionTestObjectUnionObject
    {
        $object = new UnionTestObjectUnionObject("union");
        $this->selectField($object);

        return $object;
    }
}
