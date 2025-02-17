<?php

namespace GraphQL\Tests\SchemaObject;

use GraphQL\SchemaObject\QueryObject;
use GraphQL\Mutation;

class RootMutationObject extends QueryObject
{
    public const OBJECT_NAME = "";

    public function __construct()
    {
        parent::__construct();
        $this->query = new Mutation();
    }
}
