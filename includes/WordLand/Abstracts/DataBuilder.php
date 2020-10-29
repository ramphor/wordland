<?php
namespace WordLand\Abstracts;

use WordLand\Constracts\DataBuilder as DataBuilderConstract;

abstract class DataBuilder implements DataBuilderConstract
{
    protected $isBuildContent = false;
    
    public function enableGetContent()
    {
        $this->isBuildContent = true;
    }
}
