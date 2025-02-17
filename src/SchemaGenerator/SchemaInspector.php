<?php

namespace GraphQL\SchemaGenerator;

use GraphQL\Client;

/**
 * Class SchemaInspector
 *
 * @codeCoverageIgnore
 *
 * @package GraphQL\SchemaGenerator
 */
class SchemaInspector
{
    private const TYPE_SUB_QUERY = <<<QUERY
type{
  name
  kind
  description
  ofType{
    name
    kind
    ofType{
      name
      kind
      ofType{
        name
        kind
        ofType{
          name
          kind
        }
      }
    }
  }
}
QUERY;


    /**
     * @var Client
     */
    protected $client;

    /**
     * SchemaInspector constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $type query or mutation
     * @return array
     */
    public function getRootSchema(string $type): array
    {
        $schemaQuery = "{
  __schema{
    {$type}Type{
      name
      kind
      description
      fields(includeDeprecated: true){
        name
        description
        isDeprecated
        deprecationReason
        " . static::TYPE_SUB_QUERY . "
        args{
          name
          description
          defaultValue
          " . static::TYPE_SUB_QUERY . "
        }
      }
    }
  }
}";
        $response = $this->client->runRawQuery($schemaQuery, true);

        return $response->getData()['__schema'][$type.'Type'];
    }

    /**
     * @param string $objectName
     *
     * @return array
     */
    public function getObjectSchema(string $objectName): array
    {
        $schemaQuery = "{
  __type(name: \"$objectName\") {
    name
    kind
    possibleTypes {
      kind
      name
    }
    fields(includeDeprecated: true){
      name
      description
      isDeprecated
      deprecationReason
      " . static::TYPE_SUB_QUERY . "
      args{
        name
        description
        defaultValue
        " . static::TYPE_SUB_QUERY . "
      }
    }
  }
}";
        $response = $this->client->runRawQuery($schemaQuery, true);

        return $response->getData()['__type'];
    }

    /**
     * @param string $objectName
     *
     * @return array
     */
    public function getInputObjectSchema(string $objectName): array
    {
        $schemaQuery = "{
  __type(name: \"$objectName\") {
    name
    kind
    inputFields {
      name
      description
      defaultValue
      " . static::TYPE_SUB_QUERY . "
    }
  }
}";
        $response = $this->client->runRawQuery($schemaQuery, true);

        return $response->getData()['__type'];
    }

    /**
     * @param string $objectName
     *
     * @return array
     */
    public function getEnumObjectSchema(string $objectName): array
    {
        $schemaQuery = "{
  __type(name: \"$objectName\") {
    name
    kind
    enumValues {
      name
      description
    }
  }
}";
        $response = $this->client->runRawQuery($schemaQuery, true);

        return $response->getData()['__type'];
    }

    /**
     * @param string $objectName
     *
     * @return array
     */
    public function getUnionObjectSchema(string $objectName): array
    {
        $schemaQuery = "{
  __type(name: \"$objectName\") {
    name
    kind
    possibleTypes {
      kind
      name
    }
  }
}";
        $response = $this->client->runRawQuery($schemaQuery, true);

        return $response->getData()['__type'];
    }
}
