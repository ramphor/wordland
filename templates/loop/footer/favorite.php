<?php
use WordLand\Modules\FavoriteProperty;

global $property;

show_post_in_collection_status(
    $property->ID,
    FavoriteProperty::MODULE_NAME
);
