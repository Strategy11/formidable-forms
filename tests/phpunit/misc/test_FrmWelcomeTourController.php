<?php

/**
 * @group welcome-tour
 *
 * @covers FrmWelcomeTourController
 */
#[\PHPUnit\Framework\Attributes\Group( 'welcome-tour' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmWelcomeTourController::class )]
class test_FrmWelcomeTourController extends FrmUnitTest {

	public function test_check_for_form_embeds() {
		$this->assertFalse( $this->check_for_form_embeds() );

		$this->factory->post->create(
			array(
				'post_content' => 'Before [formidable id=5] after',
			)
		);

		$this->assertTrue( $this->check_for_form_embeds() );
	}

	/**
	 * The Add Fields spotlight sits beside the active tab with placement "right", which covered the
	 * Field Options and Outline tabs. It now opens below the tab, so every tab stays visible.
	 */
	public function test_add_fields_spotlight_does_not_cover_the_other_tabs() {
		$previous_checklist = $this->get_private_property( 'FrmWelcomeTourController', 'checklist' );
		$previous_steps     = $this->get_private_property( 'FrmWelcomeTourController', 'steps' );

		$this->set_private_property(
			'FrmWelcomeTourController',
			'checklist',
			array(
				'active_step'     => 1,
				'active_step_key' => 'add-fields',
			)
		);
		$this->set_private_property(
			'FrmWelcomeTourController',
			'steps',
			array(
				'steps' => array(
					array( 'title' => 'Create your first form' ),
					array( 'title' => 'Add fields to your form' ),
				),
			)
		);

		$spotlight = $this->run_private_method( array( 'FrmWelcomeTourController', 'get_spotlight_data' ), array() );

		$this->set_private_property( 'FrmWelcomeTourController', 'checklist', $previous_checklist );
		$this->set_private_property( 'FrmWelcomeTourController', 'steps', $previous_steps );

		$this->assertSame( '.frm-settings-panel .frm-tabs-navs li.frm-active', $spotlight['target'] );
		$this->assertSame( 'bottom', $spotlight['placement'] ?? 'right' );
		$this->assertSame( 'middle', $spotlight['left-position'] );
	}

	/**
	 * Calls the private FrmWelcomeTourController::check_for_form_embeds method.
	 *
	 * @return bool
	 */
	private function check_for_form_embeds() {
		return $this->run_private_method( array( 'FrmWelcomeTourController', 'check_for_form_embeds' ), array() );
	}
}
