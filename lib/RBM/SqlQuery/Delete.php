<?php

namespace RBM\SqlQuery;
/**
 * Created by JetBrains PhpStorm.
 * User: rbessuges
 * Date: 01/09/12
 * Time: 00:33
 * To change this template use File | Settings | File Templates.
 */
class Delete extends AbstractQuery
{
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
     * @return Filter
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @param Filter $filter
     */
    public function setFilter($filter)
    {
        $this->_filter = $filter;
    }


}
