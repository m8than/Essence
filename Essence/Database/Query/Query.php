<?php

namespace Essence\Database\Query;

use PDO;
use Essence\Database\Database;
use Essence\Application\EssenceApplication;
use Essence\Database\Query\Parts\Whereable;
use Essence\Database\Query\Parts\Where;

class Query
{
    use Whereable;

    const FETCH_ASSOC = PDO::FETCH_ASSOC;
    const FETCH_MAPPED_LIST = PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE;
    const FETCH_MAP = PDO::FETCH_KEY_PAIR;
    const FETCH_COLUMN = PDO::FETCH_COLUMN;
    const FETCH_NUM = PDO::FETCH_NUM;

    /**
     * PDO object
     *
     * @var PDO
     */
    private $_pdo;

    /**
     * Stores table
     *
     * @var string
     */
    private $table;
    private $where = [];
    private $set = [];
    private $col = '*';
    private $orderby = [];
    private $groupby = [];
    private $distinct = false;
    
    private $joins = [];
    private $limit = null;
    private $skip = null;

    /**
     * Constructor
     *
     * @param PDO $pdo
     */
    public function __construct($table, PDO $pdo)
    {
        $this->_pdo = $pdo;
        $this->table = $table;
    }

    public function test()
    {
        return PartBuilder::whereStr($this->where);
    }

    /**
     * Factory method
     *
     * @return self
     */
    public static function create($table = '')
    {
        return get(self::class, [$table]);
    }

    /**
     * Sets table
     *
     * @param string $table
     * @param string $alias
     * @return Query
     */
    public function table($table, $alias = '')
    {
        $this->table = $table .  ($alias != '' ? ' ' . $alias : '');
        return $this;
    }
    /**
     * Sets variables
     * 
     * @param string|array $col
     * @param mixed|null $value
     * @return Query
     */
    public function set($col, $value = null)
    {
        $values = $this->_processVariableInput($col, $value);
        foreach($values as $key => $value) {
            $this->set[$key] = $value;
        }
        return $this;
    }

    /**
     * Set return columns
     *
     * @param string|array $col
     * @return Query
     */
    public function columns($col)
    {
        $this->col = is_array($col) ? implode(',', $col) : $col;
        return $this;
    }
    
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
        $this->joins[] = $this->_joinBuilder(
                            'JOIN',
                            $table,
                            $on_or_column1,
                            $operator_or_column2,
                            $column2
                        );
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
        $this->joins[] = $this->_joinBuilder(
                            'INNER JOIN',
                            $table,
                            $on_or_column1,
                            $operator_or_column2,
                            $column2
                        );
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
        $this->joins[] = $this->_joinBuilder(
                            'LEFT JOIN',
                            $table,
                            $on_or_column1,
                            $operator_or_column2,
                            $column2
                        );
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
        $this->joins[] = $this->_joinBuilder(
                            'RIGHT JOIN',
                            $table,
                            $on_or_column1,
                            $operator_or_column2,
                            $column2
                        );
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
        $on_array = [];
        if(is_array($on_or_column1)) {
            foreach($on_or_column1 as $key => $value) {
                if (is_int($key)) {
                    // if input only [column, value] assume =
                    switch(count($value))
                    {
                        case 3:
                            $on_array[] = $value;
                            break;
                        case 2:
                            $on_array[] = [$value[0], '=', $value[1]];
                            break;
                        case 1:
                            $on_array[] = [key($value), '=', $value[key($value)]];
                            break;
                    }
                } else {
                    $on_array[] = [$key, '=', $value];
                }
            }
        } else if(is_null($column2)) {
            //assume the operator input = the value if value is null
            $on_array[] = [$on_or_column1, '=', $operator_or_column2];
        } else {
            $on_array[] = [$on_or_column1, $operator_or_column2, $column2];
        }
        return [
            'type' => $type,
            'table' => $table,
            'on' => $on_array
        ];
    }
    
    /**
     * Set return count limit
     *
     * @param int $limit
     * @return Query
     */
    public function limit($limit)
    {
        $this->limit = (int)$limit;
        return $this;
    }
    /**
     * Sets orderby
     *
     * @param string|array $col
     * @param string $order
     * @return Query
     */
    public function orderBy($col, $order='ASC')
    {
        $values = $this->_processVariableInput($col, $order);
        foreach($values as $key => $value) {
            $s = strtolower($value);
            if($s == 'asc' || $s == 'desc') {
                $col = $this->_escape($key);
                $order = $this->_escape($value);
    
                $this->orderby[$col] = $order;
            } else {
                $col = $this->_escape($value);
                $this->orderby[$col] = 'ASC';
            }
        }
        return $this;
    }
    /**
     * Sets groupby
     *
     * @param string|array $col
     * @return Query
     */
    public function groupBy($col)
    {
        $values = $this->_processVariableInput($col, $col);
        foreach($values as $value) {
            $this->groupby[] = $this->_escape($value);
        }
        return $this;
    }

    /**
     * returns array of key => value pairs with variable input
     *
     * @param array|string $key
     * @param mixed $value
     * @return void
     */
    private function _processVariableInput($key, $value)
    {
        return is_array($key) ? $key : array($key => $value);
    }
}