<?php

namespace Essence\Database\Query\Parts\Join;

use Essence\Database\Query\Raw;
use Essence\Database\Query\PartBuilder;
use Essence\Database\Query\Parts\Where\Where;
use Essence\Database\Query\Parts\Where\Whereable;

class Join
{
    use Whereable;

    public const Join = 'JOIN';
    public const InnerJoin = 'INNER JOIN';
    public const OuterJoin = 'OUTER JOIN';
    public const LeftJoin = 'LEFT JOIN';
    public const RightJoin = 'RIGHT JOIN';

    private $type;
    private $table_primary;
    private $table_join;
    private $where = [];

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Sets on variables
     *
     * @param string|array|callable $col
     * @param mixed $operator
     * @param mixed|null $value
     * @return self
     */
    public function on($col, $operator='', $value = null)
    {
        if (is_string($col)) {
            // if 3 parameters and missing . on both column and value
            if ($value != null && strpos($col, '.') === false && strpos($value, '.') === false) {
                $col = $this->table_primary . '.' . $col;
                $value = $this->table_join . '.' . $value;
            } elseif (strpos($col, '.') === false && strpos($operator, '.') === false) {
                $col = $this->table_primary . '.' . $col;
                $operator = $this->table_join . '.' . $operator;
            }
        }
        if ($value == null) {
            return $this->where(
                Raw::create($col),
                Raw::create($operator)
            );
        } else {
            return $this->where(
                Raw::create($col),
                $operator,
                Raw::create($value)
            );
        }
    }

    public function tables($to, $from)
    {
        $this->table_primary = $to;
        $this->table_join = $from;
    }

    public function build()
    {
        list($sql, $binds) = PartBuilder::where($this->where, true);
        return [$this->type . ' ' . $this->table_join . ' ON (' . $sql . ')', $binds];
    }
}