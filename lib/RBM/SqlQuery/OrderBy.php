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

class OrderBy
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    const NULLS_LAST  = 'LAST';
    const NULLS_FIRST = 'FIRST';

    /** @var Column */
    protected $_column;

    /** @var string */
    protected $_direction;


    /** @var string */
    protected $_nulls;

    /** @var boolean */
    protected $_useAlias;

    public function __construct(Column $column, $direction, $useAlias, $nulls = null)
    {
        $this->setColumn($column);
        $this->setDirection($direction);
        $this->setUseAlias($useAlias);
        $this->setNulls($nulls);
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
        if(!in_array($direction, array(self::ASC, self::DESC))){
            throw new \InvalidArgumentException("Specified direction '$direction' is not allowed");
        }
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


    /**
     * @param null|string $mnulls
     */
    public function setNulls($nulls = null)
    {
        if(!is_null($nulls) && !in_array($nulls, array(self::NULLS_FIRST, self::NULLS_LAST))){
            throw new \InvalidArgumentException("Specified nulls '$nulls' is not allowed");
        }
        $this->_nulls = $nulls;
    }

    /**
     * @return null|string
     */
    public function getNulls()
    {
        return $this->_nulls;
    }


}
