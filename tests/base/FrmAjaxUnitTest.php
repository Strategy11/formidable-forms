<?php
/**
 * @group ajax
 */
class FrmAjaxUnitTest extends WP_Ajax_UnitTestCase {

	function setUp() {
		parent::setUp();
		FrmAppController::install();

		$this->factory->form = new Form_Factory( $this );
		$this->factory->field = new Field_Factory( $this );
		$this->factory->entry = new Entry_Factory( $this );
	}
}