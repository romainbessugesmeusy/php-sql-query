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

class Token
{
    const SELECT          = "SELECT";
    const FROM            = "FROM";
    const ALIAS           = "AS";
    const JOIN            = "JOIN";
    const LEFT            = "LEFT";
    const RIGHT           = "RIGHT";
    const INNER           = "INNER";
    const OUTER           = "OUTER";
    const INTO            = "INTO";
    const VALUES          = "VALUES";
    const WHERE           = "WHERE";
    const LIMIT           = "LIMIT";
    const GROUP_BY        = "GROUP BY";
    const ORDER_BY        = "ORDER BY";
    const ASC             = "ASC";
    const DESC            = "DESC";
    const CONJONCTION_AND = "AND";
    const CONJONCTION_OR  = "OR";
    const LIKE            = "LIKE";
    const NOT_LIKE        = "NOT LIKE";

    const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    const NULL              = "NULL";

    protected $_value;

    protected $_alias;


    public static function SELECT()
    {
        return new self(self::SELECT);
    }

    public static function CURRENT_TIMESTAMP()
    {
        return new self(self::CURRENT_TIMESTAMP);
    }

    public static function NULL()
    {
        return new self(self::NULL);
    }

    public function __construct($value)
    {
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param mixed $alias
     */
    public function setAlias($alias)
    {
        $this->_alias = $alias;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->_alias;
    }
}