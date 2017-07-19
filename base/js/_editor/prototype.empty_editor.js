/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 

/**
 * Editor setup
 *
 * @since 2.0					this.$breadcrumbs
 * @since 1.2					$outermost
 * @since 1.0
 *
 * @param element element		Element to initialize editor to
 * @param object $output_to		jQuery object of the area the output is going to
 * @param object $outermost
 *
 * @return object
 */
var Empty_Editor = function( element, $output_to, $outermost ) {
	this.element = element;
	this.$element = jQuery( this.element );
	this.$output_to = $output_to;
	this.$containment = $outermost;
	
	this.$gutter = jQuery( '#empty-helper_gutter' );
	
	/**
	 * @since 2.0
	 */
	this.$breadcrumbs = jQuery( '.empty-breadcrumbs .path' );
	
	this.active = [];
	
	this.__construct();
}

/**
 * Editor prototype
 *
 * @since 1.0
 */
Empty_Editor.prototype = {
	/**
	 * Initialize editor
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	__construct: function() {
		var scope = this;
		this.$rows = this.$element.find( '> .row > .row-inner' );
		this.$cols = this.$element.find( '> .row > .row-inner > [class*="col-"]' );
		
		/**
		 * @since 1.2	$containment position
		 */
		if ( this.$containment.css( 'position' ) === 'static' ) {
			this.$containment.css( { position: 'relative' } );
		}
		
		/**
		 * @since 2.5.8		Enable drag for 'contenteditable' elements; prevent element jumps while sorting
		 * @since 1.8		Overhaul
		 * @since 1.0.2		beforeStop
		 * @since 1.0		Sortable
		 */
		this.$element.sortable( {
			cursor: 'ns-resize',
			axis: 'y'
		} );
		 
		this.$rows.sortable( {
			connectWith: '.row-inner',
			cursor: 'move',
			containment: scope.$containment,
			tolerance: 'pointer',
			//cancel: '[contenteditable]',
			
			start: function( event, ui ) {
				ui.item.data( 'drag', true );
				
				/**
				 * @since 2.5.8		ui.placeholder.width( 1 ) removed, let get_element_next handle this
				 * @since 1.8		Modify placeholder
				 */
				scope.$placeholder_clone = ui.placeholder.clone();
				ui.placeholder.attr( 'class', null ).append( scope.$placeholder_clone ).addClass( 'empty-sortable-placeholder' );
				ui.placeholder.height( scope.$placeholder_clone.outerHeight( true ) );
			
				/**
				 * @since 1.8	Scope helper function to get current next element; Modify next element (init)
				 */
				this.$next = scope.get_element_next( ui.placeholder );
				if ( this.$next ) this.$next.css( { marginLeft: '+=' + ( ui.item.outerWidth( true ) - 1.5 ) } );
			},
			sort: function( event, ui ) {
				/**
				 * @since 1.8	Change placeholder (clone) position
				 */
				var column_width = ui.placeholder.parents( '.row-inner' ).first().width() / 12;
				var placeholder_left = Math.round( ui.position.left / column_width ) * column_width;
				scope.$placeholder_clone.css( { left: placeholder_left } );
				
				/**
				 * @since 1.8	Check for hits
				 */
				ui.placeholder.removeClass( 'disabled' );
				ui.item.removeClass( 'disabled' );
				
				ui.placeholder.siblings().each( function( index ) {
					if ( ui.item.is( this ) ) return 'continue';
					if ( ! hit( jQuery( this ), scope.$placeholder_clone ) ) return 'continue';
					
					ui.placeholder.addClass( 'disabled' );
					ui.item.addClass( 'disabled' );
				} );
			},
			change: function( event, ui ) {
				/**
				 * @since 1.8	Modify next element (finish)
				 */
				$new_next = scope.get_element_next( ui.placeholder );
				if ( this.$next ) {
					scope.build_classes( this.$next, this.$next.parents( '.row-inner' ).first() );
				}
				
				this.$next = $new_next;
				if ( $new_next ) {
					this.$next.css( { marginLeft: '-=1.5' } );
				}
			}, 
			beforeStop: function( event, ui ) {
				/**
				 * @since 1.8	If disabled: cancel
				 */
				if ( ui.item.hasClass( 'disabled' ) ) {
					ui.item.attr( 'style', null );
					if ( this.$next ) this.$next.attr( 'style', null );
					
					scope.$rows.sortable( 'cancel' );
					scope.$rows.sortable( 'refresh' );
					
					return false;
				}
				
				/**
				 * @since 2.5.8		Set placeholder width to 1 to get correct classes
				 * @since 1.9		Reset item style to prevent jump to next row
				 * @since 1.8.1		Set margin for element itself
				 */
				ui.placeholder.width( 1 );
				var margin_left = parseFloat( scope.$placeholder_clone.css( 'left' ) ) + parseFloat( scope.$placeholder_clone.css( 'margin-left' ) );
				ui.item.css( { left: 0, marginLeft: 0 } );
				
				jQuery( scope.get_elements( ui.placeholder, '*', 'prev' ) ).each( function( index ) {
					if ( ui.item.is( this ) ) return 'continue';
					
					margin_left -= jQuery( this ).outerWidth( true );
				} );
				
				ui.item.css( { marginLeft: margin_left } );
			},
			stop: function( event, ui ) {
				ui.item.data( 'drag', false );
				
				scope.build_classes( ui.item, ui.item.parents( '.row-inner' ).first() );
			
				if ( this.$next ) {
					this.$next.css( { marginLeft: '-=' + ui.item.outerWidth( true ) } );
					
					scope.build_classes( this.$next, this.$next.parents( '.row-inner' ).first() );
				}
				
				scope.output();
			}
		} );
		
		/**
		 * @since 1.0	Resizable
		 */
		this.$cols.resizable( {
			handles: 'e, w',
			
			start: function( event, ui ) {
				/**
				 * @since 1.0	Set correct grid
				 */
				scope.$cols.resizable( 'option', 'grid', [ jQuery( this ).parents( '.row-inner' ).first().width() / 12, 0 ] );
				
				/**
				 * @since 1.6.1		enforce_width; margin_left
				 * @since 1.6		Invoke current element (on resize start); Get the whitespace to work with; Save resize delta
				 * @since 1.2		Define variables
				 */
				this.last_width = ui.element.outerWidth();
				this.currentElement = {
					left: ui.originalPosition.left,
					width: ui.originalSize.width
				};
				this.whitespace = scope.get_whitespace( ui.element );
				this.resize_delta = 0;
				this.enforce_width = false;
				this.margin_left = this.whitespace.before;
				
				/**
				 * @since 1.6.2		Next element for margin-left manipulation
				 */
				var next = scope.get_elements( ui.element );
				this.$next = next[0];
				if ( this.$next ) this.next_margin_left = parseFloat( this.$next.css( 'margin-left' ) );
			},
			resize: function( event, ui ) {	
				/*
				 * @since 1.0		Add gutter to left value so element size won't be compromised
				 */		
				ui.size.width += scope.$gutter.width();
				if ( this.last_width != ui.size.width ) this.resize_delta += ui.size.width - this.last_width;
				
				/**
				 * @since 1.6	Determine resize direction and origin
				 */
				if ( this.currentElement.width < ui.size.width && this.currentElement.left === ui.position.left ) {
					this.resize_info = { what: 'enlarge', from: 'east' };
				}
				if ( this.currentElement.width < ui.size.width && this.currentElement.left !== ui.position.left ) {
					this.resize_info = { what: 'enlarge', from: 'west' };
				}
				if ( this.currentElement.width > ui.size.width && this.currentElement.left === ui.position.left ) {
					this.resize_info = { what: 'shrink', from: 'east' };
				}
				if ( this.currentElement.width > ui.size.width && this.currentElement.left !== ui.position.left ) {
					this.resize_info = { what: 'shrink', from: 'west' };
				}
				 
				this.currentElement = {
					left: ui.position.left,
					width: ui.size.width
				};
				
				/**
				 * @since 1.6.2		Whitespace tolerance
				 */
				if ( ui.size.width < this.last_width ) this.enforce_width = false;
				if ( this.resize_info.from === 'east' && this.resize_delta > this.whitespace.after + 10 ) { 
					this.enforce_width = true;
					this.resize_delta = this.whitespace.after;
				}
				if ( this.resize_info.from === 'west' && this.resize_delta > this.whitespace.before + 10 ) { 
					this.enforce_width = true;
					this.resize_delta = this.whitespace.before;
				}
				
				if ( this.enforce_width ) ui.size.width = this.last_width;
				
				/**
				 * @since 1.6.2		Alter margin-left value of following element
				 * @since 1.6.1		Transform left value into margin-left
				 */
				if ( this.resize_info.from === 'west' ) {
					this.margin_left = this.whitespace.before - this.resize_delta;	
				}
				ui.element.css( { left: 0, marginLeft: this.margin_left } );
				
				if ( this.$next && this.resize_info.from === 'east' ) this.$next.css( { marginLeft: this.next_margin_left - this.resize_delta } );
				
				/**
				 * @since 1.2	Set variables for next run
				 */
				this.last_width = ui.size.width;
				
				/**
				 * @since 1.6.2		-1 to prevent flickering
				 */
				ui.size.width -= 0.5;
			},
			stop: function( event, ui ) {
				/**
				 * @since 1.6.2		Set offset for the next element if there is any
				 * @since 1.2		Set offset when the resize direction was west (updated)
				 */
				scope.build_classes( ui.element, jQuery( this ).parents( '.row-inner' ).first() );
				if ( this.$next ) scope.build_classes( this.$next, this.$next.parents( '.row-inner' ).first() );
				
				scope.output();
			}
		} );
		
		/**
		 * @since 1.9		Prevent element jump to new row when drag starts
		 */
		this.$rows.children( '.ui-sortable-handle' ).on( 'mousedown', function( event ) {
			jQuery( this ).data( 'width_init', jQuery( this ).outerWidth() );
		} );
		this.$rows.children( '.ui-sortable-handle' ).on( 'mousemove', function( event ) {
			if ( event.which === 1 && jQuery( ':active' ).filter( this ).length !== 0 ) {
				jQuery( this ).outerWidth( jQuery( this ).data( 'width_init' ) - 1 );
			} else {
				jQuery( this ).outerWidth( '' );
			}
		} );
		
		/**
		 * @since 2.0		Breadcrumbs
		 */
		this.set_breadcrumbs();
	},
	
	
	
	/**
	 * Set active element
	 *
	 * @since 2.0				Breadcrumbs
	 * @since 1.9.1				Disable functions in certain context
	 * @since 1.0
	 *
	 * @param object $element	jQuery object
	 * @param event event		
	 *
	 * @return void
	 */
	set_active: function( $element, event ) {
		var scope = this;
		if ( ! event.ctrlKey && ! event.metaKey ) this.unset_active();
		jQuery( '#editor_actions_additional' ).find( '[data-condition]' ).show();
		
		jQuery( '#editor_actions_additional' ).removeClass( 'hidden' );
		$element.addClass( 'active' );
		this.active.push( $element );
		
		if ( this.active.length > 1 ) {
			jQuery( '#editor_actions_additional' ).find( '[data-condition]' ).hide();
		} else {
			jQuery( '#editor_actions_additional' ).find( '[data-condition]' ).each( function( index ) {
				var condition = jQuery( this ).attr( 'data-condition' );
				if ( scope.active[0].is( condition ) ) return 'continue';
				
				jQuery( this ).hide();
			} );
		}
		
		/**
		 * @since 2.0
		 */
		this.set_breadcrumbs();
	},
	
	/**
	 * Unset active element
	 *
	 * @since 2.0		Breadcrumbs
	 * @since 1.0	
	 *
	 * @return void
	 */
	unset_active: function() {
		jQuery( '#editor_actions_additional' ).addClass( 'hidden' );
		jQuery( this.active ).each( function( index ) {
			jQuery( this ).removeClass( 'active' );
		} );
		this.active = [];
		
		/**
		 * @since 2.0
		 */
		this.set_breadcrumbs();
	},
	
	
	
	/**
	 * Add elements to editor environment
	 *
	 * @since 1.0
	 *
	 * @param string slug	Element and template slug
	 *
	 * @return void
	 */
	add: function( slug ) {
		var template = jQuery( 'script[type="text/template"]#empty_element-' + slug ).html();
		
		switch ( slug ) {
			case 'row':
				$add_to = this.$element;
				break;
			default:
				if ( this.active.length > 0 ) {
					if ( this.active[0].is( '.row' ) ) {
						$add_to = this.active[0].find( '.row-inner' );
					} else {
						$add_to = this.active[0].closest( '.row-inner' );
					}
				} else {
					$add_to = this.$element.find( '.row' ).last().find( '.row-inner' );
				}
				
				if ( $add_to.length === 0 ) {
					this.add( 'row' );
					this.add( 'slug' );
					
					return false;
				}
				break;
		}
		
		$add_to.append( template.trim() );
		this.__construct();
		
		this.output();
	},
	
	/**
	 * Clone element and append after
	 *
	 * @since 1.0.2
	 *
	 * @param object $original
	 * @param object $element
	 *
	 * @return void
	 */
	clone: function( $original = this.active[0], $element = this.active[0] ) {
		$clone = $original.clone();
		$clone.find( '.ui-resizable-handle' ).remove();
		
		$original.after( $clone );
		this.active.push( $clone );
		this.unset_active();
		this.__construct();
		
		this.output();
	},
	
	/**
	 * Remove after confirmation
	 *
	 * @since 2.5.8						Call __construct after element removal
	 * @since 1.9						Unset active elements; Get next elements to build offset
	 * @since 1.0
	 *
	 * @param string|object target
	 *
	 * @return void
	 */
	remove: function( target ) {
		var scope = this;
	
		if ( confirm( translation.question_remove ) ) {
			/**
			 * since 1.0	Remove active elements by default
			 */
			if ( ! target ) {
				jQuery( this.active ).each( function( index ) {
					var $next = scope.get_elements( jQuery( this ) );
					if ( $next[0] ) {
						$next[0].css( { marginLeft: parseFloat( $next[0].css( 'margin-left' ) ) + jQuery( this ).outerWidth( true ) } );
						scope.build_classes( $next[0], $next[0].parents( '.row-inner' ).first() );
					}
				
					jQuery( this ).remove();
				} );
				
				this.unset_active();
			}
		}
		
		/**
		 * @since 2.5.8		Call __construct
		 */
		this.__construct();
		
		this.output();
	},
	
	
	
	/**
	 * Build classes
	 *
	 * Build the right classes for a passed element based on it's style attribute
	 *
	 * @since 1.6.1					No zero
	 * @since 1.0
	 *
	 * @param object $target
	 * @param object $reference
	 *
	 * @return void
	 */
	build_classes: function( $target, $reference = this.$rows.first() ) {
		/**
		 * Variables
		 */
		var breakpoint, col_width, attr_class,
		cols_target, col_class, col_regex,
		offset_target, offset_class, offset_regex;
	
		breakpoint = jQuery( 'body' ).attr( 'data-empty_breakpoints_current' );
		col_width = $reference.width() / 12;

		/**
		 * @since 1.0	Width corresponding to breakpoint
		 */
		cols_target = Math.round( $target.outerWidth() / col_width );
		
		col_class = 'col-' + breakpoint + '-' + cols_target;
		col_regex = new RegExp( 'col-' + breakpoint + '-[0-9]{1,2}', 'g' );
		attr_class = $target.attr( 'class' ).replace( col_regex, col_class );
		
		if ( attr_class !== $target.attr( 'class' ) ) $target.attr( 'class', attr_class );
		$target.addClass( col_class );

		/**
		 * @since 1.6.1		No zero
		 * @since 1.0.1		Offset corresponding to breakpoint
		 */
		offset_target = Math.round( parseFloat( $target.css( 'margin-left' ) ) / col_width );
		offset_target = offset_target > 0 ? offset_target : 0;
		
		offset_class = 'col-' + breakpoint + '-offset-' + offset_target;
		offset_regex = new RegExp( 'col-' + breakpoint + '-offset-[0-9]{1,2}', 'g' );
		attr_class = $target.attr( 'class' ).replace( offset_regex, offset_class );

		if ( attr_class !== $target.attr( 'class' ) ) $target.attr( 'class', attr_class );
		$target.addClass( offset_class );
		
		
		
		$target.attr( 'style', null );
	},
	
	/**
	 * Reset breakpoint
	 *
	 * @since 1.9		Reset single element
	 * @since 1.0
	 *
	 * @param boolean single
	 *
	 * @return void
	 */
	reset: function( single = false ) {
		var question = single ? translation.question_reset.single : translation.question_reset.all;
	
		if ( confirm( question ) ) {
			var breakpoint = jQuery( 'body' ).attr( 'data-empty_breakpoints_current' );
			var class_regex = new RegExp( '[\ ]?col-' + breakpoint + '-(offset-)?[0-9]{1,2}', 'g' );
			
			if ( ! single ) {
				var elements = this.$cols;
			} else {
				var elements = this.active;
			}
			jQuery( elements ).each( function( index ) {
				jQuery( this ).attr( 'class', jQuery( this ).attr( 'class' ).replace( class_regex, '' ) );
				if ( breakpoint == BREAKPOINTS[0].slug ) jQuery( this ).addClass( 'col-' + breakpoint + '-12' );
			} );
		}
		
		this.output();
	},
	
	
	
	/**
	 * Edit selected element part
	 *
	 * Function handles opening of the right editor and updating content within the original part
	 * Edit function can handle the following 3 part types:
	 * - WYSIWYG
	 * - MEDIA
	 * - TEXT
	 *
	 * @since 1.1
	 *
	 * @param element $part
	 *
	 * @return void
	 */
	edit: function( buffer, $part ) {
		switch ( $part.attr( 'data-empty-element_part_type' ) ) {
			case 'WYSIWYG':
				WYSIWYG.show( $part );
				break;
			case 'MEDIA':
				MEDIA.open( $part );
				break;
			case 'TEXT':
				$part[0].focus();
				break;
		}
	},
	
	
	
	/**
	 * Get editor elements (by selector) on the same height as the reference object
	 *
	 * @since 1.6					Overhaul
	 * @since 1.3
	 *
	 * @param object $reference
	 * @param string selector
	 * @param string type
	 *
	 * @return array				Filled with jQuery objects
	 */
	get_elements: function( $reference, selector = '*', type = 'next') {
		/**
		 * Variables
		 */
		var elements, tolerance, reference_top, reference_bottom, element_top, element_bottom;
		var $elements = [];
		
		elements = $reference[ type.toLowerCase() + 'All' ]( selector );
		
		tolerance = 0;
		reference_top = $reference.offset().top - tolerance;
		reference_bottom = $reference.offset().top + $reference.outerHeight() + tolerance;
		
		jQuery( elements ).each( function( index ) {
			if ( jQuery( this ).hasClass( 'empty-placeholder' ) ) return 'continue';
			
			element_top = jQuery( this ).offset().top;
			element_bottom = jQuery( this ).offset().top + jQuery( this ).outerHeight();
			
			/**
			 * @since 1.6	Match boundaries
			 */
			if ( 
			( element_top >= reference_top && element_top < reference_bottom ) ||
			( element_bottom <= reference_bottom && element_bottom > reference_top )
			) {
				$elements.push( jQuery( this ) );
			}
		} );
		
		return $elements;
	},
	
	/**
	 * Get next element for reference object
	 *
	 * @since 2.5.8		Set $placeholder width to 1 only when whitespace of next element isn't enough
	 * @since 1.8
	 *
	 * @param object $placeholder
	 *
	 * @return object
	 */
	get_element_next: function( $placeholder ) {
		$placeholder.parents( '.row-inner' ).first().addClass( 'ui-sortable-active' );
		$placeholder.width( $placeholder.find( '.ui-sortable-placeholder' ).outerWidth() );
		
		var next_margin = Math.max( 0, parseFloat( $placeholder.next().css( 'margin-left' ) ) - $placeholder.width() );
		$placeholder.next().css( { marginLeft: -this.$gutter.width() } );
		
		var next = this.get_elements( $placeholder );
		if ( next[0] ) {
			var next_whitespace = this.get_whitespace( next[0] );
			if ( next_whitespace.before < $placeholder.outerWidth( true ) ) $placeholder.width( 1 );
		}
		
		$placeholder.parents( '.row-inner' ).first().removeClass( 'ui-sortable-active' );
		$placeholder.next().css( { marginLeft: ''} );
		
		return next[0];
	},
	
	/**
	 * Get whitespace for specific element (in px)
	 *
	 * @since 1.6				Overhaul
	 * @since 1.4.1
	 *
	 * @param object $element
	 * @param string selector
	 *
	 * @return object
	 */
	get_whitespace: function( $element, selector = '*' ) {
		var next, $next, prev, $prev, 
		before, after,
		max_right;
	
		next = this.get_elements( $element, selector, 'next' );
		prev = this.get_elements( $element, selector, 'prev' );
		$next = next[0];
		$prev = prev[0];
		
		if ( $next ) {
			after = parseFloat( $next.css( 'margin-left' ) );
		} else {
			max_right = $element.parents( '.row-inner' ).first().offset().left + $element.parents( '.row-inner' ).first().outerWidth();
			after = max_right - $element.offset().left - $element.outerWidth();
		}
		
		after = Math.max( 0, after );
		before = Math.max( 0, parseFloat( $element.css( 'margin-left' ) ) );
		
		return {
			before: before,
			after: after
		}
	},
	
	
	
	/**
	 * Toggle class
	 *
	 * @since 1.9
	 *
	 * @param string cclass
	 *
	 * @return void
	 */
	toggle: function( cclass ) {
		if ( ! this.active[0] ) return false;
		
		if ( cclass === 'hidden' ) {
			cclass = 'hidden-' + jQuery( 'body' ).attr( 'data-empty_breakpoints_current' );
		}	
		
		this.active[0].toggleClass( cclass );
	},
	
	
	
	/**
	 * Set breadcrumbs
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	set_breadcrumbs: function() {
		/**
		 * Variables
		 */
		var path, breadcrumbs, connector, string;
		var element_before = false;
	
		breadcrumbs = jQuery( 'script[type="text/template"]#empty_breadcrumb-start' ).html();
		connector = jQuery( 'script[type="text/template"]#empty_breadcrumb-connector' ).html();
		
		if ( this.active.length > 0 ) {
			if ( this.active.length > 1 ) {
				breadcrumbs += connector + '<span>' + translation.breadcrumbs.multiple + '</span>';
			} else {
				if ( this.active[0].is( '.row' ) ) {
					path = this.active[0].children().parents().addBack();
				} else {
					path = this.active[0].find( '*' ).parents().addBack();
				}
				path = path.filter( '[data-empty-element_part], .row, [class*="col-"]' );
				this.crumbs = path;
				
				jQuery( this.crumbs ).each( function( index ) {
					var $this = jQuery( this );
				
					if ( $this.is( '.row' ) ) {
						string = translation.row;
					} else if ( $this.is( '[class*="col-"]' ) ) {
						string = translation.element;
					} else if ( $this.is( '[data-empty-element_part]' ) ) {
						string = '<i class="fa fa-pencil" aria-hidden="true"></i> ' + $this.attr( 'data-empty-element_part_type' );
					}
				
					breadcrumbs += ( element_before ? '<span>&</span>' : connector ) + '<span class="empty-crumb"><a href="#" data-empty_trigger="click" data-empty_do="editor:breadcrumb:' + index + '">' + string + '</a></span>';
					
					/**
					 * @since 2.0.1		Set variable to swap connector for ampersand
					 */
					if ( $this.is( '[data-empty-element_part]' ) ) element_before = true;
				} );
			}
		}
		
		this.$breadcrumbs.html( breadcrumbs );
	},
	
	/**
	 * Breadcrumb function
	 *
	 * @since 2.0
	 *
	 * @param string index
	 * @param event event
	 *
	 * @return void
	 */
	breadcrumb: function( index, event ) {
		event.preventDefault();
		var crumb = this.crumbs[ index ];
		if ( ! crumb ) return false;
		$crumb = jQuery( crumb );
		
		if ( $crumb.is( '.row, [class*="col-"]' ) ) {
			this.set_active( $crumb, event );
		} else if ( $crumb.is( '[data-empty-element_part]' ) ) {
			this.edit( '', $crumb );
		}
	},
	
	
	
	/**
	 * Output editable content
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	output: function() {
		this.$output_to.html( this.$element.html().trim() );
	}
}