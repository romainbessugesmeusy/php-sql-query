<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 17/02/13
 * Time: 15:19
 * To change this template use File | Settings | File Templates.
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
