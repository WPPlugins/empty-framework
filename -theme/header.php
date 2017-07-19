<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 

global $brk_sm;
$breakpoints = new Empty_Breakpoints();
$breakpoints_smallest = $breakpoints->get_first();
$brk_sm = $breakpoints_smallest->slug;
?>



<!DOCTYPE html>
<html class="no-js <?php echo detect_browser(); ?>" <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
		
		<title><?php wp_title( get_bloginfo( 'name' ) . ' | ' ); ?></title>
		
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon.ico">
		<link rel="apple-touch-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/touch-icon.png">
		
		<?php wp_head(); ?>
		
		<script>
		jQuery( document ).ready( function() { jQuery( 'html' ).removeClass( 'no-js' ); } ); 
		window.HOME = "<?php echo esc_url( home_url( '/' ) ); ?>";
		window.BROWSER = "<?php echo detect_browser(); ?>";
		</script>
	</head>
	<body <?php body_class(); ?>>
		<div id="page">
			<header id="page-header">
				<div class="wrap">
					<div class="row">
						<div class="row-inner">
							<div id="branding" class="col-<?php echo $brk_sm; ?>-12">
								<a id="site_title" class="site-info" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
							</div>
							<nav id="navigation-main" class="col-<?php echo $brk_sm; ?>-12">
								<div class="trigger"></div>
								<?php 
								wp_nav_menu( array(
									'container'			=> false,
									'fallback_cb'		=> false,
									'theme_location'		=> 'navigation-main'
								) );
								?>
							</nav>
						</div>
					</div>
				</div>
			</header>
			
			<main id="page-main">
				<div class="wrap">