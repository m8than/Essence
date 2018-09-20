<?php

namespace Essence\Database\ORM;

use PDO;
use ArrayAccess;

class Record implements ArrayAccess
{
    /**
     * PDO object
     *
     * @var PDO
     */
    private $_pdo;
    
    public function __construct($id, PDO $dbc)
    {
        
    }
    
    /**
     * Factory method
     *
     * @return static
     */
    public static function create()
    {
        return get(self::class, [0]);
    }

    /**
     * Factory method
     *
     * @return static
     */
    public static function fetch($id)
    {
        return get(self::class, [$id]);
    }
}
?>