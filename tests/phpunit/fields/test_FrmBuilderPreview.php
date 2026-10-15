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
				$this->assertMatchesRegularExpression( '/<select\s+name="item_meta\[' . $field->id . '\]\[state\]"/', $layout );
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

	/**
	 * Dropdown previews contain only the selected choice or their empty-state label.
	 */
	public function test_dropdown_preview_keeps_only_displayed_option() {
		$cases = array(
			array(
				'default'     => 'Second',
				'placeholder' => 'Choose',
				'label'       => 'Second',
			),
			array(
				'default'     => '',
				'placeholder' => 'Choose',
				'label'       => 'Choose',
			),
			array(
				'default'     => '',
				'placeholder' => '',
				'label'       => ' ',
			),
			array(
				'default'     => 'Missing',
				'placeholder' => 'Choose',
				'label'       => 'Choose',
			),
		);

		foreach ( $cases as $case ) {
			$form_id = $this->factory->form->create();
			$field   = $this->factory->field->create_and_get(
				array(
					'type'          => 'select',
					'form_id'       => $form_id,
					'options'       => array( '', 'First', 'Second' ),
					'default_value' => $case['default'],
					'field_options' => array( 'placeholder' => $case['placeholder'] ),
				)
			);
			$object  = new FrmFieldSelect( $field );
			ob_start();
			$object->show_on_form_builder();
			$preview = ob_get_clean();
			$this->assertSame( 1, substr_count( $preview, '<option' ), $preview );
			$this->assertStringContainsString( $case['label'] . '</option>', $preview );

			$front = FrmFormsController::get_form_shortcode( array( 'id' => $form_id ) );
			$this->assertStringContainsString( '>First</option>', $front );
			$this->assertStringContainsString( '>Second</option>', $front );
		}
	}

	/**
	 * Country and state previews avoid rendering a full list while keeping valid defaults.
	 */
	public function test_address_dropdown_preview_contains_one_option() {
		foreach ( array(
			'international' => 'country',
			'us'            => 'state',
		) as $type => $name ) {
			$form_id = $this->factory->form->create();
			$value   = 'country' === $name ? 'Canada' : 'Maine';
			$field   = $this->factory->field->create_and_get(
				array(
					'type'          => 'address',
					'form_id'       => $form_id,
					'default_value' => array( $name => $value ),
					'field_options' => array( 'address_type' => $type ),
				)
			);
			$object  = new FrmFieldAddress( $field );
			ob_start();
			$object->show_on_form_builder();
			$preview = ob_get_clean();
			$this->assertSame( 1, substr_count( $preview, '<option' ) );
			$this->assertStringContainsString( '>' . $value . '</option>', $preview );
			$this->assertStringContainsString( 'data-frm-preview-choices=', $preview );
		}
	}

	/**
	 * PHP duplicates persisted values. Unsaved builder values remain a JavaScript overlay.
	 */
	public function test_duplication_preserves_saved_database_values() {
		$form_id                  = $this->factory->form->create();
		$field                    = $this->factory->field->create_and_get(
			array(
				'type'          => 'select',
				'form_id'       => $form_id,
				'name'          => 'Saved label',
				'options'       => array( 'Saved A', 'Saved B' ),
				'default_value' => 'Saved B',
				'field_options' => array(
					'placeholder'        => 'Saved placeholder',
					'required_indicator' => '*',
				),
			)
		);
		$builder                  = FrmFieldsHelper::setup_edit_vars( $field );
		$builder['name']          = 'Unsaved label';
		$builder['options']       = array( 'Unsaved choice' );
		$builder['default_value'] = 'Unsaved choice';
		$builder['placeholder']   = 'Unsaved placeholder';

		$result   = FrmField::duplicate_single_field( $field->id, $form_id );
		$original = FrmField::getOne( $field->id );
		$copy     = FrmField::getOne( $result['field_id'] );
		$this->assertNotSame( $original->id, $copy->id );
		$this->assertNotSame( $original->field_key, $copy->field_key );

		foreach ( array( $original, $copy ) as $stored ) {
			$this->assertSame( 'Saved label', $stored->name );
			$this->assertSame( array( 'Saved A', 'Saved B' ), $stored->options );
			$this->assertSame( 'Saved B', $stored->default_value );
			$this->assertSame( 'Saved placeholder', $stored->field_options['placeholder'] );
			$this->assertSame( '*', $stored->field_options['required_indicator'] );
		}
		$this->assertSame( 'Unsaved label', $builder['name'] );
	}
}
