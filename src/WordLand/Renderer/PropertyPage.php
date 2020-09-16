<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;

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
        $property = get_post($this->propertyId);
        if (!$property || $property->post_type !== 'property') {
            return;
        }
    }
}
