<?php
namespace WordLand;

use WordLand\Abstracts\Data;

class Property extends Data
{
    public $ID;
    public $name;
    public $metas = array();

    public function setMeta($key, $value)
    {
        $this->metas[$key] = $value;
    }

    public function getMeta($key, $value)
    {
        return $this->metas[$key] = $value;
    }

    public function is_visible() {
        return true;
    }
}
