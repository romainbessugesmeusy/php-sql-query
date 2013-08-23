<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 28/04/13
 * Time: 17:19
 * To change this template use File | Settings | File Templates.
 */

require 'bootstrap.php';

$select = new \RBM\SqlQuery\Select("Project");
$select->cols("project_id", "name");
$select->setGroup(["project_id", "name"]);

$messages = $select->join("ProjectMessages", "project_id");
$messages->having()->greaterThanEquals(new \RBM\SqlQuery\Func("COUNT", [new \RBM\SqlQuery\Column("message_id", $messages->getTable())]), 0);

$renderer = new \RBM\SqlQuery\Renderer\Generic();
echo $renderer->format($select);
