<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$a = FrmAppHelper::simple_get( 't', 'sanitize_title', 'advanced_settings' );

$form_action = apply_filters( 'frm_form_action_settings', $form_action, $form_action->post_excerpt );
$form_action = apply_filters( 'frm_form_' . $form_action->post_excerpt . '_action_settings', $form_action );

$data_attrs = array(
	'data-actionkey' => $action_key,
);

if ( FrmOnSubmitAction::$slug === $form_action->post_excerpt ) {
	$data_attrs['data-on-submit-type'] = FrmOnSubmitHelper::get_action_type( $form_action );
}
?>
<div
	id="frm_form_action_<?php echo esc_attr( $action_key ); ?>"
	class="widget frm_form_action_settings frm_single_<?php echo esc_attr( $form_action->post_excerpt ); ?>_settings <?php echo esc_attr( $form_action->post_status === 'publish' ? '' : 'frm_disabled_action' ); ?>"
	<?php FrmAppHelper::array_to_html_params( $data_attrs, true ); ?>
>
	<div class="widget-top">
		<div class="widget-title-action">
			<button type="button" class="widget-action hide-if-no-js" aria-expanded="false">
				<i class="frm_icon_font frm_arrow_right_icon" aria-hidden="true"></i>
			</button>
		</div>
		<span class="frm_email_icons alignright">
			<?php if ( $action_control->action_options['limit'] > 2 ) { ?>
				<a href="javascript:void(0)" class="frm_duplicate_form_action" title="<?php esc_attr_e( 'Duplicate', 'formidable' ); ?>">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_clone_icon' ); ?>
				</a>
			<?php } ?>
			<a href="javascript:void(0)" data-removeid="frm_form_action_<?php echo esc_attr( $action_key ); ?>" class="frm_remove_form_action" data-frmverify="<?php esc_attr_e( 'Delete this form action?', 'formidable' ); ?>" data-frmverify-btn="frm-button-red" title="<?php esc_attr_e( 'Delete', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_delete_icon ' ); ?>
			</a>

			<?php
			FrmHtmlHelper::toggle(
				$action_control->get_field_id( 'post_status', '' ),
				$action_control->get_field_name( 'post_status', '' ),
				array(
					'checked'     => $form_action->post_status === 'publish',
					'on_label'    => 'publish',
					'off_label'   => 'OFF',
					'show_labels' => false,
					'echo'        => true,
				)
			);
			?>
		</span>
		<div class="widget-title">
			<h4>
				<span class="frm_form_action_icon frm-outer-circle <?php echo esc_attr( strpos( $action_control->action_options['classes'], 'frm-inverse' ) === false ? '' : ' frm-inverse' ); ?>">
					<?php FrmAppHelper::icon_by_class( $action_control->action_options['classes'] ); ?>
				</span>
				<?php echo esc_html( $form_action->post_title ); ?>
			</h4>
		</div>
	</div>
	<div class="widget-inside">
		<?php
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// only load settings if they are just added or are open
			include( dirname( __FILE__ ) . '/_action_inside.php' );
		} else {
			// include hidden settings so action won't get lost on update
			?>
		<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'post_excerpt', '' ) ); ?>" class="frm_action_name" value="<?php echo esc_attr( $form_action->post_excerpt ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'ID', '' ) ); ?>" value="<?php echo esc_attr( $form_action->ID ); ?>" />
		<?php } ?>
	</div>
</div>
