<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 07/02/13
 * Time: 14:51
 * To change this template use File | Settings | File Templates.
 */

namespace RBM\SqlQuery;

use RBM\SqlQuery\IRenderer;
use RBM\SqlQuery\Column;
use RBM\SqlQuery\Delete;
use RBM\SqlQuery\Filter;
use RBM\SqlQuery\Func;
use RBM\SqlQuery\Helper;
use RBM\SqlQuery\Insert;
use RBM\SqlQuery\OrderBy;
use RBM\SqlQuery\RendererException;
use RBM\SqlQuery\Select;
use RBM\SqlQuery\Table;
use RBM\SqlQuery\Update;

abstract class AbstractRenderer implements IRenderer
{
    public function render(IQuery $query)
    {
        if ($query instanceof Select) return $this->_renderSelect($query);
        if ($query instanceof Update) return $this->_renderUpdate($query);
        if ($query instanceof Delete) return $this->_renderDelete($query);
        if ($query instanceof Insert) return $this->_renderInsert($query);

        throw new RendererException("Query must be an instance of Select, Update, Delete or Insert");
    }

    protected function _renderSelect(Select $select)
    {
        if ($select->getIsJoin()) {
            return $this->_renderJoin($select);
        }

        $parts = array();
        $cols  = ($select->getForcedColumns()) ? $select->getForcedColumns() : $select->getAllColumns();

        array_walk($cols, function (&$col) {
            $col = $this->_renderColumnWithAlias($col);
        });

        $parts[] = "SELECT";
        $parts[] = "\t" . implode("\n\t, ", $cols);
        $parts[] = "FROM";
        $parts[] = "\t" . $this->_renderTableWithAlias($select->getTable());

        $joins = $select->getAllJoins();

        array_walk($joins, function (&$join) {
            $join = $this->_renderJoin($join);
        });

        $parts[] = implode("\n", $joins);

        $filters = $this->_renderSelectFilters($select->getAllFilters());

        if (count($filters)) {
            $parts[] = "WHERE";
            $parts[] = implode($select->getFilterOperator() . ' ', $filters);
        }

        if (count($select->getGroup())) {

            $groupCols = $select->getGroup();

            array_walk($groupCols, function (&$col) {
                $col = $this->_renderColumn($col);
            });

            $parts[] = "GROUP BY";
            $parts[] = "\t" . implode(', ', $groupCols);
        }


        if (count($select->getAllOrderBy())) {

            $orderBys = $select->getAllOrderBy();

            array_walk($orderBys, function (&$orderBy) {
                $orderBy = $this->_renderOrderBy($orderBy);
            });

            $parts[] = "ORDER BY";
            $parts[] = "\t" . implode(', ', $orderBys);
        }

        $parts[] = $this->_renderLimit($select);

        $sql = implode("\n", $parts);

        return $sql;
    }

    protected function _renderUpdate(Update $update)
    {
        $table = $this->_renderTable($update->getTable());
        $sql   = "UPDATE {$table} SET ";

        $assigns = array();
        foreach ($update->getValues() as $col => $value) {
            $col       = $this->_renderColumn(Helper::prepareColumn($col, $update->getTable()));
            $assigns[] = "$col = $value";
        }
        $sql .= implode(", ", $assigns);
        if (!is_null($update->getFilter())) {
            $sql .= " WHERE {$this->_renderFilter($update->getFilter())}";
        }
        return $sql;
    }

    /**
     * @param Insert $insert
     * @return string
     */
    protected function _renderInsert(Insert $insert)
    {
        $cols = $insert->getColumns();
        $vals = $insert->getValues();

        array_walk($cols, function (&$col) {
            $col = $this->_renderColumn($col);
        });

        array_walk($vals, function (&$val) {
            $val = $this->_renderValue($val);
        });

        $cols  = implode(", ", $cols);
        $vals  = implode(", ", $vals);
        $table = $this->_renderTable($insert->getTable());
        return "INSERT INTO {$table} ($cols) VALUES ($vals)";
    }

    /**
     * @param Delete $delete
     * @return string
     */
    protected function _renderDelete(Delete $delete)
    {
        $table = $this->_renderTable($delete->getTable());
        $sql   = "DELETE FROM {$table}";
        if ($delete->getFilter()) {
            $sql .= " WHERE {$this->_renderFilter($delete->getFilter())}";
        }
        return $sql;
    }

