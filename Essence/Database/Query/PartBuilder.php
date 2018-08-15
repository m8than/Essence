<?php

namespace Essence\Database\Query;

use Essence\Database\Query\Parts\Where;

class PartBuilder
{
    /**
     * Builds where string for the query builder and returns the string and bind variables
     *
     * @param array $wheres
     * @return array [
     *      (string) built where string,
     *      (array) bind variables
     * ]     * 
     */
    public static function whereStr($wheres)
    {
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
                    $rnd = self::_randString();
                    $bind[$rnd] = $where[3];
                    $stringParts[] = "{$where[0]}{$where[1]} {$where[2]} :{$rnd}";
                } else {
                    $varList = '';
                    //assume whereIn
                    foreach($where[3] as $var) {
                        $rnd = self::_randString();
                        $bind[$rnd] = $var;
                        $varList .= ':' . $rnd . ',';
                    }
                    $varList = substr($varList, 0, -1);
                    $stringParts[] = "{$where[0]}{$where[1]} {$where[2]} ({$varList})";
                }
            } else if (count($where) == 2) {
                if ($where[1] instanceof Where) {
                    $info = $where[1]->getWhereStr();
                    $stringParts[] = "{$where[0]}({$info[0]})";
                    $bind += $info[1];
                }
            }
        }

        return [implode(' ', $stringParts), $bind];
    }

    public static function joinStr($joins)
    {

    }

    private static function _randString()
    {
        return bin2hex(openssl_random_pseudo_bytes(8 / 2));
    }
}