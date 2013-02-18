<?php

require '../vendor/autoload.php';


use RBM\SqlQuery\Select;
use RBM\SqlQuery\Filter;
use RBM\SqlQuery\Renderer\MySql;

Select::setDefaultRenderer(new MySql());

function printQuery($query){
    echo $query;
    echo PHP_EOL . PHP_EOL . "===========================" . PHP_EOL;
}
