<?php

class test_FrmHtmlHelper extends FrmUnitTest {

	public function test_toggle_numeric_labels_are_visible_and_label_the_switch() {
		foreach ( array( 0, '0', 1, '1', '01', '1.0', '1e0' ) as $label ) {
			$html = FrmHtmlHelper::toggle(
				'number_toggle',
				'number_toggle',
				array(
					'show_labels' => true,
					'off_label'   => $label,
					'on_label'    => $label,
				)
			);

			$this->assertStringContainsString( 'aria-labelledby="number_toggle_off_label number_toggle_on_label"', $html, 'Both numeric labels should name the switch.' );
			$this->assertStringContainsString(
				'id="number_toggle_off_label" class="frm_off_label frm_toggle_opt frm-leading-none">' . $label . '</span>',
				$html,
				'The numeric off label should be visible.'
			);
			$this->assertStringContainsString(
				'id="number_toggle_on_label" class="frm_on_label frm_toggle_opt frm-leading-none">' . $label . '</span>',
				$html,
				'The numeric on label should be visible.'
			);
			$this->assertStringContainsString( 'value="' . $label . '"', $html, 'The on label should remain the default submitted value.' );
			$this->assertStringContainsString( 'data-off="' . $label . '"', $html, 'The numeric off value should be available to the toggle script.' );
		}
	}

	public function test_toggle_missing_label_preserves_default_value_and_external_name() {
		$html = FrmHtmlHelper::toggle( 'default_toggle', 'default_toggle', array( 'show_labels' => true ) );

		$this->assertStringContainsString( 'value="1"', $html, 'An omitted on label should preserve the default checkbox value.' );
		$this->assertStringContainsString( 'aria-labelledby="default_toggle_label"', $html, 'A toggle without visible labels should use its external label.' );
		$this->assertStringNotContainsString( 'id="default_toggle_on_label"', $html, 'The default checkbox value should not become a visible label.' );
	}

	public function test_toggle_empty_on_label_uses_only_visible_off_label() {
		$html = FrmHtmlHelper::toggle(
			'empty_toggle',
			'empty_toggle',
			array(
				'show_labels' => true,
				'off_label'   => 'Off',
				'on_label'    => '',
				'value'       => 'enabled',
			)
		);

		$this->assertStringContainsString( 'aria-labelledby="empty_toggle_off_label"', $html, 'Only the visible off label should name the switch.' );
		$this->assertStringNotContainsString( 'id="empty_toggle_on_label"', $html, 'An empty on label should not render a span.' );
		$this->assertStringContainsString( 'value="enabled"', $html, 'An explicit value should override the on label.' );
	}

	public function test_toggle_hidden_labels_and_explicit_accessible_name() {
		foreach ( array( false, true ) as $show_labels ) {
			$html = FrmHtmlHelper::toggle(
				'named_toggle',
				'named_toggle',
				array(
					'show_labels'     => $show_labels,
					'off_label'       => '0',
					'on_label'        => '1',
					'aria-label-attr' => '0',
				)
			);

			$this->assertStringContainsString( 'aria-label="0"', $html, 'An explicit numeric accessible name should take priority.' );
			$this->assertStringNotContainsString( 'aria-labelledby=', $html, 'An explicit accessible name should replace label references.' );

			if ( $show_labels ) {
				continue;
			}

			$this->assertStringNotContainsString( 'id="named_toggle_off_label"', $html, 'The off label should stay hidden when labels are disabled.' );
			$this->assertStringNotContainsString( 'id="named_toggle_on_label"', $html, 'The on label should stay hidden when labels are disabled.' );
		}
	}
}
