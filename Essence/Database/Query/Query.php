<?php

namespace Essence\Database\Query;

use PDO;
use Essence\Database\Database;
use Essence\Application\EssenceApplication;
use Essence\Database\Query\Parts\Where\Whereable;
use Essence\Database\Query\Parts\Join\Joinable;

class Query
{
    use Whereable;
    use Joinable;

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
    private $joins = [];
    private $set = [];
    private $col = '*';
    private $orderby = [];
    private $groupby = [];
    private $distinct = false;
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
        return $this->joins;
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