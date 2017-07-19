<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Helper class to sort and output CSS values
 *
 * @since 1.0
 */
class Empty_Css {
	/**
	 * Setup
	 *
	 * @since 1.0
	 *
	 * @var array $raw			Values from database
	 * @var array $selectors	All CSS properties keyed by selector
	 */
	private $raw;
	public $selectors;
	 
	/**
	 * Generate CSS values
	 *
	 * @since 1.0
	 *
	 * @param string $breakpoint_slug	Breakpoint slug for which to return CSS options
	 *
	 * @return object
	 */
	public function __construct( $breakpoint_slug ) {
	 	if ( empty( $breakpoint_slug ) ) return false;
	 	$css = get_option( 'empty_css' );
	 	$typography = get_option( 'empty_typography' );
	 	$this->raw = array_merge( empty( $css ) ? array() : $css, empty( $typography ) ? array() : $typography );
	 	
	 	foreach ( $this->raw as $selector => $properties ) {
	 		foreach ( $properties as $property => $value ) {
	 			$value = $this->build_value( $value, $breakpoint_slug );
	 			if ( empty( $value ) ) continue;
	 			
	 			if ( ! empty( $properties['selector'] ) ) $selector = $properties['selector'];
	 			$selectors = explode( ',', $selector );
	 			
	 			foreach ( $selectors as $selector_single ) {
	 				$this->selectors[ trim( $selector_single ) ][ $property ] = $value;
	 			}
	 		}
	 	}
	}
	 
	 
	 
	/**
	 * Build complete values for CSS output
	 *
	 * @since 1.0
	 *
	 * @param array $value				Value including breakpoint values
	 * @param string $breakpoint_slug	Breakpoint slug to get value to
	 *
	 * @return array
	 */
	private function build_value( $value, $breakpoint_slug ) {
	 	if ( empty( $value[ $breakpoint_slug ] ) ) {
	 		$helper = new Empty_Breakpoints();
	 		$previous = $helper->get_previous( $breakpoint_slug );
	 		if ( ! $previous ) return false;
	 		
	 		return $this->build_value( $value, $previous->slug );
	 	}
	 	
	 	if ( gettype( $value[ $breakpoint_slug ] ) !== 'array' ) return $value[ $breakpoint_slug ];
	 	
	 	$part_value = $this->get_value_part( $value, $breakpoint_slug );
	 	$part_unit = $this->get_value_part( $value, $breakpoint_slug, 'UNIT' );
	 	
	 	
	 	
	 	/* Look for connection */
	 	if ( ! empty( $value['connection'] ) ) {
	 		$path = explode( '[', $value['connection'] );
	 		if ( $path[0] === 'empty_css' ) {
		 		$connect_value = $this->raw;
		 		for ( $p = 1; $p < count( $path ); $p++ ) {
		 			$key = substr( $path[ $p ], 0, -1 );
		 			$connect_value = empty( $connect_value[ $key ] ) ? '' : $connect_value[ $key ];
		 		}
		 		
		 		$part_connect_value = $this->get_value_part( $connect_value, $breakpoint_slug );
		 		
		 		$part_value = $part_value * $part_connect_value;
		 		$part_unit = $this->get_value_part( $connect_value, $breakpoint_slug, 'UNIT' );
		 	}
	 	}
	 	
	 	
	 	
	 	if ( empty( $part_value ) || empty( $part_unit ) ) return false;
	 	
	 	return $part_value . $part_unit;
	}
	
	/**
	 * Retrieve value part (value/unit)
	 *
	 * @since 1.0
	 *
	 * @param array $value				Value including breakpoint values
	 * @param string $breakpoint_slug	Breakpoint slug to get value to
	 * @param string $get				
	 */
	private function get_value_part( $value, $breakpoint_slug, $get = 'VALUE' ) {
		if ( empty( $value[ $breakpoint_slug ][ strtolower( $get ) ] ) ) {
			$helper = new Empty_Breakpoints();
			$previous = $helper->get_previous( $breakpoint_slug );
			if ( ! $previous ) return false;
			
			return $this->get_value_part( $value, $previous->slug, $get );
		}
		
		return $value[ $breakpoint_slug ][ strtolower( $get ) ];
	}
	
	
	
	/**
	 * Retrieve numeric value
	 *
	 * @since 1.0
	 *
	 * @param string $value
	 *
	 * @return boolean|float
	 */
	public function get_value( $value ) {
		if ( empty( $value ) ) return false;
		
		return preg_replace( '/[^0-9^.]/', '', $value );
	}
	
	/**
	 * Retrieve value unit
	 *
	 * @since 1.0
	 *
	 * @param string $value
	 *
	 * @return boolean|float
	 */
	public function get_unit( $value ) {
		if ( empty( $value ) ) return false;
		
		return preg_replace( '/[0-9.]/', '', $value );
	}
	
	
	
	/**
	 * Retrieve raw data for selector
	 *
	 * @since 1.1
	 *
	 * @param string $selector
	 *
	 * @return boolean|array
	 */
	public function get_raw( $selector ) {
		if ( empty( $this->raw[ $selector ] ) ) return false;
		
		return $this->raw[ $selector ];
	}
}