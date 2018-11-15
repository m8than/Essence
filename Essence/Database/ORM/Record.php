<?php

namespace Essence\Database\ORM;

use PDO;
use ArrayAccess;
use Essence\Database\PDO\EssencePDO;

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
    
    private $new_record = null;

    public function __construct($id, $data = [], PDO $dbc)
    {
        $this->_pdo = $dbc;
        
        if ($this->table == null) {
            $class_name = basename(str_replace('\\', '/', get_called_class()));
            $this->table = $class_name . 's';
        }

        if (!is_null($id)) {
            $this->new_record = false;
            $this->data[$this->key] = $id;
            if (count($data)) {
                $this->data = array_merge($this->data, $data);
            } else {
                $this->data = $this->_loadData($this->table, $this->key, $id);
            }
        } else {
            $this->new_record = true;
        }
    }

    public function save($data = [])
    {
        $data = array_merge($this->data, $data);
        $this->data = $data;
        $this->_processData($data);
        
        if (!$this->new_record) {
            $this->_updateData($this->table, $this->key, $data);
        } else {
            $this->_insertData($this->table, $this->key, $data);
            $this->new_record = false;
        }
        return $this;
    }

    public function delete()
    {
        if (isset($this->data['deleted'])) {
            $this->deleted = 1;
            $this->save();
        } else {
            $this->_deleteData($this->table, $this->key);
        }
        return $this;
    }

    private function _processData(&$data)
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
            $method = $column . 'Attr';
            $this->data[$column] = $this->$method();
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

    protected function hasOne($model, $local_key, $foreign_key = null)
    {
        $id = $this->data[$local_key];
        $foreign_key = $foreign_key ?? $model::getKey();
        $foreign_table = $model::getTable();

        $stmt = $this->_pdo->prepare("SELECT * FROM {$foreign_table} WHERE {$foreign_key} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        return $model::fetch(0, $stmt->fetch(EssencePDO::FETCH_ASSOC));
    }

    protected function hasMany($model, $local_key, $foreign_key = null)
    {
        $id = $this->data[$local_key];
        $foreign_key = $foreign_key ?? $model::getKey();
        $foreign_table = $model::getTable();

        $stmt = $this->_pdo->prepare("SELECT * FROM {$foreign_table} WHERE {$foreign_key} = :id");
        $stmt->execute(['id' => $id]);

        $rows = $stmt->fetchAll(EssencePDO::FETCH_ASSOC);
        $result = [];
        foreach($rows as $row) {
            $result[] = $model::fetch(0, $row);
        }
        return $result;
    }

    protected function belongsTo($model, $foreign_key)
    {
        $table = $model::getTable();
        $id = $this->data[$this->_getKey()];

        $stmt = $this->_pdo->prepare("SELECT * FROM {$table} WHERE {$foreign_key} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        return $model::fetch(0, $stmt->fetch(EssencePDO::FETCH_ASSOC));
    }

    protected function belongsToMany($model, $link_table = null)
    {
        $model_link_key = $model::getLinkKey();
        $local_link_key = static::getLinkKey($this);

        $model_table = $model::getTable();
        $model_key = $model::getKey();

        $local_id = $this->data[$this->_getKey()];
        $link_table = $this->getLinkTable($model);

        $stmt = $this->_pdo->prepare("SELECT * FROM {$model_table} WHERE {$model_key} IN (SELECT {$model_link_key} FROM {$link_table} WHERE {$local_link_key} = :id)");
        $stmt->execute(['id' => $local_id]);

        $rows = $stmt->fetchAll(EssencePDO::FETCH_ASSOC);
        $result = [];
        foreach($rows as $row) {
            $result[] = $model::fetch(0, $row);
        }
        return $result;
    }

    public static function getLinkKey($obj = null)
    {
        if ($obj != null) {
            return strtolower($obj::getShortName()) . '_' . $obj->_getKey();
        } else {
            return strtolower(static::getShortName()) . '_' . static::getKey();
        }
    }

    private function getLinkTable($foreign_model)
    {
        $tables = [$foreign_model::getTable(), $this->_getTable()];
        sort($tables);
        return '_' . $tables[0] . '_' . $tables[1];
    }

    public function getId()
    {
        return $this->data[$this->_getKey()];
    }

    public function attach($foreign_model_class, $foreign_id = null)
    {
        $foreign_model_classes = is_array($foreign_model_class) ? $foreign_model_class : [$foreign_model_class];

        foreach($foreign_model_classes as $foreign_model_class) {
            $link_table = $this->getLinkTable($foreign_model_class);

            $foreign_link_key = $foreign_model_class::getLinkKey($foreign_model_class);
            $local_link_key = static::getLinkKey($this);

            $foreign_uniq_id = $foreign_id ?? $foreign_model_class->getId();
            $local_id = $this->getId();

            $stmt = $this->_pdo->prepare("INSERT INTO {$link_table} ({$foreign_link_key}, {$local_link_key}) VALUES (:foreign_id, :local_id)");
            $stmt->execute(['foreign_id' => $foreign_uniq_id, 'local_id' => $local_id]);
        }
    }
    
    public function detach($foreign_model_class, $foreign_id = null)
    {
        $foreign_model_classes = is_array($foreign_model_class) ? $foreign_model_class : [$foreign_model_class];

        foreach($foreign_model_classes as $foreign_model_class) {
            $link_table = $this->getLinkTable($foreign_model_class);

            $foreign_link_key = $foreign_model_class::getLinkKey($foreign_model_class);
            $local_link_key = static::getLinkKey($this);

            $foreign_uniq_id = $foreign_id ?? $foreign_model_class->getId();
            $local_id = $this->getId();

            $stmt = $this->_pdo->prepare("DELETE FROM {$link_table} WHERE {$foreign_link_key}=:foreign_id AND {$local_link_key}=:local_id");
            $stmt->execute(['foreign_id' => $foreign_uniq_id, 'local_id' => $local_id]);
        }
    }

    public static function getShortName()
    {
        return basename(str_replace('\\', '/', static::class));
    }

    public function _getTable()
    {
        return $this->table;
    }

    public static function getTable()
    {
        return static::create()->_getTable();
    }

    public function _getKey()
    {
        return $this->key;
    }

    public static function getKey()
    {
        return static::create()->_getKey();
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

        if (!isset($this->data[$this->_getKey()])) {
            $this->data[$this->getKey()] = $this->_pdo->lastInsertId();
        }
    }

    private function _deleteData($table, $key)
    {
        $stmt = $this->_pdo->prepare("DELETE FROM {$table} WHERE {$key} = :id");
        $stmt->execute(['id' => $this->data[$key]]);
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