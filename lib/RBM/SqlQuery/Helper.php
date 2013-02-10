<?php

namespace RBM\SqlQuery;

class Helper
{

    /**
     * @static
     * @param $arg
     * @param $table
     * @return Column[]
     */
    public static function prepareColumns($arg, $table = null)
    {
        $columns = (!is_array($arg)) ? array($arg) : $arg;
        array_walk($columns, function(&$column) use ($table)
        {
            $column = Helper::prepareColumn($column, $table);
        });
        return $columns;
    }

    /**
     * @param $arg
     * @return Column
     * @throws Exception
     */
    public static function prepareColumn($arg, $table = null)
    {
        if (is_string($arg)) {
            /** @var $table Table */
            $arg = new Column($arg, $table);
        } else if (is_array($arg)){
            $v = array_values($arg);
            $k = array_keys($arg);
            $arg = new Column($v[0], $table, $k[0]);
        } else if (!is_a($arg, '\RBM\SqlQuery\Column')) {
            throw new Exception('Invalid column provided, string or Column expected');
        }
        return $arg;
    }

    /**
     * @static
     * @param $arg
     * @return Table
     * @throws Exception
     */
    public static function prepareTable($arg)
    {
        if (is_string($arg)) {
            $arg = new Table($arg);
        } else if (is_array($arg)){
            $v = array_values($arg);
            $k = array_keys($arg);
            $arg = new Table($v[0]);
            $arg->setAlias($k[0]);
        } else if (!is_a($arg, '\RBM\SqlQuery\Table')) {
            throw new Exception('Invalid table provided, string or Table expected : '.gettype($arg).' given\n');
        }
        return $arg;
    }

    /**
     * @static
     * @param $values
     * @return array
     */
    public static function prepareValues($values)
    {
        array_walk($values, function (&$value){
           $value = Helper::prepareValue($value);
        });
        return $values;
    }

    /**
     * @static
     * @param $value mixed
     * @return int|string
     */
    public static function prepareValue($value)
    {
        if (is_null($value)) {
            return 'NULL';
        } elseif (is_numeric($value)){
            if(is_float($value)){
                return number_format(floatval($value), 10, '.', '');
            } else {
                return intval($value);
            }
        } elseif (is_string($value)) {
            return "'". str_replace("'", "''", $value) ."'";
        } elseif (is_bool($value)) {
            return $value ? 1 : 0;
        }
        return $value;
    }
}
