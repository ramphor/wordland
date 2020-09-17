<?php
namespace WordLand;

use WordLand\Abstracts\Data;

class Property extends Data
{
    protected $name;
    protected $metas;

    public function __set($key, $name)
    {
        $this->metas[$key] = $name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
