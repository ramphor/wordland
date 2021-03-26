<div class="user-info">
    <div class="user-image">
        <?php echo get_avatar($user ? $user->ID : 0, 32) ?>
    </div>
    <a
        href="<?php echo get_author_posts_url($user->ID); ?>"
        title="<?php echo esc_attr(sprintf(
            __('Agent %s\'s profile', 'wordland'),
            $user->display_name
        )); ?>"
    >
        <?php echo $user->display_name; ?>
    </a>
</div>
