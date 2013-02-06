<?php

namespace RBM\SqlQuery;

class Table
{


    protected $_name;

    protected $_alias;

    protected $_schema;

    protected $_view = false;

    public function __construct($name, $schema = null)
    {
        if (is_array($name)) {
            $keys = array_keys($name);
            $this->_alias = reset($keys);
            $this->_name = reset($name);
        } else {
            $this->_name = $name;
        }

        if (!is_null($schema)) {
            $this->_schema = $schema;
        }
    }

    public function setView($view)
    {
        $this->_view = ($view);
    }

    public function isView()
    {
        return $this->_view;
    }

    public function __toString()
    {
        return $this->_schema . '.' . strtoupper($this->_name);
    }

    public function getCompleteName()
    {
        $alias = ($this->_alias) ? " AS {$this->_alias}" : '';
        return $this->_schema . '.' . strtoupper($this->_name).$alias;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setAlias($alias)
    {
        $this->_alias = $alias;
    }

    public function getAlias()
    {
        return $this->_alias;
    }

    public function setSchema($schema)
    {
        $this->_schema = $schema;
    }

    public function getSchema()
    {
        return $this->_schema;
    }
}
