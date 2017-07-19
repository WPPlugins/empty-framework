/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 
 
<?php 
$breakpoints = new Empty_Breakpoints();
/**
 * @since 1.1	Use defined breaks
 */
$breaks = json_decode( EMPTY_BREAKS );

$options = get_option( 'empty_options' );
$colors = $options['editor_colors'];
$slugs = '';

$color_wrap = empty( $colors['wrap'] ) ? 'rgb(224, 240, 248)' : $colors['wrap'];
$color_row = empty( $colors['row'] ) ? 'rgb(224, 240, 248)' : $colors['row'];
$color_element = empty( $colors['element'] ) ? 'rgba(175, 255, 245, 0.5)' : $colors['element'];
$color_select_row = empty( $colors['select_row'] ) ? 'rgb(0, 180, 245)' : $colors['select_row'];
$color_select_element = empty( $colors['select_element'] ) ? 'rgb(50, 200, 210)' : $colors['select_element'];
?>

/**
 * Editor
 */
#empty_editor {
	background-color: <?php echo $color_wrap; ?>;
}
#empty_editor .row.active {
	outline: 1px dashed <?php echo $color_select_row; ?>;
	position: relative;
	z-index: 99;
}
#empty_editor [data-empty-element]:hover .empty-element_wrap,
#empty_editor [data-empty-element]:active .empty-element_wrap,
#empty_editor [data-empty-element].active .empty-element_wrap {
	outline: 1px dashed <?php echo $color_select_element; ?>;
	position: relative;
	z-index: 99;
}
#empty_editor [data-empty-element]:hover .ui-resizable-handle,
#empty_editor [data-empty-element]:active .ui-resizable-handle,
#empty_editor [data-empty-element].active .ui-resizable-handle {
	display: block;
	background: <?php echo $color_select_element; ?>;
}



