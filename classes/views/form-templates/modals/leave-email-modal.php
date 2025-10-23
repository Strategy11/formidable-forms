<?php
/**
 * Form Templates - Leave email modal.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$args = $args ?? $leave_email_args ?? array();
?>
<div id="frm-leave-email-modal" class="frm_wrap frm-form-templates-modal-item frm_hidden">
	<?php if ( ! empty( $args['title'] ) ) { ?>
		<div class="frm_modal_top">
			<div class="frm-modal-title">
				<h2><?php echo esc_html( $args['title'] ); ?></h2>
			</div>
		</div>
	<?php } ?>

	<div class="inside frm_grid_container frm-px-md frm-py-0 frm-mt-xs frm-mb-0">
		<?php if ( ! empty( $args['api_url'] ) ) { ?>
			<div id="frmapi-email-form" class="frmapi-form frm_hidden" data-url="<?php echo esc_url( $args['api_url'] ); ?>">
				<span class="frm-wait"></span>
			</div>
		<?php } ?>

		<?php if ( ! empty( $args['description'] ) ) { ?>
			<p class="frm-text-grey-500">
				<?php echo esc_html( $args['description'] ); ?>
			</p>
		<?php } ?>

		<div id="frm_leave_email_wrapper" class="frm-fields frm_form_field">
			<p class="frm-mt-0 frm-mb-xs">
				<label class="<?php echo FrmDashboardController::is_dashboard_page() ? 'frm_hidden' : ''; ?>" for="frm_leave_email"><?php esc_html_e( 'Email address', 'formidable' ); ?></label>
				<input class="frm-input-field" id="frm_leave_email" type="email" placeholder="<?php esc_attr_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $args['default_email'] ?? $user->user_email ); ?>" />
			</p>

			<?php
			FrmAppHelper::print_setting_error(
				array(
					'id'     => 'frm_leave_email_error',
					'errors' => array(
						'invalid' => __( 'Email is invalid', 'formidable' ),
						'empty'   => __( 'Email is empty', 'formidable' ),
					),
					'class'  => 'frm-justify-center frm-items-center',
				)
			);
			?>
		</div>
	</div>

	<div class="frm_modal_footer frm-flex-box frm-justify-end frm-pt-sm frm-pb-md">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php echo esc_html( $args['close_button_text'] ?? __( 'Close', 'formidable' ) ); ?>
		</a>
		<a href="#" id="frm-get-code-button" class="button button-primary frm-button-primary" role="button">
			<?php echo esc_html( $args['submit_button_text'] ?? __( 'Subscribe', 'formidable' ) ); ?>
		</a>
	</div>
</div>
