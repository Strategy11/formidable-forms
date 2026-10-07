<?php

/**
 * @group database
 *
 * @covers FrmDb
 */
#[\PHPUnit\Framework\Attributes\Group( 'database' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmDb::class )]
class test_FrmDb extends FrmUnitTest {

	/**
	 * @dataProvider datetime_conditions
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'datetime_conditions' )]
	public function test_datetime_conditions_preserve_search_compatibility( $key, $value, $cast ) {
		$args = array( $key => $value );
		FrmDb::get_where_clause_and_values( $args );
		$this->assertSame( $cast, str_contains( $args['where'], 'CAST(' ), 'Only complete, valid datetime comparisons should avoid the cast.' );
	}

	public static function datetime_conditions() {
		yield 'lower bound' => array( 'created_at >', '2026-09-29 00:00:00', false );
		yield 'upper bound' => array( 'updated_at <', '2026-09-29 23:59:59', false );
		yield 'qualified column' => array( 'it.created_at >', '2026-09-29 00:00:00', false );
		yield 'strict comparison' => array( 'created_at >-', '2026-09-29 00:00:00', false );
		yield 'explicit comparison' => array( 'created_at >=-', '2026-09-29 00:00:00', false );
		yield 'equality' => array( 'created_at', '2026-09-29 00:00:00', false );
		yield 'leap day' => array( 'created_at >', '2024-02-29 00:00:00', false );
		yield 'text search' => array( 'created_at LIKE', 'multi', true );
		yield 'partial date search' => array( 'created_at LIKE', '2026-09', true );
		yield 'complete date search' => array( 'created_at LIKE', '2026-09-29 00:00:00', true );
		yield 'partial comparison' => array( 'created_at >', '2026-09', true );
		yield 'date only' => array( 'created_at >', '2026-09-29', true );
		yield 'invalid leap day' => array( 'created_at >', '2026-02-29 00:00:00', true );
		yield 'invalid time' => array( 'created_at >', '2026-09-29 24:00:00', true );
		yield 'zero date' => array( 'created_at >', '0000-00-00 00:00:00', true );
		yield 'out of range year' => array( 'created_at >', '0999-09-29 00:00:00', true );
		yield 'trailing newline' => array( 'created_at >', "2026-09-29 00:00:00\n", true );
		yield 'array' => array( 'created_at', array( '2026-09-29 00:00:00' ), true );
		yield 'null' => array( 'created_at', null, true );
		yield 'numeric' => array( 'created_at >', 0, true );
		yield 'expression' => array( 'DATE(created_at) >', '2026-09-29 00:00:00', true );
	}

	public function test_datetime_count_matches_cast_at_boundaries() {
		global $wpdb;

		$form_id = $this->factory->form->create();

		foreach ( array( '2026-09-28 23:59:59', '2026-09-29 00:00:00', '2026-09-29 00:00:01' ) as $date ) {
			$this->factory->entry->create(
				array(
					'form_id'    => $form_id,
					'created_at' => $date,
				)
			);
		}

		$args       = array(
			'form_id'        => $form_id,
			'created_at >'   => '2026-09-29 00:00:00',
			'is_draft'       => 0,
			'parent_item_id' => 0,
		);
		$count      = FrmDb::get_count( 'frm_items', $args );
		$cast_count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i WHERE form_id = %d AND CAST(created_at AS CHAR) >= %s AND is_draft = 0 AND parent_item_id = 0',
				$wpdb->prefix . 'frm_items',
				$form_id,
				'2026-09-29 00:00:00'
			)
		);
		$this->assertSame( '', $wpdb->last_error, 'The datetime count should run without a database error.' );
		$this->assertSame( 2, $count, 'The inclusive lower bound should include the exact boundary.' );
		$this->assertSame( (int) $cast_count, $count, 'Native comparisons should preserve the count returned by the cast.' );
	}

	public function test_esc_order() {
		$orders = array(
			'it.created_at ASC'          => ' ORDER BY it.created_at asc',
			'(select+sleep(3)) #'        => ' ORDER BY select+sleep3 asc',
			'count(*) DESC'              => ' ORDER BY count(*) desc',
			'field_order DESC'           => ' ORDER BY field_order desc',
			' ORDER BY field_order DESC' => ' ORDER BY field_order desc',
			'meta_value'                 => ' ORDER BY meta_value ',
			'meta_1754+0 asc'            => ' ORDER BY meta_1754+0 asc',
		);

		foreach ( $orders as $start => $expected ) {
			$actual = FrmDb::esc_order( $start );
			$this->assertSame( $expected, $actual );
		}
	}

	public function test_db_column_exists() {
		$this->assertTrue( FrmDb::db_column_exists( 'frm_fields', 'field_key' ) );
		$this->assertTrue( FrmDb::db_column_exists( 'frm_items', 'is_draft' ) );
		$this->assertFalse( FrmDb::db_column_exists( 'frm_fields', 'missing_column' ) );
	}
}
