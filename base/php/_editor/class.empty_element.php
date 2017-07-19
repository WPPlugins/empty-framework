<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Editor element
 *
 * @since 1.0
 */
class Empty_Element {
	/**
	 * Setup
	 *
	 * @since 1.0
	 *
	 * @var string $slug
	 * @var string $title
	 * @var string $raw		The elements raw markup	
	 * @var string $markup	The elements markup which will be outputed	
	 * @var array $parts	Array containing objects with the type, needle, replacement and actions
	 */
	public $slug, $title;
	private $raw, $markup, $parts;
	
	/**
	 * Initialize
	 *
	 * @since 1.0
	 *
	 * @param string $title
	 * @param string $markup
	 * @param array $parts
	 *
	 * @return object
	 */
	public function __construct( $slug, $title, $markup, $parts = null ) {
		if ( empty( $slug ) || empty( $title ) || empty( $markup ) ) return false;
		
		$this->slug = $slug;
		$this->title = $title;
		$this->raw = $markup;
		
		if ( gettype( $parts ) !== 'array' ) return false;
		
		$parts_clean = array_values( $parts );
		foreach ( $parts_clean as $i => $part ) {
			$part_clean = array_values( $part );
			$type = $part[0];
			$needle = $part[1];
			
			switch ( $type ) {
				case 'TEXT':
					$actions = array(
						'contenteditable'		=> 'true',
						'data-empty_do'			=> 'editor:edit:this',
						'data-empty_trigger'	=> 'double'
					);
					break;
				case 'WYSIWYG':
					$actions = array(
						'data-empty_do'			=> 'editor:edit:this',
						'data-empty_trigger'	=> 'double'
					);
					break;
				case 'MEDIA':
					$actions = array(
						'data-empty_do'			=> 'editor:edit:this',
						'data-empty_trigger'	=> 'double',
						'data-media'			=> null
					);
					break;
			}
			
			$parts_clean[ $i ] = (object) array(
				'type' 		=> $type,
				'needle' 	=> $needle,
				'actions'	=> $actions
			);
		}
		$this->parts = $parts_clean;
	}
	
	
	
	/**
	 * Build editable element
	 *
	 * This function only works when the element is initialized by the Empty_Parser class
	 * otherwise the build_editable function will always return FALSE
	 *
	 * @since 1.2						$parts_contents is now array containing objects with "replace" & "original" values
	 * @since 1.1.1						Pass $classes to element wrap
	 * @since 1.1						Param $parts_contents to prefill parts, Param $content to prefill element
	 * @since 1.0
	 *
	 * @param array $parts_contents		Array containing objects with with "replace" & "original" values keyed by "needle"
	 * @param string $content			Content to put into the innermost element(s)
	 *
	 * @return boolean|string
	 */
	public function editable( $parts_contents = array(), $content = null, $classes = null ) {
		if ( empty( $this->raw ) || ! function_exists( 'str_get_html' ) ) return false;
		$breakpoints = new Empty_Breakpoints();
		$first = $breakpoints->get_first();
		
		$this->markup = $this->raw;
		if ( $this->parts ) {
			foreach ( $this->parts as $i => $part ) {
				/**
				 * @since 1.1	Set part content & original
				 */
				$part_content = empty( $parts_contents[ $part->needle ] ) ? '' : $parts_contents[ $part->needle ]->replace;
				$part_original = empty( $parts_contents[ $part->needle ]->original ) ? '' : $parts_contents[ $part->needle ]->original; 
				
				/**
				 * @since 1.0	Wrap part
				 */
				$part_markup = $this->wrap( $part_content, 'part', array( $part->needle, $part->type ) );
				$part_html = str_get_html( $part_markup );
				
				foreach ( $part_html->find( '[data-empty-element_part]' ) as $part_node ) {
					/**
					 * @since 1.0	Set part attributes
					 */
					foreach ( $part->actions as $attribute => $value ) {
						/**
						 * @since 1.2	Compare attribute null values to originally submitted elements values and use those
						 */
						if ( $value === null ) {
							$value = empty( $part_original->{ $attribute } ) ? null : $parts_contents[ $part->needle ]->original->{ $attribute };
						}
						
						$part_node->{ $attribute } = $value;
					}
				} 
				
				$part_markup = $part_html->save();
				$part_html->clear();
				unset( $part_html );
				
				$this->markup = str_replace( $part->needle, $part_markup, $this->markup );
			}
		}
		
		/**
		 * @since 1.1.1		Build element classes
		 */
		if ( empty( $classes ) ) $classes = 'col-' . $first->slug . '-12';
		
		/**
		 * @since 1.0	Wrap element
		 */
		if ( $this->slug !== 'row' ) $this->markup = $this->wrap( $this->markup, 'element', array( $classes, $this->slug ) );
		
		/**
		 * @since 1.0	Build SimpleHTMLDOM object
		 */
		$html = str_get_html( $this->markup );
		
		/**
		 * @since 1.1	Set content
		 */
		if ( $content ) {
			foreach ( $this->innermost( $html ) as $node ) {
				$node->innertext = $content;
			}
		}
		
		/**
		 * @since 1.0	Save HTML as markup
		 */
		$this->markup = $html->save();
		$html->clear();
		unset( $html );
		
		return apply_filters( 'empty-element_' . $this->slug . '_editable', $this->markup );
	}
	
