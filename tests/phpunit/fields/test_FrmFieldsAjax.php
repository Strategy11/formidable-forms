<?php

/**
 * @group ajax
 * @group free
 *
 * @covers FrmAppHelper
 * @covers FrmField
 * @covers FrmFieldsController
 */
#[\PHPUnit\Framework\Attributes\Group( 'ajax' )]
#[\PHPUnit\Framework\Attributes\Group( 'free' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmAppHelper::class )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmField::class )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFieldsController::class )]
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
	 * @see FrmFieldsController::duplicate
	 * @see FrmField::duplicate
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

	public function test_load_field_separates_settings_only_for_supported_fields_when_requested() {
		$field_ids = array();

		foreach ( array( 'text', 'submit', 'textarea' ) as $type ) {
			$field_ids[ $type ] = $this->factory->field->create(
				array(
					'form_id' => $this->form_id,
					'type'    => $type,
				)
			);
		}

		$_POST    = array(
			'action'         => 'frm_load_field',
			'nonce'          => wp_create_nonce( 'frm_ajax' ),
			'form_id'        => $this->form_id,
			'field_ids'      => array_values( $field_ids ),
			'defer_settings' => 1,
		);
		$response = json_decode( $this->trigger_action( 'frm_load_field' ), true );
		$this->assertIsArray( $response );

		foreach ( $field_ids as $type => $field_id ) {
			$loaded = $response[ $field_id ];
			$this->assertSame( $type, $loaded['type'] );

			if ( 'submit' === $type ) {
				$this->assertArrayNotHasKey( 'settingsHtml', $loaded );
				$this->assertStringContainsString( 'id="frm-single-settings-' . $field_id . '"', $loaded['html'] );
				continue;
			}
			$this->assertStringNotContainsString( 'id="frm-single-settings-', $loaded['html'] );
			$this->assertStringContainsString( 'id="frm-single-settings-' . $field_id . '"', $loaded['settingsHtml'] );
			$this->assertStringNotContainsString( 'frm-deferred-settings-meta', $loaded['html'] );
			$this->assertSame( $type, $loaded['settingsMeta']['type'] );
		}
	}

	/**
	 * The batch field load defers tooltip text and ships it in its own JSON response,
	 * since admin_footer never runs to print it on admin-ajax.php.
	 *
	 * @see FrmFieldsController::load_field
	 * @see FrmAppHelper::get_tooltip_attr
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
			$this->assertArrayNotHasKey( 'settingsHtml', $response[ $field_id ] );
			$this->assertStringContainsString( 'id="frm-single-settings-' . $field_id . '"', $html );
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
	 * @see FrmAppHelper::get_tooltip_attr
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
	 * @see FrmFieldsController::load_field_settings
	 */
	public function test_load_field_settings_requires_permission() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );

		$_POST    = $this->get_load_field_settings_request( array( $this->create_text_field() ) );
		$response = $this->trigger_action( 'frm_load_field_settings' );
		$this->assertSame( FrmAppHelper::get_settings()->admin_permission, $response );
		$this->assertStringNotContainsString( 'frm-single-settings-', (string) $this->_last_response );
	}

	/**
	 * @see FrmFieldsController::load_field_settings
	 */
	public function test_load_field_settings_requires_nonce() {
		$field_id = $this->create_text_field();

		foreach ( array( 'invalid', null ) as $nonce ) {
			$_POST = $this->get_load_field_settings_request( array( $field_id ) );

			if ( null === $nonce ) {
				unset( $_POST['nonce'] );
			} else {
				$_POST['nonce'] = $nonce;
			}

			$_REQUEST = $_POST;

			$this->_last_response = '';
			$this->assertSame( '-1', $this->trigger_action( 'frm_load_field_settings' ) );
		}
	}

	/**
	 * @see FrmFieldsController::load_field_settings
	 */
	public function test_load_field_settings_rejects_incomplete_request() {
		$field_id = $this->create_text_field();
		$requests = array(
			'missing form id'   => array( 'form_id' => 0 ),
			'empty field ids'   => array( 'field_ids' => array() ),
			'missing field ids' => array( 'field_ids' => null ),
		);

		foreach ( $requests as $label => $changes ) {
			$_POST = array_filter(
				array_merge( $this->get_load_field_settings_request( array( $field_id ) ), $changes ),
				static function ( $value ) {
					return null !== $value;
				}
			);

			$this->_last_response = '';
			$response             = json_decode( $this->trigger_action( 'frm_load_field_settings' ), true );
			$this->assertSame( array( 'success' => false ), $response, $label );
		}
	}

	/**
	 * @see FrmFieldsController::load_field_settings
	 */
	public function test_load_field_settings_returns_requested_fields() {
		$field_ids     = array( $this->create_text_field(), $this->create_text_field( 'textarea' ) );
		$other_form_id = $this->factory->form->create();
		$other_field   = $this->create_text_field( 'text', $other_form_id );
		$_POST         = $this->get_load_field_settings_request( array_merge( $field_ids, array( $other_field ) ) );
		$response      = json_decode( $this->trigger_action( 'frm_load_field_settings' ), true );
		$this->assertTrue( $response['success'] );

		$settings = $response['data']['settings'];
		$this->assertSame( $field_ids, array_keys( $settings ) );
		$this->assertArrayNotHasKey( $other_field, $settings, 'A field from another form must not be returned.' );

		foreach ( $field_ids as $field_id ) {
			$this->assertStringContainsString( 'id="frm-single-settings-' . $field_id . '"', $settings[ $field_id ] );
			// Only the settings are sent, since the browser already drew the preview.
			$this->assertStringNotContainsString( 'id="frm_field_id_' . $field_id . '"', $settings[ $field_id ] );
		}

		$this->assertArrayHasKey( 'tooltips', $response['data'] );
		$this->assertArrayHasKey( 'selectOptions', $response['data'] );
	}

	/**
	 * @param string $type    The field type.
	 * @param int    $form_id The form to add the field to. Defaults to the test form.
	 *
	 * @return int
	 */
	private function create_text_field( $type = 'text', $form_id = 0 ) {
		return $this->factory->field->create(
			array(
				'form_id' => $form_id ? $form_id : $this->form_id,
				'type'    => $type,
			)
		);
	}

	/**
	 * The preview uses the posted settings, but nothing is saved.
	 *
	 * @see FrmFieldsController::refresh_field_preview
	 */
	public function test_refresh_field_preview_uses_unsaved_settings_in_memory() {
		global $wpdb;
		$field_id = $this->create_text_field( 'textarea' );
		$before   = FrmField::getOne( $field_id );
		$query    = $wpdb->prepare( 'SELECT field_options FROM %i WHERE id = %d', $wpdb->prefix . 'frm_fields', $field_id );
		$saved    = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$_POST = $this->get_refresh_field_preview_request(
			$field_id,
			array(
				'size_' . $field_id         => '300',
				'placeholder_' . $field_id  => 'Unsaved <script>alert(1)</script>placeholder',
				'size_' . ( $field_id + 1 ) => '999',
			)
		);

		$response = json_decode( $this->trigger_action( 'frm_refresh_field_preview' ), true );
		$this->assertTrue( $response['success'] );
		$html = $response['data']['html'];
		$this->assertStringContainsString( 'width:300px;', $html );
		$this->assertStringContainsString( 'placeholder="Unsaved alert(1)placeholder"', $html, 'Posted settings are sanitized like a save.' );
		$this->assertStringNotContainsString( '999', $html, 'Settings of another field are ignored.' );

		$this->assertSame( $saved, $wpdb->get_var( $query ), 'The preview must not save the field.' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$this->assertEquals( $before->field_options, FrmField::getOne( $field_id )->field_options, 'The cached field keeps its saved settings.' );
	}

	/**
	 * @see FrmFieldsController::refresh_field_preview
	 */
	public function test_refresh_field_preview_allowed_settings_filter() {
		$field_id = $this->create_text_field( 'textarea' );
		$seen     = array();
		$filter   = function ( $allowed, $field ) use ( &$seen ) {
			$seen[] = array( $allowed, (int) $field->id );
			return array( 'placeholder' );
		};
		add_filter( 'frm_refresh_field_preview_settings', $filter, 10, 2 );

		$_POST = $this->get_refresh_field_preview_request(
			$field_id,
			array(
				'size_' . $field_id        => '300',
				'placeholder_' . $field_id => 'Allowed',
			)
		);

		$response = json_decode( $this->trigger_action( 'frm_refresh_field_preview' ), true );
		remove_filter( 'frm_refresh_field_preview_settings', $filter, 10 );

		$this->assertSame( array( array( null, $field_id ) ), $seen, 'The filter starts from null, which allows every setting.' );
		$this->assertStringContainsString( 'placeholder="Allowed"', $response['data']['html'] );
		$this->assertStringNotContainsString( 'width:300px', $response['data']['html'] );
	}

	/**
	 * @see FrmFieldsController::refresh_field_preview
	 */
	public function test_refresh_field_preview_requires_permission() {
		$field_id = $this->create_text_field();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );

		$_POST    = $this->get_refresh_field_preview_request( $field_id, array() );
		$response = $this->trigger_action( 'frm_refresh_field_preview' );
		$this->assertSame( FrmAppHelper::get_settings()->admin_permission, $response );
	}

	/**
	 * @see FrmFieldsController::refresh_field_preview
	 */
	public function test_refresh_field_preview_requires_nonce() {
		$_POST          = $this->get_refresh_field_preview_request( $this->create_text_field(), array() );
		$_POST['nonce'] = 'invalid';
		$_REQUEST       = $_POST;

		$this->assertSame( '-1', $this->trigger_action( 'frm_refresh_field_preview' ) );
	}

	/**
	 * @see FrmFieldsController::refresh_field_preview
	 */
	public function test_refresh_field_preview_rejects_missing_field() {
		$_POST    = $this->get_refresh_field_preview_request( 999999, array() );
		$response = json_decode( $this->trigger_action( 'frm_refresh_field_preview' ), true );
		$this->assertFalse( $response['success'] );
	}

	/**
	 * Field types that set $allow_in_repeater to false are marked for the builder drop check.
	 *
	 * @see FrmFieldsController::load_single_field
	 */
	public function test_builder_field_marks_types_not_allowed_in_repeater() {
		ob_start();
		FrmFieldsController::load_single_field( $this->create_text_field( 'gdpr' ), array( 'id' => $this->form_id ) );
		$gdpr = ob_get_clean();

		ob_start();
		FrmFieldsController::load_single_field( $this->create_text_field(), array( 'id' => $this->form_id ) );
		$text = ob_get_clean();

		$this->assertStringContainsString( 'data-norepeater="1"', $gdpr );
		$this->assertStringNotContainsString( 'data-norepeater', $text );
	}

	/**
	 * @param int   $field_id The field to preview.
	 * @param array $settings The posted field_options.
	 *
	 * @return array
	 */
	private function get_refresh_field_preview_request( $field_id, $settings ) {
		return array(
			'action'        => 'frm_refresh_field_preview',
			'nonce'         => wp_create_nonce( 'frm_ajax' ),
			'form_id'       => $this->form_id,
			'field_id'      => $field_id,
			'field_options' => $settings,
		);
	}

	/**
	 * @param int[] $field_ids The fields to request settings for.
	 *
	 * @return array
	 */
	private function get_load_field_settings_request( $field_ids ) {
		return array(
			'action'    => 'frm_load_field_settings',
			'nonce'     => wp_create_nonce( 'frm_ajax' ),
			'form_id'   => $this->form_id,
			'field_ids' => $field_ids,
		);
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
