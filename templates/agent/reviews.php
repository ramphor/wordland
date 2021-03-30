<section class="agent-reviews">
    <div class="agent-review-inner">
        <h2 class="agent-review-heading">
            <span><?php echo __('Agent Reviews', 'wordland'); ?></span>
        </h2>
        <div class="agent-review-subheading">
            <strong><?php echo esc_html(__('Write a Review', 'wordland')); ?></strong>
        </div>

        <div class="agent-review-content">
            <?php if (is_user_logged_in()): ?>
                <?php echo $open_form; ?>
                    <div class="agent-review-rating">
                        <span class="rating-text">
                            <?php echo esc_html(__('Your Rating & Review', 'wordland')); ?>
                        </span>
                        <?php echo $embrati; ?>
                        <input type="hidden" class="agent-rating" name="agent_rating" />
                        <input type="hidden"name="agent_id" value="<?php echo $agent_id; ?>">
                    </div>

                    <div class="agent-review">
                        <div class="agent-review-title edit-field">
                            <div class="input-wrap">
                                <label for="wordland-review-title"><?php echo esc_html(__('Review Title', 'wordland')); ?></label>
                                <input type="text" id="wordland-review-title" name="review_title" />
                            </div>

                            <div class="input-wrap">
                                <label for="wordland-review-body"><?php echo esc_html(__('Review Title', 'wordland')); ?></label>
                                <textarea type="text" name="review_body"></textarea>
                            </div>
                        </div>
                    </div>


                    <div class="submit-actions">
                        <button id="wordland-submit-review"><?php echo esc_html(__('Write Review', 'wordland')) ?></button>
                    </div>

                <?php echo $close_form; ?>
            <?php else: ?>
                <div class="agent-review-message">
                    <?php echo sprintf(
                        __('You need to <a href="%s" id="%s">login in</a> order to post a review', 'wordland'),
                        esc_attr($login_url),
                        'wordland-login-url'
                    ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
