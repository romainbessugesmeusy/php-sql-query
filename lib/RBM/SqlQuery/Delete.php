<?php

namespace RBM\SqlQuery;
/**
 * Created by JetBrains PhpStorm.
 * User: rbessuges
 * Date: 01/09/12
 * Time: 00:33
 * To change this template use File | Settings | File Templates.
 */
class Delete implements IQuery
{
    /** @var string|Table */
    protected $_table;
    /** @var Filter */
    protected $_filter;

    public function filter()
    {
        if(is_null($this->_filter)){
            $this->_filter = new Filter();
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
     * @param Table|string $table
     */
    public function setTable($table)
    {
        $this->_table = $table;
    }

    /**
     * @return Table|string
     */
    public function getTable()
    {
        return Helper::prepareTable($this->_table);
    }


}
