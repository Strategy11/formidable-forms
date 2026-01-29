<?php

/**
 * @group applications
 */
class test_FrmApplicationApi extends FrmUnitTest {

	/**
	 * @covers FrmApplicationApi::get_api_info
	 */
	public function test_get_api_info() {
		$api          = new FrmApplicationApi();
		$applications = $api->get_api_info();

		$this->assertIsArray( $applications );

		if ( ! empty( $applications['error'] ) ) {
			$this->markTestSkipped( 'We cannot currently reach the API, so skip the test.' );
		}

		$business_hours_id = 28067848;
		$this->assertArrayHasKey( $business_hours_id, $applications );

		$business_hours = $applications[ $business_hours_id ];
		$this->assertIsArray( $business_hours );
		$this->assertSame( 'business-hours-template', $business_hours['slug'] );
		$this->assertArrayHasKey( 'name', $business_hours );
		$this->assertNotEmpty( $business_hours['name'] );
	}
}
