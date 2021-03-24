<div class="my-profile-header-blocks">
    <div class="header-blocks-inner">
        <?php foreach($blocks as $block):?>
        <div class="header-block">
            <?php if ($block['heading_position'] === 'top'): ?>
            <h2 class="block-heading"><?php echo $block['heading']; ?></h2>
            <div class="block-content"><?php echo $block['render']; ?></div>
            <?php else: ?>
            <div class="block-content"><?php echo $block['render']; ?></div>
            <div class="block-heading"><?php echo $block['heading']; ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
