<?php

namespace Essence\Database\Query;

use Essence\Database\Query\Parts\Where\Where;
use Essence\Database\Query\Parts\Having\Having;

class PartBuilder
{
    /**
     * Builds insert string and bind variables
     *
     * @param array $inserts
     * @return array [
     *      (string) built insert string,
     *      (array) bind variables
     * ]
     */
    public static function insert($inserts)
    {
        if(!count($inserts)) {
            return ['', []];
        }

        $binds = [];
        $valueParts = [];

        $columns = array_keys($inserts);
        $values = array_values($inserts);

        foreach($values as $value) {
            if ($value instanceof Raw) {
                $valueParts[] = $value->getValue();
            } else {
                $param = ':' . self::_bindParam();
                $valueParts[] = $param;
                $binds[$param] = $value;
            }
        }

        return ['(' . implode(',', $columns) . ') VALUES (' . implode(',', $valueParts) . ')', $binds];
    }

    /**
     * Builds update string and bind variables
     *
     * @param array $updates
     * @return array [
     *      (string) built update string,
     *      (array) bind variables
     * ]
     */
    public static function update($updates)
    {
        if (!count($updates)) {
            return ['', []];
        }

        $binds = [];
        $stringParts = [];

        foreach ($updates as $col => $value) {
            if ($value instanceof Raw) {
                $stringParts[] = $col . '=' . $value->getValue();
            } else {
                $rnd = self::_bindParam();
                $binds[$rnd] = $value;
                $stringParts[] = $col . '=:' . $rnd;
            }
        }

        return ['SET ' . implode(',', $stringParts), $binds];
    }
    /**
     * 
     * Builds where having for the query builder and returns the string and bind variables
     *
     * @param array $wheres
     * @return array [
     *      (string) built where string,
     *      (array) bind variables
     * ]
     */
    public static function having($wheres, $on = false)
    {
        if(!count($wheres)) {
            return ['', []];
        }

        $stringParts = [];
        $bind = [];

        foreach($wheres as $where) {
            if (!count($stringParts)) {
                //if first one connector use blank
                $where[0] = '';
            } else {
                $where[0] = $where[0] . ' ';
            }

            if (count($where) == 4) {
                if (!is_array($where[3])) {
                    //assume where
                    if($where[1] instanceof Raw) {
                        $where[1] = $where[1]->getValue();
                    } else {
                        $rnd = self::_bindParam();
                        $bind[$rnd] = $where[1];
                        $where[1] = ':' . $rnd;
                    }

                    if($where[3] instanceof Raw) {
                        $where[3] = $where[3]->getValue();
                    } else {
                        $rnd = self::_bindParam();
                        $bind[$rnd] = $where[3];
                        $where[3] = ':' . $rnd;
                    }
                    
                    $stringParts[] = "{$where[0]}{$where[1]} {$where[2]} {$where[3]}";
                } else {
                    $varList = '';
                    
                    if($where[3] instanceof Raw) {
                        $varList = $where[3]->getValue();
                    } else {
                        //assume whereIn
                        foreach($where[3] as $var) {
                            $rnd = self::_bindParam();
                            $bind[$rnd] = $var;
                            $varList .= ':' . $rnd . ',';
                        }
                        $varList = substr($varList, 0, -1);
                    }

                    $stringParts[] = "{$where[0]}{$where[1]} {$where[2]} ({$varList})";
                }
            } else if (count($where) == 2) {
                if ($where[1] instanceof Having) {
                    $info = $where[1]->build();
                    $stringParts[] = "{$where[0]}({$info[0]})";
                    $bind += $info[1];
                }
            }
        }

        if($on) {
            return [implode(' ', $stringParts), $bind];
        } else {
            return ['HAVING ' . implode(' ', $stringParts), $bind];
        }
    }

    /**
     * Builds where string for the query builder and returns the string and bind variables
     *
     * @param array $wheres
     * @return array [
     *      (string) built where string,
     *      (array) bind variables
     * ]
     */
    public static function where($wheres, $on = false)
    {
        if(!count($wheres)) {
            return ['', []];
        }

        $stringParts = [];
        $bind = [];

        foreach($wheres as $where) {
            if (!count($stringParts)) {
                //if first one connector use blank
                $where[0] = '';
            } else {
                $where[0] = $where[0] . ' ';
            }

            if (count($where) == 4) {
                if (!is_array($where[3])) {
                    //assume where
                    if($where[1] instanceof Raw) {
                        $where[1] = $where[1]->getValue();
                    } else {
                        $rnd = self::_bindParam();
                        $bind[$rnd] = $where[1];
                        $where[1] = ':' . $rnd;
                    }

                    if($where[3] instanceof Raw) {
                        $where[3] = $where[3]->getValue();
                    } else {
                        $rnd = self::_bindParam();
                        $bind[$rnd] = $where[3];
                        $where[3] = ':' . $rnd;
                    }
                    
                    $stringParts[] = "{$where[0]}{$where[1]} {$where[2]} {$where[3]}";
                } else {
                    $varList = '';
                    
                    if($where[3] instanceof Raw) {
                        $varList = $where[3]->getValue();
                    } else {
                        //assume whereIn
                        foreach($where[3] as $var) {
                            $rnd = self::_bindParam();
                            $bind[$rnd] = $var;
                            $varList .= ':' . $rnd . ',';
                        }
                        $varList = substr($varList, 0, -1);
                    }

                    $stringParts[] = "{$where[0]}{$where[1]} {$where[2]} ({$varList})";
                }
            } else if (count($where) == 2) {
                if ($where[1] instanceof Where) {
                    $info = $where[1]->build();
                    $stringParts[] = "{$where[0]}({$info[0]})";
                    $bind += $info[1];
                }
            }
        }

        if($on) {
            return [implode(' ', $stringParts), $bind];
        } else {
            return ['WHERE ' . implode(' ', $stringParts), $bind];
        }
    }

    public static function join(array $joins)
    {
        if(!count($joins)) {
            return ['', []];
        }

        $binds = [];
        $joinStr = [];
        foreach ($joins as $join) {
            $info = $join->build();
            $joinStr[] = $info[0];
            $binds += $info[1];
        }
        return [implode(' ', $joinStr), $binds];
    }

    public static function orderBy($orderby)
    {
        if(count($orderby)) {
            $parts = [];
            foreach ($orderby as $col => $sort) {
                $parts[] = "{$col} {$sort}";
            }
            return 'ORDER BY ' . implode(',', $parts);
        } else {
            return '';
        }
    }

    public static function groupBy($groupby)
    {
        if(count($groupby)) {
            return 'GROUP BY ' . implode(',', $groupby);
        } else {
            return '';
        }
    }

    public static function limit($limit, $skip)
    {
        if ($limit == null && $skip == null) {
            return '';
        } else if ($skip != null && $limit == null) {
            return "LIMIT {$skip},18446744073709551615";
        } else if ($limit != null && $skip == null) {
            return "LIMIT {$limit}";
        } else {
            return "LIMIT {$skip},{$limit}";
        }
    }

    private static $bindCount = 0;
    private static function _bindParam()
    {
        return 'b' . self::$bindCount++;
    }
}