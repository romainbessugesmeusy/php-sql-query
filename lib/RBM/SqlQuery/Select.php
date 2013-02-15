<?php

namespace RBM\SqlQuery;

class Select extends AbstractQuery
{
    const JOIN_LEFT = 'LEFT';
    const JOIN_RIGHT = 'RIGHT';
    const JOIN_INNER = 'INNER';
    /** @var Table */
    protected $_table;
    /** @var Filter */
    protected $_filter;
    /** @var Filter */
    protected $_joinCondition;
    /** @var Column[] */
    protected $_columns = array();
    /** @var Select[] */
    protected $_joins = array();
    /** @var OrderBy[] */
    protected $_orderBy = array();
    /** @var Column[] */
    protected $_group = array();
    /** @var Column[] */
    protected $_forcedColumns;
    /** @var int */
    protected $_limitStart;
    /** @var int */
    protected $_limitCount;
    /** @var string */
    protected $_filterClass = '\RBM\SqlQuery\Filter';
    /** @var string */
    protected $_filterOperator = "AND";
    /** @var string */
    protected $_camelizedTableName = "";
    /** @var bool */
    protected $_isJoin = false;
    /** @var string */
    protected $_joinType;

    /**
     * @param string|Table|null $table
     * @param string|array|Column[] $cols
     */
    public function __construct($table = null, $cols = "*")
    {
        if ($table)
            $this->setTable($table);

        if ($cols)
            $this->setColumns($cols);
    }

    public function __clone()
    {
        return unserialize(serialize($this));
    }

    /**
     * @param $table
     * @param null $selfColumn
     * @param null $refColumn
     * @param array $columns
     * @param string $selectClass
     * @return Select
     */
    public function join($table, $selfColumn = null, $refColumn = null, $columns = array(), $selectClass = '\RBM\SqlQuery\Select')
    {
        $table = Helper::prepareTable($table);
        $key = $table->getCompleteName();

        if (isset($this->_joins[$key])) {
            return $this->_joins[$key];
        }

        /** @var $select Select */
        $select = new $selectClass($table, $columns);

        if (!is_null($selfColumn)) {
            if (is_null($refColumn)) {
                $refColumn = $selfColumn;
            }

            $select->joinCondition()->equals($refColumn, Helper::prepareColumn($selfColumn, $this->getTable()));
        }
        return $this->addJoin($select);
    }

    /**
     * @return Filter
     */
    public function joinCondition()
    {
        if (!isset($this->_joinCondition)) {
            $cls = $this->_filterClass;
            $this->_joinCondition = new $cls();
            $this->_joinCondition->setTable($this->_table);
        }
        return $this->_joinCondition;

    }

    /**
     * @return boolean
     */
    public function getIsJoin()
    {
        return $this->_isJoin;
    }

    /**
     * @return int
     */
    public function getLimitCount()
    {
        return $this->_limitCount;
    }

    /**
     * @return int
     */
    public function getLimitStart()
    {
        return $this->_limitStart;
    }

    /**
     * @return OrderBy[]
     */
    public function getOrderBy()
    {
        return $this->_orderBy;
    }

    /**
     * @param Table|string $table
     * @return bool
     */
    public function hasJoin($table)
    {
        $table = Helper::prepareTable($table);
        return isset($this->_joins[(string)$table]);
    }

    /**
     * @internal param $cols
     * @return Select
     */
    public function cols()
    {
        $this->_columns = func_get_args();
        return $this;
    }

