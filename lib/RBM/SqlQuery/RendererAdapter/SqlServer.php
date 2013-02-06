<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 06/02/13
 * Time: 21:49
 * To change this template use File | Settings | File Templates.
 */

namespace RBM\SqlQuery\RendererAdapter;


use RBM\SqlQuery\Column;
use RBM\SqlQuery\Delete;
use RBM\SqlQuery\Filter;
use RBM\SqlQuery\Func;
use RBM\SqlQuery\Helper;
use \RBM\SqlQuery\IRenderer;
use RBM\SqlQuery\Insert;
use RBM\SqlQuery\OrderBy;
use RBM\SqlQuery\RendererException;
use RBM\SqlQuery\Select;
use RBM\SqlQuery\Table;
use RBM\SqlQuery\Update;

class SqlServer implements IRenderer
{

    public function renderOperator($operator)
    {
        return $operator;
    }

    public function renderNull()
    {
        return "NULL";
    }

    public function renderIsNull()
    {
        return "IS NULL";
    }

    public function renderIsNotNull()
    {
        return "IS NOT NULL";
    }

    public function renderBoolean($value)
    {
        return ($value) ? "1" : "0";
    }

    public function renderValue($value)
    {
        if (is_string($value)) return "'" . str_replace("'", "''", $value) . "'";
        if (is_bool($value)) return $this->renderBoolean($value);
        return $value;
    }


    public function renderSelect(Select $select)
    {
        if ($select->getIsJoin()) {
            return $this->renderJoin($select);
        }


        $parts = array();
        $cols = ($select->getForcedColumns()) ? $select->getForcedColumns() : $select->getAllColumns();

        array_walk($cols, function (&$col) {
            $col = $this->renderColumnWithAlias($col);
        });

        $parts[] = "SELECT";
        $parts[] = "\t" . implode("\n\t, ", $cols);
        $parts[] = "FROM";
        $parts[] = "\t" . $this->renderTableWithAlias($select->getTable());

        $joins = $select->getAllJoins();

        array_walk($joins, function (&$join) {
            $join = $this->renderJoin($join);
        });

        $parts[] = implode("\n", $joins);

        $filters = $select->getAllFilters();

        $filtersStr = array();
        foreach ($filters as $filter) {
            if ($str = trim((string)$this->renderFilter($filter), "\t\n ")) {
                $filtersStr[] = "\t$str\n";
            }
        }

        if (count($filtersStr)) {
            $parts[] = "WHERE";
            $parts[] = implode(' AND ', $filtersStr);
        }

        if (count($select->getGroup())) {

            $groupCols = $select->getGroup();

            array_walk($groupCols, function (&$col) {
                $col = $this->renderColumn($col);
            });

            $parts[] = "GROUP BY";
            $parts[] = "\t" . implode(', ', $groupCols);
        }


        if (count($select->getAllOrderBy())) {

            $orderBys = $select->getAllOrderBy();

            array_walk($orderBys, function (&$orderBy) {
                $orderBy = $this->renderOrderBy($orderBy);
            });

            $parts[] = "ORDER BY";
            $parts[] = "\t" . implode(', ', $orderBys);
        }

        $sql = implode("\n", $parts);

        if (!is_null($select->getLimitStart()) || !is_null($select->getLimitCount())) {
            $sql = $this->_applyLimit($select, $sql);
        }

        return $sql;
    }

    public function renderJoin(Select $select)
    {
        $sql = ($select->getJoinType()) ? "{$select->getJoinType()} " : "";
        return $sql . "JOIN\n\t{$this->renderTableWithAlias($select->getTable())} ON {$this->renderFilter($select->getJoinCondition())}";
    }

    /**
     * @param \RBM\SqlQuery\Func $func
     * @return string
     */
    public function renderFunc(Func $func)
    {
        $format = "$func->getName(%s)";
        return sprintf($format, implode(', ', $func->getArgs()));
    }

    public function renderFilter(Filter $filter)
    {
        if ($filter->isEmpty()) {
            return '';
        }

        /**
         * Render the IN (...)
         */
        $ins = array();
        foreach ($filter->getIns() as $col => $values) {
            $col = Helper::prepareColumn($col, $filter->getTable());
            $col = $this->renderColumn($col);
            $ins[] = "( {$col} NOT IN ({$values}) )";
        }

        /**
         * Render the NOT IN (...)
         */
        $notIns = array();
        foreach ($filter->getNotIns() as $col => $values) {
            $col = Helper::prepareColumn($col, $filter->getTable());
            $col = $this->renderColumn($col);
            if (is_array($values)) {
                $values = implode(',', $values);
            } else {
                $values = (string)$values;
            }

            $notIns[] = "( {$col} NOT IN ({$values}) )";
        }


        $betweens = $filter->getBetweens();
        array_walk($betweens, function (&$between) {
            $between = "( "
                . $this->renderColumn($between["column"])
                . " BETWEEN "
                . $this->renderValue($between["a"])
                . " AND "
                . $this->renderValue($between["b"])
                . " )";
        });

        $comparisons = $filter->getComparisons();
        array_walk($comparisons, function (&$comparison) {
            $str = ($comparison["subject"] instanceof Column) ? $this->renderColumn($comparison["subject"]) : $this->renderValue($comparison["subject"]);
            $str .= $this->renderOperator($comparison["operator"]);
            $str .= ($comparison["target"] instanceof Column) ? $this->renderColumn($comparison["target"]) : $this->renderValue($comparison["target"]);
            $comparison = "( $str )";
        });

        $isNulls = $filter->getIsNull();
        array_walk($isNulls, function (&$isNull) {
            $isNull = "( " . $this->renderColumn($isNull["column"]) . $this->renderIsNull() . " )";
        });

        $isNotNulls = $filter->getIsNotNull();
        array_walk($isNotNulls, function (&$isNotNull) {
            $isNotNull = "( " . $this->renderColumn($isNotNull["column"]) . $this->renderIsNotNull() . " )";
        });

        $booleans = $filter->getBooleans();
        array_walk($booleans, function (&$boolean) {
            $boolean = "(ISNULL("
                . $this->renderColumn($boolean["column"])
                . ", 0) = "
                . $this->renderBoolean($boolean["value"])
                . " )";
        });

        $clauses = array_merge($ins, $notIns, $betweens, $comparisons, $isNulls, $isNotNulls, $booleans);

        $clauses = array_filter($clauses, function ($var) {
            return (trim((string)$var, "\t\n "));
        });

        foreach ($filter->getSubFilters() as $subFilter) {
            $clauses[] = "({$this->renderFilter($subFilter)})";
        }

        return implode("\n\t {$filter->getOperator()} ", $clauses);

    }

