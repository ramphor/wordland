<?php
use WordLand\Modules\FavoriteProperty;

global $property;

show_post_in_collection_status(
    FavoriteProperty::MODULE_NAME,
    $property->ID
);
