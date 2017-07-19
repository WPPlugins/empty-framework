<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */



/**
 * Helper class to build files inside WordPress environment
 *
 * @since 1.0
 */
class Empty_File {
	/**
	 * Setup
	 *
	 * @since 1.0
	 *
	 * @var string $path
	 * @var string $file
	 * @var string $template
	 * @var boolean $credentials
	 */
	private $path, $file, $template, $credentials;
	
	/**
	 * Init
	 *
	 * @since 1.0
	 *
	 * @param string $path
	 * @param string $filename
	 * @param string $template
	 *
	 * @return object
	 */
	public function __construct( $path, $file, $template ) {
		if ( empty( $path ) || empty( $file) || empty( $template ) || ! file_exists( $template ) ) return false;
		
		$this->path = $path;
		$this->file = $file;
		$this->template = $template;
		
		$this->credentials = request_filesystem_credentials( '', '', false, $this->path );
		if ( $this->credentials === false ) {
			add_settings_error( 'empty-file_save_trigger', 'empty_file-credentials', __( 'Empty does not have write access to create required CSS files.', 'empty' ), 'error' );
		}
	}
	
	
	
	/**
	 * Function to build file and output error / success messages
	 *
	 * @since 2.5.7		Push success note to global variable
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function build() {
		if ( empty( $this->path ) || empty( $this->file ) || empty( $this->template ) ) return false;
		global $wp_filesystem;
		if ( ! WP_Filesystem( $this->credentials ) ) {
			add_settings_error( 'empty-file_save_trigger', 'empty_file-credentials', __( 'Empty does not have write access to create required CSS files.', 'empty' ), 'error' );
			return false;
		}
		
		ob_start();
		require( $this->template );
		$contents = ob_get_contents();
		ob_end_clean();
		
		$file = $wp_filesystem->put_contents( trailingslashit( $wp_filesystem->find_folder( $this->path ) ) . $this->file, $contents );
		if ( ! $file ) {
			add_settings_error( 'empty-file_save_trigger', 'empty_file-credentials', __( 'Error while writing file:', 'empty' ) . ' ' . $this->file, 'error' );
			return false;
		}
		
		/**
		 * @since 2.5.7
		 */
		$notices = get_option( 'empty_notices' );
		if ( empty( $notices ) || gettype( $notices ) !== 'array' ) {
			$notices = array(
				'files' => array(
					$this->file
				)
			);
		} else {
			$notices['files'][] = $this->file;
		}
		update_option( 'empty_notices', $notices );
		
		//add_settings_error( '', 'empty_file-success', $this->file . ' ' . __( 'created successfully.', 'empty' ), 'updated' );
		return true;
	}
}

?>