    /**
     * @param string|Column $column
     * @return $this
     */
    public function addColumn($column)
    {
        $this->_columns[] = $column;
        return $this;
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
    public function setFilter(Filter $filter)
    {
        $this->_filter = $filter;
    }

    /**
     * @return Filter
     */
    public function filter()
    {
        if (!isset($this->_filter)) {
            $cls = $this->_filterClass;
            /** @var $_filter Filter */
            $this->_filter = new $cls();
            $this->_filter->setTable($this->_table);
        }
        return $this->_filter;
    }

    /**
     * @return array
     */
    public function getAllColumns()
    {
        $cols = $this->getColumns();

        /** @var $join Select */
        foreach ($this->_joins as $join) {
            $joinCols = $join->getAllColumns();
            $cols = array_merge($cols, $joinCols);
        }
        return $cols;
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return Helper::prepareColumns($this->_columns, $this->getTable());
    }

    /**
     * @param $columns array
     */
    public function setColumns($columns)
    {
        if(!is_array($columns)){
            $columns = array($columns);
        }
        $this->_columns = $columns;
    }

    /**
     * Transforms Select in a joint
     */
    public function isJoin($isJoin = true)
    {
        $this->_isJoin = $isJoin;
    }


    /**
     * Supprime toutes les clauses de tri
     */
    public function removeOrder()
    {
        $this->_orderBy = array();
    }

    /**
     * @param Select $select
     * @param string $selfColumn
     * @param string $refColumn
     * @return Select
     */
    public function addJoin(Select $select, $selfColumn = null, $refColumn = null)
    {
        $key = (string)$select->getTable()->getCompleteName();
        if (isset($this->_joins[$key])) {
            return $this->_joins[$key];
        }

        $select->isJoin();

        if (!is_null($selfColumn)) {
            if (is_null($refColumn)) {
                $refColumn = $selfColumn;
            }

            $select->joinCondition()->equals($refColumn, Helper::prepareColumn($selfColumn, $this->getTable()));
        }

        $this->_joins[$key] = $select;

        return $this->_joins[$key];
    }

    /**
     * @return Filter[]
     */
    public function getAllFilters()
    {
        $filters = array();

        if (!is_null($this->_filter)) {
            $filters[] = $this->_filter;
        }

        /** @var $join Select */
        foreach ($this->_joins as $join) {
            $filters = array_merge($filters, $join->getAllFilters());
        }


        return $filters;
    }

    /**
     * @return OrderBy[]
     */
    public function getAllOrderBy()
    {
        $order = $this->_orderBy;
        /** @var $join Select */
        foreach ($this->_joins as $join) {
            $order = array_merge($order, $join->getAllOrderBy());
        }

        return $order;
    }

    /**
     * @return string
     */
    public function count()
    {
        /** @var $select Select */
        $select = clone $this;
        $select->setForcedColumns(array('1 AS tmp'));
        if ($this->getTable()->isView()) {
            $select->removeOrder();
        }
        return "SELECT COUNT(*) FROM ($select) AS tmp";
    }

    /**
     * @param $column
     * @param string $direction
     * @param null $table
     * @param bool $useAlias
     * @return $this
     */
    public function orderBy($column, $direction = OrderBy::ASC, $table = null, $useAlias = true)
    {
        $column = Helper::prepareColumn($column, is_null($table) ? $this->getTable() : $table);
        $this->_orderBy[] = new OrderBy($column, $direction, $useAlias);
        return $this;
    }

    /**
     * @param $start
     * @param $count
     * @return $this
     */
    public function limit($start, $count)
    {
        $this->_limitStart = intval($start);
        $this->_limitCount = intval($count);
        return $this;
    }

    /**
     * @return string
     */
    public function getFilterClass()
    {
        return $this->_filterClass;
    }

    /**
     * @param $filterClass
     */
    public function setFilterClass($filterClass)
    {
        $this->_filterClass = $filterClass;
    }

    /**
     * @return string
     */
    public function getFilterOperator()
    {
        return $this->_filterOperator;
    }

    /**
     * @param string $filterOperator
     */
    public function setFilterOperator($filterOperator)
    {
        $this->_filterOperator = $filterOperator;
    }

    /**
     * @param $joins Select[]
     */
    public function mergeJoins($joins)
    {
        $this->_joins = array_merge($this->_joins, $joins);
    }

    /**
     * @return Select[]
     */
    public function getJoins()
    {
        return $this->_joins;
    }

    /**
     * @param $joins Select[]
     */
    public function setJoins($joins)
    {
        $this->_joins = $joins;
    }

    /**
     * @return Select[]
     */
    public function getAllJoins()
    {
        $joins = $this->_joins;
        /** @var $join Select */
        foreach ($this->_joins as $join) {
            $joins = array_merge($joins, $join->getAllJoins());
        }
        return $joins;
    }

    /**
     * @return Column[]
     */
    public function getGroup()
    {
        return Helper::prepareColumns($this->_group, $this->getTable());
    }

    /**
     * @param $columns
     */
    public function setGroup($columns)
    {
        $this->_group = $columns;
    }

    /**
     * @return mixed
     */
    public function getForcedColumns()
    {
        return $this->_forcedColumns;
    }

    /**
     * @param $forcedColumns
     */
    public function setForcedColumns($forcedColumns)
    {
        $this->_forcedColumns = $forcedColumns;
    }

    /**
     * @return Filter
     */
    public function getJoinCondition()
    {
        return $this->_joinCondition;
    }

    /**
     * @param $joinCondition Filter
     */
    public function setJoinCondition($joinCondition)
    {
        $this->_joinCondition = $joinCondition;
    }

    /**
     * @return string
     */
    public function getJoinType()
    {
        return $this->_joinType;
    }

    /**
     * @param $joinType string
     */
    public function setJoinType($joinType)
    {
        $this->_joinType = $joinType;
    }
}
