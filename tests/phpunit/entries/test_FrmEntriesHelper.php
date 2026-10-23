<?php

/**
 * @group entries
 *
 * @covers FrmEntriesHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'entries' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmEntriesHelper::class )]
class test_FrmEntriesHelper extends FrmUnitTest {

	public function test_display_value() {
		$value         = serialize( array( 'Option 1', 'Option 2' ) );
		$field         = $this->factory->field->create_and_get(
			array(
				'form_id' => $this->factory->form->create(),
				'type'    => 'checkbox',
			)
		);
		$atts          = array();
		$display_value = FrmEntriesHelper::display_value( $value, $field, $atts );
		$this->assertIsString( $display_value );
		$this->assertSame( 'Option 1, Option 2', $display_value );
	}

	/**
	 * The Entry Key typed on one page of a multi-page form must survive to the next page.
	 */
	public function test_setup_new_vars_keeps_posted_item_key() {
		$form_id = $this->factory->form->create();
		$this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'text',
			)
		);
		$fields            = FrmField::get_all_for_form( $form_id );
		$_POST['item_key'] = 'My Typed Key';
		$values            = FrmEntriesHelper::setup_new_vars( $fields );
		$this->assertSame( 'my-typed-key', $values['item_key'] );

		$values = FrmEntriesHelper::setup_new_vars( $fields, '', true );
		$this->assertSame( '', $values['item_key'], 'A reset form must not carry the posted key.' );

		unset( $_POST['item_key'] );

		$values = FrmEntriesHelper::setup_new_vars( $fields );
		$this->assertSame( '', $values['item_key'] );
	}
}
