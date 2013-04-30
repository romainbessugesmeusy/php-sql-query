<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 28/04/13
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */
require "../vendor/autoload.php";

class ExtendingFilter extends \RBM\SqlQuery\Filter
{

}
class ExtendingSelect extends \RBM\SqlQuery\Select
{

}

\RBM\SqlQuery\Factory::setClassMap([
    "TBL_EXTENDING" => [
        "filter" => "ExtendingFilter",
        "select" => "ExtendingSelect",
    ]
]);

//$select = \RBM\SqlQuery\Factory::select("TBL_EXTENDING");
$filter = new ExtendingSelect();
var_dump($filter->getTable());
