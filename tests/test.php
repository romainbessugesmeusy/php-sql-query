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
$select->setTable("Post");
$select->cols("Title", "Date");
$select->filter()
    ->equals("Category", "Sport")
    ->between("Date", "2012-01-01", "2012-03-01");
$select->join("Author", "AuthorId")->cols("Name", "Title");

$renderer = new \RBM\SqlQuery\RendererAdapter\SqlServer();

echo $renderer->renderSelect($select) ;

echo PHP_EOL;

$update = new \RBM\SqlQuery\Update();
$update->setTable("Post");
$update->setValues(array(
   "Message" => "Hello",
    "AuthorId" => 1,
));
$update->filter()->equals("Category", "Sport");

echo $renderer->renderUpdate($update);