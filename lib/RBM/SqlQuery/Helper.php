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

class Helper
{

    /**
     * @static
     * @param $arg
     * @param $table
     * @return Column[]
     */
    public static function prepareColumns($arg, $table = null)
    {
        $columns = (!is_array($arg)) ? array($arg) : $arg;

        $preparedColumns = array();

        foreach ($columns as $index => $column) {
            $column = Helper::prepareColumn($column, $table);
            if (!is_int($index))
                $column->setAlias($index);

            $preparedColumns[] = $column;
        }
        return $preparedColumns;
    }


    /**
     * @param string|array|Column[] $arg
     * @param Table|string $table
     * @return Column
     * @throws Exception
     */
    public static function prepareColumn($arg, $table = null)
    {
        if (is_string($arg)) {
            /** @var $table Table */
            $arg = new Column($arg, $table);
        } else if (is_array($arg)) {
            $v   = array_values($arg);
            $k   = array_keys($arg);
            $arg = new Column($v[0], $table, $k[0]);
        } else if (!is_a($arg, '\RBM\SqlQuery\Column')) {
            throw new Exception('Invalid column provided, string or \RBM\SqlQuery\Column expected');
        }
        return $arg;
    }

    /**
     * @static
     * @param $arg
     * @return Table
     * @throws Exception
     */
    public static function prepareTable($arg)
    {
        if (is_string($arg)) {
            $arg = new Table($arg);
        } else if (is_array($arg)) {
            $v   = array_values($arg);
            $k   = array_keys($arg);
            $arg = new Table($v[0]);
            $arg->setAlias($k[0]);
        } else if (!is_a($arg, '\RBM\SqlQuery\Table')) {
            throw new Exception('Invalid table provided, string or \RBM\SqlQuery\Table expected : ' . gettype($arg) . ' given\n');
        }
        return $arg;
    }
}
