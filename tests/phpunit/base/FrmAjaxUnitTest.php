<?php

/**
 * @group ajax
 */
class FrmAjaxUnitTest extends WP_Ajax_UnitTestCase {

	protected $field_id         = 0;
	protected $user_id          = 0;
	protected $is_pro_active    = false;
	protected $contact_form_key = 'contact-with-email';

	public static function wpSetUpBeforeClass( $factory ) {
		$_POST = array();
		FrmHooksController::trigger_load_hook( 'load_ajax_hooks' );
		FrmHooksController::trigger_load_hook( 'load_form_hooks' );
	}

	public static function wpTearDownAfterClass() {
	}

	public function setUp(): void {
		parent::setUp();

		FrmHooksController::trigger_load_hook( 'load_ajax_hooks' );
		FrmHooksController::trigger_load_hook( 'load_form_hooks' );

		$this->factory        = new FrmUnitTestFactory();
		$this->factory->form  = new Form_Factory( $this );
		$this->factory->field = new Field_Factory( $this );
		$this->factory->entry = new Entry_Factory( $this );
	}

	public function set_as_user_role( $role ) {
		// create user
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		$user    = new WP_User( $user_id );
		$this->assertTrue( $user->exists(), 'Problem getting user ' . $user_id );

		// log in as user
		wp_set_current_user( $user_id );
		$this->user_id = $user_id;
		$this->assertTrue( current_user_can( $role ) );
	}

	public function trigger_action( $action ) {
		$response = '';

		try {
			$this->_handleAjax( $action );
		} catch ( WPAjaxDieStopException $e ) {
			$response = $e->getMessage();
			unset( $e );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		if ( '' === $response ) {
			$response = $this->_last_response;
		}

		return $response;
	}
}
