<?php

namespace Essence\Database\ORM;

use Essence\Database\Query\Query;

class RelationQuery extends Query
{
    private $model;

    /**
     * Sets model to use when retrieving the data in the relationship
     *
     * @param string $model
     * @return self
     */
    public function setModel($model) {
        $this->model = $model;
        return $this;
    }

    /**
     * Gets model to use when retrieving relationship data
     *
     * @return string
     */
    public function getModel() {
        return $this->model;
    }
}