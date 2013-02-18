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

use RBM\SqlQuery\Table;

class TableTest extends PHPUnit_Framework_TestCase {

    public function testConstruct()
    {
        $table = new Table("project");
        $this->assertEquals("project", $table->getName());
    }

    public function testAlias()
    {
        $table = new Table("project");
        $this->assertNull($table->getAlias());
        $table->setAlias("pr");
        $this->assertEquals("pr", $table->getAlias());

        $table = new Table(["p" => "project"]);
        $this->assertEquals("p", $table->getAlias());
    }

    public function testSchema()
    {
        $table = new Table("project");
        $this->assertNull($table->getSchema());
        $table = new Table("project", "dbo");
        $this->assertEquals("dbo", $table->getSchema());
    }

    public function testCompleteName()
    {
        $table = new Table("project");
        $table->setAlias("p");
        $table->setSchema("dbo");
        $this->assertEquals("dbo.project AS p", $table->getCompleteName());
    }

    public function testView()
    {
        $table = new Table("orders_stat");
        $this->assertFalse($table->isView());
        $table->setView(true);
        $this->assertTrue($table->isView());
    }
}
