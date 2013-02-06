<?php

namespace RBM\SqlQuery;

class Select
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
    /** @var Column[] */
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
    protected $_camelizedTableName = "";
    /** @var bool */
    protected $_isJoin = false;
    /** @var string */
    protected $_joinType;


    /**
     * @param Table $table
     * @param string|array|Column[] $cols
     */
    public function __construct(/**  */)
    {
        $camelizedTableName = $this->_getCamelizedTableName();

        if (get_called_class() === '\RBM\SqlQuery\Select') {
            if (func_num_args() > 0) {
                $this->setTable(func_get_arg(0));
            }
            if (func_num_args() > 1) {
                $this->setColumns(func_get_arg(1));
            }
        } else {
            if (!isset($this->_table)) {
                $this->_table = new Table('TBL_' . preg_replace('#([a-z])([A-Z])#', '$1_$2', $camelizedTableName));
            }
            if ($this->_filterClass === 'Filter') {
                //$this->_guessFilterClass();
            }
        }
    }


    public function __clone()
    {
        return unserialize(serialize($this));
    }

    /**
     * @return string
     */
    public function getSelectSql()
    {

    }

    /**
     * @return string
     */
    public function getJoinSql()
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->_isJoin) {
            try {
                return $this->getJoinSql();
            } catch (Exception $e) {
                return "ERROR BUILDING JOIN : " . $e->getMessage();
            }
        }
        try {
            return $this->getSelectSql();
        } catch (Exception $e) {
            return "ERROR BUILDING SELECT : " . $e->getMessage();
        }
    }

    /**
     * @param $table
     * @return Select
     */
    public function join($table, $selfColumn = null, $refColumn = null)
    {
        $table = Helper::prepareTable($table);
        $key = $table->getCompleteName();

        if (isset($this->_joins[$key])) {
            return $this->_joins[$key];
        }

        $selectClass = preg_replace_callback('#([A-Z]+)#', function($matches)
        {
            return ucfirst(strtolower($matches[1]));
        }, $table->getName());

        //todo supprimer la référence à TBL_
        $selectClass = preg_replace('#^Tbl_#', '', $selectClass) . '_Select';

        if (!class_exists($selectClass)) {
            $selectClass = '\RBM\SqlQuery\Select';
        }

        /** @var $select Select */
        $select = new $selectClass();

        if ($selectClass == '\RBM\SqlQuery\Select') {
            $select->setTable($table);
        }

        if (!is_null($selfColumn)) {
            if (is_null($refColumn)) {
                $refColumn = $selfColumn;
            }

            $select->joinCondition()->equals($refColumn, Helper::prepareColumn($selfColumn, $this->getTable()));
        }
        return $this->addJoin($select);
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

    public function getOrderBy()
    {
        return $this->_orderBy;
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
     * @param $table Table|string
     */
    public function hasJoin($table)
    {
        $table = Helper::prepareTable($table);
        return isset($this->_joins[(string)$table]);
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
     * @internal param $cols
     * @return Select
     */
    public function cols()
    {
        $this->_columns = func_get_args();
        return $this;
    }

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
        if($this->getTable()->isView()){
            $select->removeOrder();
        }
        return "SELECT COUNT(*) FROM ($select) AS tmp";
    }

    /**
     * @param string|Column $column
     * @param string $direction
     * @param null|string $table
     * @return Select
     */
    public function orderBy($column, $direction = OrderBy::ASC, $table = null, $useAlias = true)
    {
        $column = Helper::prepareColumn($column, is_null($table) ? $this->getTable() : $table);
        $this->_orderBy[] = new OrderBy($column, $direction, $useAlias);
        return $this;
    }

    /**
     * Supprime toutes les clauses de tri
     */
    public function removeOrder()
    {
        $this->_orderBy = array();
    }

    /**
     * @param $start int
     * @param $count int
     * @return Select
     */
    public function limit($start, $count)
    {
        $this->_limitStart = intval($start);
        $this->_limitCount = intval($count);
        return $this;
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
    public function getFilterClass()
    {
        return $this->_filterClass;
    }

    /**
     * @param $table string|Table
     */
    public function setTable($table)
    {
        $this->_table = $table;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return Helper::prepareTable($this->_table);
    }

    /**
     * @param $joins Select[]
     */
    public function setJoins($joins)
    {
        $this->_joins = $joins;
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
     * @param $columns
     */
    public function setGroup($columns)
    {
        $this->_group = $columns;
    }

    /**
     * @return Column[]
     */
    public function getGroup()
    {
        return Helper::prepareColumns($this->_group, $this->getTable());
    }

    /**
     * @param $forcedColumns
     */
    public function setForcedColumns($forcedColumns)
    {
        $this->_forcedColumns = $forcedColumns;
    }

    /**
     * @return mixed
     */
    public function getForcedColumns()
    {
        return $this->_forcedColumns;
    }

    /**
     * Transforms Select in a joint
     */
    public function isJoin($isJoin = true)
    {
        $this->_isJoin = $isJoin;
    }

    /**
     * @param $joinCondition Filter
     */
    public function setJoinCondition($joinCondition)
    {
        $this->_joinCondition = $joinCondition;
    }

    /**
     * @return Filter
     */
    public function getJoinCondition()
    {
        return $this->_joinCondition;
    }

    /**
     * @param $joinType string
     */
    public function setJoinType($joinType)
    {
        $this->_joinType = $joinType;
    }

    /**
     * @return string
     */
    public function getJoinType()
    {
        return $this->_joinType;
    }

    /**
     * @param $columns array
     */
    public function setColumns($columns)
    {
        $this->_columns = $columns;
    }

    public function getColumns()
    {
        return Helper::prepareColumns($this->_columns, $this->getTable());
    }

    private function _getCamelizedTableName()
    {
        if (!$this->_camelizedTableName) {
            $this->_camelizedTableName = preg_replace('#_Select$#', '', get_class($this));
        }
        return $this->_camelizedTableName;
    }


    private function _guessFilterClass()
    {
        $camelizedTableName = $this->_getCamelizedTableName();
        $filterCls = $camelizedTableName . '_Filter';
        if (class_exists($filterCls)) {
            $this->_filterClass = $filterCls;
        }
    }


}
