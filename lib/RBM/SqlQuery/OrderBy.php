<?php

namespace RBM\SqlQuery;

class OrderBy
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    /** @var Column */
    protected $_column;

    /** @var string */
    protected $_direction;

    /** @var boolean */
    protected $_useAlias;

    public function __construct(Column $column, $direction, $useAlias)
    {
        $this->setColumn($column);
        $this->setDirection($direction);
        $this->setUseAlias($useAlias);
    }

    /**
     * @param Column $column
     */
    public function setColumn($column)
    {
        $this->_column = $column;
    }

    /**
     * @return Column
     */
    public function getColumn()
    {
        return $this->_column;
    }

    /**
     * @param string $direction
     */
    public function setDirection($direction)
    {
        $this->_direction = $direction;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->_direction;
    }

    /**
     * @param boolean $useAlias
     */
    public function setUseAlias($useAlias)
    {
        $this->_useAlias = $useAlias;
    }

    /**
     * @return boolean
     */
    public function getUseAlias()
    {
        return $this->_useAlias;
    }


}
