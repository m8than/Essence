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
    private $distinct = false;
    private $where = [];
    private $having = [];
    private $joins = [];
    private $set = [];
    private $col = '*';
    private $orderby = [];
    private $groupby = [];
    private $limit = null;
    private $skip = null;

    /**
     * Constructor
     *
     * @param string $table
     * @param PDO $pdo
     */
    public function __construct($table, PDO $pdo)
    {
        $this->_pdo = $pdo;
        $this->table = $table;
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
     * Run update query
     * 
     * @param array $col_values
     * @return array
     */
    public function update($col_values = [])
    {
        $this->set($col_values);
        
        /**
         * Build all the parts of the sql query
         */
        $parts = ["UPDATE {$this->table}"];

        list($joinStr, $joinBinds) = PartBuilder::join($this->joins);
        $parts[] = $joinStr;

        list($updateStr, $updateBinds) = PartBuilder::update($this->set);
        $parts[] = $updateStr;

        list($whereStr, $whereBinds) = PartBuilder::where($this->where);
        $parts[] = $whereStr;

        /**
         * Assemble sql query out of the parts
         */
        $parts = array_filter($parts);
        $sql = implode(' ', $parts);
        
        /**
         * Execute query
         */
        $stmt = $this->_pdo->prepare($sql);
        return $stmt->execute($joinBinds + $updateBinds + $whereBinds);
    }
    
    /**
     * Set query to use select distinct
     *
     * @param bool $set
     * @return self
     */
    public function distinct($set = true)
    {
        $this->distinct = $set;
        return $this;
    }

    /**
     * Increments a value
     *
     * @param string $column
     * @param int $value
     * @return self
     */
    public function increment($column, $value = 1)
    {
        return $this->set($column, Raw::create($column . '+' . intval($value)));
    }

    /**
     * Decrements a value
     *
     * @param string $column
     * @param int $value
     * @return self
     */
    public function decrement($column, $value = 1)
    {
        return $this->set($column, Raw::create($column . '-' . intval($value)));
    }

    /**
     * Run insert query
     * 
     * @param array $col_values
     * @return array
     */
    public function insert($col_values = [])
    {
        $this->set($col_values);

        /**
         * Build all the parts of the sql query
         */
        $parts = ["INSERT INTO {$this->table}"];

        list($insertStr, $insertBinds) = PartBuilder::insert($this->set);
        $parts[] = $insertStr;

        /**
         * Assemble sql query out of the parts
         */
        $parts = array_filter($parts);
        $sql = implode(' ', $parts);
        
        /**
         * Execute query
         */
        $stmt = $this->_pdo->prepare($sql);
        return $stmt->execute($insertBinds);
    }

    /**
     * Run query and returns single row of  results-
     *
     * @param integer $returnType
     * @return array
     */
    public function selectRow($returnType = Query::FETCH_ASSOC)
    {
        $this->limit(1);
        $result = $this->select($returnType);
        return count($result) ? $result[0] : null;
    }

    /**
     * Run query and returns results
     *
     * @param integer $returnType
     * @return array
     */
    public function select($returnType = Query::FETCH_ASSOC)
    {
        /**
         * Build all the parts of the sql query
         */  
        if($this->distinct) {
            $parts = ["SELECT DISTINCT {$this->col} FROM {$this->table}"];
        } else {
            $parts = ["SELECT {$this->col} FROM {$this->table}"];
        }

        list($joinStr, $joinBinds) = PartBuilder::join($this->joins);
        $parts[] = $joinStr;

        list($whereStr, $whereBinds) = PartBuilder::where($this->where);
        $parts[] = $whereStr;

        $parts[] = PartBuilder::groupBy($this->groupby);
        $parts[] = PartBuilder::orderBy($this->orderby);
        $parts[] = PartBuilder::limit($this->limit, $this->skip);
        
        list($havingStr, $havingBinds) = PartBuilder::having($this->having);
        $parts[] = $havingStr;
        
        /**
         * Assemble sql query out of the parts
         */
        $parts = array_filter($parts);
        $sql = implode(' ', $parts);

        /**
         * Execute query
         */
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($whereBinds + $joinBinds + $havingBinds);

        return $stmt->fetchAll($returnType);
    }

    /**
     * Sets table
     *
     * @param string $table
     * @param string $alias
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function limit($limit)
    {
        $this->limit = (int)$limit;
        return $this;
    }
    
    /**
     * Set return count offset
     *
     * @param int $skip
     * @return self
     */
    public function skip($skip)
    {
        $this->skip = (int)$skip;
        return $this;
    }

    /**
     * Sets orderby
     *
     * @param string|array $col
     * @param string $order
     * @return self
     */
    public function orderBy($col, $order='ASC')
    {
        $values = $this->_processVariableInput($col, $order);
        foreach($values as $key => $value) {
            $s = strtolower($value);
            if($s == 'asc' || $s == 'desc') {
                $col = Database::escape($key);
                $order = Database::escape($value);
    
                $this->orderby[$col] = $order;
            } else {
                $col = Database::escape($value);
                $this->orderby[$col] = 'ASC';
            }
        }
        return $this;
    }
    /**
     * Sets groupby
     *
     * @param string|array $col
     * @return self
     */
    public function groupBy($col)
    {
        $values = $this->_processVariableInput($col, $col);
        foreach($values as $value) {
            $this->groupby[] = Database::escape($value);
        }
        return $this;
    }

    /**
     * returns array of key => value pairs with variable input
     *
     * @param array|string $key
     * @param mixed $value
     * @return array
     */
    private function _processVariableInput($key, $value)
    {
        return is_array($key) ? $key : array($key => $value);
    }
}