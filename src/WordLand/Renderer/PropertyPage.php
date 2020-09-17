<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\Manager\PropertyBuilderManager;
use WordLand\Template;

class PropertyPage extends Renderer
{
    protected $status = 'any';
    protected $showTitle = true;

    protected $propertyId;

    public function setPropertyId($id)
    {
        $this->propertyId = $id;
    }

    public function get_content()
    {
        $propertyPost = get_post($this->propertyId);
        if (!$propertyPost || $propertyPost->post_type !== 'property') {
            return;
        }
        $propertyBuilder = PropertyBuilderManager::getBuilder($propertyPost);
        $propertyBuilder->build();
        $property = $propertyBuilder->getProperty();

        return Template::render('content/single-property', array(
            'property' => $property,
        ), 'wordland_single_property', false);
    }
}
