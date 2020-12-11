<?php
namespace WordLand\Abstracts;

use WordLand\Constracts\PropertyBuilder;
use WordLand\Property;
use WordLand\GeoLocation;

abstract class PropertyBuilderAbstract implements PropertyBuilder
{
    protected $property;
    protected $originalPost;

    public function __construct($post = null)
    {
        $this->reset();
        if ($post) {
            $this->setPost($post);
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function reset()
    {
        $this->property = new Property();
    }

    public function setPost($post)
    {
        if (!is_a($post, \WP_Post::class)) {
            return;
        }

        $this->originalPost = $post;
    }


    public function buildBaseData()
    {
        if (is_null($this->originalPost)) {
            return;
        }
        $this->property->ID   = (int)$this->originalPost->ID;
        $this->property->name = apply_filters(
            'the_title',
            $this->originalPost->post_title
        );

        $this->property->description = get_the_excerpt($this->originalPost);
    }

    public function buildWordLandData()
    {
        if (isset($this->originalPost->property_id)) {
            $this->property->price      = floatval($this->originalPost->price);
            $this->property->unit_price = floatval($this->originalPost->unit_price);
            $this->property->size       = intval($this->originalPost->size);
            $this->property->bedrooms   = intval($this->originalPost->bedrooms);
            $this->property->bathrooms  = intval($this->originalPost->bathrooms);

            if (isset($this->originalPost->latitude) && $this->originalPost->latitude) {
                $this->property->geolocation = new GeoLocation(
                    floatval($this->originalPost->latitude),
                    floatval($this->originalPost->longitude)
                );
            }
        }
    }

    // The alias of buildBaseData
    public function build()
    {
        $this->buildBaseData();
        $this->buildWordLandData();
        $this->getPrimaryAgent();
    }

    public function getProperty($scope = 'global')
    {
        if (!$this->property->ID) {
            return;
        }

        return apply_filters_ref_array(
            'wordland_builder_get_property',
            array(
                &$this->property,
                $this,
                $scope
            )
        );
    }
}
