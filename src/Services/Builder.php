<?php

namespace Ifresh\FilemakerModel\Services;

use Ifresh\FilemakerModel\FilemakerModel;

class Builder
{
    protected $model;
    private $query = [];

    public function __construct(FilemakerModel $model)
    {
        $this->model = $model;
    }

    public function where($column, $operator, $value = null)
    {
        if (! $value) {
            $value    = $operator;
            $operator = '';
        }
        $this->query[] = [$column => $operator . $value];

        return $this;
    }

    public function get()
    {
        $result =  $this->model->api()->query($this->query);

        return $this->model->parseRecords($result);
    }
}
