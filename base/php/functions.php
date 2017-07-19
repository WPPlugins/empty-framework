<?php 
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Retrieve empty template
 *
 * @since 1.2				Boolean to return file contents as string
 * @since 1.0
 *
 * @param boolean $string
 * @param string $filename
 *
 * @return boolean|string
 */
function empty_template( $filename, $string = false ) {
	if ( empty( $filename ) ) return false;
	$file = EMPTY_ROOT . 'base/tmp/' . $filename;
	if ( ! file_exists( $file ) ) return false;
	
	if ( $string ) {
		ob_start();
		require( $file );
		$file = ob_get_contents();
		ob_end_clean();
	}
	
	return $file;
}



/**
 * Recompile empty editor content
 *
 * Not recommended for outputting the empty editor content
 * Simply use native functions "the_content" and "get_the_content"
 * Only use when elements changed and recompilation of content is necessary
 *
 * @since 1.1
 *
 * @param int $post_id
 *
 * @return boolean|string
 */
function get_the_econtent( $post_id = null ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : $post_id;
	if ( empty( $post_id ) ) return false;
	
	$parser = new Empty_Parser( $post_id );
	return $parser->compile();
}

function the_econtent( $post_id = null ) {
	echo get_the_econtent( $post_id );
}

?>