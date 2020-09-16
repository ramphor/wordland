<?php
namespace WordLand\Builder;

use WordLand\Constracts\Renderer;

class RendererBuilder
{
    protected $renderer;

    public function __construct($renderer = null)
    {
        if (!is_null($renderer)) {
            $this->setRenderer($renderer);
        }
    }

    public function setRenderer($renderer)
    {
        if (!is_a($renderer, Renderer::class)) {
            error_log(sprintf('The $renderer must be an instance of %s', Renderer::class));
            return;
        }

        $this->renderer = $renderer;
    }

    public function build($args = array())
    {
        if (is_array($args)) {
            foreach ($args as $key => $val) {
                $method = preg_replace_callback(array('/^([a-z])/', '/[_|-]([a-z])/', '/.+/'), function ($matches) {
                    if (isset($matches[1])) {
                        return strtoupper($matches[1]);
                    }
                    return sprintf('set%s', $matches[0]);
                }, $key);

                if (method_exists($this->renderer, $method)) {
                    $this->renderer->$method($val);
                } else {
                    $this->renderer->add_meta($key, $val);
                }
            }
        }
    }

    public function getRenderer()
    {
        return $this->renderer;
    }
}
