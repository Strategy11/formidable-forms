<?php

class test_FrmXMLHelper extends FrmUnitTest {

	public function test_remove_defaults() {
		$defaults = array();
		$saved    = array();

		$this->run_private_method(
			array( 'FrmXMLHelper', 'remove_defaults' ),
			array( $defaults, &$saved )
		);
		$this->assertEquals( array(), $saved );

		$defaults = array(
			'x'    => 'X',
			'y'    => 'Y',
			'z'    => 'Z',
			'b'    => 'B',
			'arr2' => array(
				'x' => 'X',
				'z' => 'Z',
			),
		);

		$saved = array(
			'a'   => 'A',
			'b'   => 'B',
			'c'   => 'C',
			'arr' => array(
				'x' => 'X',
				'y' => 'Y',
			),
		);

		$this->run_private_method(
			array( 'FrmXMLHelper', 'remove_defaults' ),
			array( $defaults, &$saved )
		);
		$this->assertEquals(
			array(
				'a'   => 'A',
				'c'   => 'C',
				'arr' => array(
					'x' => 'X',
					'y' => 'Y',
				),
			),
			$saved
		);

		$defaults = array(
			'a'   => 'A',
			'b'   => 'B',
			'arr' => array(
				'x' => 'X',
			),
		);

		$saved = array(
			'a'   => 'A',
			'b'   => 'B',
			'c'   => 'C',
			'arr' => array(
				'x' => 'X',
				'y' => 'Y',
			),
		);

		$this->run_private_method(
			array( 'FrmXMLHelper', 'remove_defaults' ),
			array( $defaults, &$saved )
		);
		$this->assertEquals(
			array(
				'c'   => 'C',
				'arr' => array(
					'x' => 'X',
					'y' => 'Y',
				),
			),
			$saved
		);
	}

	/**
	 * @covers FrmXMLHelper::populate_postmeta
	 */
	public function test_populate_postmeta() {
		$post             = array();
		$meta             = new stdClass();
		$meta->meta_key   = 'frm_dyncontent';
		$meta->meta_value = '[{"box":1,"content":"<div id=\"box_1\">Box 1 Content<\/div>"},{"box":2,"content":"Box 2 Content\nBox 2 Line 2"}]';
		$imported         = array(
			'forms' => array(),
		);

		$this->populate_postmeta( $post, $meta, $imported );

		$this->assertTrue( array_key_exists( 'postmeta', $post ) );
		$this->assertTrue( ! empty( $post['postmeta'] ) );
		$this->assertTrue( array_key_exists( 'frm_dyncontent', $post['postmeta'] ) );
		$this->assertTrue( is_array( $post['postmeta']['frm_dyncontent'] ) );
		$this->assertEquals(
			array(
				array(
					'box'     => 1,
					'content' => '<div id=\"box_1\">Box 1 Content<\/div>',
				),
				array(
					'box'     => 2,
					'content' => 'Box 2 Content\nBox 2 Line 2',
				),
			),
			$post['postmeta']['frm_dyncontent']
		);
	}

	private function populate_postmeta( &$post, $meta, $imported ) {
		$this->run_private_method( array( 'FrmXMLHelper', 'populate_postmeta' ), array( &$post, $meta, $imported ) );
	}

	/**
	 * @covers FrmXMLHelper::maybe_fix_xml
	 */
	public function test_maybe_fix_xml() {
		$wp_comment        = '<!-- generator="WordPress/5.2.4" created="2019-10-23 19:33" -->';
		$simple_xml_string = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL . $wp_comment . PHP_EOL . '<channel></channel>';
		$xml_string        = chr( 13 ) . $simple_xml_string;
		$this->maybe_fix_xml( $xml_string );

		$this->assertEquals( $simple_xml_string, $xml_string );

		$conflicting_meta_tag = '<meta name="generator" content="Equity 1.7.13" />';
		$xml_string           = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL . $wp_comment . PHP_EOL . $conflicting_meta_tag . '<channel></channel>';
		$this->maybe_fix_xml( $xml_string );

		$this->assertEquals( $simple_xml_string, $xml_string );
	}

	private function maybe_fix_xml( &$xml_string ) {
		$this->run_private_method( array( 'FrmXMLHelper', 'maybe_fix_xml' ), array( &$xml_string ) );
	}

	/**
	 * @covers FrmXMLHelper::cdata
	 * @covers FrmAppHelper::maybe_utf8_encode
	 */
	public function test_cdata() {
		$this->assertEquals( '<![CDATA[Name]]>', FrmXMLHelper::cdata( 'Name' ) );
		$this->assertEquals( '<![CDATA[29yf4d]]>', FrmXMLHelper::cdata( '29yf4d' ) );
		$this->assertEquals( '<![CDATA[United States]]>', FrmXMLHelper::cdata( 'United States' ) );
		$this->assertEquals( '<![CDATA[["Red","Blue"]]]>', FrmXMLHelper::cdata( serialize( array( 'Red', 'Blue' ) ) ) );
		$this->assertEquals( '<![CDATA[[60418,60419,60420]]]>', FrmXMLHelper::cdata( serialize( array( 60418, 60419, 60420 ) ) ) );
		$this->assertEquals(
			'<![CDATA[{"browser":"Mozilla\/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko\/20100101 Firefox\/37.0","referrer":"http:\/\/localhost:8888\/features\/wp-admin\/admin-ajax.php?action=frm_forms_preview&form=boymfd"}]]>',
			FrmXMLHelper::cdata(
				serialize(
					array(
						'browser'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko/20100101 Firefox/37.0',
						'referrer' => 'http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd',
					)
				)
			)
		);
		$this->assertEquals( '5', FrmXMLHelper::cdata( '5' ), 'Numbers do not need to be wrapped' );
		$this->assertEquals( '<![CDATA[2023-05-21]]>', FrmXMLHelper::cdata( '2023-05-21' ) );

		// Test that a ISO-8859-1 characters (\xC1 and \xE9) convert to UTF-8.
		$this->assertEquals( '<![CDATA[HelloÁWorld]]>', FrmXMLHelper::cdata( "Hello\xC1World" ) ); // \xC1 is the Á character.
		$this->assertEquals( '<![CDATA[é]]>', FrmXMLHelper::cdata( "\xE9" ) ); // \xE9 is the é character.
	}
}
