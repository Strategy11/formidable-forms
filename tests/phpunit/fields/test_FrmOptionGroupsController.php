<?php

/**
 * @group fields
 *
 * @covers FrmOptionGroupsController
 */
class test_FrmOptionGroupsController extends FrmUnitTest {

	public function test_bulk_edit_preserves_values_and_ends_groups() {
		$options = array(
			'Plain',
			'## Fruit',
			array(
				'label' => 'Apple',
				'value' => 'a',
			),
			'Pear',
			'##',
			'End',
		);
		$grouped = FrmOptionGroupsController::bulk_edit_options( $options, array( 'type' => 'select' ) );

		$this->assertSame(
			array(
				'Plain',
				array(
					'label' => 'Apple',
					'value' => 'a',
					'group' => 'Fruit',
				),
				array(
					'label' => 'Pear',
					'value' => 'Pear',
					'group' => 'Fruit',
				),
				'End',
			),
			$grouped,
			'Group markers should preserve separate values and allow returning to ungrouped options.'
		);
		$this->assertSame( $options, FrmOptionGroupsController::bulk_edit_options( $options, array( 'type' => 'radio' ) ), 'Radio options should keep literal group markers.' );
		$this->assertSame(
			$options,
			FrmOptionGroupsController::bulk_edit_options(
				$options,
				array(
					'type'       => 'select',
					'post_field' => 'post_category',
				)
			),
			'Category dropdowns should keep their own option handling.'
		);
	}

	public function test_saved_groups_render_without_pro_and_do_not_leak_between_fields() {
		$form_id = $this->factory->form->create();
		$options = array(
			array(
				'label' => 'Choose',
				'value' => '',
			),
			array(
				'label' => 'Apple',
				'value' => 'a',
				'group' => 'Fruit " & <food>',
			),
			array(
				'label' => 'Pear',
				'value' => 'p',
				'group' => 'Fruit " & <food>',
			),
			array(
				'label' => 'Plain',
				'value' => 'plain',
			),
			array(
				'label' => 'Zero',
				'value' => 'zero',
				'group' => '0',
			),
		);
		$field   = $this->factory->field->create_and_get(
			array(
				'form_id'       => $form_id,
				'type'          => 'select',
				'options'       => $options,
				'field_options' => array( 'separate_value' => 1 ),
			)
		);
		$this->assertSame( $options, $field->options, 'Saved option groups should retain their data.' );
		$plain_field = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'type'    => 'select',
				'options' => array(
					array(
						'label' => 'Zero',
						'value' => 'Zero',
						'group' => '0',
					),
				),
			)
		);
		$this->set_front_end();
		$html     = do_shortcode( '[formidable id="' . $form_id . '"]' );
		$document = new DOMDocument();
		$document->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING );
		$xpath  = new DOMXPath( $document );
		$groups = $xpath->query( '//select[@name="item_meta[' . $field->id . ']"]/optgroup' );
		$this->assertCount(
			1,
			$xpath->query( '//select[@name="item_meta[' . $plain_field->id . ']"]/optgroup[@label="0"]' ),
			'Groups should survive when separate values are disabled.'
		);
		$this->assertCount( 2, $groups, 'Each consecutive group should render once.' );
		$this->assertSame( 'Fruit " & <food>', $groups->item( 0 )->getAttribute( 'label' ), 'Group labels should be escaped without losing their text.' );
		$this->assertCount( 2, $groups->item( 0 )->getElementsByTagName( 'option' ), 'Both fruit options should belong to the same group.' );
		$this->assertSame( '0', $groups->item( 1 )->getAttribute( 'label' ), 'A numeric group name should be preserved.' );
		$this->assertCount( 1, $xpath->query( '//select[@name="item_meta[' . $field->id . ']"]/option[@value="plain"]' ), 'Ungrouped options should remain outside optgroups.' );

		ob_start();
		FrmOptionGroupsController::after_dropdown_options();
		$this->assertSame( '', ob_get_clean(), 'Rendering should close the last group and reset its state.' );
	}

	public function test_builder_includes_group_inputs_only_for_supported_options() {
		$field = array(
			'id'      => 123,
			'type'    => 'select',
			'options' => array( array( 'group' => 'Fruit' ) ),
		);
		ob_start();
		FrmOptionGroupsController::admin_single_opt(
			array(
				'field'   => $field,
				'opt_key' => 0,
			)
		);
		$html = ob_get_clean();
		$this->assertStringContainsString( 'name="field_options[options_123][0][group]"', $html, 'Builder inputs should submit the option group.' );
		$this->assertStringContainsString( 'value="Fruit"', $html, 'Builder inputs should restore the saved group.' );

		$field['type'] = 'checkbox';
		ob_start();
		FrmOptionGroupsController::admin_single_opt(
			array(
				'field'   => $field,
				'opt_key' => 0,
			)
		);
		$this->assertSame( '', ob_get_clean(), 'Checkbox options should not get group controls.' );
	}
}
