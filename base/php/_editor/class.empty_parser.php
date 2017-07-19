<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
require_once( EMPTY_ROOT . 'inc/inc.simplehtmldom.php' );
require_once( EMPTY_ROOT . 'base/php/_editor/class.empty_element.php' );



/**
 * Visual editor content parser
 *
 * @since 1.0
 */
class Empty_Parser {
	/**
	 * Setup
	 *
	 * @since 1.1				$post_id, $html
	 * @since 1.0
	 *
	 * @var array $elements		Array containing element objects
	 * @var int $post_id
	 * @var object $html			SimpleHTMLDOM object
	 */
	public static $elements;
	private $post_id, $html;
	
	/**
	 * Initialize parser and register elements
	 *
	 * @since 1.5.1			Buffer element templates
	 * @since 1.5			Build custom elements
	 * @since 1.1			Param $post_id
	 * @since 1.0
	 *
	 * @param int $post_id	
	 *
	 * @return object
	 */
	public function __construct( $post_id = null ) {
		$this->post_id = empty( $post_id ) ? get_the_ID() : $post_id;
		
		/**
		 * @since 1.5.1		Replace file_get_contents with require and buffer (to guarantee output of PHP functions)
		 */
		ob_start();
		require( empty_template( '_editor/elements/html.element_row.php' ) );
		$markup_row = ob_get_contents();
		ob_end_clean();
		
		ob_start();
		require( empty_template( '_editor/elements/html.element_text.php' ) );
		$markup_text = ob_get_contents();
		ob_end_clean();
		
		ob_start();
		require( empty_template( '_editor/elements/html.element_media.php' ) );
		$markup_media = ob_get_contents();
		ob_end_clean();
		
		self::$elements = array(
			'row'	=> new Empty_Element(
				'row',
				__( 'Row', 'empty' ),
				$markup_row
			),
			'text'	=> new Empty_Element(
				'text',
				__( 'Text', 'empty' ),
				$markup_text,
				array(
					array(
						'WYSIWYG',
						'%CONTENT%'
					)
				)
			),
			'media'	=> new Empty_Element(
				'media',
				__( 'Media', 'empty' ),
				$markup_media,
				array(
					array(
						'MEDIA',
						'%CONTENT%'
					)
				)
			)
		);
		
		/**
		 * @since 1.5
		 */
		$elements = get_posts( array(
			'post_type'			=> 'empty-element',
			'orderby'			=> 'menu_order',
			'posts_per_page'	=> -1
		) );
		
		foreach ( $elements as $element ) {
			$markup = get_post_meta( $element->ID, 'empty_markup', true );
			$slug = get_post_meta( $element->ID, 'empty_slug', true );
			if ( empty( $markup ) || empty( $slug ) ) continue;
			$parts = get_post_meta( $element->ID, 'empty_parts', true );
			$parts_arr = array();
			
			foreach ( $parts as $part ) {
				if ( empty( $part['replace'] ) || empty( $part['type'] ) ) continue;
				
				$parts_arr[] = array(
					$part['type'],
					$part['replace']
				);
			}
			
			self::$elements[ $slug ] = new Empty_Element( 
				$slug,
				$element->post_title,
				$markup,
				$parts_arr
			);
		}
	}
	
	
	
	/**
	 * Return editable content
	 *
	 * @since 1.3.1				Submit originally found part to the parser function
	 * @since 1.3				Update parts to current version / markup
	 * @since 1.1
	 *
	 * @return boolean|string
	 */
	public function editable() {
		if ( empty( $this->post_id ) ) return false;
		
		$editable_content = get_post_meta( $this->post_id, 'empty-editable_content', true );
		if ( ! empty( $editable_content ) ) {
			$this->html = str_get_html( $editable_content );
		
			/**
			 * @since 1.3	Update parts
			 */
			foreach ( $this->html->find( '[data-empty-element]' ) as $element ) {
				$slug = $element->{'data-empty-element'};
				if ( empty( self::$elements[ $slug ] ) ) continue;
				
				$contents = array();
				foreach ( $element->find( '[data-empty-element_part]' ) as $part ) {
					$contents[ $part->{'data-empty-element_part'} ] = (object) array(
						'needle'		=> $part->{'data-empty-element_part'},
						'replace'	=> $part->innertext,
						'original'	=> $part
					);
				}
				
				$element->outertext = self::$elements[ $slug ]->editable( $contents, '', $element->class );
			}
			
			$markup = $this->html->save();
			$this->html->clear();
			unset( $this->html );
			
			/**
			 * @since 1.1	Return
			 */
			return $this->clean( $markup );
		}
		
		$content = '';
		$original_content = apply_filters( 'the_content', get_post_field( 'post_content', $this->post_id ) );
		if ( ! empty( $original_content ) ) {
			$content = self::$elements['text']->editable( array( '%CONTENT%' => (object) array( 'replace' => $original_content ) ) );
		}
		
		/**
		 * @since 1.1	Return
		 */
		return $this->clean( self::$elements['row']->editable( '', $content ) );
	}
	
