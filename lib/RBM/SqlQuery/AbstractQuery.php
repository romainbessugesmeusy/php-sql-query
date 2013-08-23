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

abstract class AbstractQuery implements IQuery
{
    /** @var IRenderer */
    protected static $_defaultRenderer;
    /** @var Table */
    protected $_table;
    /** @var string */
    protected $_filterOperator = "AND";
    /** @var Filter */
    protected $_filter;

    /**
     * @param IRenderer $defaultRenderer
     */
    public static function setDefaultRenderer(IRenderer $defaultRenderer)
    {
        self::$_defaultRenderer = $defaultRenderer;
    }

    /**
     * @return IRenderer
     */
    public static function getDefaultRenderer()
    {
        return self::$_defaultRenderer;
    }

    /**
     * The constructor will look up in the factory classmap
     * to deduce its table, if not defined in class
     */
    public function __construct()
    {
        if (!isset($this->_table) && $table = Factory::getTableForClass(get_class($this))){
            $this->setTable($table);
        }
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return is_null($this->_table) ? null : Helper::prepareTable($this->_table);
    }

    /**
     * @param $table string|Table
     */
    public function setTable($table)
    {
        $this->_table = $table;
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
     * @throws Exception
     */
    public function setFilter(Filter $filter)
    {
        if ($filter->getTable()->getCompleteName() != $this->getTable()->getCompleteName()) {
            throw new Exception('filter table mismatch select table');
        }
        $this->_filter = $filter;
    }

    /**
     * @return Filter
     */
    public function filter()
    {
        if (!isset($this->_filter)) {
            $this->_filter = Factory::filter($this);
        }
        return $this->_filter;
    }

    /**
     * @return Filter
     */
    public function where()
    {
        return $this->filter();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($renderer = static::getDefaultRenderer()) {
            return $renderer->render($this);
        }
        return "ERROR: no default renderer specified";
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

}