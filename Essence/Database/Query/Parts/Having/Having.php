<?php

namespace Essence\Database\Query\Parts\Having;

use Essence\Database\Query\PartBuilder;

class Having {
    use Havingable;
    
    public function build()
    {
        return PartBuilder::having($this->having);
    }
}