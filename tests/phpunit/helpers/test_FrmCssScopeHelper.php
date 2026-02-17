<?php

/**
 * @group helpers
 */
class test_FrmCssScopeHelper extends FrmUnitTest {

	/**
	 * @var FrmCssScopeHelper
	 */
	private $helper;

	/**
	 * @var string The CSS classname to use for the scope.
	 */
	private $scope_name = 'formidable-style-test';

	/**
	 * Set up the test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->helper = new FrmCssScopeHelper();
	}

	/**
	 * Test basic selector nesting.
	 */
	public function test_nest_basic_selector() {
		$css    = '.button { color: red; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .button', $result );
		$this->assertStringContainsString( '.button.' . $this->scope_name, $result );
	}

	/**
	 * Test nesting with multiple selectors.
	 */
	public function test_nest_multiple_selectors() {
		$css    = '.button, .link { color: blue; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .button', $result );
		$this->assertStringContainsString( '.button.' . $this->scope_name, $result );
		$this->assertStringContainsString( '.' . $this->scope_name . ' .link', $result );
		$this->assertStringContainsString( '.link.' . $this->scope_name, $result );
	}

	/**
	 * Test nesting with multiple properties.
	 */
	public function test_nest_multiple_properties() {
		$css    = '.button { color: red; background: blue; padding: 10px; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .button', $result );
		$this->assertStringContainsString( 'color: red;', $result );
		$this->assertStringContainsString( 'background: blue;', $result );
		$this->assertStringContainsString( 'padding: 10px;', $result );
	}

	/**
	 * Test nesting with descendant selectors.
	 */
	public function test_nest_descendant_selectors() {
		$css    = '.parent .child { color: green; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .parent .child', $result );
		$this->assertStringContainsString( '.parent.' . $this->scope_name . ' .child', $result );
	}

	/**
	 * Test nesting with pseudo-classes.
	 */
	public function test_nest_pseudo_classes() {
		$css    = '.button:hover { color: red; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .button:hover', $result );
	}

	/**
	 * Test nesting with pseudo-elements.
	 */
	public function test_nest_pseudo_elements() {
		$css    = '.button::before { content: "→"; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .button::before', $result );
	}

	/**
	 * Test nesting with media queries.
	 */
	public function test_nest_media_query() {
		$css    = '@media (max-width: 768px) { .button { color: red; } }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '@media (max-width: 768px)', $result );
		$this->assertStringContainsString( '.' . $this->scope_name . ' .button', $result );
	}

	/**
	 * Test nesting with keyframes (should not nest content).
	 */
	public function test_nest_keyframes() {
		$css    = '@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '@keyframes fadeIn', $result );
		$this->assertStringContainsString( 'from { opacity: 0; }', $result );
		$this->assertStringContainsString( 'to { opacity: 1; }', $result );
		// Should NOT have the scope prefix inside keyframes
		$this->assertStringNotContainsString( '.' . $this->scope_name . ' from', $result );
	}

	/**
	 * Test nesting with CSS comments.
	 */
	public function test_nest_removes_comments() {
		$css    = '/* Formidable CSS comment */ .button { color: red; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringNotContainsString( '/* Formidable CSS comment */', $result );
		$this->assertStringContainsString( '.' . $this->scope_name . ' .button', $result );
	}

	/**
	 * Test nesting with multiline CSS.
	 */
	public function test_nest_multiline_css() {
		$css    = '.button {
			color: red;
			background: blue;
			padding: 10px;
		}';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .button', $result );
		$this->assertStringContainsString( 'color: red;', $result );
	}

	/**
	 * Test nesting with attribute selectors.
	 */
	public function test_nest_attribute_selectors() {
		$css    = 'input[type="text"] { border: 1px solid #ccc; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' input[type="text"]', $result );
	}

	/**
	 * Test nesting with empty CSS.
	 */
	public function test_nest_empty_css() {
		$css    = '';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertSame( '', $result );
	}

	/**
	 * Test unnesting basic selector.
	 */
	public function test_unnest_basic_selector() {
		$css    = '.' . $this->scope_name . ' .button { color: red; }';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertStringContainsString( '.button { color: red; }', $result );
		$this->assertStringNotContainsString( '.' . $this->scope_name . ' .button', $result );
	}

	/**
	 * Test unnesting multiple selectors.
	 */
	public function test_unnest_multiple_selectors() {
		$css    = '.' . $this->scope_name . ' .button, ' . $this->scope_name . ' .link { color: blue; }';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertStringContainsString( '.button', $result );
		$this->assertStringContainsString( '.link', $result );
		$this->assertStringNotContainsString( '.' . $this->scope_name, $result );
	}

	/**
	 * Test unnesting with media queries.
	 */
	public function test_unnest_media_query() {
		$css    = '@media (max-width: 768px) { ' . $this->scope_name . ' .button { color: red; } }';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertStringContainsString( '@media (max-width: 768px)', $result );
		$this->assertStringContainsString( '.button', $result );
		$this->assertStringNotContainsString( '.' . $this->scope_name . ' .button', $result );
	}

	/**
	 * Test unnesting with non-prefixed selectors (should remain unchanged).
	 */
	public function test_unnest_non_prefixed_selector() {
		$css    = '.other-scope .button { color: red; }';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertStringContainsString( '.other-scope .button', $result );
	}

	/**
	 * Test unnesting mixed prefixed and non-prefixed selectors.
	 */
	public function test_unnest_mixed_selectors() {
		$css    = '.' . $this->scope_name . ' .button, .other .link { color: blue; }';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertStringContainsString( '.button', $result );
		$this->assertStringContainsString( '.other .link', $result );
	}

	/**
	 * Test nesting with complex nested structures.
	 */
	public function test_nest_complex_nested_structure() {
		$css    = '@media (max-width: 768px) {
			.button { color: red; }
			.link { color: blue; }
		}';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '@media (max-width: 768px)', $result );
		$this->assertStringContainsString( '.' . $this->scope_name . ' .button', $result );
		$this->assertStringContainsString( '.' . $this->scope_name . ' .link', $result );
	}

	/**
	 * Test nesting with ID selectors.
	 */
	public function test_nest_id_selector() {
		$css    = '#element-id { color: red; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' #element-id', $result );
	}

	/**
	 * Test nesting with child combinator.
	 */
	public function test_nest_child_combinator() {
		$css    = '.parent > .child { color: red; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .parent > .child', $result );
	}

	/**
	 * Test nesting with adjacent sibling combinator.
	 */
	public function test_nest_adjacent_sibling_combinator() {
		$css    = '.first + .second { margin-left: 10px; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .first + .second', $result );
	}

	/**
	 * Test nesting with general sibling combinator.
	 */
	public function test_nest_general_sibling_combinator() {
		$css    = '.first ~ .second { color: blue; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .first ~ .second', $result );
	}

	/**
	 * Test nesting with braces in strings.
	 */
	public function test_nest_with_braces_in_strings() {
		$css    = '.button::after { content: "{test}"; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' .button::after', $result );
		$this->assertStringContainsString( 'content: "{test}"', $result );
	}

	/**
	 * Test nesting with @supports rule.
	 */
	public function test_nest_supports_rule() {
		$css    = '@supports (display: grid) { .container { display: grid; } }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '@supports (display: grid)', $result );
		$this->assertStringContainsString( '.' . $this->scope_name . ' .container', $result );
	}

	/**
	 * Test with element selectors.
	 */
	public function test_nest_element_selectors() {
		$css    = 'div { margin: 0; } p { padding: 10px; }';
		$result = $this->helper->nest( $css, $this->scope_name );

		$this->assertStringContainsString( '.' . $this->scope_name . ' div', $result );
		$this->assertStringContainsString( 'div.' . $this->scope_name, $result );
		$this->assertStringContainsString( '.' . $this->scope_name . ' p', $result );
		$this->assertStringContainsString( 'p.' . $this->scope_name, $result );
	}

	/**
	 * Test unnesting with empty CSS.
	 */
	public function test_unnest_empty_css() {
		$css    = '';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertSame( '', $result );
	}

	/**
	 * Test unnesting a direct-scoped selector.
	 */
	public function test_unnest_direct_scope_selector() {
		$css    = 'h2.' . $this->scope_name . ' { color: red; }';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertStringContainsString( 'h2', $result );
		$this->assertStringNotContainsString( $this->scope_name, $result );
	}

	/**
	 * Test unnesting both descendant and direct-scoped selectors together.
	 */
	public function test_unnest_both_scope_forms() {
		$css    = '.' . $this->scope_name . ' .button,' . "\n" . '.button.' . $this->scope_name . ' { color: red; }';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertStringContainsString( '.button', $result );
		$this->assertStringNotContainsString( $this->scope_name, $result );

		// Should deduplicate: .button should appear only once in the selector.
		$count = substr_count( $result, '.button' );
		$this->assertSame( 1, $count, 'Selector should be deduplicated after unnesting both forms.' );
	}

	/**
	 * Test roundtrip: nest then unnest should return the original selector.
	 */
	public function test_nest_unnest_roundtrip() {
		$css    = 'h2 { color: red; }';
		$nested = $this->helper->nest( $css, $this->scope_name );
		$result = $this->helper->unnest( $nested, $this->scope_name );

		$this->assertStringContainsString( 'h2', $result );
		$this->assertStringContainsString( 'color: red;', $result );
		$this->assertStringNotContainsString( $this->scope_name, $result );
	}

	/**
	 * Test roundtrip with descendant selectors.
	 */
	public function test_nest_unnest_roundtrip_descendant() {
		$css    = '.parent .child { color: green; }';
		$nested = $this->helper->nest( $css, $this->scope_name );
		$result = $this->helper->unnest( $nested, $this->scope_name );

		$this->assertStringContainsString( '.parent .child', $result );
		$this->assertStringNotContainsString( $this->scope_name, $result );
	}

	/**
	 * Test unnesting a direct-scoped descendant selector.
	 */
	public function test_unnest_direct_scope_descendant() {
		$css    = '.parent.' . $this->scope_name . ' .child { color: green; }';
		$result = $this->helper->unnest( $css, $this->scope_name );

		$this->assertStringContainsString( '.parent .child', $result );
		$this->assertStringNotContainsString( $this->scope_name, $result );
	}
}
