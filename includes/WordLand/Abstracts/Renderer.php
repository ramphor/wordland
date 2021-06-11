<?php
namespace WordLand\Abstracts;

use WordLand\Constracts\Renderer as RendererConstract;
use WordLand\Template;

abstract class Renderer implements RendererConstract
{
    protected $props = array();
    protected $metas = array();
    protected $query;
    protected $title;

    public function __construct($query = null)
    {
        $this->query = &$query;
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

    public function addProp($prop_name, $prop_value)
    {
        $this->props[$prop_name] = apply_filters(
            "wordland_renderer_set_{$this->get_name()}_{$prop_name}_prop",
            $prop_value
        );
    }

    public function setProps($props)
    {
        if (is_array($props)) {
            $this->props = $props;
        }
    }

    public function getProp($name, $defaultValue = null)
    {
        if (isset($this->props[$name])) {
            return $this->props[$name];
        }
        return $defaultValue;
    }

    protected function getHeaderContent()
    {
        if (!$this->title) {
            return;
        }

        return Template::render(
            'common/header_text',
            array('text' => $this->title),
            false
        );
    }
}
