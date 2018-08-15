<?php

namespace Essence\Database\Query\QueryParts;

trait WhereTrait
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
    private $where = [];
    
    /**
     * Sets where variables
     *
     * @param string|array $col
     * @param mixed|null $value
     * @return Query
     */
    public function where($col, $operator = '=', $value = null)
    {
        $connector = $this->_whereConnector;
        $this->_whereConnector = 'AND';
        if(is_array($col)) {
            //array input
            foreach($col as $key => $value) {
                if (is_int($key)) {
                    // if input only [column, value] assume =
                    
                    switch(count($value))
                    {
                        case 3:
                            $this->where[] = array_push([$connector], $value);
                            break;
                        case 2:
                            $this->where[] = [$connector, $value[0], '=', $value[1]];
                            break;
                        case 1:
                            $this->where[] = [$connector, key($value), '=', $value[key($value)]];
                            break;
                    }
                } else {
                    $this->where[] = [$connector, $key, '=', $value];
                }
            }
        } else if(is_null($value)) {
            //assume the operator input = the value if value is null
            $this->where[] = [$connector, $col, '=', $operator];
        } else {
            $this->where[] = [$connector, $col, $operator, $value];
        }
        
        return $this;
    }

    /** 
     * Sets where variables for an in statement
     *
     * @param string $col
     * @param array $values
     * @return Query
     */
    public function whereIn($col, $values)
    {
        $connector = $this->_whereConnector;
        $this->_whereConnector = 'AND';
        $unique_val = array_values(array_unique($values));
        if (count($unique_val) > 0) {
            $this->where[] = [$connector, $col, 'IN', $unique_val];
        }
        return $this;
    }

    /** 
     * Sets where variables for a not in statement
     *
     * @param string $col
     * @param array $values
     * @return Query
     */
    public function whereNotIn($col, $values)
    {
        $connector = $this->_whereConnector;
        $this->_whereConnector = 'AND';
        $unique_val = array_values(array_unique($values));
        if (count($unique_val) > 0) {
            $this->where[] = [$connector, $col, 'NOT IN', $unique_val];
        }
        return $this;
    }

    /**
     * Modifies next where call to be a whereOr
     *
     * @return Query
     */
    public function Or()
    {
        $this->_whereConnector = 'OR';
        return $this;
    }
}

class Where {
    use WhereTrait;
}