<?php

class test_FrmXMLHelper extends FrmUnitTest {

	public function test_remove_defaults() {
		$defaults = array();
		$saved    = array();

		$this->run_private_method(
			array( 'FrmXMLHelper', 'remove_defaults' ),
			array( $defaults, &$saved )
		);
		$this->assertSame( array(), $saved );

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
		$this->assertSame(
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
		$this->assertSame(
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

		$this->assertArrayHasKey( 'postmeta', $post );
		$this->assertNotEmpty( $post['postmeta'] );
		$this->assertArrayHasKey( 'frm_dyncontent', $post['postmeta'] );
		$this->assertIsArray( $post['postmeta']['frm_dyncontent'] );
		$this->assertSame(
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

		$this->assertSame( $simple_xml_string, $xml_string );

		$conflicting_meta_tag = '<meta name="generator" content="Equity 1.7.13" />';
		$xml_string           = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL . $wp_comment . PHP_EOL . $conflicting_meta_tag . '<channel></channel>';
		$this->maybe_fix_xml( $xml_string );

		$this->assertSame( $simple_xml_string, $xml_string );
	}

	private function maybe_fix_xml( &$xml_string ) {
		$this->run_private_method( array( 'FrmXMLHelper', 'maybe_fix_xml' ), array( &$xml_string ) );
	}

	/**
	 * @covers FrmXMLHelper::cdata
	 * @covers FrmAppHelper::maybe_utf8_encode
	 */
	public function test_cdata() {
		$this->assertSame( '<![CDATA[Name]]>', FrmXMLHelper::cdata( 'Name' ) );
		$this->assertSame( '<![CDATA[29yf4d]]>', FrmXMLHelper::cdata( '29yf4d' ) );
		$this->assertSame( '<![CDATA[United States]]>', FrmXMLHelper::cdata( 'United States' ) );
		$this->assertSame( '<![CDATA[["Red","Blue"]]]>', FrmXMLHelper::cdata( serialize( array( 'Red', 'Blue' ) ) ) );
		$this->assertSame( '<![CDATA[[60418,60419,60420]]]>', FrmXMLHelper::cdata( serialize( array( 60418, 60419, 60420 ) ) ) );
		$this->assertSame(
			'<![CDATA[{"browser":"Mozilla\/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko\/20100101 Firefox\/37.0","referrer":"http:\/\/localhost:8888\/features\/wp-admin\/admin-ajax.php?action=frm_forms_preview&form=boymfd"}]]>', // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
			FrmXMLHelper::cdata(
				serialize(
					array(
						'browser'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko/20100101 Firefox/37.0',
						'referrer' => 'http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd',
					)
				)
			)
		);
		$this->assertSame( '5', FrmXMLHelper::cdata( '5' ), 'Numbers do not need to be wrapped' );
		$this->assertSame( '<![CDATA[2023-05-21]]>', FrmXMLHelper::cdata( '2023-05-21' ) );

		// Test that a ISO-8859-1 characters (\xC1 and \xE9) convert to UTF-8.
		$this->assertSame( '<![CDATA[HelloÁWorld]]>', FrmXMLHelper::cdata( "Hello\xC1World" ) ); // \xC1 is the Á character.
		$this->assertSame( '<![CDATA[é]]>', FrmXMLHelper::cdata( "\xE9" ) ); // \xE9 is the é character.
	}

	/**
	 * Field options are a settings map, and every step of an import reads them as
	 * one. A file whose value there cannot be read used to end the whole import
	 * with a fatal error on the first field it reached, so anything unreadable
	 * becomes an empty set of options and the field type's defaults fill the gaps.
	 *
	 * @covers FrmXMLHelper::fill_field_options
	 *
	 * @dataProvider unreadable_field_options_provider
	 *
	 * @param string $stored Value as it appears in the file.
	 *
	 * @return void
	 */
	public function test_fill_field_options_always_returns_an_array( $stored ) {
		$field   = new SimpleXMLElement( '<field><field_options>' . $stored . '</field_options></field>' );
		$options = $this->run_private_method( array( 'FrmXMLHelper', 'fill_field_options' ), array( $field ) );

		$this->assertIsArray( $options, "Options stored as '{$stored}' should be read as an array." );
	}

	/**
	 * @return void array<string>>
	 */
	public function unreadable_field_options_provider(): \Iterator {
		yield 'serialized null' => array( 'N;' );
		yield 'empty' => array( '' );
		yield 'not json' => array( 'not json' );
		yield 'a bare number' => array( '0' );
	}

	/**
	 * Options written by an older version are serialized rather than JSON, so
	 * they are read rather than thrown away for not being JSON.
	 *
	 * @covers FrmXMLHelper::fill_field_options
	 *
	 * @return void
	 */
	public function test_fill_field_options_reads_serialized_options() {
		$serialized = 'a:2:{s:10:"start_year";s:4:"1990";s:8:"end_year";s:4:"2050";}';
		$field      = new SimpleXMLElement( '<field><field_options>' . $serialized . '</field_options></field>' );
		$options    = $this->run_private_method( array( 'FrmXMLHelper', 'fill_field_options' ), array( $field ) );

		$this->assertSame( '1990', $options['start_year'], 'Serialized options should survive the import.' );
		$this->assertSame( '2050', $options['end_year'], 'Serialized options should survive the import.' );
	}

	/**
	 * JSON options, which is what an export writes today.
	 *
	 * @covers FrmXMLHelper::fill_field_options
	 *
	 * @return void
	 */
	public function test_fill_field_options_reads_json_options() {
		$field   = new SimpleXMLElement( '<field><field_options>{"start_year":"1990"}</field_options></field>' );
		$options = $this->run_private_method( array( 'FrmXMLHelper', 'fill_field_options' ), array( $field ) );

		$this->assertSame( '1990', $options['start_year'], 'JSON options should be read as an array.' );
	}
}
