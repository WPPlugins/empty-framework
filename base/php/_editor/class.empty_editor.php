<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
header( 'X-XSS-Protection: 0' );



/**
 * Visual editor class
 *
 * @since 1.0
 */
class Empty_Editor {
	/**
	 * Setup
	 *
	 * @since 1.0
	 *
	 * @var array $options	Empty options from the options page
	 * @var object $screen	WordPress screen object
	 */
	private $options, $screen;

	/**
	 * Initialize the editor contruct
	 *
	 * @since 1.2.1				Action to enable oEmbed AJAX
	 * @since 1.0
	 *
	 * @return object
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, '__construct_editor' ) );
		
		/**
		 * @since 1.2.1
		 */
		add_action( 'wp_ajax_oembed', array( 'Empty_Parser', 'oembed' ) );
	}
	
	/**
	 * Add WordPress hooks and actions
	 *
	 * @since 1.2.2				Output messages
	 * @since 1.2				Default editor
	 * @since 1.1.1				Save editable_content / Parser init
	 * @since 1.0
	 *
	 * @return boolean|void
	 */
	public function __construct_editor() {
		$this->options = get_option( 'empty_options' );
		if ( empty( $this->options['post_types'] ) ) return false;
		$this->screen = get_current_screen();
		if ( ! in_array( $this->screen->post_type, $this->options['post_types'] ) || $this->screen->base !== 'post' ) return false;
		
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		foreach( $this->options[ 'post_types' ] as $post_type => $value ) {
			add_filter( 'get_user_option_screen_layout_' . $post_type, array( $this, 'layout' ) );
			remove_post_type_support( $post_type, 'editor' );
			remove_post_type_support( $post_type, 'custom-fields' );
		}
		add_filter( 'screen_layout_columns', array( $this, 'layout_options' ) );
		
		add_action( 'edit_form_after_title', array( $this, 'output_bars' ) );
		add_action( 'edit_form_after_title', array( $this, 'output_editor' ) );
		add_action( 'edit_form_after_title', array( $this, 'output_helpers' ) );
		
		/**
		 * @since 1.1.1
		 */
		add_filter( 'wp_insert_post_data', array( $this, 'compile_and_save' ), 10, 2 );
		
		/**
		 * @since 1.2.2		Output messages
		 * @since 1.2
		 */
		add_filter( 'wp_default_editor', array( $this, 'default_editor' ) );
		add_action( 'edit_form_after_title', array( 'Empty_Settings', 'messages' ) );
		
	}
	
	
	
	/**
	 * Enqueue editor styles
	 *
	 * @since 1.1		New naming convention
	 * @since 1.0
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_register_style( 'empty_editor',  EMPTY_URL . 'base/css/_editor/editor.css' );
		wp_enqueue_style( 'empty_editor' );
		wp_register_style( 'empty_editor.dynamic',  EMPTY_URL . 'base/css/_editor/editor.dynamic.css' );
		wp_enqueue_style( 'empty_editor.dynamic' );
	}
	
	/**
	 * Enqueue editor sscripts	
	 *
	 * @since 1.2.3		Localize scripts
	 * @since 1.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		global $post;
		
		wp_enqueue_media( array( 'post' => $post->ID ) );
		wp_register_script( 'empty_editor',  EMPTY_URL . 'base/js/_editor/prototype.empty_editor.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-resizable' ), '', true );
		
		/**
		 * @since 1.2.3
		 */
		require( EMPTY_ROOT . 'languages/translation.javascript.php' );
		wp_localize_script( 'empty_editor', 'translation', $translation );
		
		wp_register_script( 'empty_wysiwyg',  EMPTY_URL . 'base/js/_editor/prototype.empty_wysiwyg.js', array( 'jquery' ), '', true );
		wp_register_script( 'empty_media',  EMPTY_URL . 'base/js/_editor/prototype.empty_media.js', array( 'jquery' ), '', true );
		wp_register_script( 'empty_editor.functions',  EMPTY_URL . 'base/js/_editor/editor.functions.js', array( 'jquery', 'empty_editor', 'empty_wysiwyg', 'empty_media' ), '', true );
		
		wp_enqueue_script( 'empty_editor.functions' );
	}
	
	
	
	/**
	 * Alter the editor admin layout
	 *
	 * @since 1.0
	 *
	 * @return int
	 */
	public function layout() {
		return 1;
	}
	
	/** 
	 * Alter the editor admin layout options
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function layout_options( $columns ) {
		foreach( $this->options['post_types'] as $post_type => $value ) {
			$columns[ $post_type ] = 1;
		}
		
		return $columns;
	}
	
	/**
	 * Set the default editor
	 *
	 * @since 1.2
	 *
	 * @return string
	 */
	public function default_editor() {
		return 'wysiwyg';
	}
	
	
	
	/**
	 * Output editor bars
	 *
	 * @since 1.1.2		Actions bar
	 * @since 1.0
	 *
	 * @return void
	 */
	public function output_bars() {
		global $post;

		switch ( $post->post_status ) {
			case 'publish':
				$wording = __( 'Update', 'empty' );
				$name = 'save';
				break;
			default:
				$wording = __( 'Publish', 'empty' );
				$name = 'publish';
				break;
		}
		
		require( empty_template( '_editor/html.bar_fixed.php' ) );
		require( empty_template( '_editor/html.bar_above.php' ) );
		require( empty_template( '_editor/html.bar_actions.php' ) );
	}
	
	/**
	 * Output the editor itself
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function output_editor() {
		$parser = new Empty_Parser();
		$content = $parser->editable();
	
		require( empty_template( '_editor/html.editor.php' ) );
	}
	
	/**
	 * Output editor helpers
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function output_helpers() {
		global $post;
		$breakpoints = new Empty_Breakpoints();
		$first = $breakpoints->get_first();
	
		require( empty_template( '_editor/html.helpers.php' ) );
	}
	
	
	
	/**
	 * Compile and save editable_content and content
	 *
	 * @since 1.1.1
	 *
	 * @param array $data
	 * @param array $postarr
	 *
	 * @return array
	 */
	public function compile_and_save( $data, $postarr ) {
		if ( ! in_array( $data['post_type'], $this->options['post_types'] ) ) return $data;
		$parser = new Empty_Parser();
		
		$editable_content = stripslashes( $data['post_content'] );
		if ( update_post_meta( $postarr['ID'], 'empty-editable_content', $editable_content ) ) {
			
		}
		$content = empty( $editable_content ) ? '' : $parser->compile( $editable_content );

		$data['post_content'] = $content;
		
		return $data;
	}
}



new Empty_Editor();

?>