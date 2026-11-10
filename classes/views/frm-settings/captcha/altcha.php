<?php
/**
 * ALTCHA settings panel on the Captcha tab of Global Settings.
 *
 * Markup follows Lite's captcha panels, so it hides and shows with the captcha type tabs.
 *
 * @package Formidable
 *
 * @var bool                                                                     $is_active Whether ALTCHA is the selected captcha type.
 * @var array $settings Saved ALTCHA settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$panel_attrs = array(
	'id'              => 'altcha_settings',
	'class'           => $is_active ? '' : 'frm_hidden',
	'role'            => 'tabpanel',
	'aria-labelledby' => 'frm-altcha-tab',
);

$hide_footer_attrs = array(
	'type'  => 'checkbox',
	'name'  => 'frm_altcha[hide_footer]',
	'id'    => 'frm_altcha_hide_footer',
	'value' => '1',
);

if ( 1 === $settings['hide_footer'] ) {
	$hide_footer_attrs['checked'] = 'checked';
}

$hide_logo_attrs = array(
	'type'  => 'checkbox',
	'name'  => 'frm_altcha[hide_logo]',
	'id'    => 'frm_altcha_hide_logo',
	'value' => '1',
);

if ( 1 === $settings['hide_logo'] ) {
	$hide_logo_attrs['checked'] = 'checked';
}
?>
<div<?php FrmAppHelper::array_to_html_params( $panel_attrs, true ); ?>>
	<details class="frm-spam-disclosure frm-mb-sm" open>
		<summary>
			<svg class="frmsvg frm-spam-disclosure-chevron" aria-hidden="true" focusable="false"><use href="#frm_arrowdown6_icon"></use></svg>
			<?php esc_html_e( 'ALTCHA settings', 'formidable' ); ?>
		</summary>
		<p class="howto">
			<?php esc_html_e( 'ALTCHA stops spam bots with a small proof-of-work puzzle that the visitor\'s browser solves in the background. It runs entirely on this site, with no keys, no third-party service, no cookies and no tracking.', 'formidable' ); ?>
		</p>
		<div class="frm_grid_container">
			<p class="frm6 frm_form_field">
				<label for="frm_altcha_display"><?php esc_html_e( 'Display mode', 'formidable' ); ?></label>
				<select name="frm_altcha[display]" id="frm_altcha_display">
					<?php foreach ( FrmAltchaHelper::display_modes() as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['display'], $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</p>
			<p class="frm6 frm_form_field">
				<label for="frm_altcha_widget_type"><?php esc_html_e( 'Verification control', 'formidable' ); ?></label>
				<select name="frm_altcha[widget_type]" id="frm_altcha_widget_type">
					<?php foreach ( FrmAltchaHelper::widget_types() as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['widget_type'], $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</p>

			<p class="frm6 frm_form_field">
				<label for="frm_altcha_difficulty">
					<?php esc_html_e( 'Difficulty', 'formidable' ); ?>
					<?php FrmAppHelper::tooltip_icon( __( 'How much work each visitor\'s browser does before the form can be submitted. Higher levels cost bots more, and take a few seconds longer on older phones.', 'formidable' ), array( 'data-container' => 'body' ) ); ?>
				</label>
				<select name="frm_altcha[difficulty]" id="frm_altcha_difficulty">
					<?php foreach ( FrmAltchaHelper::difficulty_labels() as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['difficulty'], $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</p>

			<p class="frm6 frm_form_field">
				<label for="frm_altcha_expires">
					<?php esc_html_e( 'Challenge expires after (minutes)', 'formidable' ); ?>
					<?php FrmAppHelper::tooltip_icon( __( 'How long a solved puzzle stays valid. A visitor who takes longer than this to submit gets a new puzzle automatically. Between 1 and 1440 minutes.', 'formidable' ), array( 'data-container' => 'body' ) ); ?>
				</label>
				<input class="widefat" type="number" name="frm_altcha[expires]" id="frm_altcha_expires" min="1" max="1440" step="1" value="<?php echo esc_attr( (string) $settings['expires'] ); ?>" />
			</p>

			<p class="frm_form_field">
				<label for="frm_altcha_auto">
					<?php esc_html_e( 'Start verifying', 'formidable' ); ?>
					<?php FrmAppHelper::tooltip_icon( __( 'When the browser starts solving the puzzle. Whatever you choose, an unsolved puzzle is finished when the form is submitted.', 'formidable' ), array( 'data-container' => 'body' ) ); ?>
				</label>
				<select name="frm_altcha[auto]" id="frm_altcha_auto">
					<?php foreach ( FrmAltchaHelper::auto_modes() as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['auto'], $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</p>

			<p class="frm_form_field">
				<label for="frm_altcha_hide_logo">
					<input<?php FrmAppHelper::array_to_html_params( $hide_logo_attrs, true ); ?> />
					<?php esc_html_e( 'Hide the ALTCHA logo', 'formidable' ); ?>
				</label>
			</p>
			<p class="frm_form_field">
				<label for="frm_altcha_hide_footer">
					<input<?php FrmAppHelper::array_to_html_params( $hide_footer_attrs, true ); ?> />
					<?php esc_html_e( 'Hide the "Protected by ALTCHA" link', 'formidable' ); ?>
				</label>
			</p>
		</div>
	</details>
</div>
