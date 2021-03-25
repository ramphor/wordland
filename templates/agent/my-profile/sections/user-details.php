<div class="section-header">
    <h2 class="section-header_text">
        <span><?php echo $heading_text; ?></span>
    </h2>
</div>
<div class="section-content profile__settings">
    <p class="section-description"><?php echo $description; ?></p>

    <div class="agent-position">
        <div class="edit-display-name">
            <div class="edit-field display-name">
                <label for="wordland-agent-position" class="field-label">
                    <?php echo esc_html(__('Title/Position', 'wordland')); ?>
                </label>
                <div class="input-wrap">
                    <input
                        id="wordland-agent-position"
                        type="text"
                        class="field-editing"
                        name="agent_postion"
                        value=""
                    />
                </div>
            </div>
        </div>
    </div>
</div>
