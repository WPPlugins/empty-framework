<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Helper class to output specific option groups
 *
 * @since 1.0
 */
class Empty_Settings {
	/**
	 * Setup
	 *
	 * @since 1.7.2			Static $breaks
	 * @since 1.7.1			Static $empty_breakpoints
	 * @since 1.4
	 *
	 * @var object $helper	Val_WP_Settings object
	 */
	protected static $helper, $empty_breakpoints, $breaks;
	
	/**
	 * Val_WP_Settings filter / actions
	 *
	 * @since 1.7.3		Rename construct to "__init" to only fire it once
	 * @since 1.6		New action to output page descriptions & messages
	 * @since 1.4		Global helper class
	 * @since 1.0
	 *
	 * @return object
	 */
	public function __init() {
		add_filter( 'val_setting_options', 						array( $this, 'populate_unit_select' ),			10, 4	);
		add_filter( 'val_setting_options', 						array( $this, 'populate_font_weight_select' ),	11, 4	);
		add_filter( 'val_setting_title', 						array( $this, 'setting_title' ), 				10, 4 	);
		add_filter( 'val_setting_description', 					array( $this, 'setting_description' ), 			10, 4 	);
		add_filter( 'val_setting_data', 							array( $this, 'setting_data' ), 				10, 4 	);
		
		add_action( 'val_settings_top', 							array( $this, 'page_top' ), 					10, 2 	);
		add_action( 'val_setting_before', 						array( $this, 'setting_before' ), 				10, 3 	);
		add_action( 'val_setting_before_radio_option', 			array( $this, 'radio_before' ), 				10, 3 	);
		add_action( 'val_setting_after', 						array( $this, 'setting_after' ), 				10, 3 	);
		add_action( 'val_setting_after_radio_option', 			array( $this, 'radio_after' ), 					10, 3 	);
		add_action( 'empty_settings_before', 					array( $this, 'settings_before' ) 						);
		add_action( 'empty_settings_after', 					array( $this, 'settings_after' ) 						);
		add_action( 'val_setting_before_input',					array( $this, 'setting_before_input' ),			10,	3	);
		add_action( 'val_setting_after_input',					array( $this, 'setting_after_input' ),			10,	3	);
		add_action( 'val_settings_before',						array( $this, 'page_description' ),				10, 2	);
		add_action( 'val_settings_before',						array( 'Empty_Settings', 'messages' ),			10, 2	);
		
		add_action( 'update_option_empty-file_save_trigger',		array( 'Empty_Settings', 'file_save_listener')			);
		
		add_action( 'wp_ajax_update_option', 					array( $this, 'ajax_update_option' ) 					);
		
		self::$helper = new Val_WP_Settings();
		
		/**
		 * @since 1.7.2
		 * @since 1.7.1
		 */
		self::$empty_breakpoints = new Empty_Breakpoints();
		self::$breaks = json_decode( EMPTY_BREAKS );
	}	
	
	
	
	/**
	 * Populate select options
	 *
	 * @since 1.0
	 *
	 * @param mixed $options
	 * @param string $meta_type
	 * @param string $type			Setting type
	 * @param string $id			Setting id
	 *
	 * @return array
	 */
	public function populate_unit_select( $options, $meta_type, $type, $id ) {
		if ( $type !== 'select' ) return $options;
		$id = explode( '[', $id );
		if ( end( $id ) !== 'unit]' ) return $options;
		$options = empty( $options ) ? array() : $options;
		
		if ( $id[0] !== 'empty_breakpoints' ) $options[null] = '—';
		$options['px'] = 'px';
		$options['pt'] = 'pt';
		$options['em'] = 'em';
		if ( $id[0] !== 'empty_breakpoints' ) $options['%'] = '%';
		
		return $options;
	}
	
