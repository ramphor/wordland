<section class="agent-section wl-agent-main-section">
    <div class="agent-avatar">
        <?php echo get_avatar($agent_id, 480); ?>

        <div class="agent-linked-socials">
            <div class="socials-wrapper">
                <a href="#" target="_blank"><i class="wl wl-facebook"></i></a>
                <a href="#" target="_blank"><i class="wl wl-twitter"></i></a>
                <a href="#" target="_blank"><i class="wl wl-pinterest"></i></a>
                <a href="#" target="_blank"><i class="wl wl-instagram"></i></a>
                <a href="#" target="_blank"><i class="wl wl-linkedin"></i></a>
            </div>
        </div>
    </div>

    <div class="agent-contact-info">
        <div class="contact-heading"><?php __('My details', 'wordland'); ?></div>

        <div class="agent-name"><?php echo $agent_name; ?></div>
        <ul class="contacts">
            <li class="contact contact-phone">
                <i class="wl wl-cellphone"></i> 899 9877 8877
            </li>
            <li class="contact contact-tel">
                <i class="wl wl-telephone"></i> 900 9877 7766
            </li>
            <li class="contact contact-mail">
                <i class="wl wl-mail"></i> lora_demo@gmail.com
            </li>
            <li class="contact contact-skype">
                <i class="wl wl-skype"></i> lora_demo
            </li>
            <li class="contact contact-web">
                <i class="wl wl-globe"></i> mysite.com
            </li>
            <li class="contact contact-org">
                <i class="wl wl-org"></i> Member of: REMAX ASSOCIATION
            </li>
        </ul>
    </div>
</section>
