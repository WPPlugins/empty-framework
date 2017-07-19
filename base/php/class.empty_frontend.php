<?php 
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Setup the WordPress frontend
 *
 * @since 2.0
 */
class Empty_Frontend {
	/**
	 * Load files, register WordPress hooks and actions
	 *
	 * @since 2.5.4		Clean content with shortcodes
	 * @since 2.5.1		Include Empty_Parser on frontend
	 * @since 2.0
	 *
	 * @return object
	 */
	public function __construct() {
		if ( is_admin() ) return false;
		require_once( EMPTY_ROOT .  'base/php/class.empty_breakpoints.php' );
		
		/**
		 * @since 2.5.4		Clean content with shortcodes
		 * @since 2.5.1
		 */
		require_once( EMPTY_ROOT .  'base/php/_editor/class.empty_parser.php' );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		add_filter( 'wp_title', array( $this, 'fill_title' ) );
		
		add_filter( 'the_content', array( $this, 'clean' ) );
	}
	
	
	
	/**
	 * Enqueue styles
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_register_style( 'empty_layout', EMPTY_URL . 'base/css/layout.css' );
		wp_enqueue_style( 'empty_layout' );
		wp_register_style( 'empty_layout.dynamic', EMPTY_URL . 'base/css/layout.dynamic.css' );
		wp_enqueue_style( 'empty_layout.dynamic' );
	}
	
	/**
	 * Enqueue scripts
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_register_script( 'empty_functions', EMPTY_URL . 'base/js/functions.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'empty_functions' );
	}
	
	
	
	/**
	 * Fill empty title
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function fill_title( $title ) {
		if ( empty( $title ) && ( is_home() || is_front_page() ) ) return get_bloginfo( 'name' );
		
		return $title;
	}
	
	/**
	 * Clean content
	 *
	 * @since 2.5.4
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function clean( $content ){
		$array = array(
		    '<p>[' 		=> '[',
		    ']</p>' 	=> ']',
		    ']<br />' 	=> ']'
		);
		$content = strtr( $content, $array );
		
		return $content;
	}
}

?>