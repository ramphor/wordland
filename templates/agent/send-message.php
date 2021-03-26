<div class="wordland-message-sender">
    <div class="message-sender-inner">
        <h2 class="message-sender-heading"><?php echo __('Contact Me', 'wordland') ?></h2>

        <?php do_action('wordland_before_message_sender_content'); ?>

        <?php echo $open_form; ?>
            <div class="message-contact-infos">
                <div class="message-contact-fields">
                    <div class="contact-field name">
                        <label><?php echo esc_html(__('Your name', 'wordland')) ?></label>
                        <div class="input-wrap">
                            <input type="text" name="from_name" value="<?php echo $from_name; ?>">
                        </div>
                    </div>

                    <div class="contact-field email">
                        <label><?php echo esc_html(__('Your email', 'wordland')) ?></label>
                        <div class="input-wrap">
                            <input type="text" name="from_email" value="<?php echo $from_email; ?>">
                        </div>
                    </div>

                    <div class="contact-field phone">
                        <label><?php echo esc_html(__('Your phone', 'wordland')) ?></label>
                        <div class="input-wrap">
                            <input type="text" name="from_phone" <?php echo $from_phone; ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="message-body">
                <label><?php echo esc_html(__('Message', 'wordland')); ?></label>
                <div class="input-wrap">
                    <textarea name="message_body"><?php echo $message_body; ?></textarea>
                </div>
            </div>

            <input type="hidden" name="object_id" value="<?php echo $current_agent->ID ?>">
            <input type="hidden" name="object_type" value="<?php echo $user_type; ?>">

            <div class="submit-actions">
                <button class="primary-button" name="send-email" value="true">Send Email</button>
                <button class="primary-button" name="send-dm" value="true">Send Private Message</button>
            </div>
        <?php echo $close_form; ?>

        <?php do_action('wordland_after_message_sender_content'); ?>
    </div>
</div>
