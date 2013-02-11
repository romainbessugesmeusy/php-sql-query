<?php

namespace RBM\SqlQuery;

class Func extends Column
{
    const SUM = 'SUM';
    const COUNT = 'COUNT';
    const SUBSTRING = 'SUBSTRING';
    const DISTINCT = 'DISTINCT';
    const CONVERT = 'CONVERT';

    protected $_args;

    public function __construct($name, $args, $alias)
    {
        $this->_name = $name;
        $this->_args = $args;
        $this->_alias = $alias;
    }

    public function getArgs()
    {
        return $this->_args;
    }


}
