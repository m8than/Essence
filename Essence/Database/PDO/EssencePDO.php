<?php
namespace Essence\Database\PDO;

use PDO;

class EssencePDO extends PDO
{
    private $_log;
    private $_query_count;
    private $_query_time;

    public function __construct(...$args)
    {
        $this->_log = array();
        $this->_query_count = 0;
        $this->_query_time = 0;
        parent::__construct(...$args);
    }

    public function addLog($sql, $run_time, $binds = [])
    {
        $run_time = $run_time * 1000;
        $bt = debug_backtrace();
        do{
            $caller = array_shift($bt);
        } while (!empty($caller) && isset($caller['class']) && in_array($caller['class'], [
            "Essence\Database\Database",
            "Essence\Database\PDO\EssencePDO",
            "Essence\Database\Query\Query",
            "Essence\Database\PDO\EssencePDOStatement",
            "Essence\Container\ContainerEntry",
            "Essence\Container\Container"
        ]));

        if(empty($caller)) {
            $this->_log[] = [
                'query' => $sql,
                'variables' => $binds,
                'ms' => $run_time,
                'class' => static::class,
                'object' => null,
                'line' => 0
            ];
        } else {
            $this->_log[] = [
                'query' => $sql,
                'variables' => $binds,
                'ms' => $run_time,
                'class' => $caller['class'],
                'object' => isset($caller['object']) ? get_class($caller['object']) : null,
                'line' => $caller['line']
            ];
        }

        $this->_query_count++;
        $this->_query_time += $run_time;
    }

    public function getLog()
    {
        return $this->_log;
    }

    public function getQueryCount()
    {
        return $this->_query_count;
    }

    public function getQueryTime()
    {
        return $this->_query_time;
    }
    /**
     * @override
     * @return EssencePDOStatement
     */
    public function query($statement, ...$args)
    {
        $start = microtime(true);
        $result = parent::query($statement, ...$args);
        $this->addLog($statement, microtime(true) - $start);
        if ($result instanceof \PDOStatement) {
            return new EssencePDOStatement($this, $result);
        }
        return $result;
    }

    public function exec($statement)
    {
        $start = microtime(true);
        $result = parent::exec($statement);
        $this->addLog($statement, microtime(true) - $start);
        return $result;
    }
    public function prepare($statement, $options = array())
    {
        $result = parent::prepare($statement, $options);
        if ($result instanceof \PDOStatement) {
            return new EssencePDOStatement($this, $result);
        }
        return $result;
    }
}
?>