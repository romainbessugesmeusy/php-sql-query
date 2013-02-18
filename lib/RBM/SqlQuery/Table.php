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

class Table
{
    /** @var string */
    protected $_name;
    /** @var string */
    protected $_alias;
    /** @var string */
    protected $_schema;
    /** @var bool */
    protected $_view = false;

    /**
     * @param array|string $name use array to quickly set the alias : ["p" => "project"]
     * @param string|null $schema
     */
    public function __construct($name, $schema = null)
    {
        if (is_array($name)) {
            $keys         = array_keys($name);
            $this->_alias = reset($keys);
            $this->_name  = reset($name);
        } else {
            $this->_name = $name;
        }

        if (!is_null($schema)) {
            $this->_schema = $schema;
        }
    }

    /**
     * @param boolean $view
     */
    public function setView($view)
    {
        $this->_view = ($view);
    }

    /**
     * @return bool
     */
    public function isView()
    {
        return $this->_view;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * @return string
     */
    public function getCompleteName()
    {
        $alias  = ($this->_alias) ? " AS {$this->_alias}" : '';
        $schema = ($this->_schema) ? "{$this->_schema}." : '';
        return $schema . $this->_name . $alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->_alias = $alias;
    }

    /**
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * @param string|null $schema
     */
    public function setSchema($schema)
    {
        $this->_schema = $schema;
    }
}
