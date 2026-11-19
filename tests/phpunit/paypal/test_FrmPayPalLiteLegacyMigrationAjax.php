<?php

/**
 * @group ajax
 */
class test_FrmPayPalLiteLegacyMigrationAjax extends FrmAjaxUnitTest {

	public function setUp(): void {
		parent::setUp();
		add_action( 'wp_ajax_frm_paypal_migrate_legacy_action', 'FrmPayPalLiteLegacyMigrationController::migrate_action' );
	}

	/**
	 * Permission failures must stop before even looking up the supplied action.
	 */
	public function test_migration_requires_the_edit_forms_capability() {
		foreach ( array( 0, $this->factory->user->create( array( 'role' => 'subscriber' ) ) ) as $user_id ) {
			wp_set_current_user( $user_id );
			$_POST                = array(
				'nonce'     => wp_create_nonce( 'frm_ajax' ),
				'action_id' => 0,
			);
			$this->_last_response = '';
			$response             = $this->trigger_action( 'frm_paypal_migrate_legacy_action' );
			$this->assertStringNotContainsString( 'legacy PayPal action could not be found', $response, 'Unauthorized requests must stop before migration.' );
			$this->assertNotEmpty( $response, 'Unauthorized requests should receive a permission error.' );
		}
	}

	/**
	 * Authorized users still need a nonce, then receive the migration result.
	 */
	public function test_migration_checks_the_nonce_before_processing() {
		$user_id = $this->factory->user->create();
		$user    = new WP_User( $user_id );
		$user->add_cap( 'frm_edit_forms' );
		wp_set_current_user( $user_id );

		foreach ( array( '', 'wrong' ) as $nonce ) {
			$_POST                = array(
				'nonce'     => $nonce,
				'action_id' => 0,
			);
			$_REQUEST['nonce']    = $nonce;
			$this->_last_response = '';
			$this->assertSame( '-1', $this->trigger_action( 'frm_paypal_migrate_legacy_action' ), 'Invalid nonces should stop migration.' );
		}

		$_POST                = array(
			'nonce'     => wp_create_nonce( 'frm_ajax' ),
			'action_id' => 0,
		);
		$_REQUEST['nonce']    = $_POST['nonce'];
		$this->_last_response = '';
		$response             = json_decode( $this->trigger_action( 'frm_paypal_migrate_legacy_action' ), true );
		$this->assertFalse( $response['success'], 'An authorized request should report invalid action IDs.' );
		$this->assertStringContainsString( 'legacy PayPal action could not be found', $response['data']['message'], 'A valid nonce should reach the migration helper.' );
	}
}
