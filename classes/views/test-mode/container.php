<?php
/**
 * Test Mode Container.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! empty( $should_show_warning ) ) {
	?>
	<div class="frm_warning_style" style="display: flex;">
		<?php if ( $should_suggest_test_mode_install ) { ?>
			<?php esc_html_e( 'To use this feature, please install and activate the Testing Mode add-on.', 'formidable' ); ?>
			<span <?php FrmAppHelper::array_to_html_params( $test_mode_install_span_attrs, true ); ?>>
			<?php
			FrmAddonsController::conditional_action_button(
				'test-mode',
				array(
					'medium' => 'test-mode',
					'class'  => 'frm-button-primary',
				)
			);
			?>
			</span>
		<?php } elseif ( $should_suggest_ai_install ) { ?>
			<?php esc_html_e( 'To autofill forms using AI, please install and activate the AI add-on.', 'formidable' ); ?>
			<span <?php FrmAppHelper::array_to_html_params( $ai_install_span_attrs, true ); ?>>
				<?php
				FrmAddonsController::conditional_action_button(
					'ai',
					array(
						'medium' => 'ai-autofill',
						'class'  => 'frm-button-primary',
					)
				);
				?>
			</span>
		<?php }//end if ?>
	</div>
	<?php
}//end if
?>
<div id="frm_testing_mode">
	<h2><?php esc_html_e( 'Testing Mode Controls', 'formidable' ); ?></h2>
	<div>
		<?php
		FrmHtmlHelper::toggle( 'frm_testmode_disable_required_fields', 'frm_testmode[disable_required_fields]', $disabled_required_fields_toggle_args );
		FrmHtmlHelper::toggle( 'frm_testmode_show_all_hidden_fields', 'frm_testmode[show_all_hidden_fields]', $show_all_hidden_fields_toggle_args );

		if ( $roles ) :
			?>
			<label>
				<?php esc_html_e( 'Preview as', 'formidable' ); ?>
				&nbsp;
				<select id="frm_testmode_preview_role" name="frm_testmode[preview_role]" <?php disabled( ! $enabled ); ?>>
					<?php
					foreach ( $roles as $role => $details ) {
						FrmHtmlHelper::echo_dropdown_option(
							$details['name'],
							$selected_role === $role,
							array(
								'value' => $role,
							)
						);
					}
					?>
			</select>
			</label>
		<?php endif; ?>

		<label>
			<?php
			esc_html_e( 'Enabled form actions', 'formidable' );
			FrmAppHelper::tooltip_icon( __( 'Selected form actions will be triggered when this test entry is submitted', 'formidable' ) );
			?>
			<div id="frm_testmode_enabled_form_actions_container" class="frm-fields">
				<select id="frm_testmode_enabled_form_actions" multiple class="frm_multiselect" name="frm_testmode[enabled_form_actions][]" <?php disabled( ! $enabled ); ?>>
				<?php
				foreach ( $form_actions as $form_action ) {
					?>
					<option value="<?php echo esc_attr( $form_action->ID ); ?>" <?php selected( in_array( $form_action->ID, $enabled_form_actions, true ), true ); ?>>
						<?php echo esc_html( $form_action->post_title ) . ' (' . absint( $form_action->ID ) . ')'; ?>
					</option>
					<?php
				}
				?>
				</select>
			</div>
		</label>
	</div>
	<hr>
	<div>
		<label id="frm_quick_jump_label"><?php esc_html_e( 'Quick jump to page:', 'formidable' ); ?></label>

		<?php
		if ( false !== $pagination && is_callable( $pagination ) ) {
			$pagination();
		} elseif ( false === $pagination ) {
			include FrmAppHelper::plugin_path() . '/classes/views/test-mode/pagination-buttons.php';
		}
		?>

		<a id="frm_testmode_fill_in_empty_form_fields" class="frm_button frm-button-primary <?php echo $enabled && $ai_enabled ? '' : 'frm_noallow'; ?>" href="#">
			<?php esc_html_e( 'Fill empty fields with AI', 'formidable' ); ?>
		</a>
	</div>
	<?php
	$start_over_button_attrs = array(
		'id'    => 'frm_testmode_start_over',
		'class' => 'frm_button frm-button-secondary frm_noallow',
		'href'  => '#',
	);
	/**
	 * @since 6.25
	 *
	 * @param array $start_over_button_attrs
	 */
	$start_over_button_attrs = apply_filters( 'frm_testmode_start_over_button_attrs', $start_over_button_attrs );
	?>
	<a <?php FrmAppHelper::array_to_html_params( $start_over_button_attrs, true ); ?>>
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/reset.svg" alt="<?php esc_attr_e( 'Reset', 'formidable' ); ?>" />
		<?php esc_html_e( 'Reset', 'formidable' ); ?>
	</a>
	<?php if ( ! empty( $should_show_upsell ) ) { ?>
		<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'test-mode' ) ); ?>" class="frm-gradient" id="frm_testmode_upgrade">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_speaker_icon', array( 'aria-hidden' => 'true' ) ); ?>
			<?php esc_html_e( 'Unlock these powerful, time saving testing features by upgrading!', 'formidable' ); ?>
		</a>
	<?php } ?>
</div>
