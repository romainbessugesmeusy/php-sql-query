<?php

namespace RBM\SqlQuery;

class Update extends AbstractQuery
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
}
