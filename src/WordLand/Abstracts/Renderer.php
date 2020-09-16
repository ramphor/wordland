<?php
namespace WordLand\Abstracts;

use WordLand\Constracts\Renderer as RendererConstract;

abstract class Renderer implements RendererConstract
{
    protected $metas = array();

    public function __toString()
    {
        return (string) $this->get_content();
    }

    public function add_meta($metaKey, $metaValue, $override = true)
    {
        if (!isset($this->metas[$metaKey]) || $override) {
            $this->metas[$metaKey] = $metaValue;
        } else {
            error_log(sprintf('The meta %s already exists. It is skipped', $metaKey));
        }
    }
}
