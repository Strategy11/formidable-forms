<?php

/**
 * Covers the width shared by the builder settings and field preview.
 */
class test_FrmBuilderSizeStyle extends FrmUnitTest {

	/**
	 * Numeric sizes use pixels and explicit units remain intact.
	 */
	public function test_builder_size_style_units_and_empty_values() {
		$cases = array(
			array( '150', 'width:150px;' ),
			array( 200, 'width:200px;' ),
			array( '50%', 'width:50%;' ),
			array( '12em', 'width:12em;' ),
			array( '', '' ),
			array( 0, '' ),
			array( array( '150' ), '' ),
		);

		foreach ( $cases as $case ) {
			$field = new FrmFieldText(
				array(
					'id'   => 1,
					'size' => $case[0],
				)
			);
			$style = $this->run_private_method( array( $field, 'get_builder_size_style' ) );
			$this->assertSame( $case[1], $style, 'The preview should use the saved width and unit.' );
		}
	}

	/**
	 * Dropdown-like types and fields without a size setting keep their own layout.
	 */
	public function test_builder_size_style_respects_settings_eligibility() {
		foreach ( array( 'select', 'data', 'time' ) as $type ) {
			$field = new class( array(
				'id'   => 1,
				'size' => '150',
			), $type ) extends FrmFieldType {
				protected function field_settings_for_type() {
					return array( 'size' => true );
				}
			};
			$this->assertFalse( $field->should_show_size_setting( array( 'size' => true ) ), 'Dropdown-like fields should omit the width setting.' );
			$this->assertSame( '', $this->run_private_method( array( $field, 'get_builder_size_style' ) ), 'Excluded previews should not receive an inline width.' );
		}

		$field = new FrmFieldDefault(
			array(
				'id'   => 1,
				'size' => '150',
			),
			'default'
		);
		$this->assertFalse( $field->should_show_size_setting( $field->display_field_settings() ), 'Fields without a size setting should keep their layout.' );
		$this->assertSame( '', $this->run_private_method( array( $field, 'get_builder_size_style' ) ), 'A saved size should not enable a hidden setting.' );
	}
}
