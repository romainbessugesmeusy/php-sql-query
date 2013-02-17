<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 07/02/13
 * Time: 18:20
 * To change this template use File | Settings | File Templates.
 */

namespace RBM\SqlQuery;


interface IQuery {

    /**
     * @return Table
     */
    public function getTable();

    /**
     * @return Filter
     */
    public function getFilter();

    /**
     * @param Filter $filter
     */
    public function setFilter(Filter $filter);

    /**
     * @return Filter
     */
    public function filter();

}