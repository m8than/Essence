<?php

namespace Essence\Database\Query\Parts\Where;

use Essence\Database\Query\PartBuilder;

class Where {
    use Whereable;
    
    public function build()
    {
        return PartBuilder::where($this->where);
    }
}