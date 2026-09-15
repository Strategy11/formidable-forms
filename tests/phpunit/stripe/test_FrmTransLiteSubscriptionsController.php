<?php

/**
 * @group stripe
 */
class test_FrmTransLiteSubscriptionsController extends FrmUnitTest {

	/**
	 * @covers FrmTransLiteSubscriptionsController::show_cancel_link
	 */
	public function test_show_cancel_link() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$form  = $this->factory->form->create_and_get();
		$entry = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );

		$sub = (object) array(
			'id'      => 5,
			'item_id' => $entry->id,
			'status'  => 'active',
			'paysys'  => 'stripe',
		);

		ob_start();
		FrmTransLiteSubscriptionsController::show_cancel_link( $sub );
		$output = ob_get_clean();

		$this->assertTrue( property_exists( $sub, 'user_id' ) );
		$this->assertEquals( $entry->user_id, $sub->user_id );
		$this->assertNotEmpty( $output );
	}
}
