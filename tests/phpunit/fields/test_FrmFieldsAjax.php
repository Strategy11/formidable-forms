<?php

/**
 * @group ajax
 * @group free
 */
class test_FrmFieldsAjax extends FrmAjaxUnitTest {

	protected $form_id = 0;

	protected $user_id = 0;

	public function setUp(): void {
		parent::setUp();

		// Set a user so the $post has 'post_author'
		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->user_id );
		FrmAppHelper::maybe_add_permissions();

		$form = $this->factory->form->create_and_get();
		$this->assertNotEmpty( $form );
		$this->form_id = $form->id;
		$this->assertIsNumeric( $this->form_id );
	}

	/**
	 * @covers FrmFieldsController::create
	 */
	public function test_create() {
		$_POST = array(
			'action'     => 'frm_insert_field',
			'nonce'      => wp_create_nonce( 'frm_ajax' ),
			'form_id'    => $this->form_id,
			'field_type' => 'text', // Create text field.
		);

		try {
			$this->_handleAjax( 'frm_insert_field' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		global $wpdb;
		$this->field_id = $wpdb->insert_id;

		$this->assertIsNumeric( $this->field_id );
		$this->assertNotEmpty( $this->field_id );

		// Make sure the field exists
		$field = FrmField::getOne( $this->field_id );
		$this->assertIsObject( $field );
	}

	/**
	 * Test duplicating a text field
	 *
	 * @covers FrmFieldsController::duplicate
	 * @covers FrmField::duplicate
	 */
	public function test_duplicating_text_field() {
		$this->assertTrue( current_user_can( 'frm_edit_forms' ), 'User does not have permission' );

		$format         = '^([a-zA-Z]\d{4})$';
		$original_field = $this->factory->field->create_and_get(
			array(
				'form_id'       => $this->form_id,
				'type'          => 'text',
				'field_options' => array(
					'format'     => $format,
					'in_section' => 0,
				),
			)
		);
		$this->assertNotEmpty( $original_field->id );
		$this->assertSame( $format, $original_field->field_options['format'] );

		$_POST = array(
			'action'   => 'frm_duplicate_field',
			'nonce'    => wp_create_nonce( 'frm_ajax' ),
			'field_id' => $original_field->id,
			'form_id'  => $original_field->form_id,
		);

		$response = $this->trigger_action( 'frm_duplicate_field' );
		$this->assertStringContainsString( '<input type="hidden" name="frm_fields_submitted[]" ', $response, 'Field was not created in form ' . $original_field->form_id . ' duplicated from field ' . $original_field->id ); // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong

		global $frm_duplicate_ids;
		$this->assertNotEmpty( $frm_duplicate_ids );
		$this->assertTrue( isset( $frm_duplicate_ids[ $original_field->id ] ), 'No id saved for duplicated field.' );
		$newest_field_id = $frm_duplicate_ids[ $original_field->id ];

		// Make sure the field exists.
		$field = FrmField::getOne( $newest_field_id );
		$this->assertIsObject( $field, 'Field id ' . $newest_field_id . ' does not exist' );
		$this->assertSame( $format, $field->field_options['format'] );

		self::check_in_section_variable( $field, 0 );
	}

	/**
	 * The batch field load defers tooltip text and ships it in its own JSON response,
	 * since admin_footer never runs to print it on admin-ajax.php.
	 *
	 * @covers FrmFieldsController::load_field
	 * @covers FrmAppHelper::get_tooltip_attr
	 */
	public function test_load_field_defers_tooltips() {
		$field_ids = array(
			$this->factory->field->create(
				array(
					'form_id' => $this->form_id,
					'type'    => 'text',
				)
			),
			$this->factory->field->create(
				array(
					'form_id' => $this->form_id,
					'type'    => 'text',
				)
			),
		);

		$_POST = array(
			'action'    => 'frm_load_field',
			'nonce'     => wp_create_nonce( 'frm_ajax' ),
			'form_id'   => $this->form_id,
			'field_ids' => $field_ids,
		);

		$response = json_decode( $this->trigger_action( 'frm_load_field' ), true );
		$this->assertIsArray( $response );
		$this->assertArrayHasKey( 'tooltips', $response );
		$this->assertNotEmpty( $response['tooltips'] );

		foreach ( $response['tooltips'] as $key => $text ) {
			$this->assertMatchesRegularExpression( '/^t[0-9a-f]{8}$/', $key );
			$this->assertSame( 't' . substr( md5( $text ), 0, 8 ), $key, 'The key must be a hash of the text so batches never collide.' );
		}

		$keys_per_field = array();

		foreach ( $field_ids as $field_id ) {
			$html = $response[ $field_id ]['html'];
			preg_match_all( '/data-tip-key="([^"]+)"/', $html, $matches );
			$this->assertNotEmpty( $matches[1], 'Field ' . $field_id . ' has no deferred tooltips.' );

			foreach ( $matches[1] as $key ) {
				$this->assertArrayHasKey( $key, $response['tooltips'], 'The response is missing the text for ' . $key . '.' );
			}

			$keys_per_field[] = $matches[1];
		}

		// Two fields of the same type carry the same tooltips, so they share every key.
		$this->assertSame( $keys_per_field[0], $keys_per_field[1] );
	}

	/**
	 * Other builder ajax requests have nothing to ship deferred text with, so they keep the title.
	 *
	 * @covers FrmAppHelper::get_tooltip_attr
	 */
	public function test_insert_field_keeps_tooltip_title() {
		$_POST = array(
			'action'     => 'frm_insert_field',
			'nonce'      => wp_create_nonce( 'frm_ajax' ),
			'form_id'    => $this->form_id,
			'field_type' => 'text',
		);

		$response = $this->trigger_action( 'frm_insert_field' );
		$this->assertStringContainsString( 'frm_help', $response );
		$this->assertStringNotContainsString( 'data-tip-key', $response );
	}

	/**
	 * Get a field object by key.
	 *
	 * @param string $field_key
	 *
	 * @return object
	 */
	protected function get_field_by_key( $field_key ) {
		$divider_field_id = FrmField::get_id_by_key( $field_key );
		$field            = FrmField::getOne( $divider_field_id );
		self::check_field_prior_to_duplication( $field );

		return $field;
	}

	/**
	 * Check in_section variable prior to duplication.
	 *
	 * @param mixed $field
	 *
	 * @return void
	 */
	protected function check_field_prior_to_duplication( $field ) {
		$this->assertTrue( isset( $field->field_options['in_section'] ), 'The in_section variable is not set correctly on import.' );
	}

	/**
	 * Check if a field is created correctly.
	 *
	 * @param int|string $newest_field_id
	 *
	 * @return void
	 */
	protected function check_if_field_id_is_created_correctly( $newest_field_id ) {
		$this->assertIsNumeric( $newest_field_id );
		$this->assertNotEmpty( $newest_field_id );
	}

	/**
	 * Check for a specific in section value.
	 *
	 * @param mixed $field
	 * @param mixed $expected
	 *
	 * @return void
	 */
	protected function check_in_section_variable( $field, $expected ) {
		$message = 'The in_section variable is not set correctly when a ' . $field->type . ' field is duplicated.';
		$this->assertTrue( isset( $field->field_options['in_section'] ), $message );

		$message = 'The in_section variable is not set to the correct value when a ' . $field->type . ' field is duplicated.';
		$this->assertSame( $expected, $field->field_options['in_section'], $message );
	}
}
