<link rel="stylesheet" id="wordland-css" href="<?php echo plugin_dir_url(WORDLAND_PLUGIN_FILE) . 'assets/css/saved_properties.css'; ?>" type="text/css" media="all">

<div class="wordland-saved-properties">
  <h1 id="saved-properties-heading">
    <span><?php echo esc_html(__('Saved properties', 'wordland')); ?></span>
  </h1>

  <div class="saved-properties-list">
    <div class="save-properties-inner">
      <ul class="save-properties">
        <li class="properties-head">
          <div class="properties-property">Property</div>
          <div class="properties-category">Category</div>
          <div class="properties-status">Status</div>
          <div class="properties-price">Price</div>
          <div class="properties-action">Action</div>
        </li>
        <?php

        foreach ($saved_properties as $saved_property) :
        ?>
          <li class="properties-item">
            <?php
            global $post;
            $post = get_post($saved_property->post_id);
            setup_postdata($post);
            ?>
            <div class="properties-property">
              <figure><a href="<?php echo the_permalink(); ?>"><?php echo get_the_post_thumbnail(); ?></a></figure>
              <p><a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a></p>
            </div>
            <div class="properties-category">
              <p>Category</p>
            </div>
            <div class="properties-status">
              status
            </div>
            <div class="properties-price">
              Price
            </div>
            <div class="properties-action">
              Action
            </div>
          </li>
        <?php
          wp_reset_postdata();
        endforeach; ?>
      </ul>
    </div>
  </div>
</div>