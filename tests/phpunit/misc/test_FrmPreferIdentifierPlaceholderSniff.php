<?php

/**
 * Tests for the Formidable.Security.PreferIdentifierPlaceholder PHPCS sniff.
 *
 * @group phpcs-sniffs
 */
class test_FrmPreferIdentifierPlaceholderSniff extends FrmUnitTest {

	/**
	 * Shared PHPCS ruleset for all tests in this class.
	 *
	 * @var \PHP_CodeSniffer\Ruleset|null
	 */
	private static $ruleset;

	/**
	 * Shared PHPCS config for all tests in this class.
	 *
	 * @var \PHP_CodeSniffer\Config|null
	 */
	private static $config;

	/**
	 * Loads PHPCS and builds the ruleset once for the whole class.
	 *
	 * @return void
	 */
	private static function init_phpcs() {
		if ( self::$ruleset ) {
			return;
		}

		$plugin_dir = dirname( __DIR__, 3 );

		if ( ! defined( 'PHP_CODESNIFFER_VERBOSITY' ) ) {
			define( 'PHP_CODESNIFFER_VERBOSITY', 0 );
		}

		if ( ! defined( 'PHP_CODESNIFFER_CBF' ) ) {
			define( 'PHP_CODESNIFFER_CBF', false );
		}

		require_once $plugin_dir . '/vendor/squizlabs/php_codesniffer/autoload.php';

		// Loading the Tokens class defines the extra token constants PHPCS needs.
		class_exists( '\PHP_CodeSniffer\Util\Tokens' );

		// A one-rule ruleset keeps every other Formidable sniff out of these assertions.
		$ruleset_path = __DIR__ . '/fixtures/prefer-identifier-placeholder-ruleset.xml';

		self::$config  = new \PHP_CodeSniffer\Config( array( '--standard=' . $ruleset_path ) );
		self::$ruleset = new \PHP_CodeSniffer\Ruleset( self::$config );
	}

	/**
	 * Runs the sniff on a snippet of PHP code.
	 *
	 * @param string $code PHP code without the opening tag.
	 *
	 * @return \PHP_CodeSniffer\Files\DummyFile
	 */
	private function process_code( $code ) {
		self::init_phpcs();

		$file = new \PHP_CodeSniffer\Files\DummyFile( "<?php\n" . $code, self::$ruleset, self::$config );
		$file->process();

		return $file;
	}

	/**
	 * Collects error sources and fixable flags from a processed file.
	 *
	 * @param \PHP_CodeSniffer\Files\DummyFile $file The processed file.
	 *
	 * @return array Array of array( 'source' => string, 'fixable' => bool ).
	 */
	private function get_error_list( $file ) {
		$list = array();
		foreach ( $file->getErrors() as $line => $cols ) {
			foreach ( $cols as $col => $errors ) {
				foreach ( $errors as $error ) {
					$list[] = array(
						'source'  => $error['source'],
						'fixable' => $error['fixable'],
					);
				}
			}
		}

		return $list;
	}

	/**
	 * Asserts the sniff fixes $code into $expected.
	 *
	 * @param string $code     The offending code.
	 * @param string $expected The expected fixed code.
	 *
	 * @return void
	 */
	private function assert_fixed( $code, $expected ) {
		$file   = $this->process_code( $code );
		$errors = $this->get_error_list( $file );

		$this->assertNotEmpty( $errors, 'Expected the sniff to flag: ' . $code );

		foreach ( $errors as $error ) {
			$this->assertSame( 'Formidable.Security.PreferIdentifierPlaceholder.TableInPrepare', $error['source'] );
			$this->assertTrue( $error['fixable'], 'Expected a fixable error for: ' . $code );
		}

		$file->fixer->fixFile();
		$this->assertSame( "<?php\n" . $expected, $file->fixer->getContents() );
	}

	/**
	 * Asserts the sniff flags $code with $source and cannot auto-fix it.
	 *
	 * @param string $code   The offending code.
	 * @param string $source The expected error source.
	 *
	 * @return void
	 */
	private function assert_flagged_only( $code, $source ) {
		$file   = $this->process_code( $code );
		$errors = $this->get_error_list( $file );

		$this->assertNotEmpty( $errors, 'Expected the sniff to flag: ' . $code );

		foreach ( $errors as $error ) {
			$this->assertSame( $source, $error['source'] );
			$this->assertFalse( $error['fixable'], 'Expected a non-fixable error for: ' . $code );
		}
	}

	/**
	 * Asserts the sniff stays silent for $code.
	 *
	 * @param string $code The clean code.
	 *
	 * @return void
	 */
	private function assert_clean( $code ) {
		$file = $this->process_code( $code );
		$this->assertSame( 0, $file->getErrorCount(), 'Expected no errors for: ' . $code );
	}

