<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rbm
 * Date: 06/02/13
 * Time: 21:49
 * To change this template use File | Settings | File Templates.
 */

namespace RBM\SqlQuery\RendererAdapter;

use RBM\SqlQuery\AbstractRenderer;
use RBM\SqlQuery\Filter;
use RBM\SqlQuery\RendererException;
use RBM\SqlQuery\Select;

class SqlServer extends AbstractRenderer
{

    public function _renderSelect(Select $select)
    {
        if ($select->getIsJoin()) {
            return $this->_renderJoin($select);
        }

        $sql = parent::_renderSelect($select);

        if (!is_null($select->getLimitStart()) || !is_null($select->getLimitCount())) {
            $sql = $this->_applyLimit($select, $sql);
        }

        return $sql;
    }

    /**
     * @param $sql
     * @param \RBM\SqlQuery\Select $select
     * @return mixed|string
     * @throws \RBM\SqlQuery\RendererException
     */
    protected function _applyLimit($sql, Select $select)
    {

        // dblog('LIMIT - '.$sql);
        $count = intval($select->getLimitCount());
        if ($count <= 0) {
            throw new RendererException("LIMIT argument count=$count is not valid");
        }

        $offset = intval($select->getLimitStart());
        if ($offset < 0) {
            throw new RendererException("LIMIT argument offset=$offset is not valid");
        }

        if ($offset == 0) {
            $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP ' . $count . ' ', $sql);
        } else {
            $orderby = stristr($sql, 'ORDER BY');

            if (!$orderby) {
                $over = 'ORDER BY (SELECT 0)';
            } else {
                //$over = preg_replace('/\"[^,]*\".\"([^,]*)\"/is', '"inner_tbl"."$1"', $orderby);
                $substr = substr($orderby, 9);
                $orders = explode(',', $substr);
                array_walk($orders, function (&$ord) {
                    $direction = substr($ord, strrpos($ord, ' ') + 1);
                    $col = substr($ord, 0, strrpos($ord, ' '));
                    $ord = 'inner_tbl.' . substr($col, strrpos($col, '.') + 1) . ' ' . $direction;
                });

                $over = 'ORDER BY ' . implode(',', $orders);
            }

            // Remove ORDER BY clause from $sql
            $sql = preg_replace('/\s+ORDER BY(.*)/s', '', $sql);
            // Add ORDER BY clause as an argument for ROW_NUMBER()
            $sql = "SELECT ROW_NUMBER() OVER ($over) AS __rownum__, * FROM ($sql) AS inner_tbl";

            $start = $offset + 1;
            $end = $offset + $count;

            $sql = "WITH outer_tbl AS ($sql) SELECT * FROM outer_tbl WHERE CAST(__rownum__ AS int) BETWEEN $start AND $end";
        }

        return $sql;
    }
}