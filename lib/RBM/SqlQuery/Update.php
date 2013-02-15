<?php

namespace RBM\SqlQuery;

class Update extends AbstractQuery
{
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
        return $this->_values;
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
