<?php

/**
 * @group styles
 */
class test_FrmStyleApi extends FrmUnitTest {

	/**
	 * @covers FrmStyleApi::get_api_info
	 */
	public function test_get_api_info() {
		$style_api = new FrmStyleApi();
		$info      = $style_api->get_api_info();

		$this->assertIsArray( $info );
		$this->assertNotEmpty( $info );

		if ( ! empty( $info['error'] ) ) {
			$this->markTestSkipped( 'We cannot currently reach the API, so skip the test.' );
		}

		$first_style_template = reset( $info );

		$this->assertIsArray( $first_style_template );
		$this->assertArrayHasKey( 'name', $first_style_template );
		$this->assertNotEmpty( $first_style_template['name'] );

		$this->assertArrayHasKey( 'icon', $first_style_template );
		$this->assertIsArray( $first_style_template['icon'] );
		$image = reset( $first_style_template['icon'] );
		$this->assertStringStartsWith( 'https://', $image );
		$this->assertStringContainsString( '.png', $image );

		// Question: Do we ever allow people to download a style template for free? Does WordPress allow this?
		$this->assertTrue( ! isset( $first_style_template['url'] ), 'In lite we always expect the style template to be ' );
	}
}
