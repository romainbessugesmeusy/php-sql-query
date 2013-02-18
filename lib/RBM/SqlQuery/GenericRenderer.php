<?php
/**
 * php-sql-query
 *
 * @author      Romain Bessuges <romainbessuges@gmail.com>
 * @copyright   2013 Romain Bessuges
 * @link        http://github.com/romainbessugesmeusy/php-sql-query
 * @license     http://github.com/romainbessugesmeusy/php-sql-query
 * @version     0.1
 * @package     php-sql-query
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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

class GenericRenderer implements IRenderer
{
    /**
     * @var bool
     */
    protected $_prettyPrinting = true;

    /**
     * @return bool
     */
    public function getPrettyPrinting()
    {
        return $this->_prettyPrinting;
    }

    /**
     * @param bool $prettyPrinting
     */
    public function setPrettyPrinting($prettyPrinting)
    {
        $this->_prettyPrinting = $prettyPrinting;
    }

    /**
     * @param IQuery $query
     * @return string
     * @throws RendererException
     */
    public function render(IQuery $query)
    {
        if ($query instanceof Select) return $this->_renderSelect($query);
        if ($query instanceof Update) return $this->_renderUpdate($query);
        if ($query instanceof Delete) return $this->_renderDelete($query);
        if ($query instanceof Insert) return $this->_renderInsert($query);

        throw new RendererException("Query must be an instance of Select, Update, Delete or Insert");
    }

    /**
     * @param Select $select
     * @return string
     */
    public function _renderSelectColumns(Select $select)
    {
        $cols = ($select->getForcedColumns()) ? $select->getForcedColumns() : $select->getAllColumns();

        array_walk($cols, function (&$col) {
            $col = $this->_renderColumnWithAlias($col);
        });

        if ($this->getPrettyPrinting()) {
            $prefix    = "\t  ";
            $separator = "\n\t, ";
        } else {
            $prefix    = " ";
            $separator = ", ";
        }
        return $prefix . implode($separator, $cols);
    }

    public function _renderSelectFrom(Select $select)
    {

        $str = "FROM";
        $str .= $this->getPrettyPrinting() ? "\n\t" : " ";
        $str .= $this->_renderTableWithAlias($select->getTable());
        return $str;

    }

    /**
     * @param Select $select
     * @return string
     */
    public function _renderSelectJoins(Select $select)
    {
        $str = "";

        $joins = $select->getAllJoins();

        if (!empty($joins)) {
            array_walk($joins, function (&$join) {
                $join = $this->_renderJoin($join);
            });

            $separator = ($this->getPrettyPrinting()) ? "\n" : " ";
            $str       = implode($separator, $joins);
        }
        return $str;
    }

    /**
     * @param Select $select
     * @return string
     */
    public function _renderSelectWhere(Select $select)
    {
        $str = "";

        $filters = $this->_renderSelectFilters($select->getAllFilters());

        if (count($filters)) {
            $str = "WHERE";

            if ($this->getPrettyPrinting()) {
                $separator = "\n" . $this->_renderConjonction($select->getFilterOperator()) . " ";
            } else {
                $separator = " " . $this->_renderConjonction($select->getFilterOperator()) . " ";
            }

            $str .= implode($separator, $filters);
        }
        return $str;
    }

    /**
     * @param Select $select
     * @return string
     */
    public function _renderSelectGroupBy(Select $select)
    {
        $str = "";
        if (count($select->getGroup())) {

            $groupCols = $select->getGroup();

            array_walk($groupCols, function (&$col) {
                $col = $this->_renderColumn($col);
            });

            $str = "GROUP BY";

            if ($this->getPrettyPrinting()) {
                $str .= "\n\t  ";
                $separator = "\n\t, ";
            } else {
                $str .= " ";
                $separator = ", ";
            }

            $str .= implode($separator, $groupCols);
        }

        return $str;
    }

    /**
     * @param Select $select
     * @return string
     */
    protected function _renderSelect(Select $select)
    {
        if ($select->getIsJoin()) {
            return $this->_renderJoin($select);
        }

        $parts = array();

        $parts[] = "SELECT";
        $parts[] = $this->_renderSelectColumns($select);

        $parts[] = $this->_renderSelectFrom($select);

        if ($joins = $this->_renderSelectJoins($select))
            $parts[] = $joins;

        if ($where = $this->_renderSelectWhere($select))
            $parts[] = $where;

        if ($groupBy = $this->_renderSelectGroupBy($select))
            $parts[] = $groupBy;

        if ($orderBy = $this->_renderSelectOrderBy($select))
            $parts[] = $orderBy;

        if ($limit = $this->_renderSelectLimit($select))
            $parts[] = $limit;


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
    protected function _renderConjonction($operator)
    {
        return $operator;
    }

    /**
     * @return string
     */
    protected function _renderNull()
    {
        return " NULL ";
    }

    /**
     * @return string
     */
    protected function _renderIsNull()
    {
        return " IS NULL ";
    }

    /**
     * @return string
     */
    protected function _renderIsNotNull()
    {
        return " IS NOT NULL ";
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
        $sql .= "JOIN";
        if ($this->getPrettyPrinting()) {
            $sql .= "\n\t";
            $on = "\n\tON ";
        } else {
            $sql .= " ";
            $on = " ON ";
        }
        $sql .= $this->_renderTableWithAlias($select->getTable());
        $sql .= $on;
        $sql .= $this->_renderFilter($select->getJoinCondition(), 1);
        return $sql;
    }

    /**
     * @todo make it better
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
        array_walk($filters, function (& $filter) {
            $filter = $this->_renderFilter($filter);
        });
        return $filters;
    }

    /**
     * @param Filter $filter
     * @return string
     */
    protected function _renderFilter(Filter $filter, $depth = 0)
    {
        if ($filter->isEmpty()) {
            return '';
        }

        $indent  = str_repeat("\t", $depth);
        $clauses = $this->_renderFilterClauses($filter, $depth);
        return implode("\n$indent " . $this->_renderConjonction($filter->getConjonction()) . " ", $clauses);

    }

    /**
     * @param Filter $filter
     * @param int $depth
     * @return array
     */
    protected function _renderFilterClauses(Filter $filter, $depth = 0)
    {

        $depth++;
        $indent = str_repeat("\t", $depth);

        $ins         = $this->_renderFilterIns($filter);
        $notIns      = $this->_renderFilterNotIns($filter);
        $betweens    = $this->_renderFilterBetweens($filter);
        $comparisons = $this->_renderFilterComparisons($filter);
        $isNulls     = $this->_renderFilterIsNulls($filter);
        $isNotNulls  = $this->_renderFilterIsNotNulls($filter);
        $booleans    = $this->_renderFilterBooleans($filter);

        $clauses = array_merge($ins, $notIns, $betweens, $comparisons, $isNulls, $isNotNulls, $booleans);

        foreach ($filter->getSubFilters() as $subFilter) {
            $clauses[] = "(\n{$this->_renderFilter($subFilter, $depth + 1)} \n$indent)\n";
        }

        array_walk($clauses, function (&$clause) use ($depth, $indent) {
            $clause = "\n" . $indent . $clause;
        });

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
                . $this->_renderColumn($between["subject"])
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
            $str .= $this->_renderConjonction($comparison["conjonction"]);
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
            $isNull = "( " . $this->_renderColumn($isNull["subject"]) . $this->_renderIsNull() . " )";
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
            $isNotNull = "( " . $this->_renderColumn($isNotNull["subject"]) . $this->_renderIsNotNull() . " )";
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
                . $this->_renderColumn($boolean["subject"])
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
    protected function _renderTableSchema(Table $table)
    {
        return $table->getSchema();
    }

    /**
     * @param Table $table
     * @return string
     */
    protected function _renderTable(Table $table)
    {
        $schema = ($table->getSchema()) ? "{$this->_renderTableSchema($table->getSchema())}." : '';
        return $schema . $this->_renderTableName($table);
    }

    /**
     * @param Table $table
     * @return string
     */
    protected function _renderTableName(Table $table)
    {
        return $table->getName();
    }

    /**
     * @param Table $table
     * @return string
     */
    protected function _renderTableWithAlias(Table $table)
    {
        $alias  = ($table->getAlias()) ? " AS {$table->getAlias()}" : '';
        $schema = ($table->getSchema()) ? "{$this->_renderTableSchema($table)}." : '';
        return $schema . $this->_renderTableName($table) . $alias;
    }

    /**
     * @param Select $select
     * @return string
     */
    protected function _renderSelectOrderBy(Select $select)
    {
        $str = "";

        if ($cnt = count($select->getAllOrderBy())) {

            $orderBys = $select->getAllOrderBy();

            array_walk($orderBys, function (&$orderBy) {
                $orderBy = $this->_renderOrderBy($orderBy);
            });

            $str = "ORDER BY";
            if ($this->getPrettyPrinting()) {
                $str .= ($cnt > 1) ? "\n\t  " : "\n\t";
                $separator = "\n\t, ";
            } else {
                $str .= " ";
                $separator = ", ";
            }
            $str .= implode($separator, $orderBys);
        }

        return $str;
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

        return "{$table}.{$this->_renderColumnName($column)}";
    }

    /**
     * @param Column $column
     * @return string
     */
    protected function _renderColumnName(Column $column)
    {
        $name = $column->getName();
        if ($name === Column::ALL)
            return $this->_renderColumnAll();

        return $name;
    }

    /**
     * @param Column $column
     * @return string
     */
    protected function _renderColumnWithAlias(Column $column)
    {
        if (($alias = $column->getAlias()) && !$column->isAll())
            return $this->_renderColumn($column) . " AS " . $alias;

        return $this->_renderColumn($column);
    }

    /**
     * @param Column $column
     * @return string
     */
    protected function _renderColumnAll()
    {
        return '*';
    }

    /**
     * @param Select $select
     * @return string
     */
    protected function _renderSelectLimit(Select $select)
    {
        $mask = is_null($select->getLimitStart()) ? '0' : '1';
        $mask .= is_null($select->getLimitCount()) ? '0' : '1';
        $separator = ($this->getPrettyPrinting()) ? "\n\t" : ' ';
        switch ($mask) {
            case '10':
                return "LIMIT{$separator}{$select->getLimitStart()}";
            case '11':
                return "LIMIT{$separator}{$select->getLimitStart()}, {$select->getLimitCount()}";
            case '01':
                return "LIMIT{$separator}0, {$select->getLimitCount()}";
        }
        return '';
    }
}