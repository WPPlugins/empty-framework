<?php 
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



define( 'THEME_ROOT', trailingslashit( dirname( __FILE__ ) ) );

require_once( THEME_ROOT . 'base/php/class.empty_theme_global.php' );
require_once( THEME_ROOT . 'base/php/class.empty_theme_backend.php' );
require_once( THEME_ROOT . 'base/php/class.empty_theme_frontend.php' );



/**
 * Detect browser and return readable class
 *
 * @since 1.0
 *
 * @return string
 */
function detect_browser() {
	global $is_chrome, $is_edge, $is_gecko, $is_IE, $is_safari;
	
	if ( $is_chrome ) 	return 'chrome';
	if ( $is_edge ) 	return 'edge';
	if ( $is_gecko ) 	return 'gecko';
	if ( $is_IE ) 		return 'ie';
	if ( $is_safari ) 	return 'safari';
}

?>