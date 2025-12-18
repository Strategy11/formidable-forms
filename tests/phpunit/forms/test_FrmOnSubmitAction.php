<?php

/**
 * @group forms
 */
class test_FrmOnSubmitAction extends FrmUnitTest {

	public $factory;
	public function test_adding_sanitize_url_after_updating() {
		$form_id     = $this->factory->form->create();
		$field_id    = $this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'text',
			)
		);
		$id_base     = 'on_submit';
		$option_name = 'frm_' . $id_base . '_action';
		$number      = -1;

		$action_id = $this->factory->post->create(
			array(
				'post_type'    => FrmFormActionsController::$action_post_type,
				'menu_order'   => $form_id,
				'post_excerpt' => $id_base,
			)
		);

		$_POST[ $option_name ] = array(
			$number => array(
				'ID'           => $action_id,
				'post_status'  => 'publish',
				'post_title'   => 'Confirmation',
				'post_content' => array(
					'success_action' => 'redirect',
					'success_url'    => 'https://example.com/?param=[' . $field_id . ']',
				),
			),
		);

		$action = new FrmOnSubmitAction();
		$action->update_callback( $form_id );

		$updated_action = get_post( $action_id );
		$post_content   = (array) FrmAppHelper::maybe_json_decode( $updated_action->post_content );

		$this->assertFalse( empty( $post_content['success_url'] ) );
		$this->assertEquals( 'https://example.com/?param=[' . $field_id . ' sanitize_url=1]', $post_content['success_url'] );
	}
}
