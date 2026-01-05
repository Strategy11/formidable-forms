<?php

/**
 * @group shortcodes
 */
class test_FrmShortcodeHelper extends FrmUnitTest {

	/**
	 * @covers FrmShortcodeHelper::get_shortcode_attribute_array
	 */
	public function test_get_shortcode_attribute_array() {
		$shortcodes = array(
			' id="x" minimize=1' => array(
				'id'       => 'x',
				'minimize' => '1',
			),
			' '                  => array(),
			''                   => array(),
		);

		foreach ( $shortcodes as $shortcode => $expected ) {
			$atts = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcode );
			$this->assertSame( $expected, $atts );
		}
	}

	/**
	 * @covers FrmShortcodeHelper::get_shortcode_tag
	 */
	public function test_get_shortcode_tag() {
		$shortcodes = array(
			'[25]',
			'[25 show=label sep=", "]',
			'[if 25 show=label]content[/if 25]',
			'[foreach 25]content[/foreach 25]',
		);

		foreach ( $shortcodes as $shortcode ) {
			preg_match_all( "/\[(if |foreach )?(\d+)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $shortcode, $matches, PREG_PATTERN_ORDER );

			$args = array();

			if ( str_contains( $shortcode, '[foreach' ) ) {
				$args['foreach'] = true;
			} elseif ( str_contains( $shortcode, '[if' ) ) {
				$args['conditional'] = true;
			}

			$this->assertNotEmpty( $matches[0][0] );
			$tag = FrmShortcodeHelper::get_shortcode_tag( $matches, 0, $args );
			$this->assertEquals( '25', $tag );
		}
	}

	/**
	 * @covers FrmShortcodeHelper::remove_inline_conditions
	 */
	public function test_remove_inline_conditions() {
		$title = 'Testing';
		$codes = array(
			array(
				'html'       => 'Before [form_name] After',
				'with_title' => 'Before ' . $title . ' After',
				'no_title'   => 'Before  After',
			),
			array(
				'html'       => 'Before [if form_name]Name: [form_name][/if form_name] After',
				'with_title' => 'Before Name: ' . $title . ' After',
				'no_title'   => 'Before  After',
			),
		);

		foreach ( $codes as $code ) {
			$with_title = $code['html'];
			FrmShortcodeHelper::remove_inline_conditions( true, 'form_name', $title, $with_title );
			$this->assertEquals( $code['with_title'], $with_title );

			$no_title = $code['html'];
			FrmShortcodeHelper::remove_inline_conditions( false, 'form_name', '', $no_title );
			$this->assertEquals( $code['no_title'], $no_title );
		}
	}
}
