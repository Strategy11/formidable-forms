<?php

/**
 * @group fields
 */
#[\PHPUnit\Framework\Attributes\Group( 'fields' )]
class test_FrmBuilderPreview extends FrmUnitTest {

	/**
	 * Alternate layouts match the address renderer and retain saved subfield content.
	 */
	public function test_address_preview_templates() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get(
			array(
				'type'          => 'address',
				'form_id'       => $form_id,
				'default_value' => array( 'city' => 'Halifax' ),
				'field_options' => array( 'city_desc' => 'Your city' ),
			)
		);
		$values  = FrmFieldsHelper::setup_edit_vars( $field );
		$object  = new FrmFieldAddress( $field );
		ob_start();
		$object->show_primary_options( array( 'field' => $values ) );
		$html = ob_get_clean();

		foreach ( array( 'international', 'us', 'europe', 'generic' ) as $type ) {
			$matched = preg_match( '/<template data-frm-address-type="' . $type . '">(.*?)<\/template>/s', $html, $matches );
			$this->assertSame( 1, $matched );
			$layout = $matches[1];
			$this->assertStringContainsString( 'value="Halifax"', $layout );
			$this->assertStringContainsString( 'Your city', $layout );

			if ( in_array( $type, array( 'us', 'generic' ), true ) ) {
				$this->assertStringNotContainsString( 'data-sub-field-name="country"', $layout );
			} else {
				$this->assertStringContainsString( 'data-sub-field-name="country"', $layout );
			}

			if ( 'europe' === $type ) {
				$this->assertStringNotContainsString( 'data-sub-field-name="state"', $layout );
				$this->assertLessThan( strpos( $layout, 'data-sub-field-name="city"' ), strpos( $layout, 'data-sub-field-name="zip"' ) );
			} elseif ( 'us' === $type ) {
				$this->assertStringContainsString( '<select name="item_meta[' . $field->id . '][state]"', $layout );
			}
		}
	}

	/**
	 * Label text has a separate update target from required markers and badges.
	 */
	public function test_builder_label_preserves_marker_target() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get(
			array(
				'type'          => 'text',
				'name'          => 'Contact name',
				'form_id'       => $form_id,
				'required'      => 1,
				'field_options' => array( 'required_indicator' => '(required)' ),
			)
		);
		$object  = new FrmFieldText( $field );
		ob_start();
		$object->show_label_on_form_builder();
		$html = ob_get_clean();

		$this->assertStringContainsString( '<span class="frm_builder_field_name">Contact name</span>', $html );
		$this->assertStringContainsString( 'id="frm_required_indicator_' . $field->id . '"', $html );
		$this->assertStringContainsString( '(required)', $html );
	}
}
