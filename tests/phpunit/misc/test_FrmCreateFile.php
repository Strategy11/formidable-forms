<?php

/**
 * @group misc
 */
class test_FrmCreateFile extends FrmUnitTest {

	/**
	 * @var array<string>
	 */
	private $paths_to_clean_up = array();

	public function tearDown(): void {
		foreach ( $this->paths_to_clean_up as $path ) {
			if ( is_dir( $path ) ) {
				@rmdir( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			} elseif ( file_exists( $path ) ) {
				@unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}
		$this->paths_to_clean_up = array();

		parent::tearDown();
	}

	/**
	 * @covers FrmCreateFile::create_file
	 */
	public function test_create_file_returns_true_on_successful_write() {
		$uploads   = wp_upload_dir();
		$folder    = 'frm-test-create-file-' . wp_generate_password( 8, false );
		$file_name = 'test.css';

		$create_file = new FrmCreateFile(
			array(
				'file_name'   => $file_name,
				'folder_name' => $folder,
			)
		);

		$content                   = 'body{color:#123456}';
		$result                    = $create_file->create_file( $content );
		$written_path              = $uploads['basedir'] . '/' . $folder . '/' . $file_name;
		$this->paths_to_clean_up[] = $written_path;
		$this->paths_to_clean_up[] = $uploads['basedir'] . '/' . $folder . '/index.php';
		$this->paths_to_clean_up[] = $uploads['basedir'] . '/' . $folder;

		$this->assertTrue( $result, 'create_file() should return true when the file is actually written to disk.' );
		$this->assertSame( $content, file_get_contents( $written_path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	/**
	 * The write-permission early return (`! $this->has_permission`) can only be reached in this
	 * harness by forcing the private flag directly: the local/direct filesystem method always
	 * succeeds here, and there is no practical, non-flaky way to make WP_Filesystem's credential
	 * check fail deterministically in an automated integration run. Reflection on the private
	 * property is used only for this one guard clause, not for the method under test itself.
	 *
	 * @covers FrmCreateFile::create_file
	 */
	public function test_create_file_returns_false_without_permission() {
		$create_file = new FrmCreateFile(
			array(
				'file_name'   => 'no-permission.css',
				'folder_name' => 'frm-test-no-permission',
			)
		);

		$permission_property = $this->get_accessible_property( $create_file, 'has_permission' );
		$permission_property->setValue( $create_file, false );

		$result = $create_file->create_file( 'body{color:#000}' );

		$this->assertFalse( $result, 'create_file() should return false when there is no filesystem permission.' );
	}

	/**
	 * Forces the directory-creation early return (`! $dirs_exist`) with a real filesystem
	 * collision rather than a mock: a plain file is created where FrmCreateFile needs to create a
	 * directory of the same name, so both mkdir() and the is_dir() fallback genuinely fail.
	 *
	 * @covers FrmCreateFile::create_file
	 */
	public function test_create_file_returns_false_when_directory_cannot_be_created() {
		$blocking     = 'frm-test-blocking-' . wp_generate_password( 8, false );
		$blocked_path = wp_upload_dir()['basedir'] . '/' . $blocking;

		$this->assertNotFalse(
			file_put_contents( $blocked_path, 'not a directory' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			'Test setup: failed to create the blocking file.'
		);
		$this->paths_to_clean_up[] = $blocked_path;

		$create_file = new FrmCreateFile(
			array(
				'file_name'   => 'blocked.css',
				'folder_name' => $blocking,
			)
		);

		$result = $create_file->create_file( 'body{color:#000}' );

		$this->assertFalse( $result, 'create_file() should return false when its target directory cannot be created.' );
	}

	/**
	 * The widened create_file() return type (void -> bool) must not affect its two internal
	 * callers: both discard the return value and remain void themselves.
	 *
	 * @covers FrmCreateFile::append_file
	 * @covers FrmCreateFile::combine_files
	 */
	public function test_append_file_and_combine_files_are_unaffected_by_the_widened_return_type() {
		$uploads = wp_upload_dir();
		$folder  = 'frm-test-callers-' . wp_generate_password( 8, false );

		$append_target = new FrmCreateFile(
			array(
				'file_name'   => 'append.css',
				'folder_name' => $folder,
			)
		);

		$append_result = $append_target->append_file( 'first-part;' );
		$this->assertNull( $append_result, 'append_file() must remain void regardless of create_file()\'s widened return type.' );

		$append_result_2 = $append_target->append_file( 'second-part;' );
		$this->assertNull( $append_result_2 );

		$appended_path             = $uploads['basedir'] . '/' . $folder . '/append.css';
		$this->paths_to_clean_up[] = $appended_path;
		$this->paths_to_clean_up[] = $uploads['basedir'] . '/' . $folder . '/index.php';
		$this->paths_to_clean_up[] = $uploads['basedir'] . '/' . $folder;

		$this->assertSame( 'first-part;second-part;', file_get_contents( $appended_path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$source_a = $uploads['basedir'] . '/' . $folder . '/source-a.css';
		$source_b = $uploads['basedir'] . '/' . $folder . '/source-b.css';
		file_put_contents( $source_a, 'a{color:#111}' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		file_put_contents( $source_b, 'b{color:#222}' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		$this->paths_to_clean_up[] = $source_a;
		$this->paths_to_clean_up[] = $source_b;

		$combine_target = new FrmCreateFile(
			array(
				'file_name'   => 'combined.css',
				'folder_name' => $folder,
			)
		);

		$combine_result = $combine_target->combine_files( array( $source_a, $source_b ) );
		$this->assertNull( $combine_result, 'combine_files() must remain void regardless of create_file()\'s widened return type.' );

		$combined_path             = $uploads['basedir'] . '/' . $folder . '/combined.css';
		$this->paths_to_clean_up[] = $combined_path;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertSame( "a{color:#111}\nb{color:#222}\n", file_get_contents( $combined_path ) );
	}
}