	/**
	 * Compile editable content
	 *
	 * @since 1.1
	 *
	 * @param string $content	Fallback to post editable_content meta value
	 *
	 * @return boolean|string
	 */
	public function compile( $content = null ) {
		if ( empty( $this->post_id ) && empty( $content ) ) return false;
		if ( empty( $content ) ) $content = $this->editable();
		
		$this->html = str_get_html( $content );
		
		foreach ( $this->html->find( '[data-empty-element]' ) as $element ) {
			$slug = $element->{'data-empty-element'};
			$element->{'data-empty-element'} = null;
			
			$contents = array();
			foreach ( $element->find( '[data-empty-element_part]' ) as $part ) {
				$contents[] = (object) array(
					'needle'		=> $part->{'data-empty-element_part'},
					'replace'	=> $part->innertext	
				);
			}
			
			$element->innertext = self::$elements[ $slug ]->compile( $contents );
		}
		
		$markup = $this->html->save();
		$this->html->clear();
		unset( $this->html );
		
		return $this->clean( $markup );
	}
	
	
	
	/**
	 * Clean markup
	 *
	 * Removes spaces between '>' and '<' characters
	 * Remove .ui-sortable & .ui-sortable-handle classes
	 * Remove .ui-resizable-handle elements
	 *
	 * @since 1.2.1				Remove jQuery UI resizable helper elements
	 * @since 1.2				Remove jQuery UI helper classes
	 * @since 1.1.1
	 *
	 * @param string $markup
	 *
	 * @return string
	 */
	private function clean( $markup ) {
		$html = str_get_html( $markup );
		$kill_classes = array( 'ui-sortable-handle', 'ui-sortable', 'ui-resizable' );
		$kill_elements = array( '.ui-resizable-handle' );
		
		/**
		 * Remove target elements
		 *
		 * @since 1.2.1
		 */
		foreach ( $kill_elements as $selector ) {
			foreach ( $html->find( $selector ) as $element ) {
				$element->outertext = '';
			}
		}
		
		/**
		 * Remove target classes
		 *
		 * @since 1.2
		 */
		foreach ( $kill_classes as $class ) {
			foreach ( $html->find( '.' . $class ) as $element ) {
				$classes = trim( str_replace( $class, '', $element->class ) );
				$element->class = $classes;
			}
		}
		
		/**
		 * Remove style attribute
		 *
		 * @since 1.2
		 */
		foreach ( $html->find( 'div[style]' ) as $element ) {
			$element->style = null;
		}
		
		$markup = $html->save();
		$html->clear();
		unset( $html );
		$markup_clean = preg_replace( '/>\s+</', '><', $markup );
		
		return $markup_clean;
	}
	
	
	
	/**
	 * Build oEmbeds
	 *
	 * @since 1.4
	 *
	 * @param string $url
	 * @param boolean $ajax
	 *
	 * @return string|void
	 */
	public static function oembed( $url, $ajax = false ) {
		$ajax = empty( $_POST['ajax'] ) ? $ajax : $_POST['ajax'];
		$url = $ajax ? stripslashes( $_POST['url'] ) : $url;
		
		$embed_markup = wp_oembed_get( $url );
		
		if ( $ajax ) {
			echo json_encode( $embed_markup );
			wp_die();
		}
		return $embed_markup;
	}
}

?>