    /**
     * @param $value
     * @return string
     */
    protected function _renderValue($value)
    {
        if (is_string($value)) return "'" . str_replace("'", "''", $value) . "'";
        if (is_bool($value)) return $this->_renderBoolean($value);
        if ($value instanceof IQuery) return $this->render($value);
        return $value;
    }

    /**
     * @param $values
     * @return mixed
     */
    protected function _renderValues($values)
    {
        array_walk($values, function (&$value) {
            $value = $this->_renderValue($value);
        });
        return $values;
    }

    /**
     * @param $operator
     * @return mixed
     */
    protected function _renderOperator($operator)
    {
        return $operator;
    }

    /**
     * @return string
     */
    protected function _renderNull()
    {
        return "NULL";
    }

    /**
     * @return string
     */
    protected function _renderIsNull()
    {
        return "IS NULL";
    }

    /**
     * @return string
     */
    protected function _renderIsNotNull()
    {
        return "IS NOT NULL";
    }

    /**
     * @param $value
     * @return string
     */
    protected function _renderBoolean($value)
    {
        return ($value) ? "1" : "0";
    }

    /**
     * @param Select $select
     * @return string
     */
    protected function _renderJoin(Select $select)
    {
        $sql = ($select->getJoinType()) ? "{$select->getJoinType()} " : "";
        return $sql . "JOIN\n\t{$this->_renderTableWithAlias($select->getTable())} ON {$this->_renderFilter($select->getJoinCondition())}";
    }

    /**
     * @param \RBM\SqlQuery\Func $func
     * @return string
     */
    protected function _renderFunc(Func $func)
    {
        $name   = $func->getName();
        $format = "$name(%s)";
        return sprintf($format, implode(', ', $func->getArgs()));
    }

    /**
     * @param array $filters
     * @return array
     */
    protected function _renderSelectFilters(array $filters)
    {
        $filtersStr = array();
        foreach ($filters as $filter) {
            if ($str = trim((string)$this->_renderFilter($filter), "\t\n ")) {
                $filtersStr[] = "\t$str\n";
            }
        }
        return $filtersStr;
    }

    /**
     * @param Filter $filter
     * @return string
     */
    protected function _renderFilter(Filter $filter)
    {
        if ($filter->isEmpty()) {
            return '';
        }

        $clauses = $this->_renderFilterClauses($filter);

        return implode("\n {$this->_renderOperator($filter->getOperator())} ", $clauses);

    }

    /**
     * @param Filter $filter
     * @return array
     */
    protected function _renderFilterClauses(Filter $filter)
    {

        $ins         = $this->_renderFilterIns($filter);
        $notIns      = $this->_renderFilterNotIns($filter);
        $betweens    = $this->_renderFilterBetweens($filter);
        $comparisons = $this->_renderFilterComparisons($filter);
        $isNulls     = $this->_renderFilterIsNulls($filter);
        $isNotNulls  = $this->_renderFilterIsNotNulls($filter);
        $booleans    = $this->_renderFilterBooleans($filter);

        $clauses = array_merge($ins, $notIns, $betweens, $comparisons, $isNulls, $isNotNulls, $booleans);

        $clauses = array_filter($clauses, function ($var) {
            return (trim((string)$var, "\t\n "));
        });

        foreach ($filter->getSubFilters() as $subFilter) {
            $clauses[] = "({$this->_renderFilter($subFilter)})";
        }
        return $clauses;
    }

    /**
     * @param Filter $filter
     * @return array
     */
    protected function _renderFilterIns(Filter $filter)
    {
        $ins = array();

        foreach ($filter->getIns() as $col => $values) {
            $col    = Helper::prepareColumn($col, $filter->getTable());
            $col    = $this->_renderColumn($col);
            $values = $this->_renderValues($values);
            $values = implode(", ", $values);
            $ins[]  = "( {$col} IN ({$values}) )";
        }

        return $ins;
    }

    /**
     * @param Filter $filter
     * @return array
     */
    protected function _renderFilterNotIns(Filter $filter)
    {
        $notIns = array();

        foreach ($filter->getNotIns() as $col => $values) {
            $col      = Helper::prepareColumn($col, $filter->getTable());
            $col      = $this->_renderColumn($col);
            $values   = $this->_renderValues($values);
            $values   = implode(", ", $values);
            $notIns[] = "( {$col} NOT IN ({$values}) )";
        }

        return $notIns;
    }

    /**
     * @param Filter $filter
     * @return array
     */
    protected function _renderFilterBetweens(Filter $filter)
    {
        $betweens = $filter->getBetweens();
        array_walk($betweens, function (&$between) {
            $between = "( "
                . $this->_renderColumn($between["column"])
                . " BETWEEN "
                . $this->_renderValue($between["a"])
                . " AND "
                . $this->_renderValue($between["b"])
                . " )";
        });

        return $betweens;
    }

