<?php
namespace Essence\Database\PDO;

use PDO;
use PDOStatement;

//TODO: Log variables for prepared statements
class EssencePDOStatement
{
    /**
     * The variable bindings.
     *
     * @var array
     */
    private $binds = [];

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

    public function __construct(PDO $pdo, PDOStatement $statement)
    {
        $this->_pdo = $pdo;
        $this->_statement = $statement;
    }
    
    public function __call($name, array $arguments)
    {
        return $this->_statement->$name(...$arguments);
    }
    
    public function __get($name)
    {
        return $this->_statement->$name;
    }

    /**
     * @see \PDOStatement::bindParam
     */
    public function bindParam($parameter, &$value)
    {
        $this->binds[$parameter] = &$value;
        return $this->statement->bindParam($parameter, $value);
    }

    /**
     * @see \PDOStatement::bindValue
     */
    public function bindValue($parameter, $value, $type = \Pdo::PARAM_STR)
    {
        $this->binds[$parameter] = $value;
        return $this->statement->bindValue($parameter, $value, $type);
    }
    
    /**
     * @see \PDOStatement::execute
     */
    public function execute(array $input_parameters = array())
    {
        $start = microtime(true);
        $result = $this->_statement->execute($input_parameters);
        $this->_pdo->addLog($this->_statement->queryString, microtime(true) - $start, $this->binds + $input_parameters);
        return $result;
    }
}
?>