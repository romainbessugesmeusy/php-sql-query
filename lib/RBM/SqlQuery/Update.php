<?php

namespace RBM\SqlQuery;

class Update
{

    /** @var Table */
    protected $_table;

    /** @var Filter */
    protected $_filter;

    /** @var array */
    protected $_values;

    /** @var string */
    protected $_filterClass = '\RBM\SqlQuery\Filter';

    public function filter()
    {
        $cls = $this->_filterClass;

        if (is_null($this->_filter)) {
            $this->_filter = new $cls();
            $this->_filter->setTable($this->getTable());

        }
        return $this->_filter;
    }

    /**
     * @param Filter $filter
     */
    public function setFilter($filter)
    {
        $this->_filter = $filter;
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->_filter;
    }

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

    /**
     * @param string $filterClass
     */
    public function setFilterClass($filterClass)
    {
        $this->_filterClass = $filterClass;
    }

    /**
     * @return string
     */
    public function getFilterClass()
    {
        return $this->_filterClass;
    }
}
