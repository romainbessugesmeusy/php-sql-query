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

use RBM\SqlQuery\Filter;
use RBM\SqlQuery\Table;
use RBM\SqlQuery\Column;

class FilterTest extends PHPUnit_Framework_TestCase
{

    protected function _getFilter()
    {
        $filter = new Filter();
        $filter->setTable("project");
        return $filter;
    }

    public function testConstruct()
    {
        $filter = new Filter();
        $filter->setTable("project");
        $this->assertInstanceOf('\RBM\SqlQuery\Table', $filter->getTable());
    }

    public function testEmpty()
    {
        $filter = $this->_getFilter();
        $this->assertTrue($filter->isEmpty());
        $filter->equals("id", 1);
        $this->assertFalse($filter->isEmpty());

    }

    protected function _testCompareMethod($method, $expectedOperator)
    {
        $filter = $this->_getFilter();
        if (!method_exists($filter, $method)) {
            throw new Exception("method '$method' does not exist in Filter class");
        }

        $filter->{$method}("id", 1);
        $this->assertCount(1, $filter->getComparisons());

        $compare = $filter->getComparisons();
        $eq      = $compare[0];

        $this->assertArrayHasKey("subject", $eq);
        $this->assertArrayHasKey("conjonction", $eq);
        $this->assertArrayHasKey("target", $eq);

        if (isset($eq["conjonction"])) {
            $this->assertEquals($expectedOperator, $eq["conjonction"]);
        }
    }

    public function testEquals()
    {
        $this->_testCompareMethod("equals", Filter::OPERATOR_EQUAL);
    }

    public function testNotEquals()
    {
        $this->_testCompareMethod("notEquals", Filter::OPERATOR_NOT_EQUAL);
    }

    public function testGreaterThan()
    {
        $this->_testCompareMethod("greaterThan", Filter::OPERATOR_GREATER_THAN);
    }

    public function testGreaterThanEqual()
    {
        $this->_testCompareMethod("greaterThanEquals", Filter::OPERATOR_GREATER_THAN_OR_EQUAL);
    }

    public function testLowerThan()
    {
        $this->_testCompareMethod("lowerThan", Filter::OPERATOR_LOWER_THAN);
    }

    public function testLowerThanEquals()
    {
        $this->_testCompareMethod("lowerThanEquals", Filter::OPERATOR_LOWER_THAN_OR_EQUAL);
    }

    public function testLike()
    {
        $this->_testCompareMethod("like", Filter::OPERATOR_LIKE);
    }

    public function testNotLike()
    {
        $this->_testCompareMethod("notLike", Filter::OPERATOR_NOT_LIKE);
    }

    public function testILike()
    {
        $this->_testCompareMethod("ilike", Filter::OPERATOR_ILIKE);
    }

    public function testNotILike()
    {
        $this->_testCompareMethod("notILike", Filter::OPERATOR_NOT_ILIKE);
    }

    public function testBetweens()
    {
        $f = $this->_getFilter();
        $f->between("date_created", "2012-01-01", "2013-01-01");

        $b = $f->getBetweens();

        $this->assertFalse($f->isEmpty());
        $this->assertCount(1, $b);

        $this->assertArrayHasKey("subject", $b[0]);
        $this->assertArrayHasKey("a", $b[0]);
        $this->assertArrayHasKey("b", $b[0]);
    }

    public function testIn()
    {
        $f = $this->_getFilter();
        $f->in("status", ["draft", "published"]);
        $this->assertFalse($f->isEmpty());

        $ins = $f->getIns();
        $this->assertCount(1, $ins);
        $this->assertArrayHasKey("status", $ins);
        $this->assertCount(2, $ins["status"]);
        $this->assertEquals("draft", $ins["status"][0]);
        $this->assertEquals("published", $ins["status"][1]);
    }

    public function testNotIn()
    {
        $f = $this->_getFilter();
        $f->notIn("status", ["draft", "published"]);
        $this->assertFalse($f->isEmpty());

        $nins = $f->getNotIns();
        $this->assertCount(1, $nins);
        $this->assertArrayHasKey("status", $nins);
        $this->assertCount(2, $nins["status"]);
        $this->assertEquals("draft", $nins["status"][0]);
        $this->assertEquals("published", $nins["status"][1]);
    }

    public function testBool()
    {
        $f = $this->_getFilter();
        $f->addBitClause("deleted", true);
        $this->assertFalse($f->isEmpty());

        $b = $f->getBooleans();
        $this->assertCount(1, $b);

        $this->assertArrayHasKey("subject", $b[0]);
        $this->assertArrayHasKey("value", $b[0]);
        $this->assertEquals(true, $b[0]["value"]);
    }

    public function testConjonction()
    {
        $f = $this->_getFilter();
        $this->assertEquals(Filter::CONJONCTION_AND, $f->getConjonction());
        $f->conjonction(Filter::CONJONCTION_OR);
        $this->assertEquals(Filter::CONJONCTION_OR, $f->getConjonction());
        $this->setExpectedException('\RBM\SqlQuery\Exception');
        $f->conjonction("THIS IS NOT A VALID CONJONCTION");
    }

    public function testSubFilter()
    {
        $f  = $this->_getFilter();
        $sf = $f->subFilter();
        $this->assertInstanceOf('\RBM\SqlQuery\Filter', $sf);
    }
}
