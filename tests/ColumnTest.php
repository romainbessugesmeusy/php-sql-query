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

use RBM\SqlQuery\Column;
use RBM\SqlQuery\Table;

class ColumnTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $col = new Column("id", "project");
        $this->assertEquals("id", $col->getName());
        $this->assertInstanceOf('\RBM\SqlQuery\Table', $col->getTable());
        $this->assertEquals("project", $col->getTable()->getName());
    }

    public function testSetName()
    {
        $col = new Column("id", "project");
        $col->setName("project_id");
        $this->assertEquals("project_id", $col->getName());
    }

    public function testSetTable()
    {
        $col = new Column("id", "project");
        $table = new Table("user");
        $col->setTable($table);
        $this->assertInstanceOf('\RBM\SqlQuery\Table', $col->getTable());
        $this->assertEquals("user", $col->getTable()->getName());
    }

    public function testAlias()
    {
        $col = new Column("id", "project");
        $col->setAlias("project_id");
        $this->assertEquals("project_id", $col->getAlias());
    }
}
