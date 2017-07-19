/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 
 
<?php 
$breakpoints = new Empty_Breakpoints();
/**
 * @since 1.0.1		Use defined breakpoints
 */
$breaks = json_decode( EMPTY_BREAKS ); 
 
$slugs = '';

foreach ( $breaks as $i => $breakpoint ) { 
	$bo = $breakpoint;
	$css = new Empty_Css( $bo->slug );
	
	$slugs .= ( $i == 0 ? '' : ' ' ) . $bo->slug;
	$prefix = '[data-empty_breakpoints*="' . $slugs . '"] ';
	?>
	/* Breakpoint: <?php echo $bo->slug; ?> */	
	/* Magic */
	<?php
	if ( $css->selectors ) {
		foreach ( $css->selectors as $selector => $properties ) {
			if ( $selector === '*' ) continue;
			
			$new_selector = array();
			foreach ( explode( ',' , $selector ) as $s ) {
				$s = trim( $s );
				$new_selector[] = $prefix . ( $s === '.wrap' ? ' ' : ' ' ) . ( $s === 'body' ? '' : $s );
			}
			$selector = implode( ', ', $new_selector );
			
			echo $selector . ' {';
			
			foreach ( $properties as $property => $value ) {
				echo $property . ': ' . $value . ';';
			}
			
			echo '}';
		}
	}
	?>
	
<?php } ?>