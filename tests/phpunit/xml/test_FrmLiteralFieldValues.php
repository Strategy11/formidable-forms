<?php

class test_FrmLiteralFieldValues extends FrmUnitTest {

	public function test_literal_xml_preserves_source_bytes() {
		$field  = (object) array( 'type' => 'code' );
		$source = "<?php\r\n\$path = 'C:\\temp';\0]]>";
		add_filter( 'frm_xml_field_value_is_literal', '__return_true' );

		try {
			$this->assertSame( array( 'encoding' => 'base64' ), FrmXMLHelper::field_value_xml_attributes( $field ), 'Literal values declare their encoding.' );
			$xml = simplexml_load_string( '<default_value encoding="base64">' . FrmXMLHelper::field_value_to_xml( $source, $field ) . '</default_value>' );
			$this->assertSame( $source, FrmXMLHelper::field_value_from_xml( $xml, $field ), 'Literal source survives an XML round trip.' );
			$this->assertSame( '{"key":1}', FrmXMLHelper::field_value_from_xml( '{"key":1}', $field ), 'Unmarked literal JSON remains text.' );
		} finally {
			remove_filter( 'frm_xml_field_value_is_literal', '__return_true' );
		}
	}

	public function test_compound_values_keep_legacy_decoding() {
		$field = (object) array( 'type' => 'checkbox' );
		$this->assertSame( array(), FrmXMLHelper::field_value_xml_attributes( $field ), 'Existing fields have no encoding marker.' );
		$this->assertSame( array( 'red', 'blue' ), FrmXMLHelper::field_value_from_xml( '["red","blue"]', $field ), 'Existing compound values still decode.' );
	}
}
