<ul class="property-categories">
    <?php foreach($categories as $category): ?>
    <li>
        <a href="<?php echo $category->link(); ?>"><?php echo $category->name; ?></a>
    </li>
    <?php endforeach; ?>
</ul>
