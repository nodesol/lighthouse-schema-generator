<?php

namespace Nodesol\LighthouseSchemaGenerator\Helpers;

use ReflectionType;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionException;

class Reflection
{
    /**
     * @param object|class-string $objectOrClass
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function reflectionClass(object|string $objectOrClass): ReflectionClass
    {
        return (new ReflectionClass($objectOrClass));
    }

    /**
     * @param object $object
     * @return ReflectionObject
     */
    public function reflectionObject(object $object): ReflectionObject
    {
        return (new ReflectionObject($object));
    }

    /**
     * @param ReflectionMethod $method
     * @return ReflectionType|null
     */
    public function getReturnType(ReflectionMethod $method): ?ReflectionType
    {
        return $method->getReturnType();
    }
}
