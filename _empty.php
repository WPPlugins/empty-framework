<?php
/*
Plugin Name: empty framework
Plugin URI: http://www.empty-framework.com
Description: empty is a framework for the rapid grid-based development of responsive WordPress Themes.
Version: 2.5.8
Author: Valentin Alisch
Author URI: http://www.valentinalisch.de
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0
Text Domain: empty
Domain Path: /languages
*/



/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */ 

define( 'EMPTY_VERSION', '2.5.8' );
define( 'EMPTY_ROOT', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'EMPTY_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
load_plugin_textdomain( 'empty', false, basename( dirname( __FILE__ ) ) . '/languages/' );

require_once( EMPTY_ROOT . 'base/php/functions.php' );



require_once( EMPTY_ROOT . 'base/php/class.empty_admin.php' );
require_once( EMPTY_ROOT . 'base/php/class.empty_frontend.php' );

new Empty_Admin();
new Empty_Frontend();
if ( file_exists( EMPTY_ROOT . 'pro/_empty_pro.php' ) ) {
	require_once( EMPTY_ROOT . 'pro/_empty_pro.php' );	
}

?>