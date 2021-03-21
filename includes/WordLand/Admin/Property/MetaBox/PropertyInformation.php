<?php
namespace WordLand\Admin\Property\MetaBox;

class PropertyInformation
{
    public function registerMetaboxes()
    {
        add_meta_box(
            'property-information-box',
            __('Property Informations', 'wordland'),
            array($this, 'render'),
            'property',
            'advanced',
            'high'
        );
    }

    // Render metabox content
    public function render()
    {
        ?>
        <div id="wordland-editting-infos-box"></div>
        <?php
    }
}
