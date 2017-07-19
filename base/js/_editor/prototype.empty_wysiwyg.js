/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 

/**
 * WYSIWYG setup
 *
 * @since 1.0
 *
 * @param object editor
 *
 * @return object
 */
var Empty_WYSIWYG = function( element ) {
	this.element = element;
	this.$element = jQuery( element );
	this.$output_to = null;
	
	this.__construct();
}

/**
 * WYSIWYG prototype
 *
 * @since 1.0
 */
Empty_WYSIWYG.prototype = {
	/**
	 * Initialize WYSIWYG
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	__construct: function() {
		var scope = this;
		
		jQuery( '#wp-empty-wysiwyg-editor-tools' ).remove();
	},
	
	
	
	/**
	 * Show editor and prefill with specified content
	 *
	 * @since 1.0
	 *
	 * @param object $object
	 *
	 * @return void
	 */
	show: function( $object ) {
		/**
		 * Variables
		 */
		var offset_top, offset_left;
	
		/**
		 * @since 1.0.1		Get editor by ID
		 * @since 1.0		Set content
		 */
		tinyMCE.get( 'empty-wysiwyg' ).setContent( $object.html() );
		tinyMCE.get( 'empty-wysiwyg' ).focus();
	
		/**
		 * @since 1.0.1		Minimal width for WYSIWYG editor
		 * @since 1.0		Add coordinates to WYSIWYG
		 */
		this.$element.width( Math.max( 450, $object.width() ) + 22 );
	
		offset_top 	= $object.offset().top 
					- this.$element.find( '.mce-toolbar-grp.mce-first' ).outerHeight( true )
					- jQuery( '#wpadminbar' ).height()
					- 11
					+ 10000
					;
		offset_left	= $object.offset().left
					- this.$element.parent().offset().left
					- 11
					;
		
		this.$element.css( {
			top: Math.round( offset_top ),
			left: offset_left
		} );
		
		/**
		 * @since 1.0	Set $output_to element
		 */
		this.$output_to = $object;
	},
	
	/**
	 * Hide editor and reset
	 *
	 * @since 2.5.8		Clean editor after close
	 * @since 1.0
	 *
	 * @return void
	 */
	hide: function() {
		this.$element.css( { top: -10000 } );
		this.$output_to = null;
		
		/*
		 * @since 2.5.8
		 */
		tinyMCE.get( 'empty-wysiwyg' ).setContent( '' );
	},
	 
	 
	
	/**
	 * Output editor content to element
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	output: function() {
		this.$output_to.html( tinyMCE.activeEditor.getContent().trim() );
	}
}