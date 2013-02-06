<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 06/02/13
 * Time: 21:45
 * To change this template use File | Settings | File Templates.
 */

namespace RBM\SqlQuery;

interface IRenderer
{

    public function renderOperator($operator);

    public function renderNull();

    public function renderIsNull();

    public function renderIsNotNull();

    public function renderBoolean($value);

    public function renderValue($value);

    public function renderTable(Table $table);

    public function renderTableWithAlias(Table $table);

    public function renderColumn(Column $column);

    public function renderColumnWithAlias(Column $column);

    public function renderSelect(Select $select);

    public function renderJoin(Select $select);

    public function renderUpdate(Update $update);

    public function renderInsert(Insert $insert);

    public function renderDelete(Delete $delete);

    public function renderFunc(Func $func);

    public function renderFilter(Filter $filter);

    public function renderOrderBy(OrderBy $orderBy);
}