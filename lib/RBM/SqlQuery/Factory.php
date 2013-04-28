<?php

namespace RBM\SqlQuery;

class Factory
{

    const TYPE_SELECT = 'select';
    const TYPE_INSERT = 'insert';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const TYPE_FILTER = 'filter';

    /** @var array */
    protected static $_tableClassMap = array(
        "*" => array(
            "filter" => '\RBM\SqlQuery\Filter',
            "select" => '\RBM\SqlQuery\Select',
            "insert" => '\RBM\SqlQuery\Insert',
            "update" => '\RBM\SqlQuery\Update',
            "delete" => '\RBM\SqlQuery\Delete',
        )
    );

    /**
     * @param $table
     * @return Select
     */
    public static function select($table)
    {
        return self::_createObject($table, self::TYPE_SELECT);
    }

    /**
     * @param $table
     * @return Filter
     */
    public static function filter($table)
    {
        return self::_createObject($table, self::TYPE_FILTER);
    }

    /**
     * @param $table
     * @return Insert
     */
    public static function insert($table)
    {
        return self::_createObject($table, self::TYPE_INSERT);
    }

    /**
     * @param $table
     * @return Update
     */
    public static function update($table)
    {
        return self::_createObject($table, self::TYPE_UPDATE);
    }

    /**
     * @param $table
     * @return Delete
     */
    public static function delete($table)
    {
        return self::_createObject($table, self::TYPE_DELETE);
    }


    /**
     * @param $table
     * @param $map
     */
    public static function setClassMapForTable($table, $map)
    {
        $table                     = Helper::prepareTable($table);
        $id                        = $table->getSchema() . $table->getName();
        self::$_tableClassMap[$id] = $map;
    }

    public static function setClassMap($map)
    {
        foreach ($map as $table => $tableMap) {
            self::setClassMapForTable($table, $tableMap);
        }
    }

    /**
     * @param $table
     * @param $which
     * @return string
     */
    private static function _getClassForTable(Table $table, $which)
    {
        $id = $table->getSchema() . $table->getName();
        return (isset(self::$_tableClassMap[$id]) && isset(self::$_tableClassMap[$id][$which])) ?
            self::$_tableClassMap[$id][$which]
            : self::$_tableClassMap['*'][$which];
    }

    /**
     * @param $table
     * @param $which
     * @return Filter|IQuery
     */
    private static function _createObject($table, $which)
    {
        $className = self::_getClassForTable(Helper::prepareTable($table), $which);
        /** @var IQuery|Filter $object */
        $object = new $className;
        $object->setTable($table);
        return $object;
    }
}