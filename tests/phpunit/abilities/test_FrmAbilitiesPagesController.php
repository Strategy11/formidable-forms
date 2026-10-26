<?php

/** Tests page publication and per-record permission boundaries through MCP abilities. */
class test_FrmAbilitiesPagesController extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
		$this->set_current_user_to_1();
		$settings      = FrmAppHelper::get_settings();
		$settings->mcp = 1;
		$settings->store();
		FrmMcpController::reset();
		WP_Ability_Categories_Registry::get_instance();
		WP_Abilities_Registry::get_instance();
		do_action( 'wp_abilities_api_categories_init' );
		do_action( 'wp_abilities_api_init' );
	}

	public function test_create_defaults_to_draft_and_preserves_formidable_shortcodes() {
		wp_get_current_user()->add_cap( 'unfiltered_html', false );
		$result = wp_get_ability( 'formidable-forms/create-page' )->execute(
			array(
				'title'   => 'Connected experience',
				'content' => '<p>Explore the examples.</p>[display-frm-data id="42"]<script>alert(1)</script>',
			)
		);
		$this->assertNotWPError( $result, 'Editors should be able to create a draft page.' );
		$this->assertSame( 'draft', $result['result']['status'], 'New pages must default to draft.' );
		$this->assertStringContainsString( '[display-frm-data id="42"]', $result['result']['content'], 'Native shortcodes must survive page creation.' );
		$this->assertStringNotContainsString( '<script>', $result['result']['content'], 'Page content must be sanitized.' );
		$updated = wp_get_ability( 'formidable-forms/update-page' )->execute(
			array(
				'id'     => $result['result']['id'],
				'status' => 'publish',
			)
		);
		$this->assertNotWPError( $updated, 'A publisher should be able to publish their page.' );
		$this->assertSame( 'publish', $updated['result']['status'], 'The requested publication state must be saved.' );
	}

	public function test_page_updates_preserve_styles_only_with_unfiltered_html_permission() {
		$content = '<style>.example { color: red; }</style><p class="example">Original</p>';
		$result  = wp_get_ability( 'formidable-forms/create-page' )->execute(
			array(
				'title'   => 'Styled page',
				'content' => $content,
			)
		);
		$this->assertNotWPError( $result );
		$this->assertSame( $content, $result['result']['content'] );

		$content = str_replace( 'Original', 'Updated', $content );
		$result  = wp_get_ability( 'formidable-forms/update-page' )->execute(
			array(
				'id'      => $result['result']['id'],
				'content' => $content,
			)
		);
		$this->assertNotWPError( $result );
		$this->assertSame( $content, $result['result']['content'] );

		wp_get_current_user()->add_cap( 'unfiltered_html', false );
		$result = wp_get_ability( 'formidable-forms/update-page' )->execute(
			array(
				'id'      => $result['result']['id'],
				'content' => $content,
			)
		);
		$this->assertNotWPError( $result );
		$this->assertStringNotContainsString( '<style>', $result['result']['content'] );
	}

	public function test_publication_and_reads_enforce_capabilities() {
		$private = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
				'post_author' => get_current_user_id(),
			)
		);
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		$user    = new WP_User( $user_id );
		$user->add_cap( 'publish_pages', false );
		$user->add_cap( 'edit_others_pages', false );
		wp_set_current_user( $user_id );
		$this->assertWPError(
			wp_get_ability( 'formidable-forms/create-page' )->execute(
				array(
					'title'  => 'Must remain unpublished',
					'status' => 'publish',
				)
			),
			'A user without publishing permission must not publish a page.'
		);
		$this->assertWPError( wp_get_ability( 'formidable-forms/get-page' )->execute( array( 'id' => $private ) ), 'A user must not read a private page they cannot edit.' );
		$this->assertWPError(
			wp_get_ability( 'formidable-forms/update-page' )->execute(
				array(
					'id'    => $private,
					'title' => 'Unauthorized change',
				)
			),
			'A user must not update another author\'s protected page.'
		);
		wp_set_current_user( 0 );
		$this->assertWPError( wp_get_ability( 'formidable-forms/list-pages' )->execute( array() ), 'Anonymous users must not inspect unpublished pages.' );
	}

	public function test_page_operations_reject_other_post_types_and_unknown_properties() {
		$post_id = self::factory()->post->create();
		$this->assertWPError(
			wp_get_ability( 'formidable-forms/update-page' )->execute(
				array(
					'id'    => $post_id,
					'title' => 'Wrong type',
				)
			),
			'Page operations must not modify ordinary posts.'
		);
		$this->assertWPError(
			wp_get_ability( 'formidable-forms/create-page' )->execute(
				array(
					'title'       => 'Unexpected settings',
					'post_author' => 999,
				)
			),
			'Clients must not bypass the explicit input schema.'
		);
		$meta = wp_get_ability( 'formidable-forms/list-pages' )->get_meta();
		$this->assertTrue( $meta['annotations']['readonly'], 'Discovery abilities must declare that they are read only.' );
	}
	public function test_subscriber_creation_rejects_role_escalation_and_unauthorized_callers() {
		$user = wp_get_current_user();
		$user->add_cap( 'create_users' );
		$user->add_cap( 'frm_edit_entries' );
		$ability = wp_get_ability( 'formidable-forms/create-subscriber' );
		$this->assertWPError(
			$ability->execute(
				array(
					'username' => 'mcp-privileged',
					'email'    => 'privileged@example.com',
					'role'     => 'administrator',
				)
			)
		);
		$result = $ability->execute(
			array(
				'username'   => 'mcp-subscriber',
				'email'      => 'subscriber@example.com',
				'first_name' => 'Maya',
				'last_name'  => 'Chen',
			)
		);
		$this->assertNotWPError( $result );
		$user = get_userdata( $result['result']['id'] );
		$this->assertSame( array( 'subscriber' ), $user->roles );
		$this->assertArrayNotHasKey( 'user_pass', $result['result'] );
		wp_set_current_user( $user->ID );
		$this->assertWPError(
			$ability->execute(
				array(
					'username' => 'mcp-not-allowed',
					'email'    => 'not-allowed@example.com',
				)
			)
		);
	}

	public function test_uploads_reject_invalid_bytes_and_disallowed_extensions() {
		$ability = wp_get_ability( 'formidable-forms/upload-media' );
		$this->assertWPError(
			$ability->execute(
				array(
					'filename'       => 'sample.jpg',
					'content_base64' => 'invalid base64!',
				)
			)
		);
		$this->assertWPError(
			$ability->execute(
				array(
					'filename'       => 'sample.php',
					'content_base64' => base64_encode( '<?php echo 1;' ),
				)
			)
		);
		$this->assertWPError(
			$ability->execute(
				array(
					'filename'       => 'sample.png',
					'content_base64' => base64_encode( 'This is not an image.' ),
				)
			)
		);
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertWPError(
			$ability->execute(
				array(
					'filename'       => 'sample.png',
					'content_base64' => base64_encode( 'sample' ),
				)
			)
		);
	}

	public function test_offline_records_require_a_submitted_entry_and_cannot_select_a_gateway() {
		$ability = wp_get_ability( 'formidable-forms/record-offline-payment' );
		$this->assertWPError(
			$ability->execute(
				array(
					'entry_id'  => 999999,
					'amount'    => 10,
					'reference' => 'OFFLINE-1',
				)
			)
		);
		$this->assertWPError(
			$ability->execute(
				array(
					'entry_id'  => 1,
					'amount'    => 10,
					'reference' => 'OFFLINE-1',
					'paysys'    => 'stripe',
				)
			)
		);
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertWPError(
			$ability->execute(
				array(
					'entry_id'  => 1,
					'amount'    => 10,
					'reference' => 'OFFLINE-1',
				)
			)
		);
	}
	public function test_offline_payments_keep_native_currency_without_a_gateway_action() {
		wp_get_current_user()->add_cap( 'frm_edit_entries' );
		$form_id  = $this->factory->form->create();
		$entry_id = FrmEntry::create( array( 'form_id' => $form_id ) );
		$result   = wp_get_ability( 'formidable-forms/record-offline-payment' )->execute(
			array(
				'entry_id'  => (int) $entry_id,
				'amount'    => 28.50,
				'reference' => 'MCP-OFFLINE-CAD',
				'currency'  => 'CAD',
				'test'      => true,
			)
		);
		$this->assertNotWPError( $result );
		$model   = new FrmTransLitePayment();
		$payment = $model->get_one( $result['result']['id'] );
		$this->assertSame( 'manual', $payment->paysys );
		$this->assertSame( array( 'currency' => 'CAD' ), FrmTransLiteAppHelper::get_action_settings( array( 'payment' => $payment ) ) );

		foreach ( array( array( 'currency' => 'CAD' ), serialize( array( 'currency' => 'CAD' ) ) ) as $meta ) {
			$this->assertSame(
				array( 'currency' => 'CAD' ),
				FrmTransLiteAppHelper::get_action_settings(
					array(
						'payment' => array(
							'paysys'     => 'manual',
							'meta_value' => $meta,
						),
					)
				)
			);
		}
		$this->assertSame(
			array(),
			FrmTransLiteAppHelper::get_action_settings(
				array(
					'payment' => array(
						'paysys'     => 'manual',
						'meta_value' => array( 'currency' => 'ZZZ' ),
					),
				)
			)
		);
		$this->assertSame(
			array(),
			FrmTransLiteAppHelper::get_action_settings(
				array(
					'payment' => array(
						'paysys'     => 'stripe',
						'meta_value' => array( 'currency' => 'CAD' ),
					),
				)
			)
		);
	}
}
