<?php
/**
 * On Submit redirect settings
 *
 * @package Formidable
 * @since 6.17
 *
 * @var array $args See {@see FrmOnSubmitHelper::show_redirect_settings()}.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$id_attr = $args['action_control']->get_field_id( 'success_url' );
?>
<div class="frm_form_field frm_has_shortcodes">
	<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'Redirect URL', 'formidable' ); ?></label>
	<input
		type="text"
		id="<?php echo esc_attr( $id_attr ); ?>"
		name="<?php echo esc_attr( $args['action_control']->get_field_name( 'success_url' ) ); ?>"
		value="<?php echo esc_attr( $args['form_action']->post_content['success_url'] ); ?>"
	/>
</div>

<?php
$id_attr   = $args['action_control']->get_field_id( 'open_in_new_tab' );
$name_attr = $args['action_control']->get_field_name( 'open_in_new_tab' );
?>
<div class="frm_form_field">
	<?php
	FrmHtmlHelper::toggle(
		$id_attr,
		$name_attr,
		array(
			'div_class' => 'with_frm_style frm_toggle',
			'checked'   => ! empty( $args['form_action']->post_content['open_in_new_tab'] ),
			'echo'      => true,
		)
	);
	?>
	<label for="<?php echo esc_attr( $id_attr ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'new_tab' ); ?>>
		<?php esc_html_e( 'Open in new tab', 'formidable' ); ?>
	</label>
</div>

<?php
$id_attr        = $args['action_control']->get_field_id( 'redirect_delay' );
$redirect_delay = ! empty( $args['form_action']->post_content['redirect_delay'] );
?>
<div class="frm_form_field">
	<?php
	FrmHtmlHelper::toggle(
		$id_attr,
		$args['action_control']->get_field_name( 'redirect_delay' ),
		array(
			'div_class'  => 'with_frm_style frm_toggle',
			'checked'    => $redirect_delay,
			'echo'       => true,
			'input_html' => array(
				'data-toggleclass' => 'frm_redirect_delay_settings',
			),
		)
	);
	?>
	<label for="<?php echo esc_attr( $id_attr ); ?>">
		<?php esc_html_e( 'Delay redirect and show message', 'formidable' ); ?>
	</label>
</div>

<?php
$css_class = 'frm_redirect_delay_settings';
if ( ! $redirect_delay ) {
	$css_class .= ' frm_hidden';
}
?>
<div class="<?php echo esc_attr( $css_class ); ?>">
	<?php
	$id_attr    = $args['action_control']->get_field_id( 'redirect_delay_time' );
	$delay_time = intval( $args['form_action']->post_content['redirect_delay_time'] );
	if ( $delay_time < 1 ) {
		$delay_time = 8;
	}
	?>
	<div class="frm_form_field">
		<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'Delay time', 'formidable' ); ?></label>
		<span class="frm_input_with_suffix">
			<input
				type="number"
				min="1"
				step="1"
				id="<?php echo esc_attr( $id_attr ); ?>"
				name="<?php echo esc_attr( $args['action_control']->get_field_name( 'redirect_delay_time' ) ); ?>"
				value="<?php echo intval( $delay_time ); ?>"
				style="width:60px;"
			/><span class="frm_suffix"><?php esc_html_e( 'seconds', 'formidable' ); ?></span>
		</span>
	</div>

	<?php
	$id_attr = $args['action_control']->get_field_id( 'redirect_delay_msg' );
	?>
	<div class="frm_form_field frm_has_shortcodes">
		<label for="<?php echo esc_attr( $id_attr ); ?>" class="screen-reader-text">
			<?php esc_html_e( 'Redirect message', 'formidable' ); ?>
		</label>

		<?php
		wp_editor(
			$args['form_action']->post_content['redirect_delay_msg'],
			$id_attr,
			array(
				'textarea_name' => $args['action_control']->get_field_name( 'redirect_delay_msg' ),
				'textarea_rows' => 4,
				'editor_class'  => 'frm_not_email_message',
			)
		);
		?>
	</div>
</div>
