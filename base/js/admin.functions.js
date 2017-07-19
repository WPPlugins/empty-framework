/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Setup
 *
 * @since 1.0
 *
 * @return void
 */
jQuery( window ).on( 'load', function( $ ) {
	/**
	 * Listener
	 */
	jQuery( '[data-empty_do][data-empty_trigger="load"]' ).each( function( index ) {
		empty_DO( jQuery( this ).data( 'empty_do' ), jQuery( this ) );	
	} );
} );
 
jQuery( document ).ready( function( $ ) {
	/** 
	 * Set 
	 */
	/** 
	 * Set bar 
	 */
	jQuery( '.empty-bar-fixed:not( .hidden )' ).each( function( index ) {
		empty_set_bar( jQuery( this ) );
	} );
	
	/** 
	 * Set select 
	 */
	jQuery( '[class^="empty-setting"] select' ).each( function( index ) {
		empty_set_select( jQuery( this ) );
	} );
	jQuery( document ).on( 'change', '[class^="empty-setting"] select', function( event ) {
		empty_set_select( jQuery( this ) );
	} );
	
	/** 
	 * Set previous 
	 */
	jQuery( '[class^="empty-setting"] [data-basename]' ).each( function( index ) {
		empty_set_previous( jQuery( this ) );
	} );
	
	/** 
	 * Reorder admin tables 
	 */
	jQuery( 'table.form-table [name="empty-file_save_trigger"]' ).each( function( index ) {
		empty_move_hidden( jQuery( this ) );
	} );
	
	
	/** 
	 * Listener 
	 */
	/** 
	 * Click 
	 */
	jQuery( document ).on( 'click', '[data-empty_do][data-empty_trigger="click"]', function( event ) {
		empty_DO( jQuery( this ).data( 'empty_do' ), jQuery( this ), event );
	} );
	
	/** 
	 * Ready
	 */
	jQuery( 'input[name*="color"]' ).each( function( index ) {
		jQuery( this ).parents( '[class^="empty-setting"]' ).next( '.empty-setting-label' ).find( 'label' ).css( 'background-color', jQuery( this ).val() );
	} );
	
	/** 
	 * Change 
	 */
	jQuery( document ).on( 'change', '[class^="empty-setting"] [data-basename]', function( event ) {
		jQuery( '[data-basename="' + jQuery(this).data( 'basename' ) + '"]' ).each( function( index ) {
			empty_set_previous( jQuery( this ) );
		} );
	} );
	jQuery( document ).on( 'change keyup', 'input[name*="color"]', function( event ) {
		jQuery( this ).parents( '[class^="empty-setting"]' ).next( '.empty-setting-label' ).find( 'label' ).css( 'background-color', jQuery( this ).val() );
	} );
	
	/** 
	 * Scroll 
	 */
	jQuery( document ).on( 'scroll', function( event ) {
		var scrollTop = jQuery( document ).scrollTop();
		if ( this.prevTop === undefined ) this.prevTop = 0;
		var direction = this.prevTop > scrollTop ? 'up' : 'down';
		
		jQuery( '[data-empty_do][data-empty_trigger="scroll"]' ).each( function( index ) {
			var distance = jQuery( this ).offset().top + jQuery( this ).outerHeight() + jQuery( this ).data( 'empty_trigger_distance' );	
		
			switch ( jQuery( this ).data( 'empty_trigger_direction' ) ) {
				case 'up':
					if ( direction !== 'up' ) break;
					
					if ( scrollTop <= distance && document.prevTop > distance ) {
						empty_DO( jQuery( this ).data( 'empty_do' ), jQuery( this ), event );
					}
					break;
				case 'down':
					if ( direction !== 'down' ) break;
					
					if ( scrollTop >= distance && document.prevTop < distance ) {
						empty_DO( jQuery( this ).data( 'empty_do' ), jQuery( this ), event );
					}
					break;
				default:
					if ( ( scrollTop >= distance && document.prevTop < distance ) || ( scrollTop <= distance && document.prevTop > distance ) ) {
						empty_DO( jQuery( this ).data( 'empty_do' ), jQuery( this ), event );
					}
					break;
			}
		} );
		
		this.prevTop = jQuery( document ).scrollTop();
	} );
	
	/**
	 * @since 1.4.6		Tooltip
	 * @since 1.4		Copy breakpoint values listener
	 */
	jQuery( document ).on( 'change', 'select[name="empty-copy_breakpoint"]', function( event ) {
		empty_DO( 'copy:breakpoint', jQuery( this ), event );
	} );
	
	jQuery( document ).on( 'mouseenter', '[data-empty_tooltip]:not( .empty-tooltip )', function( event ) {
		var $tooltip = jQuery( '.empty-tooltip[data-empty_tooltip="' + jQuery( this ).attr( 'data-empty_tooltip' ) + '"]' );
		var top = jQuery( this ).offset().top - jQuery( '#wpadminbar' ).outerHeight() - $tooltip.outerHeight() - 3;
		var left = jQuery( this ).offset().left - $tooltip.outerWidth() / 2 + 3;
		
		$tooltip.show().css( { top: top, left: left } );
	} );
	
	jQuery( document ).on( 'mouseleave', '[data-empty_tooltip]:not( .empty-tooltip )', function( event ) {
		var $tooltip = jQuery( '.empty-tooltip[data-empty_tooltip="' + jQuery( this ).attr( 'data-empty_tooltip' ) + '"]' );
		
		$tooltip.hide();
	} );
	
	/**
	 * @since 2.5.7		Copy previous value
	 */
	jQuery( document ).on( 'dblclick', '[data-previous]', function( event ) {
		var $this = jQuery( this );
		if ( $this.val() !== undefined && $this.val() !== '' ) return false;
		
		$this.val( $this.attr( 'data-previous' ) );
		jQuery( '[data-basename="' + $this.data( 'basename' ) + '"]' ).each( function( index ) {
			empty_set_previous( jQuery( this ) );
		} );
	} );
} );



