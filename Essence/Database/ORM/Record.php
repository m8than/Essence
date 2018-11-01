<?php

namespace Essence\Database\ORM;

use PDO;
use ArrayAccess;
use Essence\Database\Query\Query;

class Record implements ArrayAccess
{
    protected $key = 'id';
    protected $table = null;
    protected $relationships = [];

    /**
     * Stores a list of writeable columns
     *
     * @var array 
     */
    protected $writeable = [];

    private $data = [];

    /**
     * PDO object
     *
     * @var PDO
     */
    private $_pdo;
    
    public function __construct($id, $data = [], PDO $dbc)
    {
        $this->_pdo = $dbc;
        
        if ($this->table == null) {
            $class_name = basename(str_replace('\\', '/', get_called_class()));
            $this->table = $class_name . 's';
        }

        if ($id != null) {
            $this->data[$this->key] = $id;
            if (count($data)) {
                $this->data = array_merge($this->data, $data);
            } else {
                $this->data = $this->_loadData($this->table, $this->key, $id);
            }
        }
    }

    public function save($data = [])
    {
        $data = array_merge($this->data, $data);
        $this->_processData($data);
        if (isset($data[$this->key])) {
            $this->_updateData($this->table, $this->key, $data);
        } else {
            $this->_insertData($this->table, $this->key, $data);
        }
        return $this;
    }

    private function _processData(&$data)
    {
        foreach($data as $column => &$value)
        {
            if ($value instanceof Record) {
                $value->save();
                unset($data[$column]);
                continue;
            }

            if ($column != $this->key && !in_array($column, $this->writeable)) {
                unset($data[$column]);
                continue;
            }

            $filterMethod = $column . 'Filter';
            if (method_exists($this, $filterMethod)) {
                if (!$this->$filterMethod($value)) {
                    unset($data[$column]);
                    continue;
                }
            }
        }
    }

    private function _get($column)
    {
        if (isset($this->data[$column])) {
            return $this->data[$column];
        } else {
            $this->_loadRelation($column);
            if (isset($this->data[$column])) {
                return $this->data[$column];
            }
        }
        return null;
    }

    private function _set($column, $value)
    {
        $this->data[$column] = $value;
    }

    private function _loadRelation(...$relationNames)
    {
        foreach ($this->relationships as $class => $column) {
            $shortName = substr(strrchr($class, '\\'), 1);
            if (in_array($shortName, $relationNames) || in_array($class, $relationNames)) {
                if (!isset($this->data[$shortName])) {
                    $id = $this->data[$column];
                    $this->data[$shortName] = $class::fetch($id);
                }
            }
        }
        return $this;
    }


    public function getTable()
    {
        return $this->table;
    }

    public function getKey()
    {
        return $this->key;
    }

    private function _loadData($table, $key, $id)
    {
        $stmt = $this->_pdo->prepare("SELECT * FROM {$table} WHERE {$key} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function _updateData($table, $key, $data)
    {
        $columns = array_keys($data);
        $set_parts = array_map(function($val) { return $val.'=:'.$val; }, $columns);
        $stmt = $this->_pdo->prepare("UPDATE {$table} SET " . implode(',', $set_parts) . " WHERE {$key}=:{$key}");
        $stmt->execute($data);
    }
    
    private function _insertData($table, $key, $data)
    {
        $columns = array_keys($data);
        $bind_columns = array_map(function($val) { return ':'.$val; }, $columns);
        $stmt = $this->_pdo->prepare("INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $bind_columns) . ")");
        $stmt->execute($data);
    }

    /**
     * Factory method
     *
     * @return static
     */
    public static function create()
    {
        return get(static::class, [null, [], []]);
    }

    /**
     * Factory method
     *
     * @return static
     */
    public static function fetch($id, $data = [])
    {
        return get(static::class, [$id, $data, []]);
    }
    
    public function offsetSet($offset, $value)
    {
        $this->_set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_get($offset);
    }

    public function __get($name)
    {
        return $this->_get($name);
    }

    public function __set($name, $value)
    {
        $this->_set($name, $value);
    }
}
?>