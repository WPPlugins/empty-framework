/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 
 
<?php 
$breakpoints = new Empty_Breakpoints();
/**
 * @since 1.1.1		Use defined breakpoints
 */
$breaks = json_decode( EMPTY_BREAKS );

foreach ( $breaks as $i => $breakpoint ) {
	$bo = $breakpoint;
	$css = new Empty_Css( $bo->slug );
	?>
	/* Breakpoint: <?php echo $bo->slug; ?> */
	/**
	 * @since 1.1	.hidden class
	 */
	<?php
	$next = $breakpoints->get_next( $bo->slug );
	$hidden = '@media ' . ( $i != 0 ? '( min-width: ' . $bo->break->value . $bo->break->value . ' ) ': '' );
	
	if ( $next ) {
		$hidden .= ( $i != 0 ? 'and ' : '' ) . '( max-width: ' . $next->break['value'] . $next->break['unit'] . ' )';
	}
	
	echo $hidden . ' {';
	?>
		.hidden-<?php echo $bo->slug; ?> {
			display: none;
		}
	}
	
	<?php echo $i != 0 ? '@media ( min-width: ' . $bo->break->value . $bo->break->unit . ' ) {' : '/* @media */'; ?>
		<?php 
		if ( ! empty( $css->selectors['*']['element_spacing_horizontal'] ) ) { 
			$element_spacing_horizontal = array();
			$element_spacing_horizontal['value'] = $css->get_value( $css->selectors['*']['element_spacing_horizontal'] );
			$element_spacing_horizontal['unit'] = $css->get_unit( $css->selectors['*']['element_spacing_horizontal'] );
		} 
		?>
		
		/* Row */
		<?php if ( isset( $element_spacing_horizontal ) ) { ?>
			.row {
				margin-left: -<?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
				margin-right: -<?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
			}
		<?php } ?>
		<?php if ( ! empty( $css->selectors['.wrap']['padding-left'] ) || ! empty( $css->selectors['.wrap']['padding-right'] ) ) { ?>
			.row.full {
				<?php if ( ! empty( $css->selectors['.wrap']['padding-left'] ) ) { ?>
					margin-left: -<?php echo $css->selectors['.wrap']['padding-left']; ?>;
				<?php } ?>
				<?php if ( ! empty( $css->selectors['.wrap']['padding-right'] ) ) { ?>
					margin-right: -<?php echo $css->selectors['.wrap']['padding-right']; ?>;
				<?php } ?>
			}
			<?php if ( isset( $element_spacing_horizontal ) ) { ?>
				.row.full:not( .collapse ) > .row-inner {
					margin-left: -<?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
					margin-right: -<?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
				}
			<?php } ?>
		<?php } ?>
		
		/* Column */
		<?php if ( isset( $element_spacing_horizontal ) || ! empty( $css->selectors['*']['element_spacing_vertical'] ) ) { ?>
			[class*="col-"] {
				<?php if ( isset( $element_spacing_horizontal ) ) { ?>
					padding-left: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
					padding-right: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
				<?php } ?>
				<?php if ( ! empty( $css->selectors['*']['element_spacing_vertical'] ) ) { ?>
					margin-bottom: <?php echo $css->selectors['*']['element_spacing_vertical']; ?>;
				<?php } ?>
			}
		<?php } ?>
	
		.col-<?php echo $bo->slug; ?>-offset-0 { margin-left: 0%; }
		<?php for ( $col = 1; $col <= 12; $col ++ ) { ?>
			.col-<?php echo $bo->slug; ?>-<?php echo $col; ?> { width: <?php echo 100 / ( 12 / $col ); ?>%; }
			.col-<?php echo $bo->slug; ?>-offset-<?php echo $col; ?> { margin-left: <?php echo 100 / ( 12 / $col ); ?>%; }
		<?php } ?>
		
		/* Magic */
		<?php
		if ( $css->selectors ) {
			foreach ( $css->selectors as $selector => $properties ) {
				if ( $selector === '*' ) continue;
				
				echo $selector . ' {';
				
				foreach ( $properties as $property => $value ) {
					echo $property . ': ' . $value . ';';
				}
				
				echo '}';
			}
		}	
		?>
	<?php echo $i != 0 ? '}' : '/* */'; ?>
	
	/* Ensure max-width = total width */
	<?php if ( ! empty( $css->selectors['.row-inner']['max-width'] ) ) { ?>
		/* @media ( min-width: <?php echo $css->selectors['.row-inner']['max-width']; ?> ) {
			.row:not( .full ) .row-inner [class*="col-"]:first-child {
				padding-left: 0;
			}
			.row:not( .full ) .row-inner [class*="col-"]:last-child {
				padding-right: 0;
			}
		} */
	<?php } ?>
	
<?php } ?>