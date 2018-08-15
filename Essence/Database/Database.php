<?php

namespace Essence\Database;

use PDO;
class Database
{
    /**
     * Stores PDO object
     *
     * @var EssencePDO
     */
    private $_pdo;

    public function __construct(EssencePDO $db)
    {
        $this->_pdo = $db;
    }

    public function preparedQuery($query, $bind, $returnType)
    {
        $query = $this->_pdo->query($query);
        $query->execute($bind);
        return $query->fetchAll($returnType);
    }
}