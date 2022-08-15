<?php

/**
 * @group forms
 */
class test_FrmFormAction extends FrmUnitTest {

	/**
	 * @covers FrmFormAction::update_callback
	 */
	public function test_update_callback() {
		$form_id               = $this->factory->form->create();
		$id_base               = 'email';
		$option_name           = 'frm_' . $id_base . '_action';
		$number                = -1;
		$new_post_id           = $this->factory->post->create(
			array(
				'post_type'    => FrmFormActionsController::$action_post_type,
				'menu_order'   => $form_id,
				'post_excerpt' => $id_base,
				'post_status'  => 'publish',
			)
		);
		$_POST[ $option_name ] = array(
			$number => array(
				'ID'          => $new_post_id,
				'post_status' => 'publish',
			),
		);
		$action                = new FrmFormAction( $id_base, 'Email' );
		$action_ids            = $action->update_callback( $form_id );

		$this->assertIsArray( $action_ids );
		$this->assertEquals( array( $new_post_id ), $action_ids );
		$this->assertTrue( $action->updated );
	}
}
