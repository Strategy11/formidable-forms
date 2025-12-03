<?php

/**
 * @group direct_file_access
 */
class test_FrmDirectFileAccess extends FrmUnitTest {

	/**
	 * @param string $dir
	 * @param array $results
	 *
	 * @return array
	 */
	private function all_php_file_paths( $dir = false, &$results = array() ) {
		if ( ! $dir ) {
			$dir = FrmAppHelper::plugin_path();
		}

		$files             = scandir( $dir );
		$files_to_ignore   = array( 'set-php-version.php', 'stubs.php', '.php-cs-fixer.php', 'rector.php' );
		$folders_to_ignore = array( 'tests', 'vendor', 'languages', 'node_modules', 'js', 'phpcs-sniffs' );

		foreach ( $files as $key => $value ) {
			$path = realpath( $dir . DIRECTORY_SEPARATOR . $value );
			if ( ! is_dir( $path ) ) {
				if ( substr( $value, strlen( $value ) - 4 ) === '.php' && ! in_array( $value, $files_to_ignore, true ) ) {
					$results[] = $path;
				}
			} elseif ( $value !== '.' && $value !== '..' ) {
				if ( in_array( $value, $folders_to_ignore, true ) ) {
					continue;
				}

				$this->all_php_file_paths( $path, $results );
				if ( substr( $path, strlen( $path ) - 4 ) === '.php' ) {
					$results[] = $path;
				}
			}
		}

		return $results;
	}

	private function check_for_abspath_check( $path ) {
		return strpos( file_get_contents( $path ), "! defined( 'ABSPATH' )" ) !== false;
	}

	public function test_direct_file_access() {
		$all_php_paths = $this->all_php_file_paths();

		foreach ( $all_php_paths as $path ) {
			$this->assertTrue( $this->check_for_abspath_check( $path ), "{$path} should be checking for direct php file access but no check could be found" );
		}
	}
}
