<?php

namespace Essence\Database\Query\Parts;

use Essence\Database\Query\PartBuilder;

class Where {
    use Whereable;
    public function getWhereStr()
    {
        return PartBuilder::whereStr($this->where);
    }
}