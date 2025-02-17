<?php

namespace GraphQL\SchemaGenerator\CodeGenerator;

use GraphQL\Enumeration\FieldTypeKindEnum;
use GraphQL\Mutation;
use GraphQL\SchemaGenerator\CodeGenerator\CodeFile\ClassFile;
use GraphQL\SchemaObject\QueryObject;
use GraphQL\Util\StringLiteralFormatter;

/**
 * Class QueryObjectClassBuilder
 *
 * @package GraphQL\SchemaManager\CodeGenerator
 */
class QueryObjectClassBuilder extends ObjectClassBuilder
{
    /**
     * @var bool
     */
    private $isRootMutation = false;

    /**
     * QueryObjectClassBuilder constructor.
     *
     * @param string $writeDir
     * @param string $objectName
     * @param string $namespace
     */
    public function __construct(string $writeDir, string $objectName, string $namespace = self::DEFAULT_NAMESPACE)
    {
        if ($objectName === QueryObject::ROOT_MUTATION_OBJECT_NAME) {
            $objectName = '';
            $className = 'RootMutationObject';
            $this->isRootMutation = true;
        } else {
            $className = $objectName . 'QueryObject';
        }

        $this->classFile = new ClassFile($writeDir, $className);
        $this->classFile->setNamespace($namespace);
        if ($namespace !== self::DEFAULT_NAMESPACE) {
            $this->classFile->addImport('GraphQL\\SchemaObject\\QueryObject');
        }
        $this->classFile->extendsClass('QueryObject');

        // Special case for handling root query object
        if ($objectName === QueryObject::ROOT_QUERY_OBJECT_NAME) {
            $objectName = '';
        }
        $this->classFile->addConstant('OBJECT_NAME', $objectName);

        if ($this->isRootMutation) {
            $this->classFile->addImport(Mutation::class);
            $constructor = 'public function __construct()
{
    parent::__construct();
    $this->query = new Mutation();
}';
            $this->classFile->addMethod($constructor);
        }
    }

    /**
     * @param string $fieldName
     */
    public function addScalarField(string $fieldName, bool $isDeprecated, ?string $deprecationReason)
    {
        $upperCamelCaseProp = StringLiteralFormatter::formatUpperCamelCase($fieldName);
        $this->addSimpleSelector($fieldName, $upperCamelCaseProp, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $fieldName
     * @param string $typeName
     * @param string $typeKind
     * @param string|null $argsObjectName
     * @param bool $isDeprecated
     * @param string|null $deprecationReason
     */
    public function addObjectField(string $fieldName, string $typeName, string $typeKind, ?string $argsObjectName, bool $isDeprecated, ?string $deprecationReason)
    {
        $upperCamelCaseProp = StringLiteralFormatter::formatUpperCamelCase($fieldName);
        $this->addObjectSelector($fieldName, $upperCamelCaseProp, $typeName, $typeKind, $argsObjectName, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $fieldName
     * @param string $upperCamelName
     * @param bool $isDeprecated
     * @param string|null $deprecationReason
     */
    protected function addSimpleSelector(string $fieldName, string $upperCamelName, bool $isDeprecated, ?string $deprecationReason)
    {
        $methodName = $this->isRootMutation ? $fieldName : 'select' . $upperCamelName;
        $method = "public function $methodName(): self
{
    \$this->selectField(\"$fieldName\");

    return \$this;
}";
        $this->classFile->addMethod($method, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $fieldName
     * @param string $upperCamelName
     * @param string $fieldTypeName
     * @param string $fieldTypeKind
     * @param string|null $argsObjectName
     * @param bool $isDeprecated
     * @param string|null $deprecationReason
     */
    protected function addObjectSelector(string $fieldName, string $upperCamelName, string $fieldTypeName, string $fieldTypeKind, ?string $argsObjectName, bool $isDeprecated, ?string $deprecationReason)
    {
        $methodName = $this->isRootMutation ? $fieldName : 'select' . $upperCamelName;
        $objectClass = $fieldTypeName . ($fieldTypeKind === FieldTypeKindEnum::UNION_OBJECT ? 'UnionObject' : 'QueryObject');

        if ($argsObjectName === null) {
            $method = "public function $methodName(): $objectClass
{
    \$object = new $objectClass(\"$fieldName\");
    \$this->selectField(\$object);

    return \$object;
}";
        } else {
            $method = "public function $methodName(?$argsObjectName \$argsObject = null): $objectClass
{
    \$object = new $objectClass(\"$fieldName\");
    if (\$argsObject !== null) {
        \$object->appendArguments(\$argsObject->toArray());
    }
    \$this->selectField(\$object);

    return \$object;
}";
        }

        $this->classFile->addMethod($method, $isDeprecated, $deprecationReason);
    }

    /**
     * This method builds the class and writes it to the file system
     */
    public function build(): void
    {
        $this->classFile->writeFile();
    }
}
