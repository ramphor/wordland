<div class="user-actions">
    <?php foreach ($args as $type => $label) {
        do_action("wordland_property_user_action_{$type}", $label);
    } ?>
</div>
