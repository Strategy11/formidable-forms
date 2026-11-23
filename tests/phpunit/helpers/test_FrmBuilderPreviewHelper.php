<?php

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
	 * Pin the pixel estimates shared by browser previews and AJAX placeholders.
	 *
	 * @covers FrmBuilderPreviewHelper::get_placeholder_height
	 *
	 * @dataProvider placeholder_height_provider
	 *
	 * @param string       $type     The field type.
	 * @param array        $settings The display options.
	 * @param array|string $options  The choices shown in the preview.
	 * @param int          $expected The estimated card height in pixels.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'placeholder_height_provider' )]
	public function test_get_placeholder_height( $type, $settings, $options, $expected ) {
		$saved_field = (object) array(
			'type'          => $type,
			'options'       => $options,
			'field_options' => $settings,
		);
		$field       = array_merge(
			array(
				'type'    => $type,
				'options' => $options,
			),
			$settings
		);

		$this->assertSame( $expected, FrmBuilderPreviewHelper::get_placeholder_height( $saved_field ), 'The saved field should reserve the expected card height.' );
		$this->assertSame( $expected, FrmBuilderPreviewHelper::get_placeholder_height( $field ), 'The prepared field should reserve the expected card height.' );
	}

	/**
	 * Expected heights are literals so changes to the estimates require review.
	 * Compare these estimates with real builder cards when changing builder CSS.
	 *
	 * @return \Generator
	 */
	public static function placeholder_height_provider() {
		yield 'html' => array( 'html', array(), array(), 194 );
		yield 'summary' => array( 'summary', array(), array(), 194 );
		yield 'virtual' => array( 'virtual', array(), array(), 194 );
		yield 'textarea missing rows' => array( 'textarea', array(), array(), 89 );
		yield 'textarea zero rows' => array( 'textarea', array( 'max' => 0 ), array(), 89 );
		yield 'textarea one row minimum' => array( 'textarea', array( 'max' => 1 ), array(), 89 );
		yield 'textarea two rows' => array( 'textarea', array( 'max' => '2' ), array(), 107 );
		yield 'textarea five rows' => array( 'textarea', array( 'max' => 5 ), array(), 170 );
		yield 'page break' => array( 'break', array(), array(), 113 );
		yield 'default text' => array( 'text', array(), array(), 89 );
		yield 'default number' => array( 'number', array(), array(), 89 );
		yield 'unknown type' => array( 'custom', array(), array(), 89 );

		$choices = array( 'First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh' );

		foreach ( array( 'radio', 'checkbox' ) as $type ) {
			yield $type . ' stacked' => array( $type, array(), $choices, 231 );
			yield $type . ' empty choices minimum' => array( $type, array(), array(), 89 );
			yield $type . ' scalar choices minimum' => array( $type, array(), 'First', 89 );
			yield $type . ' two columns round up' => array( $type, array( 'align' => 'frm_two_col' ), $choices, 153 );
			yield $type . ' three columns round up' => array( $type, array( 'align' => 'frm_three_col' ), $choices, 127 );
			yield $type . ' four columns round up' => array( $type, array( 'align' => 'frm_four_col' ), $choices, 101 );
			yield $type . ' inline minimum' => array( $type, array( 'align' => 'inline' ), $choices, 89 );
			yield $type . ' buttons' => array( $type, array( 'image_options' => 'buttons' ), $choices, 364 );
			yield $type . ' inline buttons' => array(
				$type,
				array(
					'image_options' => 'buttons',
					'align'         => 'inline',
				),
				$choices,
				94,
			);
			yield $type . ' image buttons override' => array(
				$type,
				array(
					'image_options'         => 'buttons',
					'use_images_in_buttons' => 1,
				),
				$choices,
				280,
			);
			yield $type . ' image buttons in columns' => array(
				$type,
				array(
					'image_options'         => 'buttons',
					'use_images_in_buttons' => 1,
					'align'                 => 'frm_two_col',
				),
				$choices,
				181,
			);
		}
	}

	/**
	 * @covers FrmBuilderPreviewHelper::supports
	 */
	public function test_supports_lite_and_pro_field_classes() {
		$field = $this->create_field( 'text' );
		$this->assertTrue( $this->supports( $field, new FrmFieldText( $field ) ), 'The Lite text field supports browser previews.' );

		if ( class_exists( 'FrmProFieldText' ) ) {
			$this->assertTrue( $this->supports( $field, new FrmProFieldText( $field ) ), 'The Pro text field supports browser previews.' );
		}
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
