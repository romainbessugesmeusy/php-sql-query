<?php

require '../vendor/autoload.php';

$client = new \RBM\SqlQuery\Select('client');
$client->filter()->eq('id', 2);
$select = new \RBM\SqlQuery\Select('projet');
$select->filter()->eq($client, 2);

$r = new RBM\SqlQuery\Renderer\SqlServer();
echo $r->render($select);