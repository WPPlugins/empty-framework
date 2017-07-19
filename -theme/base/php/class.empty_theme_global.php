<?php 
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 


/**
 * GLOBAL helper class
 *
 * @since 1.0
 */
if ( ! class_exists( 'Empty_Theme_Global' ) ) {
	/**
	 * Setup global theme functions and actions
	 *
	 * Put actions and filters here which are required in the 
	 * WordPress backend as well as on the frontend of your theme
	 *
	 * @since 1.0
	 */
	class Empty_Theme_Global {
		/**
		 * Initialize
		 *
		 * @since 1.0
		 *
		 * @return object
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'menus' ) );
		}
		
		
		
		/**
		 * Register menus
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function menus() {
			register_nav_menus( array(
				'navigation-main'	=> __( 'Main Navigation','empty-theme' ),
				'navigation-footer'	=> __( 'Footer Navigation','empty-theme' )
			) );
		}
	}
	
	
	
	new Empty_Theme_Global();
}

?>