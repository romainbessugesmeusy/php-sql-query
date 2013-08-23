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

use RBM\SqlQuery\Select;

class NotAValidFilter
{

}

class ValidFilter extends \RBM\SqlQuery\Filter
{

}

class SelectTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $select = new Select();
        $this->assertNull($select->getTable());

        $select->setTable("project");
        $this->assertInstanceOf('\RBM\SqlQuery\Table', $select->getTable());

        $c = $select->getColumns();
        $this->assertNotEmpty($c);
        $this->assertCount(1, $c);
        $this->assertEquals(\RBM\SqlQuery\Column::ALL, $c[0]->getName());
    }

    /*
    public function testFilterClass() # Disabled testFilterClass  -- setFilterClass is missing
    {
        $select = new Select();
        $select->setFilterClass('ValidFilter');
        $f = $select->filter();
        $this->assertInstanceOf('ValidFilter', $f);

        $select = new Select();
        $ok     = false;
        try {
            $select->setFilterClass('NotAValidFilter');
        } catch (\RBM\SqlQuery\Exception $exception) {
            $ok = true;
        }

        if (!$ok) $this->fail("Exception has to be thrown when the filterClass does not extend Filter");

        $ok     = false;
        $select = new Select();
        $select->filter();
        try {
            $select->setFilterClass('ValidFilter');
        } catch (\RBM\SqlQuery\Exception $exception) {
            $ok = true;
        }

        if (!$ok) $this->fail("Exception has to be thrown when the filterClass is set after ->filter() has already been call");
    }
    */

    public function testFilter()
    {
        $select = new Select('project');
        $f = $select->filter();

        $this->assertInstanceOf('\RBM\SqlQuery\Filter', $f);
        $this->assertEquals($select->getTable(), $f->getTable());

        $f2 = new \RBM\SqlQuery\Filter();
        $f2->setTable('user');
        $ok = false;
        try {
            $select->setFilter($f2);
        } catch (\RBM\SqlQuery\Exception $e){
            $ok = true;
        }
        if (!$ok) $this->fail("Exception has to be thrown when the filter's table was not the same as select's one");
    }

    public function testJoin()
    {
        $select = new Select('project');
        $j      = $select->join('user', 'user_id');
        $this->assertInstanceOf('\RBM\SqlQuery\Select', $j);
        $this->assertInstanceOf('\RBM\SqlQuery\Table', $j->getTable());
        $this->assertEquals('user', $j->getTable()->getName());
        $this->assertInstanceOf('\RBM\SqlQuery\Filter', $j->getJoinCondition());
        $this->assertFalse($j->getJoinCondition()->isEmpty());
        // by default, the join should not include cols like select does
        $this->assertEmpty($j->getColumns());
    }

    public function testCols()
    {
        $select = new Select('project');
        $select->cols('user_id', 'project_id', 'name');
        $this->assertCount(3, $select->getColumns());

        $select->cols([
            "uid" => "user_id",
            "pid" => "project_id",
            "name",
        ]);
        $c = $select->getColumns();

        $this->assertCount(3, $c);
        $this->assertInstanceOf('\RBM\SqlQuery\Column', $c[0]);
        $this->assertInstanceOf('\RBM\SqlQuery\Column', $c[1]);
        $this->assertInstanceOf('\RBM\SqlQuery\Column', $c[2]);

        $this->assertEquals('uid', $c[0]->getAlias());
        $this->assertEquals('pid', $c[1]->getAlias());
        $this->assertNull($c[2]->getAlias());

        $this->assertEquals('user_id', $c[0]->getName());
        $this->assertEquals('project_id', $c[1]->getName());
        $this->assertEquals('name', $c[2]->getName());
    }

    public function testJoinColumns()
    {
        $select = new Select('project');
        $select->cols(['pid' => 'project_id']);
        $j = $select->join('user', 'user_id');
        $j->cols(['user_email' => 'email']);

        $c = $select->getAllColumns();
        $this->assertCount(2, $c);

        $this->assertEquals('pid', $c[0]->getAlias());
        $this->assertEquals('user_email', $c[1]->getAlias());
        $this->assertEquals('project_id', $c[0]->getName());
        $this->assertEquals('email', $c[1]->getName());
    }

    public function testOrderBy()
    {
        $select= new Select('project');
        $select->orderBy("date_created");

        $ob = $select->getOrderBy();
        $this->assertCount(1, $ob);
        $this->assertInstanceOf('\RBM\SqlQuery\OrderBy', $ob[0]);

        $select->join('user', 'owner_id', 'user_id')->orderBy('name');

        $ob = $select->getAllOrderBy();
        $this->assertCount(2, $ob);
    }

    public function testGroupBy()
    {

    }
}
