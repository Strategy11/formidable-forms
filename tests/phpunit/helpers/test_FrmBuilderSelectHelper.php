<?php

/**
 * @group builder
 */
class test_FrmBuilderSelectHelper extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
		$this->set_admin_screen( 'admin.php?page=formidable' );
		$_GET['page']       = 'formidable';
		$_GET['frm_action'] = 'edit';
		$property           = new ReflectionProperty( 'FrmBuilderSelectHelper', 'templates' );
		$property->setAccessible( true );
		$property->setValue( null, array() );
	}

	public function tearDown(): void {
		unset( $_GET['page'], $_GET['frm_action'], $_POST['action'] );
		remove_filter( 'wp_doing_ajax', '__return_true' );
		parent::tearDown();
	}

	/**
	 * @param array $options
	 * @param array|string $selected
	 * @param array $attributes
	 * @param array $option_attributes
	 */
	private function render_select( $options, $selected, $attributes = array(), $option_attributes = array() ) {
		ob_start();
		FrmBuilderSelectHelper::render( $attributes + array( 'name' => 'setting' ), $options, $selected, $option_attributes );
		return ob_get_clean();
	}

	public function test_shared_list_preserves_individual_selections() {
		$options = array(
			'a' => 'First',
			'b' => 'Second',
		);
		$first   = $this->render_select( $options, 'a' );
		$second  = $this->render_select( $options, 'b' );
		$this->assertStringContainsString( 'data-frm-options=', $first );
		$this->assertStringNotContainsString( 'Second', $first );
		$this->assertStringContainsString( 'value="b" selected=', $second );
		$this->assertCount( 1, FrmBuilderSelectHelper::get_templates() );
		$this->render_select( array( 'a' => 'Filtered label' ), 'a' );
		$this->assertCount( 2, FrmBuilderSelectHelper::get_templates() );
	}

	public function test_multiple_and_empty_selections() {
		$options = array(
			''  => 'Everyone',
			'a' => 'First',
			'b' => 'Second',
		);
		$html    = $this->render_select( $options, array( '', 'b' ), array( 'multiple' => 'multiple' ) );
		$this->assertSame( 2, substr_count( $html, '<option ' ) );
		$this->assertSame( 2, substr_count( $html, ' selected=' ) );
		$empty = $this->render_select( $options, array(), array( 'multiple' => 'multiple' ) );
		$this->assertStringNotContainsString( '<option ', $empty );
	}

	public function test_missing_value_defaults_to_first_enabled_option() {
		$html = $this->render_select(
			array(
				'a' => 'Disabled',
				'b' => 'Enabled',
			),
			'missing',
			array(),
			array( 'a' => array( 'disabled' => 'disabled' ) )
		);
		$this->assertStringContainsString( 'value="b" selected=', $html );
		$this->assertStringNotContainsString( 'Disabled', $html );
	}

	public function test_disabled_choices_and_numeric_order_are_preserved() {
		$this->render_select(
			array(
				20 => 'Twenty',
				3  => 'Three',
			),
			'20',
			array(),
			array( 3 => array( 'disabled' => 'disabled' ) )
		);
		$templates = array_values( FrmBuilderSelectHelper::get_templates() );
		$this->assertSame( array( '20', '3' ), array_column( $templates[0], 'value' ) );
		$this->assertSame( 'disabled', $templates[0][1]['attributes']['disabled'] );
	}

	public function test_labels_are_escaped_and_entities_match_browser_text() {
		$html = $this->render_select( array( 'a' => '<script>&amp;lt;</script>' ), 'a' );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '&amp;lt;', $html );
		$templates = array_values( FrmBuilderSelectHelper::get_templates() );
		$this->assertSame( '<script>&lt;</script>', $templates[0][0]['label'] );
	}

	public function test_only_supported_requests_defer_options() {
		$options = array(
			'a' => 'First',
			'b' => 'Second',
		);
		unset( $_GET['frm_action'] );
		$html = $this->render_select( $options, 'a' );
		$this->assertSame( 2, substr_count( $html, '<option ' ) );
		add_filter( 'wp_doing_ajax', '__return_true' );
		$_POST['action'] = 'frm_load_field';
		$this->assertSame( 1, substr_count( $this->render_select( $options, 'a' ), '<option ' ) );
		$_POST['action'] = 'frm_insert_field';
		$this->assertSame( 2, substr_count( $this->render_select( $options, 'a' ), '<option ' ) );
	}
}
