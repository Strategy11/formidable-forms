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

	public function test_import_passes_the_same_field_shape_as_export() {
		$seen   = array();
		$filter = function ( $literal, $field ) use ( &$seen ) {
			$seen[] = array( get_class( $field ), $field->type );
			return 'code' === $field->type;
		};
		add_filter( 'frm_xml_field_value_is_literal', $filter, 10, 2 );

		try {
			$xml_field = simplexml_load_string(
				'<field><id>5</id><field_key>abc</field_key><type><![CDATA[code]]></type><default_value><![CDATA[{"a":1}]]></default_value></field>'
			);
			$this->assertSame( '{"a":1}', FrmXMLHelper::field_value_from_xml( $xml_field->default_value, $xml_field ), 'A strict type check matches on import.' );
			FrmXMLHelper::field_value_xml_attributes(
				(object) array(
					'id'        => 5,
					'field_key' => 'abc',
					'type'      => 'code',
				)
			);
		} finally {
			remove_filter( 'frm_xml_field_value_is_literal', $filter, 10 );
		}

		$this->assertSame( array( array( 'stdClass', 'code' ), array( 'stdClass', 'code' ) ), $seen );
	}

	public function test_base64_with_whitespace_or_invalid_data() {
		$source  = "line one\r\nline \\two";
		$wrapped = chunk_split( base64_encode( $source ), 8, "\n  " );
		$xml     = simplexml_load_string( '<default_value encoding="base64"><![CDATA[' . $wrapped . ']]></default_value>' );
		$field   = (object) array( 'type' => 'code' );
		$this->assertSame( $source, FrmXMLHelper::field_value_from_xml( $xml, $field ), 'Whitespace in the encoded value is ignored.' );

		$xml = simplexml_load_string( '<default_value encoding="base64"><![CDATA[not base64!]]></default_value>' );
		$this->assertSame( 'not base64!', FrmXMLHelper::field_value_from_xml( $xml, $field ), 'Invalid base64 keeps the raw text.' );
	}

	public function test_forms_xml_export_and_import_round_trip() {
		$form_id   = $this->factory->form->create();
		$field_key = 'literal_round_trip';
		$source    = "{\"a\":1}\r\n\$path = 'C:\\temp';";
		$field_id  = $this->factory->field->create(
			array(
				'type'          => 'text',
				'form_id'       => $form_id,
				'field_key'     => $field_key,
				'default_value' => $source,
			)
		);
		$filter    = function ( $literal, $field ) use ( $field_key ) {
			return 'text' === $field->type && $field_key === $field->field_key;
		};
		add_filter( 'frm_xml_field_value_is_literal', $filter, 10, 2 );

		try {
			$path     = self::generate_xml( array( 'forms' ), array( 'ids' => array( $form_id ) ) );
			$contents = file_get_contents( $path );
			$this->assertStringContainsString( '<default_value encoding="base64"><![CDATA[' . base64_encode( $source ) . ']]></default_value>', $contents );

			FrmField::update( $field_id, array( 'default_value' => 'changed' ) );
			FrmXMLHelper::import_xml_now( simplexml_load_string( $contents ) );
		} finally {
			remove_filter( 'frm_xml_field_value_is_literal', $filter, 10 );
		}

		// FrmField::getOne() unslashes every column, so compare the stored value.
		$this->assertSame( $source, FrmDb::get_var( 'frm_fields', array( 'field_key' => $field_key ), 'default_value' ) );
	}

	public function test_unslash_filter_applies_to_every_read_path() {
		$form_id  = $this->factory->form->create();
		$field_id = $this->factory->field->create(
			array(
				'type'    => 'text',
				'form_id' => $form_id,
			)
		);
		$entry_id = $this->factory->entry->create(
			array(
				'form_id'   => $form_id,
				'item_meta' => array( $field_id => 'placeholder' ),
			)
		);
		$stored   = 'C:\\temp\\new {"a":1}';

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'frm_item_metas',
			array( 'meta_value' => $stored ),
			array(
				'item_id'  => $entry_id,
				'field_id' => $field_id,
			)
		);
		FrmEntryMeta::clear_cache();
		wp_cache_flush();

		add_filter( 'frm_should_unslash_entry_meta_value', '__return_false' );

		try {
			$this->assertSame( $stored, FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id ) );
			$this->assertSame( array( $stored ), FrmEntryMeta::get_entry_metas_for_field( $field_id ) );
			$all = FrmEntryMeta::getAll(
				array(
					'it.item_id'  => $entry_id,
					'it.field_id' => $field_id,
				),
				'',
				'',
				true
			);
			$this->assertSame( $stored, reset( $all )->meta_value );
		} finally {
			remove_filter( 'frm_should_unslash_entry_meta_value', '__return_false' );
		}

		FrmEntryMeta::clear_cache();
		wp_cache_flush();
		$this->assertSame( 'C:tempnew {"a":1}', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id ), 'Without the filter the legacy unslashing is unchanged.' );
	}
}
