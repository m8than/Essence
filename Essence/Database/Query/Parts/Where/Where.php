<?php

namespace Essence\Database\Query\Parts\Where;

use Essence\Database\Query\PartBuilder;

class Where {
    use Whereable;
    
    public function getStr()
    {
        return PartBuilder::whereStr($this->where);
    }

    public function getStrNoBinds()
    {
        return PartBuilder::whereStrNoBinds($this->where);
    }
}