/**
 * Set select labels
 *
 * @since 1.0
 *
 * @param object $target
 *
 * @return void
 */
window.empty_set_select = function( $target ) {
	var value = $target.find( ':selected' ).html();
	var $label = $target.parent().find( 'label' ).last();
	
	$label.html( value );
	$label.attr( 'data-empty-selected', $target.val() );
}

/**
 * Set empty bar distance
 *
 * @since 1.0
 *
 * @param object $target
 *
 * @return void
 */
window.empty_set_bar = function( $target ) {
	var height = $target.outerHeight( true );
	
	$target.parents( '#wpbody-content' ).css( 'margin-top', height );
}

/**
 * Set empty previously set label
 *
 * @since 1.0
 *
 * @param object $target
 *
 * @return void
 */
window.empty_set_previous = function( $target ) {
	$target.next( '.previous' ).remove();
	if ( $target.val() !== undefined && $target.val() !== '' ) return false;

	var basename = $target.data( 'basename' );
	$target.closest( '.empty-wrap' ).prevAll().each( function( index ) {
		var $previous = jQuery( this ).find( '[data-basename="' + basename + '"]' );
		var value = $previous.val();
		
		if ( value !== undefined && value !== '' ) {
			var breakpoint = jQuery( this ).data( 'breakpoint' );
			var span = '<span class="previous">' + breakpoint + ': ' + value + '</span>';
			
			switch ( $target.prop( 'tagName' ) ) {
				case 'SELECT':
					$target.find( 'option' ).first().html( span );
					$target.next().html( span );
					break;
				default:
					$target.after( span );
					break;
			}
			$target.attr( 'data-previous', value );
			
			return false;
		}
	} );
}

/**
 * Move hidden fields to table end
 *
 * @since 1.0
 *
 * @param object $target
 *
 * @return void
 */
window.empty_move_hidden = function( $target ) {
	var $table = jQuery( 'table.form-table' ).last().find( 'tbody' );
	var $row = $target.closest( 'tr' );
	
	$table.append( $row );
}

/**
 * Copy values from selected breakpoint to current breakpoint
 *
 * @since 1.4
 *
 * @param object $target
 * @param object event
 *
 * @return void
 */
window.copy_breakpoint = function( $target, event ) {
	var breakpoint_current = jQuery( 'body' ).attr( 'data-empty_breakpoints_current' );
	var breakpoint_copy = $target.val();
	var selector = '[name*="[' + breakpoint_current + ']"]';
	
	jQuery( 'input' + selector + ', select' + selector + ', textarea' + selector ).each( function( index ) {
		if ( jQuery( this ).val() ) return 'continue';
		
		find = new RegExp( '\\[' + breakpoint_current + '\\]', 'g' );
		replace = '[' + breakpoint_copy + ']';
		selector_copy = jQuery( this ).attr( 'name' ).replace( find, replace );
		
		jQuery( this ).val( jQuery( '[name="' + selector_copy + '"]' ).val() );
		empty_set_previous( jQuery( this ) );
		empty_set_select( jQuery( this ) );
	} );

	$target.val( $target.prop( 'defaultSelected' ) );
	empty_set_select( $target );
}



/**
 * Global DO function
 *
 * @since 1.4				Copy breakpoint
 * @since 1.2				Output all preceeding breakpoint slugs
 * @since 1.1				New editor_element insert feature
 * @since 1.0
 *
 * @param string action
 * @param object $target
 * @param object event
 *
 * @return void
 */
