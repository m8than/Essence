<?php
namespace Essence\Database\PDO;

use PDO;
use EssencePDOStatement as PDOStatement;

class EssencePDO extends PDO
{
    private $_log;

    private $_query_count;

    private $_query_time;

    public function __construct()
    {
        $_SESSION['mysql']['query_count'] = 0;
        $_SESSION['mysql']['query_time'] = 0;
        $this->_log = array();
        $this->_query_count = 0;
        $this->_query_time = 0;
        call_user_func_array('parent::__construct', func_get_args());
    }

    public function addLog($sql, $run_time)
    {
        $run_time = $run_time*1000;
        $bt = debug_backtrace();
        $caller = array_shift($bt);
        while (!empty($caller) && isset($caller['class']) && in_array($caller['class'], array('Database', 'Model', 'EssencePDO', 'EssencePDOStatement'))) {
            $caller = array_shift($bt);
        }
        if (empty($caller)) {
            $this->_log[] = array(
                'query' => $sql,
                'time' => $run_time,
                'class' => get_called_class(),
                'line' => 0
            );
        } elseif (isset($caller['class'])) {
            $this->_log[] = array(
                'query' => $sql,
                'time' => $run_time,
                'class' => $caller['class'],
                'line' => $caller['line']
            );
        }
        $this->_query_count++;
        $this->_query_time += $run_time;
        $_SESSION['mysql']['query_count']++;
        $_SESSION['mysql']['query_time'] += $run_time;
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
     */
    public function query($statement, ...$args)
    {
        $start = microtime(true);
        $result = parent::query(...$args);
        $this->addLog($statement, microtime(true) - $start);
        if ($result instanceof \PDOStatement) {
            return new PDOStatement($this, $result);
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
            return new PDOStatement($this, $result);
        }
        return $result;
    }
}
?>