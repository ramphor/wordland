<div class="wordland-messages-inbox">
    <div class="messages-inbox-inner">
        <div class="wordland-message-heading">
            <h2 class="messages-heading"><?php echo esc_html(__('Dashboard – Inbox', 'wordland')); ?></h2>
            <div class="messages-sub-heading"><?php echo esc_html(sprintf(
                __('You have %d unread messages', 'wordland'),
                0
            )); ?></div>
        </div>


        <div class="messages-list">
            <ul class="messages">
                <?php while($messages->have_posts()): ?>
                    <?php $messages->the_post(); ?>
                    <li class="message">
                        <div class="message-subject">
                            <?php the_title(); ?>
                        </div>
                    </li>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </ul>
        </div>
    </div>
</div>
