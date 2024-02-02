<?php

namespace Nodesol\LighthouseSchemaGenerator\Support;

use Nodesol\LighthouseSchemaGenerator\Support\Contracts\DirectiveGeneratorInterface;

class MultipleRelationDirectiveGenerator implements DirectiveGeneratorInterface
{
    public static function generate(string $fieldName, string $classOrColumnName, string $relationName = ''): string
    {
        return "    $fieldName: [$classOrColumnName] @$relationName\n";
    }
}
