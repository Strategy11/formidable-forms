<?php

/**
 * @covers FrmField::update
 */
class test_FrmFieldUpdateHooks extends FrmUnitTest {

	/**
	 * @var array<string>
	 */
	private $field_reads = array();

	/**
	 * @var array<int>
	 */
	private $updated_field = array();

	public function tearDown(): void {
		remove_filter( 'query', array( $this, 'record_field_read' ) );
		remove_action( 'frm_field_updated', array( $this, 'record_updated_field' ) );
		parent::tearDown();
	}

	/**
	 * @dataProvider update_cases
	 *
	 * @param bool $warm_cache Whether the field is cached before the update.
	 * @param bool $move Whether the update moves the field to another form.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'update_cases' )]
	public function test_previous_form_id_reuses_the_placement_lookup( $warm_cache, $move ) {
		$form_id  = (int) $this->factory->form->create();
		$target   = $move ? (int) $this->factory->form->create() : $form_id;
		$field_id = (int) $this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'text',
			)
		);
		FrmDb::cache_delete_group( 'frm_field' );

		if ( $warm_cache ) {
			FrmField::getOne( $field_id );
		}

		add_filter( 'query', array( $this, 'record_field_read' ) );
		add_action( 'frm_field_updated', array( $this, 'record_updated_field' ), 10, 2 );
		$result = FrmField::update( $field_id, array( 'form_id' => $target ) );
		remove_filter( 'query', array( $this, 'record_field_read' ) );

		$this->assertNotFalse( $result, 'The field update should succeed.' );
		$this->assertCount( $warm_cache ? 0 : 1, $this->field_reads, 'Reading the previous form ID should add no query beyond placement validation.' );
		$this->assertSame( array( $field_id, $form_id ), $this->updated_field, 'The hook should receive the original form ID for both moves and ordinary updates.' );
		$this->assertSame( $target, (int) FrmField::getOne( $field_id )->form_id, 'The field should belong to the requested destination after the update.' );
	}

	/**
	 * @return array
	 */
	public static function update_cases() {
		return array(
			'cached ordinary update'   => array( true, false ),
			'uncached ordinary update' => array( false, false ),
			'cached move'              => array( true, true ),
			'uncached move'            => array( false, true ),
		);
	}

	/**
	 * Record field table reads while an update is running.
	 *
	 * @param string $query SQL statement.
	 *
	 * @return string
	 */
	public function record_field_read( $query ) {
		global $wpdb;

		if ( str_starts_with( $query, 'SELECT' ) && str_contains( $query, $wpdb->prefix . 'frm_fields' ) ) {
			$this->field_reads[] = $query;
		}

		return $query;
	}

	/**
	 * Capture the field ID and its original form ID from the update hook.
	 *
	 * @param int $field_id Updated field ID.
	 * @param int $previous_form_id Original form ID.
	 *
	 * @return void
	 */
	public function record_updated_field( $field_id, $previous_form_id ) {
		$this->updated_field = array( $field_id, $previous_form_id );
	}
}
