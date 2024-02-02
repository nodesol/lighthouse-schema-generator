<?php

namespace Nodesol\LighthouseSchemaGenerator\Parsers;

use Exception;
use ReflectionMethod;
use ReflectionObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
        $reflector  = $this->reflection->reflectionObject($model);
        $columns    = $this->columnsParser->parse($model);
        $data       = $this->parseType($model, $reflector, $columns);
        $data       .= $this->parseQuery($model, $reflector, $columns);
        $data       .= $this->parseMutations($model, $reflector, $columns);

        return $data;
    }

    /**
     * @param Model $model
     * @param ReflectionObject $reflector
     * @param string $columns
     *
     * @return string
     */
    private function parseType(Model $model, ReflectionObject $reflector, string $columns) {
        $hidden = $model->getHidden();
        $cols = "";
        foreach(explode("\n", $columns) as $column) {
            preg_match("/^\s*(.*?):.*$/", $column, $colname);
            if(isset($colname[1]) && !in_array($colname[1], $hidden)) {
                $cols .= $column . "\n";
            }
        }

        $data      = '';

        $data .= "type {$reflector->getShortName()} {\n";
        $data .= $cols;

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

    /**
     * @param Model $model
     * @param ReflectionObject $reflector
     * @param string $columns
     *
     * @return string
     */
    private function parseQuery(Model $model, ReflectionObject $reflector, string $columns) {
        $hidden = $model->getHidden();
        $fillable = $model->getFillable();
        $find = "";
        $where = "";
        $columns = str_replace("!", "", $columns);
        foreach(explode("\n", $columns) as $column) {
            preg_match("/^\s*(.*?):.*$/", $column, $colname);
            if(isset($colname[1]) && in_array($colname[1], $fillable) && !in_array($colname[1], $hidden)) {
                $find .= $column . " @eq \n    ";
                $where .= $column . " @where(operator: \"like\") \n    ";
            }
        }
        $find = trim($find);
        $where = trim($where);
        $name = Str::snake($reflector->getShortName());
        $plural = Str::plural($name);

        $columns = str_ireplace("\n", " @eq \n    ", $columns);
        $columns2 = str_ireplace("@eq", " @where(operator: \"like\") \n    ", $columns);
        $data = <<<ENDDATA

        extend type Query {
            "Find a $name by an identifying attribute."
            $name(
                "Search by primary key or column."
                id: ID @eq
                $find

            ): {$reflector->getShortName()} @find

            "List multiple $name."
            $plural(
                $where
            ): [{$reflector->getShortName()}!]! @paginate(defaultCount: 10)
        }
        ENDDATA;

        return $data;
    }

    /**
     * @param Model $model
     * @param ReflectionObject $reflector
     * @param string $columns
     *
     * @return string
     */
    private function parseMutations(Model $model, ReflectionObject $reflector, string $columns) {
        $fillable = $model->getFillable();
        $colarray = [];
        foreach(explode("\n", $columns) as $column) {
            preg_match("/^\s*(.*?):.*$/", $column, $colname);
            if(isset($colname[1]) && in_array($colname[1], $fillable)) {
                $colarray[] = trim($column);
            }
        }
        $columns = implode(", ", $colarray);
        $data = <<<ENDDATA

        extend type Mutation {
            create{$reflector->getShortName()}($columns): {$reflector->getShortName()} @create
            update{$reflector->getShortName()}(id: ID, $columns): {$reflector->getShortName()} @update
            delete{$reflector->getShortName()}(id: ID): {$reflector->getShortName()} @delete
        }
        ENDDATA;

        return $data;
    }
}
