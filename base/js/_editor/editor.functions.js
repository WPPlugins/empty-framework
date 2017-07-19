/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Setup
 *
 * @since 1.1.1		$outermost Empty_Editor parameter
 * @since 1.0
 *
 * @return void
 */
jQuery( document ).ready( function( $ ) {
	/**
	 * Setup globals
	 */
	window.EDITOR = new Empty_Editor( 
		document.getElementById( 'empty_editor_inner' ), 
		jQuery( '.wp-editor-area#content' ),
		jQuery( '#empty_editor' )
	);
	window.WYSIWYG = new Empty_WYSIWYG(
		document.getElementById( 'wp-empty-wysiwyg-wrap' )
	);
	window.MEDIA = new Empty_Media();
	
	
	
	/** 
	 * Listener 
	 */
	/** 
	 * Click 
	 *
	 * @since 1.1.2		Keep context menu alive; Pass event to editor function
	 * @since 1.1		jQuery( this ) as second parameter
	 * @since 1.0
	 */
	jQuery( document ).on( 'click', '[data-empty_trigger="click"] a, [data-empty_trigger="double"] a, [data-empty_trigger="click"] iframe, [data-empty_trigger="double"] iframe, [data-empty_trigger="click"] button, [data-empty_trigger="double"] button', function( event ) {
		event.preventDefault();
	} );
	
	jQuery( document ).on( 'click', '[data-empty_do^="editor:"][data-empty_trigger="click"]', function( event ) {
		action = jQuery( this ).data( 'empty_do' ).split( ':' );
		EDITOR[ action[1] ]( action[2], event );
	} );
	jQuery( document ).on( 'dblclick', '[data-empty_do^="editor:"][data-empty_trigger="double"]', function( event ) {
		action = jQuery( this ).data( 'empty_do' ).split( ':' );
		EDITOR[ action[1] ]( action[2], jQuery( this ) );
	} );
	
	jQuery( document ).on( 'click', function( event ) {
		/**
		 * @since 1.1.2 
		 */
		$toElement = jQuery( event.toElement );
		if ( 
			$toElement.parents( '.row' ).length === 0 && 
			$toElement.parents( '[data-empty-element]' ).length === 0 &&
			$toElement.parents( '#editor_actions_additional' ).length === 0 &&
			$toElement.parents( '.empty-crumb' ).length === 0 &&
			! $toElement.is( '[name="empty_breakpoints_current"]' )
		) {
			EDITOR.unset_active();
		}
		if ( 
			$toElement.parents( WYSIWYG.$element.attr( 'id' ) ).length === 0 && 
			$toElement.parents( '.mce-panel' ).length === 0 &&
			$toElement.parents( '#wp-link-wrap' ).length === 0 &&
			$toElement.parents( '.empty-crumb' ).length === 0 &&
			! $toElement.is( '[name="empty_breakpoints_current"]' )
		) {
			WYSIWYG.hide();
		}
	} );
	jQuery( document ).on( 'click', '[data-empty-element], .row', function( event ) {
		event.stopPropagation();
		EDITOR.set_active( jQuery( this ), event );
	} );
	
	/**
	 * Keyup
	 */
	jQuery( document ).on( 'keyup', function( event ) {
		switch ( event.which ) {
			case 8:
				/**
				 * @since 1.1.3		Prevent remove of contenteditable
				 */
				if ( EDITOR.active.length !== 0 && ! jQuery( ':focus' ).is( '[contenteditable]' ) ) {
					EDITOR.remove();
				}
				break;
		}
	} );
	
	/**
	 * Submit
	 *
	 * @since 2.5.8		New publish function to prevent submit question
	 */
	jQuery( document ).on( 'submit', 'form', function( event ) {
		EDITOR.output();
		
		releaseLock = false;
		jQuery( window ).off( 'beforeunload.edit-post' );
	} );
} );
