<?php

namespace RBM\SqlQuery;
/**
 * Created by JetBrains PhpStorm.
 * User: rbessuges
 * Date: 31/08/12
 * Time: 22:24
 * To change this template use File | Settings | File Templates.
 */
class Column
{

    const ALL = '*';

    /** @var Table */
    protected $_table;

    /** @var string */
    protected $_name;

    /** @var string */
    protected $_alias;

    public function __construct($name, $table, $alias = null)
    {
        $this->setName($name);
        $this->setTable($table);
        $this->setAlias($alias);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string|Table $table
     */
    public function setTable($table)
    {
        $this->_table = Helper::prepareTable($table);
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        if(is_null($alias))
            return $this->_alias = null;

        if($this->isAll())
            throw new Exception("Can't use alias because column name is ALL (*)");

        $this->_alias = (string) $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * @return bool
     */
    public function isAll()
    {
        return $this->getName() == self::ALL;
    }
}
