<?php
/**
 * View for email settings
 *
 * @since x.x
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$email_styles = FrmEmailStylesController::get_email_styles();
?>
<p><?php esc_html_e( 'Customize your email template and sending preferences.', 'formidable' ); ?></p>

<h4><?php esc_html_e( 'Styles', 'formidable' ); ?></h4>
<p><?php esc_html_e( 'Change how your emails looks and feels.', 'formidable' ); ?></p>

<div id="frm-email-styles" class="frm_clearfix">
	<?php foreach ( $email_styles as $style_key => $style ) : ?>
		<div class="frm-email-style" data-style-key="<?php echo esc_attr( $style_key ); ?>">
			<div class="frm-email-style__card">
				<a href="#" class="frm-email-style__click">
					<img src="<?php echo esc_url( $style['icon_url'] ); ?>" alt="<?php echo esc_attr( $style['name'] ); ?>">
				</a>

				<div class="frm-email-style__buttons">
					<button type="button" class="frm-button-primary" data-action="choose">
						<?php esc_html_e( 'Choose', 'formidable' ); ?>
					</button>

					<button type="button" class="frm-button-primary" disabled="disabled">
						<?php esc_html_e( 'Selected', 'formidable' ); ?>
					</button>

					<button type="button" class="frm-button-secondary" data-action="preview">
						<?php esc_html_e( 'Preview', 'formidable' ); ?>
					</button>
				</div>
			</div><!-- End .frm-email-style__card -->

			<div class="frm-email-style__name">
				<?php echo esc_html( $style['name'] ); ?>
			</div>
		</div><!-- End .frm-email-style -->

	<?php endforeach; ?>
</div>