	public function populate_font_weight_select( $options, $meta_type, $type, $id ) {
		if ( $type !== 'select' ) return $options;
		$id = explode( '[', $id );
		
		if ( ! in_array( 'font-weight]', $id ) ) return $options;
		$options = empty( $options ) ? array() : $options;
		
		$options[null] = '—';
		$options[100] = '100';
		$options[200] = '200';
		$options[300] = '300';
		$options[400] = '400 (normal)';
		$options[500] = '500';
		$options[600] = '600';
		$options[700] = '700 (bold)';
		$options[800] = '800';
		$options[900] = '900';
		
		return $options;
	}
	
	/**
	 * Multiple before and after filters
	 *
	 * @since 1.0
	 *
	 * @param mixed
	 *
	 * @return mixed
	 */
	public function setting_title( $title, $meta_type, $type, $id ) {
		if ( empty( $title ) ) return $title;
	
		ob_start();
		require( empty_template( 'html.setting_title.php' ) );
		
		$return = ob_get_contents();
		ob_end_clean();
		
		return $return;
	}
	
	public function setting_description( $description, $meta_type, $type, $id ) {
		if ( empty( $description ) ) return $description;
		
		ob_start();
		switch ( $type ) {
			case 'checkbox':
				$title = $description;
				require( empty_template( 'html.setting_title.php' ) );
				break;
			default:
				require( empty_template( 'html.setting_description.php' ) );
				break;
		}
		
		$return = ob_get_contents();
		ob_end_clean();
		
		return $return;
	}
	
	public function setting_data( $data, $meta_type, $type, $id ) {
		if ( strpos( $id, 'empty_css' ) === false && strpos( $id, 'empty_typography' ) === false ) return $data;
		$basename = $id;
		
		foreach ( self::$breaks as $breakpoint ) {
			$find = '[' . $breakpoint->slug . ']';
			$basename = str_replace( $find, '', $basename );
		}
		
		return $data . ' data-basename="' . $basename . '"';
	}
	
	public function page_top( $page_slug, $page_parent ) {
		$breaks = array( 'empty_grid', 'empty_typography' );
		if ( ! in_array( $page_slug, $breaks ) ) return false;
		
		require( empty_template( 'html.bar_top.php' ) );
	}
		
	public function setting_before( $meta_type = null, $type = null, $id = null ) {
		if ( strpos( $id , 'empty') === false ) return false;
		if ( $type === 'function' || $type === 'radio' ) return false;
		
		require( empty_template( 'html.setting_before.php' ) );
	}
	public function radio_before( $meta_type = null, $type = null, $id = null ) {
		if ( strpos( $id , 'empty') === false ) return false;
		
		require( empty_template( 'html.setting_before.php' ) );
	}
	public function setting_after( $meta_type = null, $type = null, $id = null ) {
		if ( strpos( $id , 'empty') === false ) return false;
		if ( $type === 'function' || $type === 'radio' ) return false;
		
		require( empty_template( 'html.setting_after.php' ) );
	}
	public function radio_after( $meta_type = null, $type = null, $id = null ) {
		if ( strpos( $id , 'empty') === false ) return false;
		
		require( empty_template( 'html.setting_after.php' ) );
	}
	
	public function settings_before( $breakpoint = null ) {
		$data = empty( $breakpoint) ? '' : 'data-breakpoint="' . $breakpoint . '"';
		
		require( empty_template( 'html.wrap_before.php' ) );
	}
	public function settings_after( $breakpoint = null ) {
		require( empty_template( 'html.wrap_after.php' ) );
	}
	
	public function setting_before_input( $meta_type, $type, $id ) {
		if ( strpos( $id , 'empty') === false ) return false;
		if ( $type === 'checkbox' ) return false;
		
		require( empty_template( 'html.setting_before_input.php' ) );
	}
	public function setting_after_input( $meta_type, $type, $id ) {
		if ( strpos( $id , 'empty') === false ) return false;
		if ( $type === 'checkbox' ) return false;
		
		require( empty_template( 'html.setting_after_input.php' ) );
	}
	
	
	
