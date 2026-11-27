<?php

/**
 * @group ajax
 */
#[\PHPUnit\Framework\Attributes\Group( 'ajax' )]
class FrmAjaxUnitTest extends WP_Ajax_UnitTestCase {

	use FrmPHPUnitCompatibility;

	protected $field_id         = 0;
	protected $user_id          = 0;
	protected $is_pro_active    = false;
	protected $contact_form_key = 'contact-with-email';

	/**
	 * Narrows the inherited property to the Formidable factory so static analysis
	 * can resolve $this->factory->form, ->field and ->entry.
	 *
	 * @var FrmUnitTestFactory
	 */
	protected $factory;

	public static function wpSetUpBeforeClass( $factory ) {
		$_POST = array();
		FrmHooksController::trigger_load_hook( 'load_ajax_hooks' );
		FrmHooksController::trigger_load_hook( 'load_form_hooks' );
	}

	public static function wpTearDownAfterClass() {
	}

	/**
	 * Keep WordPress deprecation assertions working after PHPUnit 9.
	 *
	 * @return void
	 */
	public function expectDeprecated() {
		if ( version_compare( \PHPUnit\Runner\Version::id(), '10.0', '<' ) ) {
			parent::expectDeprecated();
			return;
		}

		$this->set_up_deprecation_expectations();
	}

	public function setUp(): void {
		parent::setUp();

		// CLI tests have no HTTP response on which to send admin headers.
		remove_action( 'admin_init', 'wp_admin_headers' );

		FrmHooksController::trigger_load_hook( 'load_ajax_hooks' );
		FrmHooksController::trigger_load_hook( 'load_form_hooks' );

		$this->factory        = new FrmUnitTestFactory();
		$this->factory->form  = new Form_Factory( $this );
		$this->factory->field = new Field_Factory( $this );
		$this->factory->entry = new Entry_Factory( $this );
	}

	public function set_as_user_role( $role ) {
		// Create user
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		$user    = new WP_User( $user_id );
		$this->assertTrue( $user->exists(), 'Problem getting user ' . $user_id );

		// Log in as user
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

		return '' === $response ? $this->_last_response : $response;
	}
}
