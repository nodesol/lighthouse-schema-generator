<?php

namespace Nodesol\LighthouseSchemaGenerator\Parsers;

use ReflectionMethod;
use ReflectionException;
use ReflectionNamedType;
use Illuminate\Database\Eloquent\Model;
use Nodesol\LighthouseSchemaGenerator\Helpers\Reflection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Nodesol\LighthouseSchemaGenerator\Support\DirectiveGenerator;

class MethodParser
{

    public function __construct(private readonly Reflection $reflection)
    {
    }

    /**
     * @param Model $model
     * @param ReflectionMethod $method
     * @return string
     * @throws ReflectionException
     */
    public function parse(Model $model, ReflectionMethod $method): string
    {
        $data = '';

        /** @var ReflectionNamedType $returnType */
        $returnType = $this->reflection->getReturnType($method);
        /** @phpstan-ignore-next-line */
        if ($returnType && method_exists($returnType, 'isBuiltin') && (!$returnType->isBuiltin()) && $method->hasReturnType()) {
            $methodName = $method->getName();
            $relation   = $this->reflection->reflectionClass($returnType->getName());
            if ($method->getNumberOfParameters() == 0 && $relation->isSubclassOf(Relation::class)) {
                $relatedClassName  = class_basename($method->invoke($model)->getRelated());
                $relationClassName = $relation->getShortName();
                $data              .= DirectiveGenerator::generate($methodName, $relatedClassName, $relationClassName);
            }
        }

        return $data;
    }
}
