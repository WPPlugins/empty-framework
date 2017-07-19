<div id="empty_helpers">
	<div id="empty-helper_gutter"></div>
	<textarea class="wp-editor-area" autocomplete="off" cols="100" rows="10" name="content" id="content" aria-hidden="true"><?php echo $post->post_content; ?></textarea>
	<?php
	wp_editor( '', 'empty-wysiwyg', array(
		'media_buttons'	=> false,
		'wpautop'		=> false
	) );
	?>
</div>