	public function test_fixes_interpolated_prefix_table() {
		$this->assert_fixed(
			'$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}frm_item_metas WHERE field_id=%d AND item_id=%d", $field_id, $entry_id ) );',
			"\$wpdb->query( \$wpdb->prepare( 'DELETE FROM %i WHERE field_id=%d AND item_id=%d', \$wpdb->prefix . 'frm_item_metas', \$field_id, \$entry_id ) );"
		);
	}

	public function test_fixes_concatenated_prefix_table() {
		$this->assert_fixed(
			"\$wpdb->query( \$wpdb->prepare( 'DELETE FROM ' . \$wpdb->prefix . 'frm_item_metas WHERE field_id=%d', \$id ) );",
			"\$wpdb->query( \$wpdb->prepare( 'DELETE FROM %i WHERE field_id=%d', \$wpdb->prefix . 'frm_item_metas', \$id ) );"
		);
	}

	public function test_fixes_wpdb_property_table() {
		$this->assert_fixed(
			"\$ids = \$wpdb->get_col( \$wpdb->prepare( 'SELECT ID FROM ' . \$wpdb->posts . ' WHERE post_type in (%s, %s)', \$a, \$b ) );",
			"\$ids = \$wpdb->get_col( \$wpdb->prepare( 'SELECT ID FROM %i WHERE post_type in (%s, %s)', \$wpdb->posts, \$a, \$b ) );"
		);
	}

	public function test_fixes_dynamic_table_variable() {
		$this->assert_fixed(
			"\$result = \$wpdb->get_results( \$wpdb->prepare( 'SHOW COLUMNS FROM ' . \$wpdb->prefix . \$table . ' LIKE %s', \$column ) );",
			"\$result = \$wpdb->get_results( \$wpdb->prepare( 'SHOW COLUMNS FROM %i LIKE %s', \$wpdb->prefix . \$table, \$column ) );"
		);
	}

	public function test_fixes_two_tables_in_one_query() {
		$this->assert_fixed(
			"\$wpdb->query( \$wpdb->prepare( 'DELETE fi FROM ' . \$wpdb->prefix . 'frm_fields AS fi LEFT JOIN ' . \$wpdb->prefix"
				. " . 'frm_forms fr ON (fi.form_id = fr.id) WHERE fi.form_id=%d OR parent_form_id=%d', \$id, \$id ) );",
			"\$wpdb->query( \$wpdb->prepare( 'DELETE fi FROM %i AS fi LEFT JOIN %i fr ON (fi.form_id = fr.id) WHERE fi.form_id=%d OR parent_form_id=%d',"
				. " \$wpdb->prefix . 'frm_fields', \$wpdb->prefix . 'frm_forms', \$id, \$id ) );"
		);
	}

	public function test_fixes_table_and_keeps_other_interpolation() {
		$this->assert_fixed(
			'$query = $wpdb->prepare( "SELECT DISTINCT item_id FROM {$wpdb->prefix}frm_item_metas WHERE meta_value'
				. ' {$operator} %s and field_id = %d", $search, $field_id );',
			'$query = $wpdb->prepare( "SELECT DISTINCT item_id FROM %i WHERE meta_value {$operator} %s and field_id = %d",'
				. ' $wpdb->prefix . \'frm_item_metas\', $search, $field_id );'
		);
	}

	public function test_fixes_backticked_interpolated_table() {
		$this->assert_fixed(
			'$sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}frm_subscriptions` WHERE id = %d", $id ) );',
			"\$sub = \$wpdb->get_row( \$wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', \$wpdb->prefix . 'frm_subscriptions', \$id ) );"
		);
	}

	public function test_fixes_prepare_without_existing_args() {
		$this->assert_fixed(
			'$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}frm_payments WHERE completed is NOT NULL" ) );',
			"\$rows = \$wpdb->get_results( \$wpdb->prepare( 'SELECT * FROM %i WHERE completed is NOT NULL', \$wpdb->prefix . 'frm_payments' ) );"
		);
	}

	public function test_fixes_double_interpolated_table_name() {
		$this->assert_fixed(
			'$results = $wpdb->get_results( $wpdb->prepare( "SELECT p.* FROM `{$wpdb->prefix}frm_{$table_name}` p'
				. ' LEFT JOIN `{$wpdb->prefix}frm_items` e ON p.item_id = e.id WHERE e.id = %d", $id ) );',
			"\$results = \$wpdb->get_results( \$wpdb->prepare( 'SELECT p.* FROM %i p LEFT JOIN %i e ON p.item_id = e.id WHERE e.id = %d',"
				. " \$wpdb->prefix . 'frm_' . \$table_name, \$wpdb->prefix . 'frm_items', \$id ) );"
		);
	}

