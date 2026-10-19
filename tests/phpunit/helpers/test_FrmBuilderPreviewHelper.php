<?php

if ( ! class_exists( 'FrmProFieldText' ) ) {
	/**
	 * Stands in for the Pro text field class, which has the same preview as the Lite one.
	 */
	class FrmProFieldText extends FrmFieldText {
	}
}

/**
 * A field class an add-on replaced, whose preview may differ from the one drawn in the browser.
 */
class test_FrmBuilderPreviewReplacedText extends FrmFieldText {
}

/**
 * @group builder
 *
 * @covers FrmBuilderPreviewHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'builder' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmBuilderPreviewHelper::class )]
class test_FrmBuilderPreviewHelper extends FrmUnitTest {

	/**
	 * @covers FrmBuilderPreviewHelper::supports
	 */
	public function test_supports_lite_and_pro_field_classes() {
		$field = $this->create_field( 'text' );

		$this->assertTrue( $this->supports( $field, new FrmFieldText( $field ) ) );
		$this->assertTrue( $this->supports( $field, new FrmProFieldText( $field ) ) );
	}

	/**
	 * @covers FrmBuilderPreviewHelper::supports
	 */
	public function test_supports_refuses_replaced_and_unknown_field_classes() {
		$field = $this->create_field( 'text' );
		$this->assertFalse( $this->supports( $field, new test_FrmBuilderPreviewReplacedText( $field ) ) );

		$checkbox = $this->create_field( 'checkbox' );
		$this->assertFalse( $this->supports( $checkbox, FrmFieldFactory::get_field_object( $checkbox ) ), 'A field type without a browser preview is rendered on the server.' );
	}

	/**
	 * @covers FrmBuilderPreviewHelper::supports
	 * @covers FrmBuilderPreviewHelper::has_input_group
	 */
	public function test_supports_falls_back_for_input_groups() {
		$options = array(
			'prepend'     => '$',
			'append'      => 'USD',
			'is_currency' => 1,
		);

		foreach ( $options as $option => $value ) {
			$field = $this->create_field( 'number', array( $option => $value ) );
			$this->assertFalse( $this->supports( $field ), 'A field with ' . $option . ' is rendered on the server.' );
		}

		// A textarea is not drawn as an input group, so these options do not apply to it.
		$textarea = $this->create_field( 'textarea', array( 'prepend' => '$' ) );
		$this->assertTrue( $this->supports( $textarea ) );
	}

	/**
	 * @covers FrmBuilderPreviewHelper::supports
	 */
	public function test_supports_falls_back_for_array_default_value() {
		$field                = $this->create_field( 'text' );
		$field->default_value = array( 'first', 'second' );

		$this->assertFalse( $this->supports( $field ) );
	}

	/**
	 * @covers FrmBuilderPreviewHelper::supports
	 */
	public function test_supports_filters() {
		$field = $this->create_field( 'text' );

		add_filter( 'frm_builder_preview_in_browser', '__return_false' );
		$this->assertFalse( $this->supports( $field ) );
		remove_filter( 'frm_builder_preview_in_browser', '__return_false' );

		$remove_text = static function ( $field_classes ) {
			unset( $field_classes['text'] );
			return $field_classes;
		};
		add_filter( 'frm_builder_preview_field_classes', $remove_text );
		$this->assertFalse( $this->supports( $field ) );
		remove_filter( 'frm_builder_preview_field_classes', $remove_text );

		$this->assertTrue( $this->supports( $field ) );
	}

	/**
	 * The browser must read the same value from the preview data as from the escaped attribute PHP prints.
	 *
	 * @covers FrmBuilderPreviewHelper::attribute_value
	 */
	public function test_attribute_value() {
		$values = array(
			'Say "hi" & <b>bye</b>'   => 'Say "hi" & <b>bye</b>',
			"It's"                    => "It's",
			"first\nsecond"           => "first\nsecond",
			"first\r\nsecond\rthird"  => "first\nsecond\nthird",
			// esc_attr() does not double encode, so an entity reads as the character it stands for.
			'Fish &amp; chips &#038;' => 'Fish & chips &',
			'0'                       => '0',
		);

		foreach ( $values as $value => $expected ) {
			$this->assertSame( $expected, $this->attribute_value( (string) $value ), 'Unexpected value read from ' . $value );
		}

		$this->assertSame( '7', $this->attribute_value( 7 ) );
		$this->assertSame( '', $this->attribute_value( null ) );
		$this->assertSame( '', $this->attribute_value( array( 'a' ) ) );
	}

	/**
	 * @param string $type    The field type.
	 * @param array  $options Field options to save with the field.
	 *
	 * @return object
	 */
	private function create_field( $type, $options = array() ) {
		return $this->factory->field->create_and_get(
			array(
				'form_id'       => $this->factory->form->create(),
				'type'          => $type,
				'field_options' => $options,
			)
		);
	}

	/**
	 * @param object            $field     The saved field.
	 * @param FrmFieldType|null $field_obj The field type object. Defaults to the class registered for the field.
	 *
	 * @return bool
	 */
	private function supports( $field, $field_obj = null ) {
		if ( null === $field_obj ) {
			$field_obj = FrmFieldFactory::get_field_object( $field );
		}

		return FrmBuilderPreviewHelper::supports( $field, $field_obj, FrmFieldsController::display_field_options( array(), $field_obj ) );
	}

	/**
	 * @param mixed $value The attribute value.
	 *
	 * @return string
	 */
	private function attribute_value( $value ) {
		return $this->run_private_method( array( 'FrmBuilderPreviewHelper', 'attribute_value' ), array( $value ) );
	}
}
