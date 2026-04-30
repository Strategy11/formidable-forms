<?php
/**
 * Plugin Feedback template (Lite).
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-plugin-feedback" class="frm_wrap frm-dismissible" data-step="<?php echo esc_attr( $step ); ?>" data-submit-action="<?php echo esc_attr( $config['ajax']['submit'] ); ?>" data-dismiss-action="<?php echo esc_attr( $config['ajax']['dismiss'] ); ?>">
	<a class="dismiss frm-flex" aria-label="<?php esc_attr_e( 'Dismiss feedback notice', 'formidable' ); ?>" role="button">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon' ); ?>
	</a>

	<form id="frm-plugin-feedback-form" class="frm-flex-col frm-gap-md frm-items-start" method="post" action="">
		<div id="frm-plugin-feedback-nps-step" class="frm-plugin-feedback-step frm-flex-col frm-gap-xs <?php echo esc_attr( $step === 'nps' ? '' : 'frm_hidden' ); ?>">
			<h2 class="frm-text-md frm-font-semibold frm-m-0"><?php esc_html_e( 'Feedback', 'formidable' ); ?></h2>
			<p class="frm-text-sm frm-m-0"><?php esc_html_e( 'How satisfied are you with our product experience overall?', 'formidable' ); ?></p>
			<?php
			FrmHtmlHelper::echo_nps(
				array(
					'id'    => 'frm-plugin-feedback-nps',
					'class' => 'frm-mt-xs',
					'name'  => 'plugin-feedback-nps-score',
					'value' => '10',
				)
			);
			?>
		</div>

		<div id="frm-plugin-feedback-reasons-step" class="frm-plugin-feedback-step frm-w-full frm-flex-col frm-gap-sm <?php echo esc_attr( $step === 'reasons' ? '' : 'frm_hidden' ); ?>">
			<h3 class="frm-text-md frm-font-semibold frm-m-0"><?php esc_html_e( 'Please select all the reasons for your score:', 'formidable' ); ?></h3>
			<div class="frm_grid_container">
				<?php foreach ( $reasons as $value => $label ) { ?>
					<label class="frm-option-box frm6 frm-mb-0" for="frm-plugin-feedback-reason-<?php echo esc_attr( $value ); ?>">
						<input type="checkbox" id="frm-plugin-feedback-reason-<?php echo esc_attr( $value ); ?>" name="plugin-feedback-reasons" value="<?php echo esc_attr( $value ); ?>">
						<span><?php echo esc_html( $label ); ?></span>
					</label>
				<?php } ?>
			</div>
			<div class="frm_form_field frm-flex-col frm-gap-xs frm-mt-xs">
				<label for="frm-plugin-feedback-details" class="frm-text-md frm-font-semibold frm-m-0">
					<?php esc_html_e( 'Please share any details about your experience:', 'formidable' ); ?>
				</label>
				<textarea id="frm-plugin-feedback-details" name="plugin-feedback-details" rows="2"></textarea>
			</div>
		</div>

		<?php
		FrmAppHelper::print_setting_error(
			array(
				'id'     => 'frm-plugin-feedback-error',
				'errors' => array(
					'invalid-nps'     => __( 'NPS score is invalid.', 'formidable' ),
					'invalid-reasons' => __( 'Please select at least one reason.', 'formidable' ),
					'server-error'    => __( 'Failed to submit feedback, try again later.', 'formidable' ),
				),
				'class'  => 'frm-items-center',
			)
		);
		?>

		<button type="submit" class="button button-primary frm-button-primary">
			<?php esc_html_e( 'Submit feedback', 'formidable' ); ?>
		</button>
	</form>

	<div id="frm-plugin-feedback-thank-you-step" class="frm-plugin-feedback-step frm-flex-col frm-gap-sm frmcenter frm-mb-sm frm_hidden">
		<h3 class="frm-text-md frm-font-semibold frm-m-0"><?php esc_html_e( 'Thank you for your feedback!', 'formidable' ); ?></h3>
		<p class="frm-text-sm frm-text-grey-500 frm-m-0"><?php esc_html_e( 'Your feedback helps us improve Formidable Forms. We appreciate you taking the time to share your experience with us.', 'formidable' ); ?></p>
	</div>
</div>
