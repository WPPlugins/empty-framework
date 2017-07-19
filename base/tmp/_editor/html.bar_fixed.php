<div class="empty-bar-fixed hidden" id="editor_actions">
	<?php Empty_Settings::viewport_switch( false ); ?>
	<?php Empty_Settings::elements( false ); ?>
	<?php Empty_Settings::grid_switch( false ); ?>
	<?php Empty_Settings::reset_button( false ); ?>
	<?php submit_button( $wording, 'primary', $name, false ); ?>
	<div class="empty-breadcrumbs">
		<?php Empty_Settings::breadcrumbs(); ?>
		<div class="path"></div>
	</div>
</div>