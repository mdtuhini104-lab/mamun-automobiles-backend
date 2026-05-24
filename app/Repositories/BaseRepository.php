<?php

namespace App\Repositories;

abstract class BaseRepository
{
    protected $model;

    public function __construct($model = null)
    {
        $this->model = $model;
    }
}
