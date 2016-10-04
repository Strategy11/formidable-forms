<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCreateFile {

	public $folder_name = '';
	public $file_name = '';
	public $error_message = '';
	public $uploads = array();
	public $chmod_dir = 0755;
	public $chmod_file = 0644;

	public function __construct( $atts ) {
		$this->folder_name = $atts['folder_name'];
		$this->file_name = $atts['file_name'];
		$this->error_message = isset( $atts['error_message'] ) ? $atts['error_message'] : '';
		$this->uploads = wp_upload_dir();
		$this->chmod_dir = defined('FS_CHMOD_DIR') ? FS_CHMOD_DIR : ( fileperms( ABSPATH ) & 0777 | 0755 );
		$this->chmod_file = defined('FS_CHMOD_FILE') ? FS_CHMOD_FILE : ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 );
	}

	public function create_file( $file_content ) {
		$creds = $this->get_creds();

		if ( empty( $creds ) || ! WP_Filesystem( $creds ) ) {
			// initialize the API - any problems and we exit
			$this->show_error_message();
		} else {
			$dirs_exist = true;

			// Create the directories if need be
			$this->create_directories( $dirs_exist );

			// only write the file if the folders exist
			if ( $dirs_exist ) {
				global $wp_filesystem;

				$new_file = $this->uploads['basedir'] . '/' . $this->folder_name . '/' . $this->file_name;
				$wp_filesystem->put_contents( $new_file, $file_content, $this->chmod_file );
			}
		}
	}

	private function create_directories( &$dirs_exist ) {
		global $wp_filesystem;

		$needed_dirs = $this->get_needed_dirs();
		foreach ( $needed_dirs as $_dir ) {
			// Only check to see if the Dir exists upon creation failure. Less I/O this way.
			if ( $wp_filesystem->mkdir( $_dir, $this->chmod_dir ) || $wp_filesystem->is_dir( $_dir ) ) {
				$index_path = $_dir . '/index.php';
				$wp_filesystem->put_contents( $index_path, "<?php\n// Silence is golden.\n?>", $this->chmod_file );
			} else {
				$dirs_exist = false;
			}
		}
	}

	private function get_needed_dirs() {
		$dir_names = explode( '/', $this->folder_name );
		$needed_dirs = array();

		$next_dir = '';
		foreach ( $dir_names as $dir ) {
			$next_dir .= '/' . $dir;
			$needed_dirs[] = $this->uploads['basedir'] . $next_dir;
		}

		return $needed_dirs;
	}

	private function get_creds() {
		$access_type = get_filesystem_method();
		if ( $access_type === 'direct' ) {
			$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );
		} else {
			$creds = $this->get_ftp_creds( $access_type );
		}
		return $creds;
	}

	private function get_ftp_creds( $type ) {
		$credentials = get_option( 'ftp_credentials', array( 'hostname' => '', 'username' => '' ) );

		$credentials['hostname'] = defined('FTP_HOST') ? FTP_HOST : $credentials['hostname'];
		$credentials['username'] = defined('FTP_USER') ? FTP_USER : $credentials['username'];
		$credentials['password'] = defined('FTP_PASS') ? FTP_PASS : '';

		// Check to see if we are setting the public/private keys for ssh
		$credentials['public_key'] = defined('FTP_PUBKEY') ? FTP_PUBKEY : '';
		$credentials['private_key'] = defined('FTP_PRIKEY') ? FTP_PRIKEY : '';

		// Sanitize the hostname, Some people might pass in odd-data:
		$credentials['hostname'] = preg_replace( '|\w+://|', '', $credentials['hostname'] ); //Strip any schemes off

		if ( strpos( $credentials['hostname'], ':' ) ) {
			list( $credentials['hostname'], $credentials['port'] ) = explode( ':', $credentials['hostname'], 2 );
			if ( ! is_numeric( $credentials['port'] ) ) {
				unset( $credentials['port'] );
			}
		} else {
			unset( $credentials['port'] );
		}

		if ( ( defined( 'FTP_SSH' ) && FTP_SSH ) || ( defined( 'FS_METHOD' ) && 'ssh2' == FS_METHOD ) ) {
			$credentials['connection_type'] = 'ssh';
		} else if ( ( defined( 'FTP_SSL' ) && FTP_SSL ) && 'ftpext' == $type ) {
			//Only the FTP Extension understands SSL
			$credentials['connection_type'] = 'ftps';
		} else if ( ! isset( $credentials['connection_type'] ) ) {
			//All else fails (And it's not defaulted to something else saved), Default to FTP
			$credentials['connection_type'] = 'ftp';
		}

		$has_creds = ( ! empty( $credentials['password'] ) && ! empty( $credentials['username'] ) && ! empty( $credentials['hostname'] ) );
		$can_ssh = ( 'ssh' == $credentials['connection_type'] && ! empty( $credentials['public_key'] ) && ! empty( $credentials['private_key'] ) );
		if ( $has_creds || $can_ssh ) {
			$stored_credentials = $credentials;
			if ( ! empty( $stored_credentials['port'] ) ) {
				//save port as part of hostname to simplify above code.
				$stored_credentials['hostname'] .= ':' . $stored_credentials['port'];
			}

			unset( $stored_credentials['password'], $stored_credentials['port'], $stored_credentials['private_key'], $stored_credentials['public_key'] );

			return $credentials;
		}

		return false;
	}

	private function show_error_message() {
		if ( ! empty( $this->error_message ) ) {
			echo '<div class="message">' . $this->error_message . '</div>';
		}
	}
}