	/**
	 * Compile element
	 *
	 * @since 1.0
	 *
	 * @param array $content	Array containing objects with "needle" & "replace" values
	 * @param boolean $wrap	
	 *
	 * @return boolean|string
	 */
	public function compile( $contents, $wrap = false ) {
		if ( empty( $this->raw ) || empty( $contents ) ) return false;
		
		$this->markup = $this->raw;
		foreach ( $contents as $content ) {
			if ( empty( $content->needle ) ) continue;
			
			$this->markup = str_replace( $content->needle, $content->replace, $this->markup );
		}
		
		return apply_filters( 'empty-element_' . $this->slug, $this->markup );
	}
	
	
	
	/**
	 * Wrap elements
	 *
	 * @since 1.0
	 *
	 * @param string $wrap	What to wrap
	 * @param string $slug	Which template to use
	 * @param array $args	Addtional arguments
	 *
	 * @return string
	 */
	private function wrap( $wrap, $slug, $args = array() ) {
		ob_start();
		require( empty_template( '_editor/elements/html.' . $slug . '_before.php' ) );
		$before = ob_get_contents();
		ob_end_clean();
		
		ob_start();
		require( empty_template( '_editor/elements/html.' . $slug . '_after.php' ) );
		$after = ob_get_contents();
		ob_end_clean();
		
		return $before . $wrap . $after;
	}
	
	/**
	 * Find part by
	 *
	 * Always returns the index of the first match
	 * Searching for keys like "type" will not match all parts if there are multiple parts of the same type
	 *
	 * @since 1.0
	 *
	 * @param string $value
	 * @param string $by
	 *
	 * @return boolean|int
	 */
	private function find_part_by( $value, $by = 'needle' ) {
		if ( empty( $this->parts ) ) return false;
		
		foreach ( $this->parts as $index => $part ) {
			if ( $part->{ $by } == $value ) return $index;	
		}
	}
	
	/**
	 * Find innermost element
	 *
	 * @since 1.1
	 *
	 * @param object $html	SimpleHTMLDOM object
	 *
	 * @return object
	 */
	private function innermost( $html ) {
		$nodes = array();
		
		if ( count( $html->find( '*' ) ) === 0 ) $nodes[] = $html;
		foreach( $html->find( '*' ) as $node ) {
			if ( count( $node->children() ) === 0 ) {
				$nodes[] = $node;
			} else {
				$nodes = array_merge( $nodes, $this->innermost( $node ) );
			}
		}
		
		return $nodes;
	}
}

?>