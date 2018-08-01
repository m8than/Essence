<?php
namespace Essence\Database\PDO;

use PDO;
use EssencePDO as PDO;

//TODO: Log variables for prepared statements
class EssencePDOStatement
{    
    /**
     * PDO object
     *
     * @var EssencePDO
     */
    private $_pdo;

    /**
     * Prepared statement
     *
     * @var PDOStatement
     */
    private $_statement;

    public function __construct(PDO $pdo, \PDOStatement $statement)
    {
        $this->_pdo = $pdo;
        $this->_statement = $statement;
    }
    
    public function __call($name, array $arguments)
    {
        return call_user_func_array(
            array($this->_statement, $name),
            $arguments
        );
    }
    
    public function __get($name)
    {
        return $this->_statement->$name;
    }
    
    public function execute(array $input_parameters = array())
    {
        $start = microtime(true);
        $result = $this->_statement->execute($input_parameters);
        $this->_pdo->addLog($this->_statement->queryString, microtime(true) - $start);
        return $result;
    }
}
?>