    /**
     * @param Filter $filter
     * @return array
     */
    protected function _renderFilterComparisons(Filter $filter)
    {
        $comparisons = $filter->getComparisons();
        array_walk($comparisons, function (&$comparison) {
            $str = ($comparison["subject"] instanceof Column) ? $this->_renderColumn($comparison["subject"]) : $this->_renderValue($comparison["subject"]);
            $str .= $this->_renderOperator($comparison["operator"]);
            if ($comparison["target"] instanceof Column) {
                $str .= $this->_renderColumn($comparison["target"]);
            } elseif ($comparison["target"] instanceof IQuery) {
                $str .= "(\n" . $this->render($comparison["target"]) . "\n)";
            } else {
                $str .= $this->_renderValue($comparison["target"]);
            }
            $comparison = "( $str )";
        });
        return $comparisons;
    }

    /**
     * @param Filter $filter
     * @return array
     */
    protected function _renderFilterIsNulls(Filter $filter)
    {
        $isNulls = $filter->getIsNull();
        array_walk($isNulls, function (&$isNull) {
            $isNull = "( " . $this->_renderColumn($isNull["column"]) . $this->_renderIsNull() . " )";
        });

        return $isNulls;
    }

    /**
     * @param Filter $filter
     * @return array
     */
    protected function _renderFilterIsNotNulls(Filter $filter)
    {
        $isNotNulls = $filter->getIsNotNull();
        array_walk($isNotNulls, function (&$isNotNull) {
            $isNotNull = "( " . $this->_renderColumn($isNotNull["column"]) . $this->_renderIsNotNull() . " )";
        });
        return $isNotNulls;
    }

    /**
     * @param Filter $filter
     * @return array
     */
    protected function _renderFilterBooleans(Filter $filter)
    {
        $booleans = $filter->getBooleans();
        array_walk($booleans, function (&$boolean) {
            $boolean = "(ISNULL("
                . $this->_renderColumn($boolean["column"])
                . ", 0) = "
                . $this->_renderBoolean($boolean["value"])
                . " )";
        });
        return $booleans;
    }

    /**
     * @param Table $table
     * @return string
     */
    protected function _renderTable(Table $table)
    {
        $schema = ($table->getSchema()) ? "{$table->getSchema()}." : '';
        return $schema . $table->getName();
    }

    /**
     * @param Table $table
     * @return string
     */
    protected function _renderTableWithAlias(Table $table)
    {
        $alias  = ($table->getAlias()) ? " AS {$table->getAlias()}" : '';
        $schema = ($table->getSchema()) ? "{$table->getSchema()}." : '';
        return $schema . $table->getName() . $alias;
    }

    /**
     * @param OrderBy $orderBy
     * @return string
     */
    protected function _renderOrderBy(OrderBy $orderBy)
    {
        if (($alias = $orderBy->getColumn()->getAlias()) && $orderBy->getUseAlias()) {
            $col = $alias;
        } else {
            $col = $this->_renderColumn($orderBy->getColumn());
        }
        return $col . ' ' . $orderBy->getDirection();
    }

    /**
     * @param Column $column
     * @return string
     */
    protected function _renderColumn(Column $column)
    {
        if ($column instanceof Func) {
            return $this->_renderFunc($column);
        }

        if ($alias = $column->getTable()->getAlias()) {
            $table = $alias;
        } else {
            $table = $this->_renderTable($column->getTable());
        }

        return "{$table}.{$column->getName()}";
    }

    /**
     * @param Column $column
     * @return string
     */
    protected function _renderColumnWithAlias(Column $column)
    {
        if ($alias = $column->getAlias()) {
            return $this->_renderColumn($column) . " AS " . $alias;
        }
        return $this->_renderColumn($column);
    }

    /**
     * @param Select $select
     * @return string
     */
    protected function _renderLimit(Select $select)
    {
        $mask = is_null($select->getLimitStart()) ? '0' : '1';
        $mask .= is_null($select->getLimitCount()) ? '0' : '1';

        switch ($mask) {
            case '10':
                return "LIMIT {$select->getLimitStart()}";
            case '11':
                return "LIMIT {$select->getLimitStart()}, {$select->getLimitCount()}";
            case '01':
                return "LIMIT 0, {$select->getLimitCount()}";
        }
        return '';
    }
}