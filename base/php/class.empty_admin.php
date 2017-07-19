<?php 
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 
 
/**
* Setup the WordPress admin environment
*
* @since 2.0
*/
class Empty_Admin {
	/**
	 * Setup
	 *
	 * @since 2.0
	 *
	 * @var array $page_options			Contains the options for the options page
	 * @var array $page_breakpoints		Contains the options for the breakpoints page
	 * @var array $page_grid			Contains the options for the grid page
	 * @var array $page_typography		Contains the options for the typography page
	 * @var object $breakpoints			Empty_Breakpoints object
	 */
	private $page_options, $page_breakpoints, $page_grid, $page_typography, $breakpoints;

	/**
	 * Load files, register WordPress hooks and actions
	 *
	 * @since 2.5.7		Output admin notices
	 * @since 2.5.6		Output load triggers
	 * @since 2.5.5		Clean filenames; Output tooltips
	 * @since 2.5.4		Output breakpoints to JS
	 * @since 2.5.3		Define breakpoints globally
	 * @since 2.5		Check for updates to perform certain tasks
	 * @since 2.1		FontAwesome
	 * @since 2.0
	 *
	 * @return object
	 */
	public function __construct() {
		if ( ! is_admin() ) return false;
		require_once( EMPTY_ROOT .  'inc/class.val_wp_settings.php' );
		
		require_once( EMPTY_ROOT .  'base/php/class.empty_breakpoints.php' );
		$this->breakpoints = new Empty_Breakpoints();
		/** 
		 * @since 2.5.3		Define breakpoints statically
		 */
		define( 'EMPTY_BREAKS', json_encode( $this->breakpoints->get_all() ) );
		
		require_once( EMPTY_ROOT .  'base/php/class.empty_css.php' );
		require_once( EMPTY_ROOT .  'base/php/class.empty_file.php' );
		require_once( EMPTY_ROOT .  'base/php/class.empty_settings.php' );
		
		require_once( EMPTY_ROOT .  'base/php/_editor/class.empty_parser.php' );
		require_once( EMPTY_ROOT .  'base/php/_editor/class.empty_editor.php' );
		
		$this->__construct_options();
		
		
		
		add_action( 'admin_menu', array( $this, 'add_pages' ) );
		$this->generate_pages();
		
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'enqueue_wysiwyg_styles' ) );
		
		/**
		 * @since 2.1
		 */
		add_action( 'admin_head', array( $this, 'enqueue_fontawesome' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'wysiwyg_init' ) );
		
		/** 
		 * @since 2.5.7		Output admin notices
		 * @since 2.5.6		Output load triggers
		 * @since 2.5.5		Clean filenames; Output tooltips
		 * @since 2.5.4		Output_js
		 * @since 2.5
		 */
		add_action( 'admin_init', array( $this, 'version_check' ) );
		
		add_action( 'admin_head', array( $this, 'output_js' ) );
		
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'clean_uploads' ) );
		add_action( 'admin_footer', array( $this, 'output_tooltips' ) );
		
		add_action( 'admin_footer', array( $this, 'output_triggers' ) );
		
		add_action( 'admin_notices', array( $this, 'output_notices' ) );
	}
	
	/**
	 * Sets default options
	 *
	 * @since 2.5.3		Generate settings for each breakpoint
	 * @since 2.1		New settings section "Editor"
	 * @since 2.0
	 *
	 * @return void
	 */
	private function __construct_options() {
		/* Options */
		$this->page_options = array(
			array(
				'settings'	=> array(
					'empty_options[post_types]'	=> array(
						'title'		=> __( 'Post Types', 'empty' ),
						'type'		=> 'function',
						'value'		=> array( 'Empty_Settings', 'post_types' )
					)
				)
			),
			array(
				'title'		=> __( 'Editor', 'empty' ),
				'settings'	=> array(
					'empty_options[editor_colors]'	=> array(
						'title'		=> __( 'Colors', 'empty' ),
						'type'		=> 'function',
						'value'		=> array( 'Empty_Settings', 'editor_colors' )
					),
					'empty-file_save_trigger' => array(
						'type'		=> 'hidden',
						'preset'	=> uniqid()
					)
				)
			)
		);
		
		/* Breakpoints */
		$settings = array();
		$i = 0;
		foreach ( json_decode( EMPTY_BREAKS ) as $breakpoint ) {
			$breakpoint = (object) $breakpoint;
			
			$settings['empty_breakpoints[' . $i . ']'] = array(
				'title'			=> $breakpoint->slug,
				'type'			=> 'function',
				'value'			=> array( 'Empty_Settings', 'breakpoint' ),
				'parameters'	=> (object) array( 'count' => $i, 'breakpoint' => $breakpoint )
			);
			
			$i++;
		}
		
		$settings['empty-file_save_trigger'] = array(
			'type'		=> 'hidden',
			'preset' 	=> uniqid()
		);
		$this->page_breakpoints = array(
			array(
				'settings'	=> $settings
			)
		);
		
		/* Grid */
		$this->page_grid = array(
			array(
				'callback'	=> '',
				'settings'	=> array(
					'empty_css[*][line-height]'		=> array(
						'title'		=> __( 'Line-height', 'empty' ),
						'type'		=> 'function',
						'value'		=> array( 'Empty_Settings', 'line_height' )
					),
					'empty_css[' . uniqid() . ']'	=> array(
						'title'		=> __( 'Page Paddings', 'empty' ),
						'type'		=> 'function',
						'value'		=> array( 'Empty_Settings', 'page_paddings' )
					),
					'empty_css[' . uniqid() . ']'	=> array(
						'title'		=> __( 'Content Width', 'empty' ),
						'type'		=> 'function',
						'value'		=> array( 'Empty_Settings', 'content_width' )
					),
					'empty_css[' . uniqid() . ']'	=> array(
						'title'		=> __( 'Row Spacing', 'empty' ),
						'type'		=> 'function',
						'value'		=> array( 'Empty_Settings', 'row_spacing' )
					),
					'empty_css[' . uniqid() . ']'	=> array(
						'title'		=> __( 'Element Spacing', 'empty' ),
						'type'		=> 'function',
						'value'		=> array( 'Empty_Settings', 'element_spacing' )
					),
					'empty-file_save_trigger'		=> array(
						'type'		=> 'hidden',
						'preset' 	=> uniqid()
					)
				)
			)
		);
		
		/* Typography */
		$this->page_typography = array(
			array(
				'callback'	=> '',
				'settings'	=> array(
					'empty_typography[' . uniqid() . ']'	=> array(
						'title' 	=> 'body',
						'type'		=> 'function',
						'value'		=> array( 'Empty_Settings', 'typography_body' )
					),
					'empty-file_save_trigger'		=> array(
						'type'		=> 'hidden',
						'preset' 	=> uniqid()
					)
				)
			)
		);
	}
	
	/**
	 * Add admin pages to menu
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function add_pages() {
		add_menu_page( 'Empty', 'Empty', 'edit_theme_options', 'empty_options', '', 'dashicons-marker' ); 
	}
	
	/**
	 * Output admin pages
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	private function generate_pages() {
		new Val_WP_Settings_Page( 'empty_options', __( 'Settings', 'empty' ), $this->page_options, 'SUBMENU', null, null, 'empty_options', 'edit_theme_options' );
		
		new Val_WP_Settings_Page( 'empty_breakpoints', __( 'Breakpoints', 'empty' ), $this->page_breakpoints, 'SUBMENU', null, null, 'themes.php', 'edit_theme_options' );
		new Val_WP_Settings_Page( 'empty_grid', __( 'Grid', 'empty' ), $this->page_grid, 'SUBMENU', null, null, 'themes.php', 'edit_theme_options' );
		new Val_WP_Settings_Page( 'empty_typography', __( 'Typography', 'empty' ), $this->page_typography, 'SUBMENU', null, null, 'themes.php', 'edit_theme_options' );
	}
	
	/**
	 * Enqueue styles
	 *
	 * @since 2.1		Enqueue FontAwesome
	 * @since 2.0
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_register_style( 'empty_admin.layout', EMPTY_URL . 'base/css/admin.layout.css' );
		wp_enqueue_style( 'empty_admin.layout' );
		wp_register_style( 'empty_admin.dynamic', EMPTY_URL . 'base/css/admin.dynamic.css' );
		wp_enqueue_style( 'empty_admin.dynamic' );
	}
	
	public function enqueue_wysiwyg_styles() {
		add_editor_style( EMPTY_URL . 'base/css/wysiwyg.layout.css' );
		add_editor_style( EMPTY_URL . 'base/css/wysiwyg.dynamic.css' );
	}
	
	public function enqueue_fontawesome() {
		?>
		<script src="https://use.fontawesome.com/3c07618aca.js"></script>
		<?php
	}
	
	/**
	 * Enqueue scripts
	 *
	 * @since 2.5		Localize scripts
	 * @since 2.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_register_script( 'empty_admin.functions', EMPTY_URL . 'base/js/admin.functions.js', array( 'jquery' ), '', true );
		
		/**
		 * @since 2.5
		 */
		require( EMPTY_ROOT . 'languages/translation.javascript.php' );
		wp_localize_script( 'empty_admin.functions', 'translation', $translation );
		
		wp_enqueue_script( 'empty_admin.functions' ); 
	}
	
	/**
	 * Set WYSIWYG init options
	 *
	 * @since 2.1
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function wysiwyg_init( $settings ) {
		$settings['paste_as_text'] = true;
		$settings['paste_as_text'] = true;
		$settings['keep_styles'] = false;
		$settings['paste_remove_styles'] = true;
		$settings['paste_remove_spans'] = true;
		$settings['paste_strip_class_attributes'] = 'all';
		
		$settings['setup'] = 'function( editor ) {
			editor.on( "keyup change", function( event ) {
				WYSIWYG.output();
			} );
		}';
		
		return $settings;
	}
	
	/**
	 * Check for updates and do tasks
	 *
	 * @since 2.5.7		Empty_Setup moved here
	 * @since 2.5
	 *
	 * @return void
	 */
	public function version_check() {
		require_once( EMPTY_ROOT . 'base/php/class.empty_setup.php' );	
		new Empty_Setup();
		
		$previous_version = get_option( 'empty-version' );
		
		if ( $previous_version !== EMPTY_VERSION ) {
			Empty_Settings::file_save_listener();	
		}
		
		update_option( 'empty-version', EMPTY_VERSION );
	}
	
	/**
	 * Output JS variables
	 *
	 * @since 2.5.4
	 *
	 * @return void
	 */
	public function output_js() {
		echo '<script>window.BREAKPOINTS = ' . EMPTY_BREAKS . ';</script>';
	}
		
	/**
	 * Clean content
	 *
	 * @since 2.5.5
	 *
	 * @param array $file
	 *
	 * @return string
	 */
	public function clean_uploads( $file ) {
		$file['name'] = remove_accents( $file['name'] );
		 
		return $file;
	}
	
	/**
	 * Output all tooltips
	 *
	 * @since 2.5.5
	 *
	 * @return void
	 */
	public function output_tooltips() {
		echo empty_template( 'messages/html.tooltip_line-height.php', true );
	}
	
	/**
	 * Output load triggers
	 *
	 * @since 2.5.6
	 *
	 * @return void
	 */
	public function output_triggers() {
		$breakpoint_current = $this->breakpoints->current;
		$breakpoint_index = $this->breakpoints->get_index( $breakpoint_current );
		$slugs = '';
		
		for ( $i = $breakpoint_index; $i >= 0; $i-- ) {
			$break = $this->breakpoints->get( $i, 'INDEX' );
			$slugs = $break['slug'] . ( $i == $breakpoint_index ? '' : ' ' ) . $slugs;
		}
	
		require( empty_template( 'html.trigger_load.php' ) );
	}
	
	/**
	 * Output admin notices
	 *
	 * @since 2.5.7
	 *
	 * @return void
	 */
	public function output_notices() {
		$notices = get_option( 'empty_notices' );
		
		foreach ( $notices as $type => $notice ) {
			switch ( $type ) {
				case 'files':
					$count = count( $notice );
					$files = implode( ', ', $notice );
					$string = sprintf( _n( '%s file %s has been created successfully.', '%s files %s have been created successfully.', $count, 'empty' ), $count, '(' . $files . ')' );
					
					add_settings_error( '', 'empty_file-success', $string, 'updated' );
					
					unset( $notices['files'] );
					break;
			}
		}
		
		update_option( 'empty_notices', $notices );
	}
}

?>