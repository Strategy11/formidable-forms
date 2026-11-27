<?php
/**
 * The challenge endpoint.
 *
 * @package Formidable
 */

require_once __DIR__ . '/FrmAltchaTestHelper.php';

/**
 * @group altcha
 */
class test_FrmAltchaChallengeAjax extends FrmAjaxUnitTest {

	public function tearDown(): void {
		$_GET = array();
		parent::tearDown();
	}

	/**
	 * The endpoint gives a challenge for the scope it is asked for, and a form challenge otherwise.
	 *
	 * @covers FrmAltchaController::send_challenge
	 */
	public function test_endpoint_issues_challenges_for_the_requested_scope() {
		$this->assertSame( FrmAltchaChallengeHelper::SCOPE_FORM, $this->solved_challenge_scope( array() ) );
		$this->assertSame( FrmAltchaChallengeHelper::SCOPE_UPLOAD, $this->solved_challenge_scope( array( 'scope' => 'upload' ) ) );
		$this->assertSame( FrmAltchaChallengeHelper::SCOPE_FORM, $this->solved_challenge_scope( array( 'scope' => 'unknown' ) ) );
	}

	/**
	 * Request a challenge, solve it and report which scope it verifies for.
	 *
	 * @param array $query The query string values.
	 *
	 * @return string
	 */
	private function solved_challenge_scope( $query ) {
		$this->_last_response = '';
		$_GET                 = $query;

		add_filter( 'frm_altcha_challenge_args', array( $this, 'cheap_challenge' ) );
		$response = $this->trigger_action( 'frm_altcha_challenge' );
		remove_filter( 'frm_altcha_challenge_args', array( $this, 'cheap_challenge' ) );

		$payload = FrmAltchaTestHelper::solve( json_decode( $response, true ) );

		foreach ( array( FrmAltchaChallengeHelper::SCOPE_FORM, FrmAltchaChallengeHelper::SCOPE_UPLOAD ) as $scope ) {
			if ( FrmAltchaChallengeHelper::verify( $payload, $scope )['verified'] ) {
				return $scope;
			}
		}

		return '';
	}

	/**
	 * @param array $args Challenge arguments.
	 *
	 * @return array
	 */
	public function cheap_challenge( $args ) {
		$args['max_number'] = 10;
		$args['counter']    = FrmAltchaTestHelper::COUNTER;
		$args['cost']       = 1;
		return $args;
	}
}
