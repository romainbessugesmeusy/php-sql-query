<?php

namespace RBM\SqlQuery;

class Insert extends AbstractQuery
{
    /** @var array */
    protected $_values;

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
     * @return Column[]
     */
    public function getColumns()
    {
        return Helper::prepareColumns(array_keys($this->_values), $this->getTable());
    }
}
