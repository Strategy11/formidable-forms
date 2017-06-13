<?php
/**
 * @since 2.03.08
 *
 * @group views
 * @group pro
 * @group view-shortcodes
 * @group conditional-shortcodes
 */
class WP_Test_Conditional_Shortcodes extends FrmUnitTest {

	/**
	 * Test [if x greater_than="-1"]...[/if x] where x is 0
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group zero-conditional
	 */
	public function test_zero_greater_than_negative_value() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '0';
		$compare_type = 'greater_than';
		$compare_to = '-1';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals="0"]...[/if x] where x is 0
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group zero-conditional
	 */
	public function test_zero_equals_zero() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '0';
		$compare_type = 'equals';
		$compare_to = '0';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}


	/**
	 * Test [if x not_equal="0"]...[/if x] where x is 0
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group zero-conditional
	 */
	public function test_zero_not_equal_zero() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '0';
		$compare_type = 'not_equal';
		$compare_to = '0';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x equals=""]...[/if x] where x is 0
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group zero-conditional
	 */
	public function test_zero_equals_blank() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '0';
		$compare_type = 'equals';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x]...[/if x] where x is 0
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group zero-conditional
	 */
	public function test_zero() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '0';

		$opening_tag = '[if ' .  $field->id . ']';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( '', '', $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x less_than="1"]...[/if x] where x is 0
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group zero-conditional
	 */
	public function test_zero_less_than_1() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '0';
		$compare_type = 'less_than';
		$compare_to = '1';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x greater_than="1"]...[/if x] where x is 0
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group zero-conditional
	 */
	public function test_zero_greater_than_1() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '0';
		$compare_type = 'greater_than';
		$compare_to = '1';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x greater_than="-1"]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group zero-conditional
	 */
	public function test_one_greater_than_negative_value() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';
		$compare_type = 'greater_than';
		$compare_to = '-1';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals="1"]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_one_equals_one() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';
		$compare_type = 'equals';
		$compare_to = '1';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals="0"]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_one_equals_zero() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';
		$compare_type = 'equals';
		$compare_to = '0';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x like="0"]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_one_like_zero() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';
		$compare_type = 'like';
		$compare_to = '0';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x not_equal="0"]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_one_not_equal_to_zero() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';
		$compare_type = 'not_equal';
		$compare_to = '0';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 */
	public function test_one() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';

		$opening_tag = '[if ' .  $field->id . ']';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( '', '', $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x less_than="2"]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_one_less_than_two() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';
		$compare_type = 'less_than';
		$compare_to = '2';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x greater_than="0"]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 */
	public function test_one_greater_than_zero() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';
		$compare_type = 'greater_than';
		$compare_to = '0';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x less_than="0"]...[/if x] where x is 1
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_one_less_than_zero() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '1';
		$compare_type = 'less_than';
		$compare_to = '0';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', array( 'field' => $field ) );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x greater_than="1"]...[/if x] where x is 5
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 */
	public function test_five_greater_than_one() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '5';
		$compare_type = 'greater_than';
		$compare_to = '1';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x less_than="1"]...[/if x] where x is 5
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_five_less_than_one() {
		$field = FrmField::getOne( 'number-field' );

		$field_value = '5';
		$compare_type = 'less_than';
		$compare_to = '1';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', array( 'field' => $field ) );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x]...[/if x] where x is a Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'cali_entry' );

		$opening_tag = '[if ' .  $field->id . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steph_entry( '', '', $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals="value"]...[/if x] where x is Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_equals_value_true() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'cali_entry' );
		$compare_type = 'equals';
		$compare_to = 'California';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steph_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals="value"]...[/if x] where x is Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_display_value_equals_value_true() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = 'California';
		$compare_type = 'equals';
		$compare_to = 'California';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steph_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals="value"]...[/if x] where x is Dynamic field and statement is false
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_equals_value_false() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'cali_entry' );
		$compare_type = 'equals';
		$compare_to = 'Utah';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steph_entry( $compare_type, $compare_to, $opening_tag );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x show="field_key"]...[/if x] where x is Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_show_field_false() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'cali_entry' );

		$opening_tag = '[if ' .  $field->id . ' show=extra-state-info"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steph_entry( '', '', $opening_tag, 'extra-state-info' );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x show="field_key"]...[/if x] where x is Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_show_field_true() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'utah_entry' );

		$opening_tag = '[if ' .  $field->id . '  show=extra-state-info"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steve_entry( '', '', $opening_tag, 'extra-state-info' );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x show="field_key" equals="Deseret"]...[/if x] where x is Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_show_field_equals_value_true() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'utah_entry' );
		$compare_type = 'equals';
		$compare_to = 'Deseret';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . ' show=extra-state-info"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steve_entry( $compare_type, $compare_to, $opening_tag, 'extra-state-info' );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x show="field_key" equals="Deseret"]...[/if x] where x is Dynamic field, conditional is false
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_show_field_equals_value_false() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'cali_entry' );
		$compare_type = 'equals';
		$compare_to = 'Deseret';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . ' show=extra-state-info"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steph_entry( $compare_type, $compare_to, $opening_tag, 'extra-state-info' );

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x show="field_key" show_info="field_key" equals="Deseret"]...[/if x] where x is Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_show_info_field_equals_value_true() {
		// TODO: make this work with field keys
		$field = FrmField::getOne( 'dynamic-city' );
		$state_info_id = FrmField::get_id_by_key( 'extra-state-info' );
		$state_field_id_in_city_form = FrmField::get_id_by_key( 'dynamic-state-level-1' );

		$field_value = FrmEntry::get_id_by_key( 'provo-entry' );
		$compare_type = 'equals';
		$compare_to = 'Deseret';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to;
		$opening_tag .= ' show=' . $state_field_id_in_city_form . ' show_info=' . $state_info_id . ']';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steph_entry( $compare_type, $compare_to, $opening_tag, $state_field_id_in_city_form );
		$atts['show_info'] = $state_info_id;

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x show="field_key" show_info="field_key" equals="FakeValue"]...[/if x] where x is Dynamic field, conditional is false
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::conditional_replace_with_value
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_show_info_field_equals_value_false() {
		// TODO: make this work with field keys
		$field = FrmField::getOne( 'dynamic-city' );
		$state_info_id = FrmField::get_id_by_key( 'extra-state-info' );
		$state_field_id_in_city_form = FrmField::get_id_by_key( 'dynamic-state-level-1' );

		$field_value = FrmEntry::get_id_by_key( 'provo-entry' );
		$compare_type = 'equals';
		$compare_to = 'FakeValue';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to;
		$opening_tag .= ' show=' . $state_field_id_in_city_form . ' show_info=' . $state_info_id . ']';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steph_entry( $compare_type, $compare_to, $opening_tag, $state_field_id_in_city_form );
		$atts['show_info'] = $state_info_id;

		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x equals=""]...[/if x] where x is a Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_equals_blank_true() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = '';
		$compare_type = 'equals';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals=""]...[/if x] where x is a Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_equals_blank_false() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'cali_entry' );
		$compare_type = 'equals';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steve_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x not_equal=""]...[/if x] where x is a Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_not_equal_blank_true() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = FrmEntry::get_id_by_key( 'cali_entry' );
		$compare_type = 'not_equal';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steve_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x not_equal=""]...[/if x] where x is a Dynamic field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group dynamic-field-conditional
	 */
	public function test_dynamic_field_not_equal_blank_false() {
		$field = FrmField::getOne( 'dynamic-state' );

		$field_value = '';
		$compare_type = 'not_equal';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x equals=""]...[/if x] where x is a Hidden field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_hidden_field_equals_blank_true() {
		$field = FrmField::getOne( 'hidden-field' );

		$field_value = '';
		$compare_type = 'equals';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals=""]...[/if x] where x is a Text field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_value_equals_blank() {
		$field = FrmField::getOne( 'text-field' );

		$field_value = 'Jamie';
		$compare_type = 'equals';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steve_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x not_equal=""]...[/if x] where x is a Text field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_value_not_equal_blank() {
		$field = FrmField::getOne( 'text-field' );

		$field_value = 'Jamie';
		$compare_type = 'not_equal';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steve_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x not_like="fakeValue"]...[/if x] where x is a Hidden field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group not-like-conditional
	 */
	public function test_blank_not_like_value() {
		$field = FrmField::getOne( 'hidden-field' );

		$field_value = '';
		$compare_type = 'not_like';
		$compare_to = 'fakeValue';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x not_equal=""]...[/if x] where x is a Hidden field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_blank_not_equal_blank() {
		$field = FrmField::getOne( 'hidden-field' );

		$field_value = '';
		$compare_type = 'not_equal';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x not_equal="value"]...[/if x] where x is a Hidden field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_blank_not_equal_to_value() {
		$field = FrmField::getOne( 'hidden-field' );

		$field_value = '';
		$compare_type = 'not_equal';
		$compare_to = 'FakeValue';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x like=""]...[/if x] where x is a Text field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_value_like_blank() {
		$field = FrmField::getOne( 'text-field' );

		$field_value = 'Jamie';
		$compare_type = 'like';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x like="Steve"]...[/if x] where x is a Text field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 */
	public function test_value_like_non_matching_value() {
		$field = FrmField::getOne( 'text-field' );

		$field_value = 'Jamie';
		$compare_type = 'like';
		$compare_to = 'Steve';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x not_like=""]...[/if x] where x is a Text field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group not-like-conditional
	 */
	public function test_value_not_like_blank() {
		$field = FrmField::getOne( 'text-field' );

		$field_value = 'Jamie';
		$compare_type = 'not_like';
		$compare_to = '';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x not_like="J"]...[/if x] where x is a Text field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group not-like-conditional
	 */
	public function test_value_not_like_substring() {
		$field = FrmField::getOne( 'text-field' );

		$field_value = 'Jamie';
		$compare_type = 'not_like';
		$compare_to = 'J';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x not_like="J"]...[/if x] where x is a Text field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group not-like-conditional
	 */
	public function test_value_not_like_substring_case_difference() {
		$field = FrmField::getOne( 'text-field' );

		$field_value = 'Jamie';
		$compare_type = 'not_like';
		$compare_to = 'j';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}


	/**
	 * Test [if x like="Uncategorized"]...[/if x] where x is a Category field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group category-conditionals
	 * TODO: equals test fails
	 */
	public function test_category_field_like_value_true() {
		$field = FrmField::getOne( 'parent-dynamic-taxonomy' );

		$cat_id = get_cat_ID( 'Uncategorized' );
		$field_value = '<a href="' . get_category_link( $cat_id ) . '" title="Uncategorized">Uncategorized</a>';
		$compare_type = 'like';
		$compare_to = 'Uncategorized';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_post_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x not_equal="Uncategorized"]...[/if x] where x is a Category field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group category-conditionals
	 */
	public function test_category_field_not_equal_value_true() {
		$field = FrmField::getOne( 'parent-dynamic-taxonomy' );

		$cat_id = get_cat_ID( 'Uncategorized' );
		$field_value = '<a href="' . get_category_link( $cat_id ) . '" title="Uncategorized">Uncategorized</a>';
		$compare_type = 'not_equal';
		$compare_to = 'FakeValue';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_post_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals="current"]...[/if x] where x is a UserID field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group user-id-conditionals
	 */
	public function test_user_id_equals_current_true() {
		$field = FrmField::getOne( 'user-id-field' );
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );
		$user_id = $entry->metas[ $field->id ];

		wp_set_current_user( $user_id );

		$field_value = $user_id;
		$compare_type = 'equals';
		$compare_to = 'current';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x not_equal="current"]...[/if x] where x is a UserID field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group user-id-conditionals
	 */
	public function test_user_id_not_equal_current_false() {
		$field = FrmField::getOne( 'user-id-field' );
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );
		$user_id = $entry->metas[ $field->id ];

		wp_set_current_user( $user_id );

		$field_value = $user_id;
		$compare_type = 'not_equal';
		$compare_to = 'current';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x equals="1"]...[/if x] where x is a UserID field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group user-id-conditionals
	 */
	public function test_user_id_equals_specific_user_true() {
		$field = FrmField::getOne( 'user-id-field' );
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$field_value = $compare_to = $entry->metas[ $field->id ];
		$compare_type = 'equals';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x equals="3"]...[/if x] where x is a UserID field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group user-id-conditionals
	 */
	public function test_user_id_equals_specific_user_false() {
		$field = FrmField::getOne( 'user-id-field' );
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$field_value = $entry->metas[ $field->id ];
		$compare_type = 'equals';
		$compare_to = $this->get_user_by_role( 'subscriber' )->ID;

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if updated_by equals="current"]...[/if x] where x is a UserID field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group updated-by-conditionals
	 */
	public function test_updated_by_equals_current_true() {
		$field = FrmField::getOne( 'user-id-field' );
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );
		$user_id = $entry->metas[ $field->id ];
		wp_set_current_user( $user_id );

		$tag = 'updated_by';
		$compare_type = 'equals';
		$compare_to = 'current';

		$opening_tag = '[if ' .  $tag . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $tag . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		FrmProContent::check_conditional_shortcode( $content, $user_id, $atts, $tag, 'if' );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if updated_by equals="current"]...[/if x] where x is a UserID field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group updated-by-conditionals
	 */
	public function test_updated_by_equals_current_false() {
		$user = $this->get_user_by_role( 'subscriber' );
		wp_set_current_user( $user->ID );

		$actual_id = 1;

		$tag = 'updated_by';
		$compare_type = 'equals';
		$compare_to = 'current';

		$opening_tag = '[if ' .  $tag . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $tag . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );

		FrmProContent::check_conditional_shortcode( $content, $actual_id, $atts, $tag, 'if' );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if created_at greater_than="2010-01-01"]...[/if x]
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group created-at-conditionals
	 */
	public function test_created_at_greater_than_true() {
		$tag = 'created_at';
		$value = '2016-01-01 00:00:00';
		$compare_type = 'greater_than';
		$compare_to = '2010-01-01';

		$opening_tag = '[if ' .  $tag . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $tag . '] After';

		$atts = $this->package_atts_for_created_at_comparison( $compare_type, $compare_to, $opening_tag );

		FrmProContent::check_conditional_shortcode( $content, $value, $atts, $tag, 'if' );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if created_at less_than="NOW"]...[/if x]
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group created-at-conditionals
	 */
	public function test_created_at_less_than_dynamic_value_true() {
		$tag = 'created_at';
		$value = '2016-01-01 00:00:00';
		$compare_type = 'less_than';
		$compare_to = 'NOW';

		$opening_tag = '[if ' .  $tag . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $tag . '] After';

		$atts = $this->package_atts_for_created_at_comparison( $compare_type, $compare_to, $opening_tag );

		FrmProContent::check_conditional_shortcode( $content, $value, $atts, $tag, 'if' );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if created_at less_than="-1 month"]...[/if x]
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group created-at-conditionals
	 */
	public function test_created_at_greater_than_dynamic_value_false() {
		$tag = 'created_at';
		$value = '2016-01-01 00:00:00';
		$compare_type = 'greater_than';
		$compare_to = '-1 month';

		$opening_tag = '[if ' .  $tag . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $tag . '] After';

		$atts = $this->package_atts_for_created_at_comparison( $compare_type, $compare_to, $opening_tag );

		FrmProContent::check_conditional_shortcode( $content, $value, $atts, $tag, 'if' );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x greater_than="2015-08-15"]...[/if x] where x is a date field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group date-conditionals
	 */
	public function test_date_greater_than_true() {
		$field = FrmField::getOne( 'date-field' );

		$field_value = '2015-08-16';
		$compare_type = 'greater_than';
		$compare_to = '2015-08-15';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x greater_than="2015-08-15"]...[/if x] where x is a date field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group date-conditionals
	 */
	public function test_date_greater_than_false() {
		$field = FrmField::getOne( 'date-field' );

		$field_value = '2015-08-16';
		$compare_type = 'greater_than';
		$compare_to = '2015-08-17';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x greater_than="12:00 AM"]...[/if x] where x is a time field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group time-conditionals
	 */
	public function test_time_greater_than_true() {
		$field = FrmField::getOne( 'time-field' );

		$field_value = '12:30 AM';
		$compare_type = 'greater_than';
		$compare_to = '12:00 AM';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x greater_than="2:00 AM"]...[/if x] where x is a time field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group time-conditionals
	 */
	public function test_time_greater_than_false() {
		$field = FrmField::getOne( 'time-field' );

		$field_value = '12:30 AM';
		$compare_type = 'greater_than';
		$compare_to = '2:00 AM';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	/**
	 * Test [if x less_than="9:00"]...[/if x] where x is a time field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group time-conditionals
	 */
	public function test_time_less_than_true() {
		$field = FrmField::getOne( 'time-field' );

		$field_value = '8:00 AM';
		$compare_type = 'less_than';
		$compare_to = '9:00';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Show me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steve_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_true( $content );
	}

	/**
	 * Test [if x less_than="4:00"]...[/if x] where x is a time field
	 *
	 * @since 2.03.08
	 *
	 * @covers FrmProContent::check_conditional_shortcode
	 *
	 * @group time-conditionals
	 */
	public function test_time_less_than_false() {
		$field = FrmField::getOne( 'time-field' );

		$field_value = '8:00 AM';
		$compare_type = 'less_than';
		$compare_to = '4:00';

		$opening_tag = '[if ' .  $field->id . ' ' . $compare_type . '="' . $compare_to . '"]';
		$content = 'Before ' . $opening_tag . 'Hide me[/if ' . $field->id . '] After';

		$atts = $this->package_atts_for_steve_entry( $compare_type, $compare_to, $opening_tag );
		$tag = $field->id;
		$args = array( 'field' => $field );

		FrmProContent::check_conditional_shortcode( $content, $field_value, $atts, $tag, 'if', $args );

		$this->assert_conditional_is_false( $content );
	}

	private function package_atts_for_jamie_entry( $compare_type, $compare_to, $opening_tag, $show = '' ) {
		$atts = array(
			'entry_id' => FrmEntry::get_id_by_key( 'jamie_entry_key_2' ),
			'entry_key' => 'jamie_entry_key_2',
		);

		$this->add_similar_atts( $compare_type, $compare_to, $opening_tag, $show, $atts );

		return $atts;

	}

	private function package_atts_for_created_at_comparison( $compare_type, $compare_to, $opening_tag ) {
		$atts = array(
			'format' => 'F j, Y',
			'short_key' => $opening_tag,
		);

		if ( $compare_type !== '' ) {
			$atts[ $compare_type ] = $compare_to;
		}

		return $atts;
	}

	private function package_atts_for_steph_entry( $compare_type, $compare_to, $opening_tag, $show = '' ) {
		$atts = array(
			'entry_id' => FrmEntry::get_id_by_key( 'steph_entry_key' ),
			'entry_key' => 'steph_entry_key',
		);

		$this->add_similar_atts( $compare_type, $compare_to, $opening_tag, $show, $atts );

		return $atts;

	}

	private function package_atts_for_steve_entry( $compare_type, $compare_to, $opening_tag, $show = '' ) {
		$atts = array(
			'entry_id' => FrmEntry::get_id_by_key( 'steve_entry_key' ),
			'entry_key' => 'steve_entry_key',
		);

		$this->add_similar_atts( $compare_type, $compare_to, $opening_tag, $show, $atts );

		return $atts;

	}

	private function package_atts_for_post_entry( $compare_type, $compare_to, $opening_tag, $show = '' ) {
		$atts = array(
			'entry_id' => FrmEntry::get_id_by_key( 'post-entry-1' ),
			'entry_key' => 'post-entry-1',
		);

		$this->add_similar_atts( $compare_type, $compare_to, $opening_tag, $show, $atts );

		return $atts;

	}

	private function add_similar_atts( $compare_type, $compare_to, $opening_tag, $show, &$atts ) {
		$atts['post_id'] = '0';
		$atts['short_key'] = $opening_tag;

		if ( $compare_type !== '' ) {
			$atts[ $compare_type ] = $compare_to;
		}

		if ( $show !== '' ) {
			$atts['show'] = $show;
		}
	}

	private function assert_conditional_is_true( $content ) {
		$this->assertSame( 'Before Show me After', $content );
	}

	private function assert_conditional_is_false( $content ) {
		$this->assertSame( 'Before  After', $content );
	}

}