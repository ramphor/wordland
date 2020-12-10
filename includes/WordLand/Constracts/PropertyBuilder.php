<?php
namespace WordLand\Constracts;

interface PropertyBuilder
{
    public function setPost($post);

    public function loadImages();

    public function getGeoLocation();

    public function buildTypes();

    public function buildCategories();

    public function buildTags();

    public function buildLocations();

    public function getPrimaryAgent();
}
