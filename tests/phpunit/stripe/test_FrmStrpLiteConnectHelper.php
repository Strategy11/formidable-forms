<?php

/**
 * @group stripe
 *
 * @covers FrmStrpLiteConnectHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'stripe' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmStrpLiteConnectHelper::class )]
class test_FrmStrpLiteConnectHelper extends FrmUnitTest {

	/**
	 * @var string
	 */
	private $current_url;

	public function setUp(): void {
		parent::setUp();
		$this->current_url = $this->run_private_method( array( 'FrmStrpLiteConnectHelper', 'get_site_url_for_connect_server' ) );
		$this->delete_connect_options();
	}

	public function tearDown(): void {
		$this->delete_connect_options();
		$_POST = array();
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	private function delete_connect_options() {
		foreach ( array( 'test', 'live' ) as $mode ) {
			foreach ( array( 'account_id', 'client_password', 'server_password', 'details_submitted', 'site_url' ) as $key ) {
				delete_option( 'frm_strp_connect_' . $key . '_' . $mode );
			}
		}
	}

	/**
	 * @covers FrmStrpLiteConnectHelper::get_mismatched_site_url
	 */
	public function test_get_mismatched_site_url_without_account() {
		update_option( 'frm_strp_connect_site_url_test', 'old-site.example.com' );
		$this->assertFalse( FrmStrpLiteConnectHelper::get_mismatched_site_url( 'test' ) );
	}

	/**
	 * @covers FrmStrpLiteConnectHelper::get_mismatched_site_url
	 */
	public function test_get_mismatched_site_url_without_saved_url() {
		update_option( 'frm_strp_connect_account_id_test', 'acct_test' );

		$this->assertFalse( FrmStrpLiteConnectHelper::get_mismatched_site_url( 'test' ) );
		$this->assertFalse( get_option( 'frm_strp_connect_site_url_test' ), 'Reading the mismatch should not save a URL.' );
	}

	/**
	 * @covers FrmStrpLiteConnectHelper::get_mismatched_site_url
	 */
	public function test_get_mismatched_site_url_with_matching_url() {
		update_option( 'frm_strp_connect_account_id_test', 'acct_test' );

		update_option( 'frm_strp_connect_site_url_test', $this->current_url );
		$this->assertFalse( FrmStrpLiteConnectHelper::get_mismatched_site_url( 'test' ) );

		update_option( 'frm_strp_connect_site_url_test', trailingslashit( $this->current_url ) );
		$this->assertFalse( FrmStrpLiteConnectHelper::get_mismatched_site_url( 'test' ), 'A trailing slash on the saved URL should be ignored.' );

		add_filter( 'home_url', 'trailingslashit' );
		update_option( 'frm_strp_connect_site_url_test', untrailingslashit( $this->current_url ) );
		$mismatched_url = FrmStrpLiteConnectHelper::get_mismatched_site_url( 'test' );
		remove_filter( 'home_url', 'trailingslashit' );
		$this->assertFalse( $mismatched_url, 'A trailing slash on the current URL should be ignored.' );
	}

	/**
	 * @covers FrmStrpLiteConnectHelper::get_mismatched_site_url
	 */
	public function test_get_mismatched_site_url_with_different_url() {
		update_option( 'frm_strp_connect_account_id_test', 'acct_test' );
		update_option( 'frm_strp_connect_site_url_test', 'old-site.example.com' );

		$this->assertSame( 'old-site.example.com', FrmStrpLiteConnectHelper::get_mismatched_site_url( 'test' ) );
		$this->assertFalse( FrmStrpLiteConnectHelper::get_mismatched_site_url( 'live' ), 'Live mode has no account, so it should not warn.' );
	}

	/**
	 * @covers FrmStrpLiteConnectHelper::save_missing_connected_site_urls
	 */
	public function test_save_missing_connected_site_urls() {
		update_option( 'frm_strp_connect_account_id_test', 'acct_test' );
		update_option( 'frm_strp_connect_account_id_live', 'acct_live' );
		update_option( 'frm_strp_connect_site_url_live', 'old-site.example.com' );

		FrmStrpLiteConnectHelper::save_missing_connected_site_urls();

		$this->assertSame( $this->current_url, get_option( 'frm_strp_connect_site_url_test' ), 'A connection with no saved URL should get the current one.' );
		$this->assertSame( 'old-site.example.com', get_option( 'frm_strp_connect_site_url_live' ), 'A saved URL should not be overwritten.' );

		delete_option( 'frm_strp_connect_account_id_test' );
		delete_option( 'frm_strp_connect_site_url_test' );
		FrmStrpLiteConnectHelper::save_missing_connected_site_urls();
		$this->assertFalse( get_option( 'frm_strp_connect_site_url_test' ), 'A mode with no account should not get a URL.' );
	}

	/**
	 * @covers FrmStrpLiteConnectHelper::reset_stripe_connect_integration
	 */
	public function test_reset_stripe_connect_integration_deletes_site_url() {
		update_option( 'frm_strp_connect_account_id_test', 'acct_test' );
		update_option( 'frm_strp_connect_site_url_test', 'old-site.example.com' );
		$_POST = array( 'testMode' => 1 );

		FrmStrpLiteConnectHelper::reset_stripe_connect_integration();

		$this->assertFalse( get_option( 'frm_strp_connect_account_id_test' ) );
		$this->assertFalse( get_option( 'frm_strp_connect_site_url_test' ) );
	}

	/**
	 * @covers FrmStrpLiteAppController::uninstall
	 */
	public function test_uninstall_deletes_site_url() {
		$this->set_user_by_role( 'administrator' );
		update_option( 'frm_strp_connect_site_url_test', 'old-site.example.com' );
		update_option( 'frm_strp_connect_site_url_live', 'old-site.example.com' );

		FrmStrpLiteAppController::uninstall();

		$this->assertFalse( get_option( 'frm_strp_connect_site_url_test' ) );
		$this->assertFalse( get_option( 'frm_strp_connect_site_url_live' ) );
	}
}
