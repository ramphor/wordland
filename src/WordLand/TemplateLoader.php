<?php
namespace WordLand;

use WordLand\Renderer\PropertyPage;
use WordLand\Builder\RendererBuilder;

class TemplateLoader
{
    protected $isThemeSupport = false;

    public function load()
    {
        if (get_theme_support('wordland')) {
            $this->isThemeSupport = true;
        }
        if ($this->isThemeSupport) {
            add_filter('template_include', array($this, 'loadCustomTemplate'));
        } else {
            add_action('template_redirect', array($this, 'renderPropertyContentForUnsupportThemes'));
        }
    }

    protected function getDefaultTemplateFile()
    {
        if (is_singular('property')) {
            $defaultFile = 'single-property.php';
        } else {
            $defaultFile = '';
        }
        return $defaultFile;
    }

    protected function getSearchTemplateFiles($defaultTemplate)
    {
        $templates   = apply_filters('wordland_template_loader_files', array(), $defaultTemplate);
        $templates[] = 'wordland.php';

        if (is_page_template()) {
            $pageTemplate = get_page_template_slug();

            if ($pageTemplate) {
                $validated_file = validate_file($pageTemplate);
                if (0 === $validated_file) {
                    $templates[] = $pageTemplate;
                } else {
                    error_log("wordland: Unable to validate template path: \"$pageTemplate\". Error Code: $validated_file.");
                }
            }
        }

        if (is_singular('property')) {
            $object       = get_queried_object();
            $name_decoded = urldecode($object->post_name);
            if ($name_decoded !== $object->post_name) {
                $templates[] = "single-property-{$name_decoded}.php";
            }
            $templates[] = "single-property-{$object->post_name}.php";
        }

        $templates[] = $defaultTemplate;
        $templates[] = wordland()->template_path() . $defaultTemplate;

        return array_unique($templates);
    }

    public function loadCustomTemplate($template)
    {
        if (is_embed()) {
            return $template;
        }
        $defaultTemplate = $this->getDefaultTemplateFile();
        if (!$defaultTemplate) {
            return $template;
        }
        $searchFiles = $this->getSearchTemplateFiles($defaultTemplate);
        $template    = locate_template($searchFiles);

        if (! $template || WORDLAND_TEMPLATE_DEBUG_MODE) {
            $template = wordland()->plugin_path() . '/templates/' . $defaultTemplate;
        }

        return $template;
    }

    public function renderPropertyContentForUnsupportThemes()
    {
        if (is_property()) {
            $this->unsupportedPageSingleProperty();
        }
    }

    public function unsupportedPageSingleProperty()
    {
        add_filter('the_content', array($this, 'renderPropertyContent'));
    }

    public function renderPropertyContent($content)
    {
        if (!is_property()) {
            return $content;
        }

        $builder = new RendererBuilder(new PropertyPage());
        $builder->build(array(
            'property_id' => get_the_ID(),
            'show_title' => false
        ));
        $renderer = $builder->getRenderer();

        // Render the output content
        return $renderer->get_content();
    }
}
