<div class="wordland-saved-searches">
    <h1 id="saved-searches-heading">
        <span><?php echo esc_html(__('Saved searches', 'wordland')); ?></span>
    </h1>

    <div class="saved-searches-list">
        <div class="save-searches-inner">
            <ul class="save-searches">
                <?php foreach($saved_searches as $saved_search): ?>
                <li class="search-item">
                    <a href="<?php echo wordland_go_to_search_url($saved_search); ?>">
                        <?php if (isset($saved_search->search_name)): ?>
                            <?php echo esc_html($saved_search->search_name); ?>
                        <?php else: ?>
                            <?php echo esc_html(__('My saved search', 'wordland')); ?>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
