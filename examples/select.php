<?php

require '../vendor/autoload.php';

use RBM\SqlQuery\Select;
use RBM\SqlQuery\Renderer\MySql;
use RBM\SqlQuery\OrderBy;
use RBM\SqlQuery\Filter;

Select::setDefaultRenderer(new MySql());

function printQuery($query)
{
    echo PHP_EOL . PHP_EOL;
    echo $query;
    echo PHP_EOL . PHP_EOL . "===========================" . PHP_EOL;
}
$select = new Select('project', [
    "pid" => "project_id",
    "uid" => "owner_id",
    "name"
]);
printQuery($select);
$select->join('user', 'owner_id', 'user_id')->cols([
    "user_email" => "email",
    "user_name"  => "name",
]);
printQuery($select);

$select->filter()->greaterThan('date_created', '20120101');
printQuery($select);

$select->join('user')->joinCondition()->addBitClause('deleted', false);
printQuery($select);

$select->orderBy('date_modified', OrderBy::DESC);
printQuery($select);

$select->limit(10, 20);
printQuery($select);

$select->filter()
    ->subFilter()
    ->equals('status', 'DRAFT')
    ->equals('status', 'PUBLISHED')
    ->conjonction(Filter::CONJONCTION_OR);
printQuery($select);