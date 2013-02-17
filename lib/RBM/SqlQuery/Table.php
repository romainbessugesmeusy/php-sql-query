<?php

namespace RBM\SqlQuery;

class Table
{
    /** @var string */
    protected $_name;
    /** @var string */
    protected $_alias;
    /** @var string */
    protected $_schema;
    /** @var bool */
    protected $_view = false;

    /**
     * @param array|string $name use array to quickly set the alias : ["p" => "project"]
     * @param string|null $schema
     */
    public function __construct($name, $schema = null)
    {
        if (is_array($name)) {
            $keys         = array_keys($name);
            $this->_alias = reset($keys);
            $this->_name  = reset($name);
        } else {
            $this->_name = $name;
        }

        if (!is_null($schema)) {
            $this->_schema = $schema;
        }
    }

    /**
     * @param boolean $view
     */
    public function setView($view)
    {
        $this->_view = ($view);
    }

    /**
     * @return bool
     */
    public function isView()
    {
        return $this->_view;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
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
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * @return string
     */
    public function getCompleteName()
    {
        $alias  = ($this->_alias) ? " AS {$this->_alias}" : '';
        $schema = ($this->_schema) ? "{$this->_schema}." : '';
        return $schema . $this->_name . $alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->_alias = $alias;
    }

    /**
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * @param string|null $schema
     */
    public function setSchema($schema)
    {
        $this->_schema = $schema;
    }
}
