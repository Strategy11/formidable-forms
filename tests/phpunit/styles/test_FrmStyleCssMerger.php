<?php

/**
 * @group styles
 *
 * @covers FrmStyleCssMerger
 */
#[\PHPUnit\Framework\Attributes\Group( 'styles' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmStyleCssMerger::class )]
class test_FrmStyleCssMerger extends FrmUnitTest {

	public function test_identical_rules_are_merged() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', ".frm_style_a.with_frm_style{--color:red;}\n.frm_style_a.with_frm_style .frm_label{color:var(--color);}" );
		$merger->add_style( 'frm_style_b', ".frm_style_b.with_frm_style{--color:blue;}\n.frm_style_b.with_frm_style .frm_label{color:var(--color);}" );

		$css = $merger->get_css();

		$this->assertStringContainsString( ':is(.frm_style_a,.frm_style_b).with_frm_style .frm_label{color:var(--color);}', $css );
		$this->assertStringContainsString( '.frm_style_a.with_frm_style{--color:red;}', $css );
		$this->assertStringContainsString( '.frm_style_b.with_frm_style{--color:blue;}', $css );
		$this->assertSame( 1, substr_count( $css, '.frm_label' ) );
	}

	public function test_rules_that_differ_by_important_are_not_merged() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', '.frm_style_a.with_frm_style .frm_label{color:var(--color);}' );
		$merger->add_style( 'frm_style_b', '.frm_style_b.with_frm_style .frm_label{color:var(--color) !important;}' );
		$merger->add_style( 'frm_style_c', '.frm_style_c.with_frm_style .frm_label{color:var(--color) !important;}' );

		$css = $merger->get_css();

		$this->assertStringContainsString( '.frm_style_a.with_frm_style .frm_label{color:var(--color);}', $css );
		$this->assertStringContainsString( ':is(.frm_style_b,.frm_style_c).with_frm_style .frm_label{color:var(--color) !important;}', $css );
	}

	public function test_each_style_keeps_its_rule_order() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', '.frm_style_a .one{color:red;} .frm_style_a .two{color:blue;}' );
		$merger->add_style( 'frm_style_b', '.frm_style_b .two{color:blue;} .frm_style_b .one{color:red;}' );

		// Style b prints the rules in the other order, so only one of them can be shared
		// without reordering a style. Style b's .two stays separate, before the shared .one.
		$expected = ".frm_style_b .two{color:blue;}\n:is(.frm_style_a,.frm_style_b) .one{color:red;}\n.frm_style_a .two{color:blue;}\n";

		$this->assertSame( $expected, $merger->get_css() );
	}

	public function test_only_the_last_copy_of_an_unscoped_rule_is_kept() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', '.frm_total input{font-size:32px;} .frm_style_a .x{color:red;}' );
		$merger->add_style( 'frm_style_b', '.frm_total input{font-size:32px;} .frm_style_b .x{color:red;}' );

		$this->assertSame( 1, substr_count( $merger->get_css(), '.frm_total input{font-size:32px;}' ) );
	}

	public function test_rules_in_media_queries_are_merged_inside_the_query() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', '@media (max-width:600px){.frm_style_a .x{width:100%;}}' );
		$merger->add_style( 'frm_style_b', '@media (max-width:600px){.frm_style_b .x{width:100%;}}' );

		$this->assertSame( "@media (max-width:600px){:is(.frm_style_a,.frm_style_b) .x{width:100%;}\n}\n", $merger->get_css() );
	}

	public function test_keyframes_with_the_same_name_stay_separate_blocks() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', '@keyframes foo{0%{opacity:0;}50%{opacity:1;}100%{opacity:0;}} .frm_style_a .x{animation:foo 1s;}' );
		$merger->add_style( 'frm_style_b', '@keyframes foo{0%{opacity:0;}100%{opacity:1;}} .frm_style_b .x{animation:foo 1s;}' );

		// The last @keyframes foo block replaces the earlier one, so style b's block must not include style a's 50% frame.
		$expected  = "@keyframes foo{0%{opacity:0;}\n50%{opacity:1;}\n100%{opacity:0;}\n}\n@keyframes foo{0%{opacity:0;}\n100%{opacity:1;}\n}\n";
		$expected .= ":is(.frm_style_a,.frm_style_b) .x{animation:foo 1s;}\n";

		$this->assertSame( $expected, $merger->get_css() );
	}

	public function test_style_with_rules_in_another_style_css_is_not_merged() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', '.frm_style_a .x{color:red;} .frm_style_b .x{color:green;}' );
		$merger->add_style( 'frm_style_b', '.frm_style_b .x{color:red;}' );
		$merger->add_style( 'frm_style_c', '.frm_style_c .x{color:red;}' );

		// Style b's own rule must stay after the rule style a prints for it, so style b keeps the red color.
		$expected = ":is(.frm_style_a,.frm_style_c) .x{color:red;}\n.frm_style_b .x{color:green;}\n.frm_style_b .x{color:red;}\n";

		$this->assertSame( $expected, $merger->get_css() );
	}

	public function test_unsupported_css_is_printed_unchanged() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', '.frm_style_a .x::before{content:"{";}' );
		$merger->add_style( 'frm_style_b', '.frm_style_b .x::before{content:"{";}' );

		$this->assertSame( ".frm_style_a .x::before{content:\"{\";}\n.frm_style_b .x::before{content:\"{\";}", $merger->get_css() );
	}

	public function test_generated_stylesheet_merges_style_rules() {
		$default_style = ( new FrmStyle() )->get_default_style();
		$this->factory->post->create(
			array(
				'post_type'    => FrmStylesController::$post_type,
				'post_status'  => 'publish',
				'post_name'    => 'merge-test',
				'post_content' => FrmAppHelper::prepare_and_encode( $default_style->post_content ),
			)
		);
		wp_cache_flush();

		// Per form styles are only printed with Pro.
		add_filter( 'frm_pro_installed', '__return_true' );

		try {
			$css = $this->run_private_method(
				array( new FrmStyle(), 'get_css_content' ),
				array( FrmAppHelper::plugin_path() . '/css/custom_theme.css.php' )
			);
		} finally {
			remove_filter( 'frm_pro_installed', '__return_true' );
		}

		$both_styles = ':is(.frm_style_' . $default_style->post_name . ',.frm_style_merge-test).with_frm_style';

		$this->assertStringContainsString( $both_styles . ' .frm_form_field.frm_left_container{', $css );
		$this->assertStringNotContainsString( '.frm_style_merge-test.with_frm_style .frm_form_field.frm_left_container', $css );
	}

	public function test_merging_can_be_turned_off() {
		$merger = new FrmStyleCssMerger();
		$merger->add_style( 'frm_style_a', '.frm_style_a .x{color:red;}' );
		$merger->add_style( 'frm_style_b', '.frm_style_b .x{color:red;}' );

		add_filter( 'frm_merge_style_css', '__return_false' );

		try {
			$this->assertSame( ".frm_style_a .x{color:red;}\n.frm_style_b .x{color:red;}", $merger->get_css() );
		} finally {
			remove_filter( 'frm_merge_style_css', '__return_false' );
		}
	}
}
