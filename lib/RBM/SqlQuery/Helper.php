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

        $preparedColumns = array();

        foreach ($columns as $index => $column) {
            $column = Helper::prepareColumn($column, $table);
            if (!is_int($index))
                $column->setAlias($index);

            $preparedColumns[] = $column;
        }
        return $preparedColumns;
    }


    /**
     * @param string|array|Column[] $arg
     * @param Table|string $table
     * @return Column
     * @throws Exception
     */
    public static function prepareColumn($arg, $table = null)
    {
        if (is_string($arg)) {
            /** @var $table Table */
            $arg = new Column($arg, $table);
        } else if (is_array($arg)) {
            $v   = array_values($arg);
            $k   = array_keys($arg);
            $arg = new Column($v[0], $table, $k[0]);
        } else if (!is_a($arg, '\RBM\SqlQuery\Column')) {
            throw new Exception('Invalid column provided, string or \RBM\SqlQuery\Column expected');
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
        } else if (is_array($arg)) {
            $v   = array_values($arg);
            $k   = array_keys($arg);
            $arg = new Table($v[0]);
            $arg->setAlias($k[0]);
        } else if (!is_a($arg, '\RBM\SqlQuery\Table')) {
            throw new Exception('Invalid table provided, string or \RBM\SqlQuery\Table expected : ' . gettype($arg) . ' given\n');
        }
        return $arg;
    }
}
