/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 

/**
 * Media manager setup
 *
 * @since 1.0
 *
 * @return object
 */
var Empty_Media = function() {
	this.$output_to = null;

	this.__construct();
}

/**
 * Media prototype
 *
 * @since 1.0
 */
Empty_Media.prototype = {
	/**
	 * Register media uploader and listeners
	 *
	 * @since 1.1.1		Initialize states
	 * @since 1.1		Embed
	 * @since 1.0
	 *
	 * @return void
	 */
	__construct: function() {
		var scope = this;
		if ( ! this.wp_manager ) {
			this.wp_manager = wp.media( {
				frame: 'post',
				state: 'insert',
				multiple: false,
				library: {
					type: 'image'
				}
			} );
			
			this.wp_manager.on( 'open', function() { scope.initialize(); } );
			this.wp_manager.on( 'insert', function() { scope.insert(); } );
			/**
			 * @since 1.1
			 */
			this.wp_manager.state( 'embed' ).on( 'select', function() { scope.embed(); } );
		}
		
		/**
		 * @since 1.1.1		Initialize each state once; Ending with default state
		 */
		this.wp_manager.setState( 'embed' );
		this.wp_manager.setState( 'insert' );
	},
	
	
	
	/**
	 * Initialize media uploader after open request
	 *
	 * @since 1.1.1		Set embed defaults
	 * @since 1.0
	 *
	 * @return void
	 */
	initialize: function() {
		/**
		 * Variables
		 */
		var selection, media_id, state, media;
	
		selection = this.wp_manager.state( 'insert' ).get('selection');
		try{ selection.reset(); } catch( e ) {};
		media_id = this.$output_to.attr( 'data-media' );
		
		/**
		 * @since 1.0	Set state
		 */
		if ( jQuery.isNumeric( media_id ) || media_id === undefined ) {
			state = 'insert';
		} else if ( jQuery.type( media_id ) === 'string' ) {
			state = 'embed';
		}
		this.wp_manager.setState( state );
		
		/**
		 * @since 1.1.1		Set defaults
		 */
		if ( jQuery.isNumeric( media_id ) && media_id !== undefined ) {
			media = wp.media.attachment( media_id );
			
			media.fetch();
			selection.add( [media] );
		} else if ( jQuery.type( media_id ) === 'string' ) {
			jQuery( '#embed-url-field' ).val( media_id );
			jQuery( '#embed-url-field' ).focus();
			jQuery( '#embed-url-field' ).trigger( 'keyup' );
		}
	},
	
	
	
	/**
	 * On insert
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	insert: function() {
		var scope = this;
		var selection, attachment, props, link, classes, content;
		
		selection = this.wp_manager.state().get( 'selection' );
		attachment = selection.toJSON()[0];
		jQuery.when.apply( $, selection.map( function( attachment ) {
			props = scope.wp_manager.state().display( attachment ).toJSON();
		} ) );
		
		switch ( attachment.type ) {
			case 'image':
				switch ( props.link ) {
					case 'file':
						link = attachment.url;
						break;
					case 'post':
						link = attachment.link;
						break;
					case 'custom':
						link = props.linkUrl;
						break;
					default:
						link = false;
						break;
				}
				
				classes = 'size-' + props.size + ' '
						+ 'wp-image-' + attachment.id + ' '
						;
				content	=	( link !== false ? '<a href="' + link + '" target="_blank">' : '' )
						+ 		'<img '
						+			'src="' + attachment.sizes[ props.size ].url + '" '
						+			'width="' + attachment.sizes[ props.size ].width + '" ' 
						+			'height="' + attachment.sizes[ props.size ].height + '" '
						+			'alt="' + attachment.title + '" '
						+			'class="' + classes.trim() + '" '
						+		'/>'
						+	( link !== false ? '</a>' : '' )
						;
						
				/**
				 * @since 1.0	Output image element
				 */
				this.$output_to.attr( 'data-media', attachment.id );
				this.$output_to.html( content );
				break;
		}
	},
	
	/**
	 * On Embed
	 * 
	 * @since 1.1
	 *
	 * @return void
	 */
	embed: function() {
		var scope = this;
		var embed = this.wp_manager.state().props.toJSON();
		var type = this.wp_manager.state().get( 'type' );
		
		switch ( type ) {
			case 'link':
				jQuery.post( ajaxurl, {
					action: 'oembed',
					ajax: true,
					url: embed.url
				} )
				.done( function( response ) {
					response = jQuery.parseJSON( response );
					
					scope.$output_to.html( response );
				} )
				.fail( function( response ) { } 
				);
			
				this.$output_to.attr( 'data-media', embed.url );
				break;
		}
	},
	
	
	
	/**
	 * Open request
	 *
	 * @since 1.0
	 *
	 * @param object $object
	 *
	 * @return void
	 */
	open: function( $object ) {
		this.$output_to = $object;
	
		this.wp_manager.open();
	},
	
	/**
	 * On close
	 *
	 * @since 1.1.1
	 *
	 * @return void
	 */
	close: function() {
		this.$output_to = null;
		this.__construct(); 
	}
}
