<?php 
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 
 
/**
 * BACKEND helper class
 *
 * @since 1.0
 */ 
if ( ! class_exists( 'Empty_Theme_Backend' ) && is_admin() ) {
	/**
	 * Setup the basic frontend theme functions
	 *
	 * All only backend related actions and filters
	 *
	 * @since 1.0
	 */
	class Empty_Theme_Backend {
		/**
		 * Initialize
		 *
		 * @since 1.0
		 *
		 * @return object
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		}
		
		
		
		/**
		 * Include style files
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function styles() {
		}
		
		/**
		 * Include script files
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function scripts() {
		}
	}
	
	
	
	new Empty_Theme_Backend();
}
 
?>