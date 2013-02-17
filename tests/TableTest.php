<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 17/02/13
 * Time: 15:37
 * To change this template use File | Settings | File Templates.
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
