<?php
namespace WordLand\Abstracts;

use WordLand\Constracts\Renderer as RendererConstract;

abstract class Renderer implements RendererConstract
{
    protected $props = array();
    protected $metas = array();
    protected $query;
    protected $title;

    public function __construct($query)
    {
        if (is_a($query, Query::class)) {
            $this->query = $query;
        }
    }

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

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setProps($props)
    {
        if (is_array($props)) {
            $this->props = $props;
        }
    }
}