window.empty_DO = function( action, $target, event ) {
	var action = action.split( ':' );
	
	switch ( action[0] ) {
		case 'set':
			switch( action[1] ) {
				case 'option':
					jQuery.post( ajaxurl, {
						'action': 'update_option',
						'option': $target.attr( 'name' ),
						'value': $target.val()
					}, function( response ) {
						//console.log( response );
					} );
					
					/**
					 * @since 1.4.5		Add attributes to EVERY WYSIWYG editor available
					 * @since 1.4.4		Add attributes to WYSIWYG editor too
					 */
					jQuery( 'body' ).attr( 'data-' + $target.attr( 'name' ), $target.val() );
					jQuery( '.wp-editor-container iframe' ).contents().find( 'body' ).attr( 'data-' + $target.attr( 'name' ), $target.val() );
					
					/**
					 * @since 1.2	Output all preceeding breakpoint slugs
					 */
					if ( $target.attr( 'name' ) === 'empty_breakpoints_current' ) {
						var slugs = $target.attr( 'value' );
						$target.parents( '.empty-setting-radio' ).first().prevAll( '.empty-setting-radio' ).each( function( index ) {
							slugs = jQuery( this ).find( 'input[type="radio"][name="empty_breakpoints_current"]' ).attr( 'value' ) + ' ' + slugs;
						} );
						jQuery( 'body' ).attr( 'data-empty_breakpoints', slugs );
						
						/**
						 * @since 1.4.5		Add attributes to EVERY WYSIWYG editor available
						 * @since 1.4.4		Add attributes to WYSIWYG editor too
						 */
						jQuery( '.wp-editor-container iframe' ).contents().find( 'body' ).attr( 'data-empty_breakpoints', slugs );
					}
					break;
				default:
 					jQuery( action[3] ).attr( action[1], action[2] );
 					/**
 					 * @since 1.4.5		Add attributes to EVERY WYSIWYG editor available
 					 * @since 1.4.4		Add attributes to WYSIWYG editor too
 					 */
 					jQuery( '.wp-editor-container iframe' ).contents().find( action[3] ).attr( action[1], action[2] );
					break;
			}
			break;
		case 'toggle':
			event.preventDefault();
			event.stopPropagation();
			switch ( action[1] ) {
				case 'this':
					$target.toggleClass( action[2] );
					break;
				case 'closest':
					$target.closest( action[2] ).toggleClass( action[3] );
					break;
				default:
					jQuery( action[2] ).toggleClass( action[3] );
					break;
			}
			break;
		case 'copy':
			switch ( action[1] ) {
				case 'breakpoint':
					copy_breakpoint( $target, event );
					break;
			}
			break;
		case 'reset':
			switch ( action[1] ) {
				case 'breakpoint':
					if ( confirm( translation.question_reset.values ) ) {
						var breakpoint = jQuery( 'body' ).attr( 'data-empty_breakpoints_current' );
						var selector = '[name*="[' + breakpoint + ']"]';
						
						jQuery( 'input' + selector + ', select' + selector + ', textarea' + selector ).each( function( index ) {
							jQuery( this ).val( null );
							empty_set_previous( jQuery( this ) );
							empty_set_select( jQuery( this ) );
						} );
					}
					break;
			}
			break;
	}
}



/**
 * Global hit function between to objects
 *
 * @since 1.4.3			Check both directions
 * @since 1.4.2			More accurate function
 * @since 1.4.1
 *
 * @param object $one
 * @param object $two
 *
 * @return boolean
 */
window.hit = function( $one, $two ) {
	var item1 = {
		left: $one.offset().left,
		top: $one.offset().top,
		right: $one.offset().left + $one.width(),
		bottom: $one.offset().top + $one.height()
	};
	var item2 = {
		left: $two.offset().left,
		top: $two.offset().top,
		right: $two.offset().left + $two.width(),
		bottom: $two.offset().top + $two.height()
	}
	
	if ( item1.left >= item2.left && item1.left <= item2.right && item1.top >= item2.top && item1.top <= item2.bottom ) return true;
	if ( item1.left >= item2.left && item1.left <= item2.right && item1.bottom >= item2.bottom && item1.bottom <= item2.top ) return true;
	if ( item1.right <= item2.right && item1.right >= item2.left && item1.top >= item2.top && item1.top <= item2.bottom ) return true;
	if ( item1.right <= item2.right && item1.right >= item2.left && item1.bottom >= item2.top && item1.bottom <= item2.top ) return true;
	
	/**
	 * @since 1.4.3
	 */
	if ( item2.left >= item1.left && item2.left <= item1.right && item2.top >= item1.top && item2.top <= item1.bottom ) return true;
	if ( item2.left >= item1.left && item2.left <= item1.right && item2.bottom >= item1.bottom && item2.bottom <= item1.top ) return true;
	if ( item2.right <= item1.right && item2.right >= item1.left && item2.top >= item1.top && item2.top <= item1.bottom ) return true;
	if ( item2.right <= item1.right && item2.right >= item1.left && item2.bottom >= item1.top && item2.bottom <= item1.top ) return true;
	
	return false;
}