<?php

/**
 * @group csv
 * @group ajax
 *
 * @covers FrmCSVExportController
 */
#[\PHPUnit\Framework\Attributes\Group( 'csv' )]
#[\PHPUnit\Framework\Attributes\Group( 'ajax' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmCSVExportController::class )]
class test_FrmCSVExportController extends FrmAjaxUnitTest {

	/**
	 * Creates a form and user with entry access for authenticated picker requests.
	 *
	 * @return object
	 */
	private function prepare_request() {
		$this->set_as_user_role( 'administrator' );
		FrmAppHelper::maybe_add_permissions();
		$this->set_as_user_role( 'administrator' );
		$form           = $this->factory->form->create_and_get();
		$_POST['form']  = $form->id;
		$_POST['nonce'] = wp_create_nonce( 'frm_ajax' );
		$_REQUEST       = $_POST;
		return $form;
	}

	public function test_preferences_validate_columns_and_preserve_order() {
		$form             = $this->prepare_request();
		$_POST['columns'] = array( 'id', 'item_key', 'unknown', 'id', array( 'ip' ), 'created_at' );
		$_REQUEST         = $_POST;
		$response         = json_decode( $this->trigger_action( 'frm_csv_export_preferences' ), true );
		$this->assertTrue( $response['success'], 'An authorized export preference should save.' );
		$this->assertSame(
			array( 'id', 'item_key', 'created_at' ),
			get_user_meta( $this->user_id, 'frm_csv_columns_' . $form->id, true ),
			'Only valid unique columns should be saved in their requested order.'
		);
	}

	public function test_preferences_are_isolated_by_user_and_form() {
		$form             = $this->prepare_request();
		$other_form       = $this->factory->form->create_and_get();
		$other_user       = $this->factory->user->create();
		$_POST['columns'] = array( 'id' );
		$_REQUEST         = $_POST;
		$this->trigger_action( 'frm_csv_export_preferences' );
		$this->assertSame( '', get_user_meta( $other_user, 'frm_csv_columns_' . $form->id, true ), 'A saved selection should not change another user.' );
		$this->assertSame( '', get_user_meta( $this->user_id, 'frm_csv_columns_' . $other_form->id, true ), 'A saved selection should not change another form.' );
	}

	public function test_preferences_reject_empty_selection() {
		$form             = $this->prepare_request();
		$_POST['columns'] = array( 'unknown' );
		$_REQUEST         = $_POST;
		$response         = json_decode( $this->trigger_action( 'frm_csv_export_preferences' ), true );
		$this->assertFalse( $response['success'], 'A selection with no valid columns should be rejected.' );
		$this->assertSame( '', get_user_meta( $this->user_id, 'frm_csv_columns_' . $form->id, true ), 'An invalid selection should not be stored.' );
	}

	public function test_preferences_require_entry_permission() {
		$form = $this->prepare_request();
		$this->set_as_user_role( 'subscriber' );
		$_POST['nonce']   = wp_create_nonce( 'frm_ajax' );
		$_POST['columns'] = array( 'id' );
		$_REQUEST         = $_POST;
		$this->trigger_action( 'frm_csv_export_preferences' );
		$this->assertSame( '', get_user_meta( $this->user_id, 'frm_csv_columns_' . $form->id, true ), 'Users without entry permission should not save export preferences.' );
	}

	public function test_preferences_require_nonce() {
		$form             = $this->prepare_request();
		$_POST['nonce']   = 'invalid';
		$_POST['columns'] = array( 'id' );
		$_REQUEST         = $_POST;
		$this->trigger_action( 'frm_csv_export_preferences' );
		$this->assertSame( '', get_user_meta( $this->user_id, 'frm_csv_columns_' . $form->id, true ), 'An invalid nonce should not save export preferences.' );
	}

	public function test_options_return_every_column_in_form_order() {
		$form   = $this->prepare_request();
		$fields = FrmField::get_all_for_form( $form->id );
		$first  = reset( $fields );
		$this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'html',
			)
		);
		$response = json_decode( $this->trigger_action( 'frm_csv_export_options' ), true );
		$this->assertTrue( $response['success'], 'An authorized user should receive the export columns.' );
		$this->assertSame( (string) $first->id, $response['data']['columns'][0]['value'], 'Available columns should retain the form order.' );
		$this->assertContains( 'item_key', $response['data']['selected'], 'Entry metadata should be available for export.' );
		$this->assertCount( 12, $response['data']['columns'], 'The picker should include the field, comment groups, and all eight metadata columns.' );
	}
}
