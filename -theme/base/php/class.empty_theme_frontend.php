<?php 
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 
 
/**
 * FRONTEND helper class
 *
 * @since 1.0
 */
if ( ! class_exists( 'Empty_Theme_Frontend' ) && ! is_admin() ) {
	/**
	 * Setup the basic frontend theme functions
	 *
	 * All only frontend related actions and filters
	 *
	 * @since 1.0
	 */
	class Empty_Theme_Frontend {
		/**
		 * Initialize
		 *
		 * @since 1.0
		 *
		 * @return object
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		}
		
		
		
		/**
		 * Include style files
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function styles() {
			wp_register_style( 'empty-theme_layout', get_stylesheet_directory_uri() . '/base/css/layout.css' );
			wp_enqueue_style( 'empty-theme_layout' );
		}
		
		/**
		 * Include script files
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function scripts() {
			wp_register_script( 'empty-theme_functions', get_stylesheet_directory_uri() . '/base/js/functions.js', array( 'jquery' ), '', true );
			wp_enqueue_script( 'empty-theme_functions' );
		}
	}
	
	
	
	new Empty_Theme_Frontend();
}
 
?>