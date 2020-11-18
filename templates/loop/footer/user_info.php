<div class="user-info">
	<div class="user-image">
		<?php echo get_avatar($user ? $user->ID : 0, 32) ?>
	</div>
	<?php echo get_the_author_posts_link(); ?>
</div>
