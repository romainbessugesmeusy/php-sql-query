<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 17/02/13
 * Time: 15:45
 * To change this template use File | Settings | File Templates.
 */

use RBM\SqlQuery\OrderBy;
use RBM\SqlQuery\Column;

class OrderByTest extends PHPUnit_Framework_TestCase {


    public function testConstruct()
    {
        $col = new Column("date_created", "project");
        $order = new OrderBy($col, OrderBy::ASC, false);
        $this->assertInstanceOf('\RBM\SqlQuery\Column', $order->getColumn());
        $this->assertEquals(OrderBy::ASC, $order->getDirection());
        $this->assertFalse($order->getUseAlias());
    }

    public function testDirection()
    {
        $col = new Column("date_created", "project");
        $order = new OrderBy($col, OrderBy::ASC, false);
        $order->setDirection(OrderBy::DESC);
        $this->assertEquals(OrderBy::DESC, $order->getDirection());
        $this->setExpectedException("InvalidArgumentException");
        $order->setDirection("this is not a valid direction");
    }


}
