<?php
/**
 * Kloook API
 *
 * @author      Romain Bessuges <romain@kloook.com>
 * @copyright   2013 Kloook
 * @link        http://api.kloook.com
 * @license     http://api.kloook.com/license
 * @version     0.1.0
 * @package     Kloook
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

class AbstractQuery implements IQuery
{

    protected $_table;

    /** @var IRenderer */
    protected $_defaultRenderer;

    public function setDefaultRenderer(IRenderer $defaultRenderer)
    {
        $this->_defaultRenderer = $defaultRenderer;
    }

    public function getDefaultRenderer()
    {
        return $this->_defaultRenderer;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return Helper::prepareTable($this->_table);
    }


    /**
     * @param $table string|Table
     */
    public function setTable($table)
    {
        $this->_table = $table;
    }

    public function __toString()
    {
        if($renderer = static::getDefaultRenderer()){
            return $renderer->render($this);
        }
        return "ERROR: no default renderer specified";
    }
}