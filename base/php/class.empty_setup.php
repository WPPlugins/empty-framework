<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Setup and fail safe in all situations
 *
 * @since 1.0
 */
class Empty_Setup {
	/**
	 * Check and set options
	 *
	 * @since 2.5.7		Set default values 0, 1; Set default post types
	 * @since 1.0.1		Set current breakpoint
	 * @since 1.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->check_breakpoints();
		$this->set_current_breakpoint();
		
		$this->set_default_values0();
		$this->set_default_values1();
		$this->set_post_types();
	}
	
	
	
	/**
	 * Check breakpoints
	 *
	 * @since 1.0
	 *
	 * @return boolean
	 */
	private function check_breakpoints() {
		$breakpoints = get_option( 'empty_breakpoints' );
		if ( ! empty( $breakpoints ) ) return true;
		
		$breakpoints = array(
			array(
				'slug'		=> 'sm',
				'title'		=> 'Small',
				'break'		=> array(
					'value'		=> 0,
					'unit'		=> 'px'
				)
			),
			array(
				'slug'		=> 'md',
				'title'		=> 'Medium',
				'break'		=> array(
					'value'		=> 768,
					'unit'		=> 'px'
				)
			),
			array(
				'slug'		=> 'lg',
				'title'		=> 'Large',
				'break'		=> array(
					'value'		=> 1024,
					'unit'		=> 'px'
				)
			)
		);
		
		return update_option( 'empty_breakpoints', $breakpoints );
	}
	
	/**
	 * Set current breakpoint
	 *
	 * @since 1.0.1
	 *
	 * @return boolean
	 */
	private function set_current_breakpoint() {
		$current_breakpoint = get_option( 'empty_breakpoints_current' );
		$helper = new Empty_Breakpoints();
		if ( ! empty( $current_breakpoint ) && $helper->get_index( $current_breakpoint ) !== false ) return true;
		
		$first = $helper->get_first();
		return update_option( 'empty_breakpoints_current', $first->slug );
	}
	
	/**
	 * Set default values for smallest breakpoint 0
	 *
	 * @since 2.5.7
	 *
	 * @return boolean
	 */
	private function set_default_values0() {
		$option = get_option( 'empty_css' );
		if ( ! empty( $option ) ) return true;
		
		$helper = new Empty_Breakpoints();
		$first = $helper->get_first();
		$slug = $first->slug;
		
		$defaults = array(
			'*' => array(
				'line-height' => array(
					$slug => array(
						'value' => 18,
						'unit'	=> 'px'
					)
				),
				'element_spacing_horizontal' => array(
					$slug => array(
						'value' => 20,
						'unit' => 'px'
					)
				),
				'element_spacing_vertical' => array(
					$slug => array(
						'value' => 1
					)
				)
			),
			'.wrap' => array(
				'padding-top' => array(
					$slug => array(
						'value' => 1
					)
				),
				'padding-bottom' => array(
					$slug => array(
						'value' => 1
					)
				),
				'padding-left' => array(
					$slug => array(
						'value' => 10,
						'unit'	=> 'px'
					)
				),
				'padding-right' => array(
					$slug => array(
						'value' => 10,
						'unit'	=> 'px'
					)
				)
			),
			'.row + .row' => array(
				'margin-top' => array(
					$slug => array(
						'value' => 2
					)
				)
			)
		);
		
		return update_option( 'empty_css', $defaults );
	}
	
	/**
	 * Set default values for smallest breakpoint 1
	 *
	 * @since 2.5.7
	 *
	 * @return boolean
	 */
	private function set_default_values1() {
		$option = get_option( 'empty_typography' );
		if ( ! empty( $option ) ) return true;
		
		$helper = new Empty_Breakpoints();
		$first = $helper->get_first();
		$slug = $first->slug;
		
		$defaults = array(
			'body' => array(
				'font-family' => array(
					$slug => 'Arial, sans-serif'
				),
				'line-height' => array(
					$slug => array( 
						'value' => 1
					)
				),
				'font-size' => array(
					$slug => array(
						'value' => 16,
						'unit'	=> 'px'
					)
				),
				'font-weight' => array(
					$slug => 400
				)
			)	
		);
		
		return update_option( 'empty_typography', $defaults );
	}
	
	/**
	 * Set defaults post types (page)
	 *
	 * @since 2.5.7
	 *
	 * @return boolean
	 */
	private function set_post_types() {
//		$option = get_option( 'empty_options' );
//		if ( ! empty( $option['post_types'] ) ) return true;
//		
//		$option['post_types']['page'] = 'page';
//		
//		return update_option( 'empty_options', $option );
	}
}

?>