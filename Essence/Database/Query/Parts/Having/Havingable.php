<?php

namespace Essence\Database\Query\Parts\Having;

use Essence\Database\Query\PartBuilder;
use Essence\Database\Query\Query;
use Essence\Database\Query\Raw;

trait Havingable
{
    /**
     * Modifier to tell where function what type of a where it is
     *
     * @var bool
     */
    private $_whereConnector = 'AND';

    /**
     * Stores where information
     *
     * @var array
     */
    private $having = [];
    
    /**
     * Sets having variables
     *
     * @param string|array|callable $col
     * @param mixed $operator
     * @param mixed|null $value
     * @return static
     */
    public function having($col, $operator='', $value = null)
    {
        $connector = $this->_whereConnector;
        $this->_whereConnector = 'AND';
        if (is_array($col)) {
            //array input
            foreach($col as $key => $value) {
                if (is_int($key)) {
                    // if input only [column, value] assume =
                    
                    switch(count($value))
                    {
                        case 3:
                            $this->having[] = [$connector, Raw::create($value[0]), $value[1], $value[2]];
                            break;
                        case 2:
                            $this->having[] = [$connector, Raw::create($value[0]), '=', $value[1]];
                            break;
                        case 1:
                            $this->having[] = [$connector, Raw::create(key($value)), '=', $value[key($value)]];
                            break;
                    }
                } else {
                    $this->having[] = [$connector, $key, '=', $value];
                }
            }
        } else if (is_callable($col)) {
            $having = new Having();
            $col($having);
            $this->having[] = [$connector, $having];
        } else if (is_null($value)) {
            //assume the operator input = the value if value is null
            $this->having[] = [$connector, Raw::create($col), '=', $operator];
        } else {
            $this->having[] = [$connector, Raw::create($col), $operator, $value];
        }
        
        return $this;
    }

    /** 
     * Sets having variables for an in statement
     *
     * @param string $col
     * @param array $values
     * @return static
     */
    public function havingIn($col, $values)
    {
        $connector = $this->_havingConnector;
        $this->_whereConnector = 'AND';
        $unique_val = array_values(array_unique($values));
        if (count($unique_val) > 0) {
            $this->having[] = [$connector, $col, 'IN', $unique_val];
        }
        return $this;
    }

    /** 
     * Sets where variables for a not in statement
     *
     * @param string $col
     * @param array $values
     * @return static
     */
    public function havingNotIn($col, $values)
    {
        $connector = $this->_havingConnector;
        $this->_whereConnector = 'AND';
        $unique_val = array_values(array_unique($values));
        if (count($unique_val) > 0) {
            $this->having[] = [$connector, $col, 'NOT IN', $unique_val];
        }
        return $this;
    }

    /**
     * Modifies next where call to be a whereOr
     *
     * @return static
     */
    public function or()
    {
        $this->_whereConnector = 'OR';
        return $this;
    }
}