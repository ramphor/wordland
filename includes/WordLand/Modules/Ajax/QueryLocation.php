<?php
namespace WordLand\Modules\Ajax;

use WordLand\Abstracts\ModuleAbstract;

/**
 * QueryLocation class
 *
 * This class use to query all locations in WordLand and suggest to end user
 */
class QueryLocation extends ModuleAbstract
{
    const MODULE_NAME = 'query_location';

    public function get_name()
    {
        return static::MODULE_NAME;
    }
}
