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

		$this->assertTrue( is_array( $applications ) );

		if ( ! empty( $applications['error'] ) ) {
			$this->markTestSkipped( 'We cannot currently reach the API, so skip the test.' );
		}

		$business_hours_id = 28067848;
		$this->assertTrue( array_key_exists( $business_hours_id, $applications ) );

		$business_hours = $applications[ $business_hours_id ];
		$this->assertTrue( is_array( $business_hours ) );
		$this->assertEquals( 'business-hours-template', $business_hours['slug'] );
		$this->assertTrue( array_key_exists( 'name', $business_hours ) );
		$this->assertNotEmpty( $business_hours['name'] );
	}
}