    public function renderTable(Table $table)
    {
        $schema = ($table->getSchema()) ? "{$table->getSchema()}." : '';
        return $schema . strtoupper($table->getName());
    }

    public function renderTableWithAlias(Table $table)
    {
        $alias = ($table->getAlias()) ? " AS {$table->getAlias()}" : '';
        $schema = ($table->getSchema()) ? "{$table->getSchema()}." : '';
        return $schema . strtoupper($table->getName()) . $alias;
    }


    public function renderUpdate(Update $update)
    {
        $sql = "UPDATE {$this->renderTable($update->getTable())} SET ";

        $assigns = array();
        foreach ($update->getValues() as $col => $value) {
            $col = $this->renderColumn(Helper::prepareColumn($col, $update->getTable()));
            $assigns[] = "$col = $value";
        }
        $sql .= implode(", ", $assigns);
        if (!is_null($update->getFilter())) {
            $sql .= " WHERE {$this->renderFilter($update->getFilter())}";
        }
        return $sql;
    }

    public function renderInsert(Insert $insert)
    {
        $cols = $insert->getColumns();
        $vals = $insert->getValues();

        array_walk($cols, function (&$col) {
            $col = $this->renderColumn($col);
        });

        array_walk($vals, function (&$val) {
            $val = $this->renderValue($val);
        });

        $cols = implode(", ", $cols);
        $vals = implode(", ", $vals);

        return "INSERT INTO {$this->renderTable($insert->getTable())} ($cols) VALUES ($vals)";
    }

    public function renderDelete(Delete $delete)
    {
        $sql = "DELETE FROM {$delete->getTable()}";
        if ($delete->getFilter()) {
            $sql .= " WHERE {$this->renderFilter($delete->getFilter())}";
        }
        return $sql;
    }

    public function renderOrderBy(OrderBy $orderBy)
    {
        if (($alias = $orderBy->getColumn()->getAlias()) && $orderBy->getUseAlias()) {
            $col = $alias;
        } else {
            $col = (string)$orderBy->getColumn();
        }
        return $col . ' ' . $orderBy->getDirection();
    }

    public function renderColumn(Column $column)
    {
        if ($column instanceof Func) {
            return $this->renderFunc($column);
        }

        if ($alias = $column->getTable()->getAlias()) {
            $table = $alias;
        } else {
            $table = $this->renderTable($column->getTable());
        }

        return "{$table}.{$column->getName()}";
    }

    public function renderColumnWithAlias(Column $column)
    {
        if ($alias = $column->getAlias()) {
            return $this->renderColumn($column) . " AS " . $alias;
        }
        return $this->renderColumn($column);
    }

    /**
     * @param $sql
     * @param \RBM\SqlQuery\Select $select
     * @return mixed|string
     * @throws \RBM\SqlQuery\RendererException
     */
    protected function _applyLimit($sql, Select $select)
    {

        // dblog('LIMIT - '.$sql);
        $count = intval($select->getLimitCount());
        if ($count <= 0) {
            throw new RendererException("LIMIT argument count=$count is not valid");
        }

        $offset = intval($select->getLimitStart());
        if ($offset < 0) {
            throw new RendererException("LIMIT argument offset=$offset is not valid");
        }

        if ($offset == 0) {
            $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP ' . $count . ' ', $sql);
        } else {
            $orderby = stristr($sql, 'ORDER BY');

            if (!$orderby) {
                $over = 'ORDER BY (SELECT 0)';
            } else {
                //$over = preg_replace('/\"[^,]*\".\"([^,]*)\"/is', '"inner_tbl"."$1"', $orderby);
                $substr = substr($orderby, 9);
                $orders = explode(',', $substr);
                array_walk($orders, function (&$ord) {
                    $direction = substr($ord, strrpos($ord, ' ') + 1);
                    $col = substr($ord, 0, strrpos($ord, ' '));
                    $ord = 'inner_tbl.' . substr($col, strrpos($col, '.') + 1) . ' ' . $direction;
                });

                $over = 'ORDER BY ' . implode(',', $orders);
            }

            // Remove ORDER BY clause from $sql
            $sql = preg_replace('/\s+ORDER BY(.*)/s', '', $sql);
            // Add ORDER BY clause as an argument for ROW_NUMBER()
            $sql = "SELECT ROW_NUMBER() OVER ($over) AS __rownum__, * FROM ($sql) AS inner_tbl";

            $start = $offset + 1;
            $end = $offset + $count;

            $sql = "WITH outer_tbl AS ($sql) SELECT * FROM outer_tbl WHERE CAST(__rownum__ AS int) BETWEEN $start AND $end";
        }

        return $sql;
    }
}