	public function test_fixes_create_index_on_table() {
		$this->assert_fixed(
			'$wpdb->query( $wpdb->prepare( "CREATE INDEX idx_is_draft_created_at ON {$wpdb->prefix}frm_items (is_draft, created_at)" ) );',
			"\$wpdb->query( \$wpdb->prepare( 'CREATE INDEX idx_is_draft_created_at ON %i (is_draft, created_at)', \$wpdb->prefix . 'frm_items' ) );"
		);
	}

	public function test_fixes_multiline_interpolated_tables() {
		$code = implode(
			"\n",
			array(
				'$result = $wpdb->get_results(',
				"\t\$wpdb->prepare(",
				"\t\t\"SELECT COUNT(*) as items_count",
				"\t\t\tFROM {\$wpdb->prefix}frm_items AS it INNER JOIN {\$wpdb->prefix}frm_forms AS fr ON it.form_id = fr.id",
				"\t\t\tWHERE it.created_at BETWEEN %s AND %s\",",
				"\t\t\$from_date,",
				"\t\t\$to_date",
				"\t)",
				');',
			)
		);

		$expected = implode(
			"\n",
			array(
				'$result = $wpdb->get_results(',
				"\t\$wpdb->prepare(",
				"\t\t'SELECT COUNT(*) as items_count",
				"\t\t\tFROM %i AS it INNER JOIN %i AS fr ON it.form_id = fr.id",
				"\t\t\tWHERE it.created_at BETWEEN %s AND %s',",
				"\t\t\$wpdb->prefix . 'frm_items', \$wpdb->prefix . 'frm_forms', \$from_date,",
				"\t\t\$to_date",
				"\t)",
				');',
			)
		);

		$this->assert_fixed( $code, $expected );
	}

	public function test_flags_unknown_sql_fragment_without_fixing() {
		$this->assert_flagged_only(
			"\$wpdb->query( \$wpdb->prepare( 'UPDATE ' . \$wpdb->prefix . 'frm_forms SET status = %s ' . \$where['where'], \$where['values'] ) );",
			'Formidable.Security.PreferIdentifierPlaceholder.TableInPrepare'
		);
	}

	public function test_flags_variable_alias_without_fixing() {
		$this->assert_flagged_only(
			"\$sql = \$wpdb->prepare( ' LEFT JOIN ' . \$wpdb->prefix . 'frm_item_metas em' . \$o_key . ' ON em' . \$o_key . '.field_id=%d ', \$o_field->id );",
			'Formidable.Security.PreferIdentifierPlaceholder.TableInPrepare'
		);
	}

	public function test_flags_unprepared_query() {
		$this->assert_flagged_only(
			"\$wpdb->query( 'DROP TABLE IF EXISTS ' . \$this->fields );",
			'Formidable.Security.PreferIdentifierPlaceholder.TableNotPrepared'
		);
	}

	public function test_flags_unprepared_interpolated_query() {
		$this->assert_flagged_only(
			'$found = $wpdb->get_var( "SELECT 1 FROM {$wpdb->posts} WHERE post_content LIKE \'%[formidable %\' LIMIT 1" );',
			'Formidable.Security.PreferIdentifierPlaceholder.TableNotPrepared'
		);
	}

	public function test_ignores_create_table_ddl() {
		$this->assert_clean( '$wpdb->query( "CREATE TABLE {$wpdb->prefix}frm_payments ( id BIGINT )" );' );
	}

	public function test_ignores_existing_identifier_placeholder() {
		$this->assert_clean( "\$wpdb->query( \$wpdb->prepare( 'DELETE FROM %i WHERE id = %d', \$wpdb->prefix . 'frm_items', \$id ) );" );
	}

	public function test_ignores_literal_table_name() {
		$this->assert_clean( "\$wpdb->query( \$wpdb->prepare( 'DELETE FROM wp_frm_items WHERE id = %d', \$id ) );" );
	}

	public function test_ignores_frmdb_helper_table_argument() {
		$this->assert_clean( "\$count = FrmDb::get_var( 'frm_forms', array( 'id' => \$id ), 'COUNT(*)' );" );
	}

	public function test_ignores_interpolated_alias_after_on() {
		$this->assert_clean( '$x = $wpdb->prepare( "SELECT * FROM %i em{$o_key} WHERE em{$o_key}.field_id = %d", $wpdb->prefix . \'frm_item_metas\', $o_field->id );' );
	}

	public function test_ignores_interpolated_alias_in_join_condition() {
		$this->assert_clean(
			'$y = $wpdb->prepare( "SELECT * FROM %i pm{$o_key} INNER JOIN %i it ON pm{$o_key}.post_id = it.post_id'
				. ' WHERE it.id = %d", $wpdb->postmeta, $wpdb->prefix . \'frm_items\', $id );'
		);
	}
}