<?php
foreach ( $breaks as $i => $breakpoint ) {
	$barr = $breakpoint; 
	$bo = (object) $breakpoint;
	$css = new Empty_Css( $bo->slug );
	
	$slugs .= ( $i == 0 ? '' : ' ' ) . $bo->slug;
	$prefix = '[data-empty_breakpoints*="' . $slugs . '"] #empty_editor';
	?>
	/* Breakpoint: <?php echo $bo->slug; ?> */
	/**
	 * @since 1.1	.hidden class
	 */
	[data-empty_breakpoints_current="<?php echo $bo->slug; ?>"] .hidden-<?php echo $bo->slug; ?> {
		opacity: 0.1;
	}
	<?php
	$next = $breakpoints->get_next( $bo->slug );
	if ( $next ) {
		?>
		[data-empty_breakpoints_current="<?php echo $bo->slug; ?>"] #empty_editor {
			max-width: <?php echo $next->break['value'] . $next->break['unit']; ?>;
		} 
		<?php
	}
	?>
	
	<?php 
	if ( ! empty( $css->selectors['*']['element_spacing_horizontal'] ) ) { 
		$element_spacing_horizontal = array();
		$element_spacing_horizontal['value'] = $css->get_value( $css->selectors['*']['element_spacing_horizontal'] );
		$element_spacing_horizontal['unit'] = $css->get_unit( $css->selectors['*']['element_spacing_horizontal'] );
	} 
	?>
	
	<?php echo $prefix; ?> .ui-resizable-e {
		right: <?php echo $element_spacing_horizontal['value'] / 2 - 3 . $element_spacing_horizontal['unit']; ?>;
	}
	<?php echo $prefix; ?> .ui-resizable-w {
		left: <?php echo $element_spacing_horizontal['value'] / 2 - 3 . $element_spacing_horizontal['unit']; ?>;
	}
	
	/* Row */
	<?php if ( isset( $element_spacing_horizontal ) ) { ?>
		[data-empty_breakpoints_current="<?php echo $bo->slug; ?>"] #empty_helpers #empty-helper_gutter {
			width: <?php echo $element_spacing_horizontal['value'] . $element_spacing_horizontal['unit']; ?>;
		}
		<?php echo $prefix; ?> .row {
			margin-left: -<?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
			margin-right: -<?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
		}
	<?php } ?>
	<?php echo $prefix; ?> .row .row-inner {
		background: repeating-linear-gradient(
			90deg, 
			<?php echo $color_row; ?> 0px, 
			<?php echo $color_row; ?> <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>, 
			white <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>, 
			white calc( 8.333333333% - <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?> ), 
			<?php echo $color_row; ?> calc( 8.333333333% - <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?> ), 
			<?php echo $color_row; ?> 8.333333333%
		);
	}
	.row.collapse .row-inner {
		background: repeating-linear-gradient(
			90deg, 
			<?php echo $color_row; ?> 0px, 
			<?php echo $color_row; ?> 0.5px, 
			white 2px, 
			white calc( 8.333333333% - 0.5px ), 
			<?php echo $color_row; ?> calc(8.333333333% - 0.5px ), 
			<?php echo $color_row; ?> 8.333333333%
		) !important;
	}
	
	<?php if ( ! empty( $css->selectors['.wrap']['padding-left'] ) || ! empty( $css->selectors['.wrap']['padding-right'] ) ) { ?>
		<?php echo $prefix; ?> .row.full {
			<?php if ( ! empty( $css->selectors['.wrap']['padding-left'] ) ) { ?>
				margin-left: -<?php echo $css->selectors['.wrap']['padding-left']; ?>;
			<?php } ?>
			<?php if ( ! empty( $css->selectors['.wrap']['padding-right'] ) ) { ?>
				margin-right: -<?php echo $css->selectors['.wrap']['padding-right']; ?>;
			<?php } ?>
		}
		<?php if ( isset( $element_spacing_horizontal ) ) { ?>
			<?php echo $prefix; ?> .row.full:not( .collapse ) > .row-inner {
				margin-left: -<?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
				margin-right: -<?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
			}
		<?php } ?>
	<?php } ?>
	
	/* Column */
	<?php if ( isset( $element_spacing_horizontal ) || ! empty( $css->selectors['*']['element_spacing_vertical'] ) ) { ?>
		<?php echo $prefix; ?> [class*="col-"] {
			<?php if ( isset( $element_spacing_horizontal ) ) { ?>
				padding-left: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
				padding-right: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
			<?php } ?>
			<?php if ( ! empty( $css->selectors['*']['element_spacing_vertical'] ) ) { ?>
				margin-bottom: <?php echo $css->selectors['*']['element_spacing_vertical']; ?>;
			<?php } ?>
		}
		
		<?php if ( ! empty( $css->selectors['*']['element_spacing_vertical'] ) ) { ?>
			<?php echo $prefix; ?> [class*="col-"]::after {
				content: '';
				position: absolute;
				top: 100%;
				<?php if ( isset( $element_spacing_horizontal ) ) { ?>
					left: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
					right: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
				<?php } ?> 
				height: <?php echo $css->selectors['*']['element_spacing_vertical']; ?>;
				background-color: <?php echo $color_element; ?>;
			}
			<?php echo $prefix; ?> .row.collapse [class*="col-"]::after {
				<?php if ( isset( $element_spacing_horizontal ) ) { ?>
					left: 0px !important;
					right: 0px !important;
				<?php } ?> 
			}
		<?php } ?>
		<?php echo $prefix; ?> .ui-sortable-placeholder::before {
			content: '';
			position: absolute;
			top: 0;
			height: 100%;
			<?php if ( isset( $element_spacing_horizontal ) ) { ?>
				left: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
				right: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
			<?php } ?> 
			background-color: <?php echo $color_element; ?>;
		}
	<?php } ?>
	<?php if ( ! empty( $css->selectors['.row + .row']['margin-top'] ) ) { ?>
		<?php echo $prefix; ?> .row + .row::before {
			content: '';
			position: absolute;
			top: -<?php echo $css->get_value( $css->selectors['.row + .row']['margin-top'] ) - 1 . $css->get_unit( $css->selectors['.row + .row']['margin-top'] ); ?>;
			<?php if ( isset( $element_spacing_horizontal ) ) { ?>
				left: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
				right: <?php echo $element_spacing_horizontal['value'] / 2 . $element_spacing_horizontal['unit']; ?>;
			<?php } ?>
			height: <?php echo $css->get_value( $css->selectors['.row + .row']['margin-top'] ) - 2 . $css->get_unit( $css->selectors['.row + .row']['margin-top'] ); ?>;
			background-color: <?php echo $color_row; ?>;
		}
	<?php } ?>

	<?php echo $prefix; ?> .col-<?php echo $bo->slug; ?>-offset-0 { margin-left: 0%; }
	<?php for ( $col = 1; $col <= 12; $col ++ ) { ?>
		<?php echo $prefix; ?> .col-<?php echo $bo->slug; ?>-<?php echo $col; ?> { width: <?php echo 100 / ( 12 / $col ); ?>%; }
		<?php echo $prefix; ?> .col-<?php echo $bo->slug; ?>-offset-<?php echo $col; ?> { margin-left: <?php echo 100 / ( 12 / $col ); ?>%; }
	<?php } ?>
	
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