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

    public function preparedQuery($query, $bind, $returnType=PDO::FETCH_ASSOC)
    {
        $stmt = $this->_pdo->prepare($query);
        $stmt->execute($bind);
        return $stmt->fetchAll($returnType);
    }

    /**
     * Use native string escaping, should use quote function instead
     * @param string text string
     */
    public static function escape($string)
    {
        $str = get(PDO::class)->quote($string);
        return substr($str, 1, strlen($str)-2);
    }

    /**
     * Use native string escaping
     * @param string text string
     */
    public static function quote($string)
    {
        return get(PDO::class)->quote($string);
    }
}