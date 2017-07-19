<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Helper class to manage breakpoints
 *
 * @since 1.0.2		Remove function output_js
 * @since 1.0
 */
class Empty_Breakpoints {
	/**
	 * Setup
	 *
	 * @since 1.0
	 *
	 * @var array $breakpoints
	 * @var string $current
	 */
	private $breakpoints;
	public $current;
	
	/**
	 * Get Wordpress option and sort them
	 *
	 * @since 1.0.2		Remove admin_head action
	 * @since 1.0.1		Output breakpoints to javascript
	 * @since 1.0
	 *
	 * @return object
	 */
	public function __construct() {
		$breakpoints = get_option( 'empty_breakpoints' );
		$this->breakpoints = $breakpoints;
		$this->current = get_option( 'empty_breakpoints_current' );
		
		$this->sort();
	}
	
	
	
	/**
	 * Get breakpoint
	 *
	 * @since 1.0
	 *
	 * @param mixed $value
	 * @param string $by
	 *
	 * @return boolean|array
	 */
	public function get( $value, $by = 'SLUG' ) {
		switch ( $by ) {
			case 'SLUG':
				$index = $this->get_index( $value );
				if ( $index === false ) return false;
				
				return $this->breakpoints[ $index ];
				break;
			case 'INDEX':
			case 'ID':
				if ( empty( $this->breakpoints[ $value ] ) ) return false;
				
				return $this->breakpoints[ $value ];
				break;
			case 'WIDTH':
				foreach ( $this->breakpoints as $i => $breakpoint ) {
					if ( empty( $breakpoint['break']['value'] ) ) continue;
					if ( $breakpoint['break']['value'] < $value ) continue;
					
					return $this->breakpoints[ $i - 1 ]; 
				}
				break;
		}
	}	
	
	/**
	 * Return all breakpoints
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_all() {
		if ( empty( $this->breakpoints ) ) return false;
		
		return $this->breakpoints;
	}
	
	/**
	 * Get breakpoint index by slug
	 *
	 * @since 1.0
	 *
	 * @param string $value
	 * @param string $by
	 *
	 * @return boolean|int|string
	 */
	public function get_index( $value, $by = 'SLUG' ) {
		$index = array_search( $value, array_column( $this->breakpoints, strtolower( $by ) ) );
		
		return $index;
	}
	
	/**
	 * Get first
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_first() {
		return (object) $this->breakpoints[0];
	}
	
	/**
	 * Get last
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_last() {
		return (object) $this->breakpoints[ count( $this->breakpoints ) - 1 ];
	}
	
	/**
	 * Return previous breakpoint
	 *
	 * @since 1.0
	 *
	 * @param string $slug
	 *
	 * @return boolean|array
	 */
	public function get_previous( $slug ) {
		$index = $this->get_index( $slug );
		if ( $index === 0 || $index === false ) return false;
		
		$prev = $index - 1;
		if ( empty( $this->breakpoints[ $prev ] ) ) return false;
		
		return (object) $this->breakpoints[ $prev ];
	}
	
	/**
	 * Return next breakpoint
	 *
	 * @since 1.0
	 *
	 * @param string $slug
	 *
	 * @return boolean|array
	 */
	public function get_next( $slug ) {
		$index = $this->get_index( $slug );
		if ( $index === count( $this->breakpoints ) || $index === false ) return false;
		
		$next = $index + 1;
		if ( empty( $this->breakpoints[ $next ] ) ) return false;
		
		return (object) $this->breakpoints[ $next ];
	}
	
	
	
	/**
	 * Sort breakpoints by break value
	 *
	 * @since 1.0
	 *
	 * @param array $breakpoints
	 *
	 * @return boolean|void
	 */
	private function sort() {
		if ( empty( $this->breakpoints ) ) return false;
	
		usort( $this->breakpoints, function( $a, $b ) {
			return $a['break']['value'] - $b['break']['value'];
		} );
	}
}