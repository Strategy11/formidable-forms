<?php

/**
 * @group forms
 *
 * @covers FrmFormState
 */
#[\PHPUnit\Framework\Attributes\Group( 'forms' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFormState::class )]
class test_FrmFormState extends FrmUnitTest {

	public function test_get_state_from_request_ignores_array_value() {
		$_POST['frm_state'] = array( 'x' );

		$result = $this->run_private_method( array( 'FrmFormState', 'get_state_from_request' ) );

		unset( $_POST['frm_state'] );

		$this->assertFalse( $result );
	}
}
