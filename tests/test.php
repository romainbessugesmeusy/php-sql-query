<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 06/02/13
 * Time: 23:16
 * To change this template use File | Settings | File Templates.
 */

define("LIBRARY_PATH", "../lib/");

spl_autoload_register(function ($classname) {
    $filename = str_replace("\\", "/", $classname) . ".php";
    if (file_exists(LIBRARY_PATH . $filename)) {

        return require LIBRARY_PATH . $filename;
    } else {
        //var_dump([$classname, LIBRARY_PATH . $filename]);die();
    }
    return false;
});



header("Content-Type:text/plain;charset=UTF-8");

$select = new \RBM\SqlQuery\Select();
$select->setTable("project");
$select->setColumns(["project_id", "name"]);
$select->limit(0, 10);

$renderer = new \RBM\SqlQuery\Renderer\MySql();

echo $renderer->render($select) ;
echo PHP_EOL;


$select = new \RBM\SqlQuery\Select();
$select->setTable("Post");
$select->cols("Title", "Date");
$select->filter()
    ->equals("Category", "Sport")
    ->between("Date", "2012-01-01", "2012-03-01");
$select->join("Author", "AuthorId")->cols("Name", "Title");

$renderer = new \RBM\SqlQuery\Renderer\SqlServer();

echo $renderer->render($select) ;

echo PHP_EOL;

$update = new \RBM\SqlQuery\Update();
$update->setTable("Post");
$update->setValues(array(
   "Message" => "Hello",
    "AuthorId" => 1,
));
$update->filter()->equals("Category", "Sport");

echo $renderer->render($update);

echo PHP_EOL;

$select1 = new \RBM\SqlQuery\Select();
$select1->setTable("Projet");
$select1->cols("ID");
$select1->limit(0, 1);
$select1->orderBy("DateCreated");

$select2 = new \RBM\SqlQuery\Select();
$select2->setTable("Client");
$select2->cols("Name");
$select2->filter()->equals("MainProject", $select1);

$renderer = new \RBM\SqlQuery\Renderer\MySql();
echo $renderer->render($select2);

echo PHP_EOL;

$select3 = new \RBM\SqlQuery\Select("project");
echo $renderer->render($select3);

echo PHP_EOL;

$select = new \RBM\SqlQuery\Select('project', ['project_id', 'name']);
$select->filter()
    ->equals('owner_id', 1)
    ->isNull('date_deleted');


	$select->filter()->subFilter()
        ->operator('OR')
        ->equals('status', 'DRAFT')
        ->equals('status', 'PUBLISHED');

echo $renderer->render($select);
