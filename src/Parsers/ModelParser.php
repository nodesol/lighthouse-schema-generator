<?php

namespace Nodesol\LighthouseSchemaGenerator\Parsers;

use Exception;
use ReflectionMethod;
use Illuminate\Database\Eloquent\Model;
use Nodesol\LighthouseSchemaGenerator\Helpers\Reflection;

class ModelParser
{

    public function __construct(protected Reflection $reflection, private readonly MethodParser $methodParser, private readonly ColumnsParser $columnsParser)
    {
    }

    /**
     * @param Model $model
     * @return string $data
     */
    public function parse(Model $model): string
    {
        $data      = '';
        $reflector = $this->reflection->reflectionObject($model);

        $data .= "type {$reflector->getShortName()} {\n";
        $data .= $this->columnsParser->parse($model);

        $publicMethods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($publicMethods as $reflectionMethod) {
            try {
                $data .= $this->methodParser->parse($model, $reflectionMethod);
            } catch (Exception) {
                continue;
            }
        }

        $data .= '}';

        return $data;
    }
}
