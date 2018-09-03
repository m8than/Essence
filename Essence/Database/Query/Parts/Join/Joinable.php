<?php

namespace Essence\Database\Query\Parts\Join;

use Essence\Database\Query\PartBuilder;

trait Joinable
{
    private $table;
    private $joins = [];
    
    /**
     * Adds a join to query
     *
     * @param string $table
     * @param string $on_or_column1
     * @param string $operator_or_column2
     * @param string $column2
     * @return Query
     */
    public function join($table, $on_or_column1, $operator_or_column2 = '=', $column2 = null)
    {
        $this->joins[] = $this->_joinBuilder(Join::Join, $table, $on_or_column1, $operator_or_column2, $column2);
        return $this;
    }

    /**
     * Adds an inner join to query
     *
     * @param string $table
     * @param string $on_or_column1
     * @param string $operator_or_column2
     * @param string $column2
     * @return Query
     */
    public function innerJoin($table, $on_or_column1, $operator_or_column2 = '=', $column2 = null)
    {
        $this->joins[] = $this->_joinBuilder(Join::InnerJoin, $table, $on_or_column1, $operator_or_column2, $column2);
        return $this;
    }

    /**
     * Adds an inner join to query
     *
     * @param string $table
     * @param string $on_or_column1
     * @param string $operator_or_column2
     * @param string $column2
     * @return Query
     */
    public function OuterJoin($table, $on_or_column1, $operator_or_column2 = '=', $column2 = null)
    {
        $this->joins[] = $this->_joinBuilder(Join::OuterJoin, $table, $on_or_column1, $operator_or_column2, $column2);
        return $this;
    }

    /**
     * Adds a left join to query
     *
     * @param string $table
     * @param string $on_or_column1
     * @param string $operator_or_column2
     * @param string $column2
     * @return Query
     */
    public function leftJoin($table, $on_or_column1, $operator_or_column2 = '=', $column2 = null)
    {
        $this->joins[] = $this->_joinBuilder(Join::LeftJoin, $table, $on_or_column1, $operator_or_column2, $column2);
        return $this;
    }

    /**
     * Adds a left join to query
     *
     * @param string $table
     * @param string $on_or_column1
     * @param string $operator_or_column2
     * @param string $column2
     * @return Query
     */
    public function rightJoin($table, $on_or_column1, $operator_or_column2 = '=', $column2 = null)
    {
        $this->joins[] = $this->_joinBuilder(Join::RightJoin, $table, $on_or_column1, $operator_or_column2, $column2);
        return $this;
    }

    /**
     * Builds join array
     *
     * @param string $type
     * @param string $table
     * @param string $on_or_column1
     * @param string $operator_or_column2
     * @param string $column2
     * @return array
     */
    private function _joinBuilder($type, $table, $on_or_column1, $operator_or_column2 = '=', $column2 = null)
    {
        $join = new Join($type);
        $join->tables($this->table, $table);

        if (is_array($on_or_column1)) {
            //array input
            foreach($on_or_column1 as $key => $value) {
                if (is_int($key)) {
                    // if input only [column, value] assume =
                    
                    switch(count($value))
                    {
                        case 3:
                            $join->on(...$value); //[$column1, operator, column]
                            break;
                        case 2:
                            $join->on($value[0], '=', $value[1]);
                            break;
                        case 1:
                            $join->on(key($value), '=', $value[key($value)]);
                            break;
                    }
                } else {
                    $join->on($key, '=', $value);
                }
            }
        } else if (is_callable($on_or_column1)) {
            $on_or_column1($join);
        } else if (is_null($column2)) {
            //assume the operator input = the column2 if column2 is null
            $join->on($on_or_column1, '=', $operator_or_column2);
        } else {
            $join->on($on_or_column1, $operator_or_column2, $column2 = null);
        }

        return $join;
    }
}