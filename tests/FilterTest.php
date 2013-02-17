<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 17/02/13
 * Time: 16:36
 * To change this template use File | Settings | File Templates.
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