	/**
	 * Post Types
	 * 
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function post_types() {
		$post_types = get_post_types( array( 'public' => true ) );
		$dont = array( 'attachment' );
		
		do_action( 'empty_settings_before' );
		foreach ( $post_types as $slug => $name ) {
			if ( in_array( $slug, $dont ) ) continue;
			
			self::$helper->output_setting( array(
				'type'			=> 'checkbox',
				'id'			=> 'empty_options[post_types][' . $slug . ']',
				'value'			=> $slug,
				'description'	=> ucfirst( $slug )
			) );
		}
		do_action( 'empty_settings_after' );
		
		echo '<p class="description">' . __( 'Which post types do you want to use the drag&drop editor for?', 'empty' ) . '</p>';
	}
	
	/**
	 * Editor colors
	 *
	 * @since 1.4
	 *
	 * @return void
	 */
	public static function editor_colors() {
		?><div><?php
			do_action( 'empty_settings_before' );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_label',
				'preset'	=> __( 'Page Margin Color', 'empty' ),
				'data'		=> 'for="empty_options[editor_colors][wrap]"'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'text',
				'id'		=> 'empty_options[editor_colors][wrap]'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_color',
				'preset'	=> 'x',
				'data'		=> 'for="empty_options[editor_colors][wrap]"'
			) );
			do_action( 'empty_settings_after' );
		?></div><div><?php
			do_action( 'empty_settings_before' );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_label',
				'preset'	=> __( 'Row Spacing Color', 'empty' ),
				'data'		=> 'for="empty_options[editor_colors][row]"'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'text',
				'id'		=> 'empty_options[editor_colors][row]'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_color',
				'preset'	=> 'x',
				'data'		=> 'for="empty_options[editor_colors][row]"'
			) );
			do_action( 'empty_settings_after' );
			do_action( 'empty_settings_before' );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_label',
				'preset'	=> __( 'Row Select Color', 'empty' ),
				'data'		=> 'for="empty_options[editor_colors][select_row]"'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'text',
				'id'		=> 'empty_options[editor_colors][select_row]'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_color',
				'preset'	=> 'x',
				'data'		=> 'for="empty_options[editor_colors][select_row]"'
			) );
			do_action( 'empty_settings_after' );
		?></div><div><?php
			do_action( 'empty_settings_before' );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_label',
				'preset'	=> __( 'Element Spacing Color', 'empty' ),
				'data'		=> 'for="empty_options[editor_colors][element]"'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'text',
				'id'		=> 'empty_options[editor_colors][element]'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_color',
				'preset'	=> 'x',
				'data'		=> 'for="empty_options[editor_colors][element]"'
			) );
			do_action( 'empty_settings_after' );
			do_action( 'empty_settings_before' );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_label',
				'preset'	=> __( 'Element Select Color', 'empty' ),
				'data'		=> 'for="empty_options[editor_colors][select_element]"'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'text',
				'id'		=> 'empty_options[editor_colors][select_element]'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'label',
				'id'		=> 'empty_color',
				'preset'	=> 'x',
				'data'		=> 'for="empty_options[editor_colors][select_element]"'
			) );
			do_action( 'empty_settings_after' );
		?></div><?php
		
		echo '<p class="description">' . __( 'Define custom colors to be used in the visual editor.', 'empty' ) . '</p>';
	}
	
	/**
	 * Breakpoint
	 *
	 * @since 1.0
	 *
	 * @param string $slug	Setting slug
	 * @param mixed $args	Additional arguments
	 *
	 * @return void
	 */
	public static function breakpoint( $slug, $args ) {
		if ( ! isset( $args->count ) || empty( $args->breakpoint ) ) return false;
		
		do_action( 'empty_settings_before' );
		self::$helper->output_setting( array(
			'type'		=> 'input',
			'id'		=> 'empty_breakpoints[' . $args->count . '][slug]',
			'title'		=> __( 'Slug', 'empty' ),
			'preset'	=> $args->breakpoint->slug,
			'OUTPUT_TITLE'
		) );
		do_action( 'empty_settings_after' );
		
		do_action( 'empty_settings_before' );
		self::$helper->output_setting( array(
			'type'		=> 'input',
			'id'		=> 'empty_breakpoints[' . $args->count . '][title]',
			'title'		=> __( 'Title', 'empty' ),
			'preset'	=> $args->breakpoint->title,
			'OUTPUT_TITLE'
		) );
		do_action( 'empty_settings_after' );
		
		do_action( 'empty_settings_before' );
		self::$helper->output_setting( array(
			'type'		=> 'number',
			'id'		=> 'empty_breakpoints[' . $args->count . '][break][value]',
			'title'		=> __( 'min-width', 'empty' ),
			'preset'	=> empty( $args->breakpoint->break->value ) ? ' ' : $args->breakpoint->break->value,
			'data'		=> 'step="0.1"',
			'OUTPUT_TITLE'
		) );
		self::$helper->output_setting( array(
			'type'		=> 'select',
			'id'		=> 'empty_breakpoints[' . $args->count . '][break][unit]',
			'preset'	=> empty( $args->breakpoint->break->unit ) ? ' ' : $args->breakpoint->break->unit
		) );
		do_action( 'empty_settings_after' );
	}
	
	/**
	 * Line-height
	 *
	 * @since 1.0
	 *
	 * @param string $id			Setting ID	
	 *
	 * @return void
	 */
	public static function line_height( $id ) {
		foreach ( self::$breaks as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'type'	=> 'number',
				'id'	=> $id . '[' . $breakpoint->slug . '][value]',
				'data'		=> 'step="0.1"'
			) );
			self::$helper->output_setting( array(
				'type'	=> 'select',
				'id'	=> $id . '[' . $breakpoint->slug . '][unit]'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
		}
	}
	
	/**
	 * Page paddings
	 *
	 * @since 1.7.5					Tooltip for "line-height"
	 * @since 1.0
	 *
	 * @param string $id			Setting ID
	 *
	 * @return void
	 */
	public static function page_paddings( $id ) {
		$id = explode( '[', $id );
		$id = $id[0] . '[.wrap]';
		
		foreach ( self::$breaks as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'type'		=> 'number',
				'id'		=> $id . '[padding-top][' . $breakpoint->slug . '][value]',
				'title'		=> __( 'top', 'empty' ),
				'data'		=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			self::$helper->output_setting( array(
				'type'			=> 'number',
				'id'			=> $id . '[padding-bottom][' . $breakpoint->slug . '][value]',
				'title'			=> __( 'bottom', 'empty' ),
				'description'	=> '&times; ' . __( 'line-height', 'empty' ) . ' <i class="fa fa-question-circle-o" aria-hidden="true" data-empty_tooltip="line-height"></i>',
				'data'			=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'type'		=> 'number',
				'id'		=> $id . '[padding-left][' . $breakpoint->slug . '][value]',
				'title'		=> __( 'left', 'empty' ),
				'data'		=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'select',
				'id'		=> $id . '[padding-left][' . $breakpoint->slug . '][unit]'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'number',
				'id'		=> $id . '[padding-right][' . $breakpoint->slug . '][value]',
				'title'		=> __( 'right', 'empty' ),
				'data'		=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			self::$helper->output_setting( array(
				'type'		=> 'select',
				'id'		=> $id . '[padding-right][' . $breakpoint->slug . '][unit]'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
		}
		
		self::$helper->output_setting( array(
			'type'		=> 'hidden',
			'id'		=> $id . '[padding-top][connection]',
			'preset'	=> 'empty_css[*][line-height]'
		) );
		self::$helper->output_setting( array(
			'type'		=> 'hidden',
			'id'		=> $id . '[padding-bottom][connection]',
			'preset'	=> 'empty_css[*][line-height]'
		) );
	}
	
	/**
	 * Content width
	 *
	 * @since 1.0
	 *
	 * @param string $id			Setting ID
	 *
	 * @return void
	 */
	public static function content_width( $id ) {
		$id = explode( '[', $id );
		$id = $id[0] . '[.row-inner][max-width]';
		
		foreach ( self::$breaks as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'id'	=> $id . '[' . $breakpoint->slug . '][value]',
				'type'	=> 'number',
				'data'	=> 'step="0.1"'
			) );
			self::$helper->output_setting( array(
				'id'	=> $id . '[' . $breakpoint->slug . '][unit]',
				'type'	=> 'select'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
		}
	}
	
	/**
	 * Row spacing
	 *
	 * @since 1.7.5					Tooltip for "line-height"
	 * @since 1.0
	 *
	 * @param string $id			Setting ID
	 *
	 * @return void
	 */
	public static function row_spacing( $id ) {
		$id = explode( '[', $id );
		$id = $id[0] . '[.row + .row]';
		
		foreach ( self::$breaks as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'id'			=> $id . '[margin-top][' . $breakpoint->slug . '][value]',
				'type'			=> 'number',
				'title'			=> __( 'Vertical Spacing', 'empty' ),
				'description'	=> '&times; ' . __( 'line-height', 'empty' ) . ' <i class="fa fa-question-circle-o" aria-hidden="true" data-empty_tooltip="line-height"></i>',
				'data'			=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
		}
		
		self::$helper->output_setting( array(
			'type'		=> 'hidden',
			'id'		=> $id . '[margin-top][connection]',
			'preset'	=> 'empty_css[*][line-height]'
		) );
	}
	
	/**
	 * Element spacing
	 *
	 * @since 1.7.5					Tooltip for "line-height"
	 * @since 1.0
	 *
	 * @param string $id			Setting ID
	 *
	 * @return void
	 */
	public static function element_spacing( $id ) {
		$id = explode( '[', $id );
		$id = $id[0] . '[*]';
		
		foreach ( self::$breaks as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'id'		=> $id . '[element_spacing_horizontal][' . $breakpoint->slug . '][value]',
				'type'		=> 'number',
				'title'		=> __( 'Horizontal Spacing', 'empty' ),
				'data'		=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			self::$helper->output_setting( array(
				'id'	=> $id . '[element_spacing_horizontal][' . $breakpoint->slug . '][unit]',
				'type'	=> 'select'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'id'			=> $id . '[element_spacing_vertical][' . $breakpoint->slug . '][value]',
				'type'			=> 'number',
				'title'			=> __( 'Vertical Spacing', 'empty' ),
				'description'	=> '&times; ' . __( 'line-height', 'empty' ) . ' <i class="fa fa-question-circle-o" aria-hidden="true" data-empty_tooltip="line-height"></i>',
				'data'			=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
		}
		
		self::$helper->output_setting( array(
			'type'		=> 'hidden',
			'id'		=> $id . '[element_spacing_vertical][connection]',
			'preset'	=> 'empty_css[*][line-height]'
		) );
	}
	
	/**
	 * Viewport switch
	 *
	 * @since 2.5.6				Remove of load function –> now located in class.empty_admin.php
	 * @since 1.4				Output all preceeding slugs
	 * @since 1.3				Display label or not
	 * @since 1.0
	 *
	 * @param boolean $label	
	 *
	 * @return boolean|void
	 */
	public static function viewport_switch( $label = true ) {
		$values = array();
		foreach( self::$breaks as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
			$values[ $breakpoint->slug ] = $breakpoint->title;
		}
		
		do_action( 'empty_settings_before' );
		self::$helper->output_setting( array(
			'type'		=> 'label',
			'id'		=> 'empty_breakpoints_label',
			'preset'	=> '<i class="fa fa-desktop" aria-hidden="true"></i>' . ( $label ? '<span>' . __( 'Viewport', 'empty' ) . '</span>' : '' ),
		) );
		self::$helper->output_setting( array(
			'type'		=> 'radio',
			'id'		=> 'empty_breakpoints_current',
			'value'		=> $values,
			'data'		=> 'data-empty_do="set:option" data-empty_trigger="click"'
		) );
		do_action( 'empty_settings_after' );
	}
	
	/**
	 * Typography body
	 *
	 * @since 1.7.5					Tooltip for "line-height"
	 * @since 1.0
	 *
	 * @param string $id			Setting ID
	 *
	 * @return void
	 */
	public static function typography_body( $id ) {
		$id = explode( '[', $id );
		$id = $id[0] . '[body]';
		
		foreach ( self::$breaks as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'id'	=> $id . '[font-family][' . $breakpoint->slug . ']',
				'type'	=> 'text',
				'title'	=> __( 'font-family', 'empty' ),
				'OUTPUT_TITLE'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'id'			=> $id . '[line-height][' . $breakpoint->slug . '][value]',
				'type'			=> 'number',
				'title'			=> __( 'line-height', 'empty' ),
				'description'	=> '&times; ' . __( 'line-height', 'empty' ) . ' <i class="fa fa-question-circle-o" aria-hidden="true" data-empty_tooltip="line-height"></i>',
				'data'			=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'id'		=> $id . '[font-size][' . $breakpoint->slug . '][value]',
				'type'		=> 'number',
				'title'		=> __( 'font-size', 'empty' ),
				'data'		=> 'step="0.1"',
				'OUTPUT_TITLE'
			) );
			self::$helper->output_setting( array(
				'id'	=> $id . '[font-size][' . $breakpoint->slug . '][unit]',
				'type'	=> 'select'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
			
			do_action( 'empty_settings_before', $breakpoint->slug );
			self::$helper->output_setting( array(
				'id'	=> $id . '[font-weight][' . $breakpoint->slug . ']',
				'type'	=> 'select',
				'title'	=> __( 'font-weight', 'empty' ),
				'OUTPUT_TITLE'
			) );
			do_action( 'empty_settings_after', $breakpoint->slug );
		}
		
		self::$helper->output_setting( array(
			'type'		=> 'hidden',
			'id'		=> $id . '[line-height][connection]',
			'preset'	=> 'empty_css[*][line-height]'
		) );
	}
	
	/**
	 * Add elements to editor
	 *
	 * @since 1.3				Display label or not
	 * @since 1.2
	 *
	 * @param boolean $label
	 *
	 * @return void
	 */
	public static function elements( $label = true ) {
		$parser = new Empty_Parser();
	
		do_action( 'empty_settings_before' );
		self::$helper->output_setting( array(
			'type'		=> 'label',
			'id'		=> 'empty_editor_element[label]',
			'preset'	=> '<i class="fa fa-plus" aria-hidden="true"></i>' . ( $label ? '<span>' . __( 'Add', 'empty' ) . '</span>' : '' ),
		) );
		foreach ( $parser::$elements as $element ) {
			$id = 'empty_element-' . $element->slug;
			$template = $element->editable();
			require( empty_template( 'script.template.php' ) );
			
			self::$helper->output_setting( array(
				'type'		=> 'button',
				'id'		=> 'empty_editor_element[' . $element->slug . ']',
				'data'		=> 'data-empty_do="editor:add:' . $element->slug . '" data-empty_trigger="click"',
				'preset'	=> $element->title,
			) );
		}
		do_action( 'empty_settings_after' );
	}
	
	/**
	 * Generate checkbox to hide grid in editor
	 *
	 * @since 1.3				Display label or not
	 * @since 1.2
	 *
	 * @param boolean $label
	 *
	 * @return void
	 */
	public static function grid_switch( $label = true ) {
		do_action( 'empty_settings_before' );
		self::$helper->output_setting( array(
			'type'		=> 'label',
			'id'		=> 'empty_editor_grid',
			'preset'	=> '<i class="fa fa-th" aria-hidden="true"></i>' . ( $label ? '<span>' . __( 'Grid', 'empty' ) . '</span>' : '' ),
		) );
		self::$helper->output_setting( array(
			'type'			=> 'radio',
			'id'			=> 'empty_editor_grid',
			'value'			=> array(
				'show'	=> '<i class="fa fa-eye" aria-hidden="true"></i>',
				'hide'	=> '<i class="fa fa-eye-slash" aria-hidden="true"></i>'
			),
			'data'			=> 'data-empty_do="set:option" data-empty_trigger="click"'
		) );
		do_action( 'empty_settings_after' );
	}
	
	/**
	 * Generate reset button
	 *
	 * @since 1.4.1
	 *
	 * @param boolean $label
	 *
	 * @return void
	 */
	public static function reset_button( $label = true ) {
		do_action( 'empty_settings_before' );
		self::$helper->output_setting( array( 
			'type'		=> 'button',
			'id'		=> 'empty-reset_breakpoint',
			'preset'	=> '<i class="fa fa-refresh" aria-hidden="true"></i>' . ( $label ? '<span>' . __( 'Reset All Elements', 'empty' ) . '</span>' : '' ),
			'data'		=> 'data-empty_do="editor:reset" data-empty_trigger="click"'
		) );
		do_action( 'empty_settings_after' );
	}
	
	/**
	 * Generate additional actions
	 *
	 * @since 1.6		Reset button for single element
	 * @since 1.4.2
	 *
	 * @return void
	 */
	public static function additional_actions() {
		do_action( 'empty_settings_before' );
//		self::$helper->output_setting( array( 
//			'type'		=> 'button',
//			'id'		=> 'empty-additional_edit',
//			'preset'	=> '<i class="fa fa-edit" aria-hidden="true"></i>',
//			'data'		=> 'data-empty_do="editor:edit" data-empty_trigger="click" data-condition="single"'
//		) );
		self::$helper->output_setting( array( 
			'type'		=> 'button',
			'id'		=> 'empty-additional_extend',
			'preset'	=> '<i class="fa fa-eye-slash" aria-hidden="true"></i>',
			'data'		=> 'data-empty_do="editor:toggle:hidden" data-empty_trigger="click" data-condition="[class*=\'col-\']"'
		) );
		self::$helper->output_setting( array( 
			'type'		=> 'button',
			'id'		=> 'empty-additional_extend',
			'preset'	=> '<i class="fa fa-expand" aria-hidden="true"></i>',
			'data'		=> 'data-empty_do="editor:toggle:full" data-empty_trigger="click" data-condition=".row"'
		) );
		self::$helper->output_setting( array( 
			'type'		=> 'button',
			'id'		=> 'empty-additional_collapse',
			'preset'	=> '<i class="fa fa-compress" aria-hidden="true"></i>',
			'data'		=> 'data-empty_do="editor:toggle:collapse" data-empty_trigger="click" data-condition=".row"'
		) );
		self::$helper->output_setting( array( 
			'type'		=> 'button',
			'id'		=> 'empty-additional_clone',
			'preset'	=> '<i class="fa fa-clone" aria-hidden="true"></i>',
			'data'		=> 'data-empty_do="editor:clone" data-empty_trigger="click" data-condition="*"'
		) );
		self::$helper->output_setting( array( 
			'type'		=> 'button',
			'id'		=> 'empty-additional_reset',
			'preset'	=> '<i class="fa fa-refresh" aria-hidden="true"></i>',
			'data'		=> 'data-empty_do="editor:reset:1" data-empty_trigger="click"'
		) );
		self::$helper->output_setting( array( 
			'type'		=> 'button',
			'id'		=> 'empty-additional_remove',
			'preset'	=> '<i class="fa fa-trash" aria-hidden="true"></i>',
			'data'		=> 'data-empty_do="editor:remove" data-empty_trigger="click"'
		) );
		do_action( 'empty_settings_after' );
	}
	
	/**
	 * Generate breakpoint copy actions
	 *
	 * @param boolean $label
	 *
	 * @since 1.5
	 *
	 * @return void
	 */
	public static function copy_actions( $label = true ) {
		$values = array(
			null		=> __( 'Copy values from &hellip;', 'empty' )
		);
		
		foreach( self::$breaks as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
		
			$values[ $breakpoint->slug ] = $breakpoint->title;
		}
	
		do_action( 'empty_settings_before' );
		self::$helper->output_setting( array( 
			'type'		=> 'button',
			'id'		=> 'empty-reset_breakpoint',
			'preset'	=> '<i class="fa fa-refresh" aria-hidden="true"></i>' . ( $label ? '<span>' . __( 'Reset All Elements', 'empty' ) . '</span>' : '' ),
			'data'		=> 'data-empty_do="reset:breakpoint" data-empty_trigger="click"'
		) );
		self::$helper->output_setting( array(
			'type'		=> 'select',
			'id'		=> 'empty-copy_breakpoint',
			'value'		=> $values
		) );
		do_action( 'empty_settings_after' );
	}
	
	/**
	 * Output page descriptions
	 *
	 * @param string $slug
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function page_description( $slug ) {
		if ( ! file_exists( empty_template( 'messages/html.description_' . $slug . '.php' ) ) ) return false;
		
		require( empty_template( 'messages/html.description_' . $slug . '.php' ) );
	}
	
	/**
	 * Output messages
	 *
	 * @since 1.6
	 *
	 * @return boolean
	 */
	public static function messages() {
		/**
		 * @since 1.6	Check for existance of files
		 */
		if ( 
			! file_exists( EMPTY_ROOT . 'base/css/admin.dynamic.css' ) ||
			! file_exists( EMPTY_ROOT . 'base/css/layout.dynamic.css' ) ||
			! file_exists( EMPTY_ROOT . 'base/css/wysiwyg.dynamic.css' ) ||
			! file_exists( EMPTY_ROOT . 'base/css/_editor/editor.dynamic.css' )
		) {
			add_settings_error( 'empty-messages', 'empty-files_missing', empty_template( 'messages/html.notice_files.php', true ), 'notice-warning' );
		}
		
		settings_errors( 'empty-messages' );
	}
	
	/**
	 * Output breadcrumbs & breadcrumbs templates
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function breadcrumbs() {
		$id = 'empty_breadcrumb-start';
		$template = '<span><strong>' . __( 'Path:', 'empty' ) . '</strong></span><span>' . __( 'Editor', 'empty' ) . '</span>';
		require( empty_template( 'script.template.php' ) );
		
		$id = 'empty_breadcrumb-connector';
		$template = '<span><i class="fa fa-angle-right" aria-hidden="true"></i></span>';
		require( empty_template( 'script.template.php' ) );
	}
	
	
	
	/**
	 * Listen to save request for all files
	 *
	 * @since 1.6		Static
	 * @since 1.4		New naming convention
	 * @since 1.3		New file admin.editor.dynamic.css
	 * @since 1.1		New file editor.dynamic.css
	 * @since 1.0
	 * 
	 * @return void
	 */
	public static function file_save_listener() {
		$css_admin_dynamic = new Empty_File( EMPTY_ROOT . 'base/css/', 'admin.dynamic.css', empty_template( 'css.admin.dynamic.php' ) );
		$css_admin_dynamic->build();
		
		$css_layout_dynamic = new Empty_File( EMPTY_ROOT . 'base/css/', 'layout.dynamic.css', empty_template( 'css.layout.dynamic.php' ) );
		$css_layout_dynamic->build();
		
		$css_editor_dynamic = new Empty_File( EMPTY_ROOT . 'base/css/', 'wysiwyg.dynamic.css', empty_template( 'css.wysiwyg.dynamic.php' ) );
		$css_editor_dynamic->build();
		
		$css_admin_editor_dynamic = new Empty_File( EMPTY_ROOT . 'base/css/_editor/', 'editor.dynamic.css', empty_template( '_editor/css.editor.dynamic.php' ) );
		$css_admin_editor_dynamic->build();
	}
	
	
	
	/**
	 * AJAX update_option
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function ajax_update_option() {
		if ( empty( $_POST['option'] ) || empty( $_POST['value'] ) ) {
			echo 0;
			wp_die();
		}
		
		$return = update_option( $_POST['option'], $_POST['value'] );
		
		echo json_encode( $return );
		wp_die();
	}
}



$helper = new Empty_Settings();
$helper->__init();

?>