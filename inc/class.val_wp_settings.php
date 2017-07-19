<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.6
 */



if ( ! class_exists( 'Val_WP_Settings' ) ) {
	/**
	 * Helper class to register and load WordPress settings
	 *
	 * @since 2.0
	 */
	class Val_WP_Settings {
		/**
		 * Setup
		 *
		 * @since 2.0
		 *
		 * @var string $page_slug	Page slug to add settings to
		 * @var array $settings		Multidimensional settings array
		 */
		private $page_slug, $settings;
		
		/**
		 * Prepare settings for registration
		 *
		 * @since 2.0
		 *
		 * @param string $page_slug		Page slug
		 * @param object $settings		Multidimensional array of settings grouped by section name
		 *
		 * @return boolean|void
		 */
		public function init_settings( $page_slug, $settings ) {
			if ( empty( $page_slug ) || empty( $settings ) ) return false;
			
			$this->page_slug = sanitize_title( $page_slug );
			$this->settings = $settings;
			
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
		
		
		
		/**
		 * Register settings
		 *
		 * @since 2.2	Setting title filter
		 * @since 2.0
		 *
		 * @return boolean|void
		 */
		public function register_settings() {
			if ( empty( $this->page_slug ) || empty( $this->settings ) ) return false;
			
			foreach ( $this->settings as $section_slug => $section_arr ) {
				if ( empty( $section_arr ) ) continue;
				$section_slug = sanitize_title( $section_slug );
				$section_o = (object) $section_arr;
				
				add_settings_section( $section_slug, empty( $section_o->title ) ? '' : $section_o->title, empty( $section_o->callback ) ? null : $section_o->callback, $this->page_slug );
				
				foreach ( $section_o->settings as $setting_slug => $setting_arr ) {
					if ( empty( $setting_arr ) ) continue;
					$setting_register = explode( '[', $setting_slug );
					$setting_register = esc_attr( $setting_register[0] );//sanitize_title( $setting_slug );
					$setting_arr['id'] = esc_attr( $setting_slug );
					$setting_o = (object) $setting_arr;
					$setting_title = apply_filters( 'val_settings_title', empty( $setting_o->title ) ? '' : $setting_o->title, $setting_slug );
					
					register_setting( $this->page_slug, $setting_register );
					
					add_settings_field( 
						$setting_slug, 
						$setting_title, 
						array( $this, 'output_setting' ), 
						$this->page_slug, 
						$section_slug, 
						$setting_arr
					);
				}
			}
		}
		
		/**
		 * Output setting
		 *
		 * @since 2.6					Ney $type "button"
		 * @since 2.5					New $type "label"
		 * @since 2.4					New $type "link"
		 * @since 2.3					New $data filter / val_setting_before_input & val_setting_after_input
		 * @since 2.2					Additional parameters for function / preset $value / title output
		 * @since 2.1					New $type "function"
		 * @since 2.0
		 *
		 * @param array $arr			Options array
		 * @param string $meta_type		Type of setting to output
		 *
		 * @return void
		 */
		public function output_setting( $arr, $meta_type = 'PAGE' ) {
			global $post, $user_id;
			
			$o = (object) $arr;
			$type = sanitize_title( $o->type );
			$id = esc_attr( $o->id );//sanitize_title( $o->id );
			$path = explode( '[', $id );
			$data = apply_filters( 'val_setting_data', ( empty( $o->data ) ? '' : $o->data ), $meta_type, $type, $id );
			$description = apply_filters( 'val_setting_description', ( empty( $o->description ) ? '' : $o->description ), $meta_type, $type, $id );
			$options = apply_filters( 'val_setting_options', ( empty( $o->value ) ? '' : $o->value ), $meta_type, $type, $id );
			$parameters = empty( $o->parameters ) ? '' : $o->parameters;
			
			if ( empty( $o->preset ) ) {
				switch ( $meta_type ) {
					case 'USER':
						$value = get_user_meta( $user_id, $path[0], true );
						break;
					case 'TERM':
						if ( empty( $_GET['tag_ID'] ) ) {
							$value = '';
							break;
						}
						$value = get_term_meta( $_GET['tag_ID'], $path[0], true );
						break;
					case 'POST':
						$value = get_post_meta( $post->ID, $path[0], true );
						break;
					case 'PAGE':
					default:
						$value = get_option( $path[0] );
						break;
				}
				for ( $p = 1; $p < count( $path ); $p++ ) {
					$key = substr( $path[ $p ], 0, -1 );
					$value = empty( $value[ $key ] ) ? '' : $value[ $key ];
				}
			} else {
				$value = $o->preset;
			}
			
			
			
			do_action( 'val_setting_before', $meta_type, $type, $id );
			
			if ( $meta_type != 'PAGE' || in_array( 'OUTPUT_TITLE', $arr ) ) echo apply_filters( 'val_setting_title', esc_html( empty( $o->title ) ? '' : $o->title ), $meta_type, $type, $id );	
			switch ( $type ) {
				case 'button':
					?>
					<button type="button" name="<?php echo $id ?>" id="<?php echo $id; ?>" <?php echo $data; ?>><?php echo $value; ?></button><?php echo $description; ?>
					<?php	
					break;
				case 'label':
					?>
					<label <?php echo $data; ?>><?php echo $value; ?></label>
					<?php
					break;
				case 'link':
					?>
					<a href="<?php echo $o->value; ?>" <?php echo $data; ?>><?php echo $value; ?></a>
					<?php
					break;
				case 'function':
					switch ( $meta_type ) {
						case 'USER':
							$arg2 = $user_id;
							break;
						case 'TERM':
							$arg2 = $_GET['tag_ID'];
							break;
						case 'POST':
							$arg2 = $post->ID;
							break;
						default:
							$arg2 = null;
							break;
					}
					if ( $arg2 ) {
						call_user_func( $o->value, $id, $arg2, $parameters );
					} else {
						call_user_func( $o->value, $id, $parameters );
					}
					break;
				case 'radio':
					if ( empty( $options ) || gettype( $options ) !== 'array' ) return false;
					
					$value = esc_attr( $value );
					//$options = array_map( 'esc_html', $options );
					foreach ( $options as $key => $output ) { 
						$key = esc_attr( $key );
						do_action( 'val_setting_before_radio_option', $meta_type, $type, $id );
						?>
						<input type="radio" name="<?php echo $id; ?>" id="<?php echo $id . '[' . $key . ']'; ?>" value="<?php echo $key; ?>" <?php checked( $key, $value ); ?> <?php echo $data; ?>><label for="<?php echo $key; ?>"><?php echo $output; ?></label>
						<?php
						do_action( 'val_setting_after_radio_option', $meta_type, $type, $id );
					}
					break;
				case 'select':
					if ( empty( $options ) || gettype( $options ) !== 'array' ) return false;
					
					$value = esc_attr( $value );
					$options = array_map( 'esc_html', $options );
					?>
					<select name="<?php echo $id; ?>" id="<?php echo $id; ?>" <?php echo $data; ?>>
						<?php 
						foreach ( $options as $key => $output ) { 
							$key = esc_attr( $key );
							?>
							<option value="<?php echo $key; ?>" <?php selected( $key, $value ); ?>><?php echo $output; ?></option>
						<?php } ?>
					</select>
					<?php
					break;
				case 'checkbox':
					$value = esc_attr( $value );
					$options = esc_attr( $options );
					do_action( 'val_setting_before_input', $meta_type, $type, $id );
					?>
					<input type="checkbox" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $options; ?>" <?php echo $data; ?> <?php checked( $value, $options ); ?> />
					<?php do_action( 'val_setting_after_input', $meta_type, $type, $id ); ?>
					<?php echo $description; ?>
					<?php
					break;
				case 'textarea':
					$value = esc_textarea( $value );
					?>
					<textarea name="<?php echo $id; ?>" id="<?php echo $id; ?>" <?php echo $data; ?> ><?php echo $value; ?></textarea><?php echo $description; ?>
					<?php
					break;
				default:
					$value = esc_attr( $value );
					do_action( 'val_setting_before_input', $meta_type, $type, $id );
					?>
					<input type="<?php echo $type; ?>" name="<?php echo $id ?>" id="<?php echo $id; ?>" value="<?php echo $value; ?>" <?php echo $data; ?> /><?php do_action( 'val_setting_after_input', $meta_type, $type, $id ); ?><?php echo $description; ?>
					<?php	
					break;
			}
			
			do_action( 'val_setting_after', $meta_type, $type, $id );
		}
	}
}



if ( ! class_exists( 'Val_WP_Settings_Page' ) ) {
	/**
	 * Helper class to add WordPress settings page
	 *
	 * @since 2.0
	 */
	class Val_WP_Settings_Page {
		/**
		 * Setup
		 *
		 * @since 2.0
		 * 
		 * @var object $init	Object containing all page settings
		 */
		private $init;
		
		/**
		 * Sets variables and WordPress actions to build settings page
		 *
		 * @since 2.0
		 *
		 * @param string $slug		Page slug
		 * @param string $title		Page title
		 * @param array $settings	Multidimensional array of settings grouped by section name
		 * @param string $type		Page type
		 * @param int $position		Page position
		 * @param string $icon		Icon URL / Dashicon slug
		 * @param string $parent	Parent slug
		 * @param string $cap		Capability needed to display page
		 * 
		 * @return object
		 */
		public function __construct( $slug, $title, $settings, $type = 'MENU', $icon = '', $position = null, $parent = null, $cap = 'manage_options' ) {
			if ( empty( $slug ) || empty( $title ) || empty( $settings ) ) return false;
			
			$this->init = (object) array(
				'slug'		=> $slug,
				'title'		=> $title,
				'settings'	=> $settings,
				'type'		=> $type,
				'icon'		=> $icon,
				'position'	=> $position,
				'cap'		=> $cap,
				'parent'	=> $parent
			);
			
			$helper = new Val_WP_Settings();
			$helper->init_settings( $this->init->slug, $this->init->settings );
			add_action( 'admin_menu', array( $this, 'add_page' ) );
		}
		
		
		
		/**
		 * Add page to menu
		 *
		 * @since 2.0
		 *
		 * @return void
		 */
		public function add_page() {
			global $menu, $submenu;
			if ( $this->init->type === 'MENU' && in_array( $this->init->slug, wp_list_pluck( $menu, 2 ) ) ) return false;
			if ( $this->init->parent && isset( $submenu[ $this->init->parent ] ) && in_array( $this->init->slug, wp_list_pluck( $submenu[ $this->init->parent ], 2 ) ) ) return false;
			
			$function = 'add_' . strtolower( $this->init->type ) . '_page';
			switch ( $this->init->type ) {
				case 'MENU':
					$function( $this->init->title, $this->init->title, $this->init->cap, $this->init->slug, array( $this, 'output_page' ), $this->init->icon, $this->init->position );
					break;
				case 'SUBMENU':
					$function( $this->init->parent, $this->init->title, $this->init->title, $this->init->cap, $this->init->slug, array( $this, 'output_page' ) );
					break;
			}
		}
		
		/**
		 * Output page
		 *
		 * @since 2.3	settings_errors();
		 * @since 2.2	$this->init->slug and $this->init->parent are passed to the action calls / new val_settings_top
		 * @since 2.0
		 *
		 * @return void
		 */
		public function output_page() {
			do_action( 'val_settings_top', $this->init->slug, $this->init->parent );
			?>
			<div class="wrap">
				<h1><?php echo esc_html( $this->init->title ); ?></h1>
				<?php settings_errors(); ?>
				<form method="post" action="options.php">
					<?php
					do_action( 'val_settings_before',  $this->init->slug, $this->init->parent );
					wp_nonce_field( $this->init->slug . '_nonce' );
					settings_fields( $this->init->slug );
					do_settings_sections( $this->init->slug );
					do_action( 'val_settings_after',  $this->init->slug, $this->init->parent );
					?>
					
					<?php 
					do_action( 'val_setting_before', 'PAGE', 'submit' );
					submit_button( '', array( 'primary' ), '', false ); 
					do_action( 'val_setting_after', 'PAGE', 'submit' );
					?>
				</form>
			</div>
			<?php
		}
	}
}



if ( ! class_exists( 'Val_WP_Settings_Meta' ) ) {
	/**
	 * Helper class to add WordPress meta box
	 *
	 * @since 2.0
	 */
	class Val_WP_Settings_Meta {
		/**
		 * Setup
		 *
		 * @since 2.0
		 * 
		 * @var object $init		Object containing all meta settings
		 * @var array $meta_keys	ID of each meta to save
		 */
		private $init;
		public $meta_keys = array();
		
		/**
		 * Sets variables and WordPress actions to build meta box
		 *
		 * @since 2.0
		 *
		 * @param string $slug			Meta slug
		 * @param string $title			Meta title
		 * @param array $settings		Multidimensional array of settings grouped by section name
		 * @param string $type			Meta type
		 * @param string $selector		WordPress post type to add box to
		 * @param string $context		Meta box context
		 * @param string $priority		Meta box priority
		 * 
		 * @return object
		 */
		public function __construct( $slug, $title, $settings, $type = 'POST', $selector = 'post', $context = 'side', $priority = 'default' ) {
			if ( empty( $slug ) || empty( $title ) || empty( $settings ) ) return false;
			//$type = sanitize_title( $type );
			$selector = sanitize_title( $selector );
			
			$this->init = (object) array(
				'slug'		=> $slug,
				'title'		=> $title,
				'settings'	=> $settings,
				'type'		=> $type,
				'selector'	=> $selector,
				'context'	=> $context,
				'priority'	=> $priority
			);
			
			switch ( $type ) {
				case 'USER':
					add_action( 'show_user_profile', array( $this, 'output_meta' ) );
					add_action( 'edit_user_profile', array( $this, 'output_meta' ) );
					add_action( 'personal_options_update', array( $this, 'save_meta' ) );
					add_action( 'edit_user_profile_update', array( $this, 'save_meta' ) );
					break;
				case 'TERM':
					add_action( $selector . '_add_form_fields', array( $this, 'output_meta' ) );
					add_action( $selector . '_edit_form_fields', array( $this, 'output_meta' ) );
					add_action( 'created_' . $selector, array( $this, 'save_meta' ) );
					add_action( 'edited_' . $selector, array( $this, 'save_meta' ) );
					break;
				case 'POST':
				default:
					if ( $selector === 'post' ) {
						$action = 'save_post';
					} else {
						$action = 'save_post_' . $selector;
					}
				
					add_action( 'add_meta_boxes', array( $this, 'add_post_meta' ) );
					add_action( $action , array( $this, 'save_meta' ), 10, 3 );
					break;
			}
			
			foreach ( $this->init->settings as $section ) {
				$section = (object) $section;
				
				foreach ( $section->settings as $setting_key => $v ) {
					$path = explode( '[' , $setting_key );
					$setting_key = esc_attr( $path[0] );
					if ( in_array( $setting_key , $this->meta_keys ) ) continue;
					
					array_push( $this->meta_keys, $setting_key );
				}
			}
		}
		
		
		
		/**
		 * Add post meta box
		 *
		 * @since 2.0
		 *
		 * @return void
		 */
		public function add_post_meta() {
			add_meta_box( $this->init->slug, $this->init->title, array( $this, 'output_meta' ), $this->init->selector, $this->init->context, $this->init->priority );
		}
		
		
		
		/**
		 * Output meta
		 *
		 * @since 2.0
		 *
		 * @return void
		 */
		public function output_meta() {
			do_action( 'val_settings_meta_before', $this->init->type, $this->init->slug );
			
			wp_nonce_field( basename( __FILE__ ), $this->init->slug . '_nonce' );
			foreach ( $this->init->settings as $section_slug => $section_arr ) {
				if ( empty( $section_arr ) ) continue;
				$section_slug = sanitize_title( $section_slug );
				$section_o = (object) $section_arr;
				
				do_action( 'val_settings_meta_before_section', $section_slug );
				
				echo esc_html( apply_filters( 'val_settings_section_title', empty( $section_o->title ) ? '' : $section_o->title ) );
				foreach ( $section_o->settings as $setting_slug => $setting_arr ) {
					if ( empty( $setting_arr ) ) continue;
					$setting_register = explode( '[', $setting_slug );
					$setting_register = esc_attr( $setting_register[0] );//sanitize_title( $setting_slug );
					$setting_arr['id'] = esc_attr( $setting_slug );
					$setting_o = (object) $setting_arr;
					
					$helper = new Val_WP_Settings();
					$helper->output_setting( $setting_arr, $this->init->type );
				}
				
				do_action( 'val_settings_meta_after_section', $section_slug );
			}
			
			do_action( 'val_settings_meta_after', $this->init->type, $this->init->slug );
		}
		
		
		
		/**
		 * Save meta
		 *
		 * @since 2.0
		 *
		 * @param int $id			WordPress post ID
		 * @param object $post		WordPress post object
		 * @param boolean $update
		 *
		 * @return void
		 */
		public function save_meta( $id, $post, $update ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $id;
			if ( ! isset( $_POST[ $this->init->slug . '_nonce' ] ) || ! wp_verify_nonce( $_POST[ $this->init->slug . '_nonce' ], basename( __FILE__ ) ) ) return $id;
			
			foreach( $this->meta_keys as $key ) {
				$function = 'update_' . $this->init->type . '_meta';
				$value = sanitize_meta( $key, empty( $_POST[ $key ] ) ? '' : $_POST[ $key ], $this->init->type );
				
				$function( $id, $key, $value );
			}
		}	
	}
}

?>