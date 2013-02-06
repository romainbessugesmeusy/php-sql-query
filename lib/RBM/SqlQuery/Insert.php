<?php

namespace RBM\SqlQuery;

class Insert
{

    /** @var Table */
    protected $_table;

    /** @var array */
    protected $_values;

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->_table = Helper::prepareTable($table);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @param array $values
     */
    public function setValues($values)
    {
        $this->_values = $values;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return Helper::prepareValues($this->_values);
    }

    public function getColumns()
    {
        return Helper::prepareColumns(array_keys($this->_values), $this->getTable());
    }
}
