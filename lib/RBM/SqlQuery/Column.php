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

class Column
{

    const ALL = '*';

    /** @var Table */
    protected $_table;

    /** @var string */
    protected $_name;

    /** @var string */
    protected $_alias;

    /**
     * @param string $name
     * @param string|\RBM\SqlQuery\Table $table
     * @param string $alias
     */
    public function __construct($name, $table, $alias = null)
    {
        $this->setName($name);
        $this->setTable($table);
        $this->setAlias($alias);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = (string) $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string|Table $table
     */
    public function setTable($table)
    {
        $this->_table = Helper::prepareTable($table);
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @param $alias
     * @return null
     * @throws Exception
     */
    public function setAlias($alias)
    {
        if(is_null($alias))
            return $this->_alias = null;

        if($this->isAll())
            throw new Exception("Can't use alias because column name is ALL (*)");

        $this->_alias = (string) $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * Check whether column name is '*' or not
     * @return bool
     */
    public function isAll()
    {
        return $this->getName() == self::ALL;
    }
}
