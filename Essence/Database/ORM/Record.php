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

    private function _processData(&$data, $recursiveSave)
    {
        foreach($data as $column => &$value)
        {
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
        } else if (method_exists($this, $column . 'Attr')) {
            $this->data[$column] = $this->($column . 'Attr')();
            if (isset($this->data[$column]) && $this->data[$column] != null) {
                return $this->data[$column];
            }
        }
        return null;
    }

    private function _set($column, $value)
    {
        $this->data[$column] = $value;
    }

    private function hasOne($model, $local_key, $foreign_key = null)
    {
        $id = $this->data[$local_key];
        $foreign_key = $foreign_key ?? $model::getKey();
        $foreign_table = $model::getTable();

        $stmt = $this->_pdo->prepare("SELECT * FROM {$foreign_table} WHERE {$foreign_key} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(EssencePDO::FETCH_ASSOC);
    }

    private function hasMany($model, $local_key, $foreign_key = null)
    {
        $id = $this->data[$local_key];
        $foreign_key = $foreign_key ?? $model::getKey();
        $foreign_table = $model::getTable();

        $stmt = $this->_pdo->prepare("SELECT * FROM {$foreign_table} WHERE {$foreign_key} = :id");
        $stmt->execute(['id' => $id]);

        $rows = $stmt->fetchAll(EssencePDO::FETCH_ASSOC);
        foreach($row as $rows) {
            yield $model::fetch(0, $row);
        }
    }

    private function belongsTo($model, $foreign_key)
    {
        $table = $model::getTable();
        $id = $this->data[$this->getKey()];

        $stmt = $this->_pdo->prepare("SELECT * FROM {$table} WHERE {$foreign_key} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(EssencePDO::FETCH_ASSOC);
    }

    private function belongsToMany($model, $link_table = null)
    {
        $model_link_key = $model::getLinkKey();
        $local_link_key = static::getLinkKey($this);

        $model_table = $model::getTable();
        $model_key = $model::getKey();

        $local_id = $this->data[$this->getKey()];
        $link_table = $this->getLinkTable($model);

        $stmt = $this->_pdo->prepare("SELECT * FROM {$model_table} WHERE {$model_key} IN (SELECT {$model_link_key} FROM {$link_table} WHERE {$local_link_key} = :id)");
        $stmt->execute(['id' => $local_id]);

        $rows = $stmt->fetchAll(EssencePDO::FETCH_ASSOC);
        foreach($row as $rows) {
            yield $model::fetch(0, $row);
        }
    }

    public static function getLinkKey($obj = null)
    {
        if ($model != null) {
            return strtolower($obj::getShortName()) . '_' . $obj->getKey();
        } else {
            return strtolower(static::getShortName()) . '_' . static::getKey();
        }
    }

    private function getLinkTable($foreign_model)
    {
        $tables = sort([$foreign_model::getTable(), $this->getTable()]);
        return $link_table ?? $table_key[0] . '_' . $tables[1];
    }

    public function getId()
    {
        return $this->data[$this->getKey()];
    }

    public function attach($foreign_model_class, $foreign_id = null)
    {
        $link_table = $this->getLinkTable($foreign_model_class);

        $foreign_link_key = $foreign_model_class::getLinkKey($foreign_model_class);
        $local_link_key = static::getLinkKey($this);

        $foreign_id = $foreign_id ?? $foreign_model_class->getId();
        $local_id = $this->getId();

        $stmt = $this->_pdo->prepare("INSERT INTO {$link_table} ({$foreign_key}, {$local_link_key}) VALUES (:foreign_id, :local_id)");
        return $stmt->execute(['foreign_id' => $foreign_id, 'local_id' => $local_id]);
    }
    
    public function detach($foreign_model)
    {
        $link_table = $this->getLinkTable($foreign_model_class);

        $foreign_link_key = $foreign_model_class::getLinkKey($foreign_model_class);
        $local_link_key = static::getLinkKey($this);

        $foreign_id = $foreign_id ?? $foreign_model_class->getId();
        $local_id = $this->getId();

        $stmt = $this->_pdo->prepare("DELETE FROM {$link_table} WHERE {$foreign_key}=:foreign_id AND {$local_link_key}=:local_id)");
        return $stmt->execute(['foreign_id' => $foreign_id, 'local_id' => $local_id]);
    }

    public static function getShortName()
    {
        return basename(str_replace('\\', '/', static::class));
    }

    public function getTable()
    {
        return $this->table;
    }

    public static function getTable()
    {
        return static::create()->getTable();
    }

    public function getKey()
    {
        return $this->key;
    }

    public static function getKey()
    {
        return static::create()->getKey();
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