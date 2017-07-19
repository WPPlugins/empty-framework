<div class="empty-bar" data-empty_do="toggle:target:#editor_actions:hidden" data-empty_trigger="scroll" data-empty_trigger_distance="-100">
	<div>
		<?php Empty_Settings::viewport_switch(); ?>
		<div class="empty-setting-link">					
			<a href="<?php menu_page_url( 'empty_breakpoints' ); ?>"><?php _e( 'Edit Breakpoints', 'empty' ); ?></a>
		</div>
		<?php submit_button( $wording, 'primary', $name, false ); ?>
	</div>
	<div>
		<?php Empty_Settings::elements(); ?>
		<?php Empty_Settings::grid_switch(); ?>
		<?php Empty_Settings::reset_button(); ?>
		<div class="empty-setting-link">
			<a href="<?php menu_page_url( 'empty_grid' ); ?>"><?php _e( 'Edit Grid', 'empty' ); ?></a>
		</div>
	</div>
	<div class="empty-breadcrumbs">
		<?php Empty_Settings::breadcrumbs(); ?>
		<div class="path"></div>
	</div>
</div>