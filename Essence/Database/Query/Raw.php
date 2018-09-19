<?php

namespace Essence\Database\Query;

use Essence\Database\Database;

class Raw
{
    /**
     * The raw value
     *
     * @var string
     */
    private $value;

    /**
     * Class constructor
     *
     * @param string $value
     * @param boolean $escape
     */
    public function __construct($value, $escape = true)
    {
        if($escape) {
            $this->value = Database::escape($value);
        } else {
            $this->value = $value;
        }
    }

    /**
     * Class factory
     *
     * @param string $value
     * @param boolean $escape
     * @return self
     */
    public static function create($value, ...$args)
    {
        if($value instanceof static) {
            return $value;
        }
        return new static($value, ...$args);
    }

    /**
     * Returns value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}