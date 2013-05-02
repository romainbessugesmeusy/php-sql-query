<?php

namespace RBM\SqlQuery;

class Factory
{

    const TYPE_SELECT = 'select';
    const TYPE_INSERT = 'insert';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const TYPE_FILTER = 'filter';

    const ALL_TABLES = "*";

    /** @var array */
    protected static $_tableClassMap = array(
        self::ALL_TABLES => array(
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
    public static function select($table, $cols = null)
    {
        return self::_createObject($table, self::TYPE_SELECT, $cols);
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
        $table = Helper::prepareTable($table);
        $id    = $table->getSchema() . $table->getName();

        foreach ($map as $type => $class) {
            $class = ($class[0] != '\\') ? '\\' . $class : $class;

            self::$_tableClassMap[$id][$type] = $class;
        }
    }

    public static function setClassMap($map)
    {
        foreach ($map as $table => $tableMap) {
            self::setClassMapForTable($table, $tableMap);
        }
    }

    /**
     * @param $className
     * @return null|string
     */
    public static function getTableForClass($className)
    {
        if ($className[0] != '\\') {
            $className = '\\' . $className;
        }

        foreach (self::$_tableClassMap as $table => $classMap) {

            if ($table != self::ALL_TABLES && in_array($className, $classMap)) {
                return $table;
            }
        }
        return null;
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
            : self::$_tableClassMap[self::ALL_TABLES][$which];
    }

    /**
     * @param $table
     * @param $which
     * @param array|null $cols
     * @return Filter|IQuery|Select
     */
    private static function _createObject($table, $which, $cols = null)
    {
        $className = self::_getClassForTable(Helper::prepareTable($table), $which);
        /** @var IQuery|Filter|Select $object */
        $object = new $className;
        $object->setTable($table);

        if ($object instanceof Select && !is_null($cols))
            $object->setColumns($cols);

        return $object;
    }
}