<?php
namespace WordLand\Abstracts;

class ManagerAbstract
{
    protected static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct()
    {
    }
}
