<?php
/**
 * View for email settings
 *
 * @since 6.25
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$email_styles   = FrmEmailStylesController::get_email_styles();
$selected_style = FrmEmailStylesController::get_default_email_style();
?>
<p><?php esc_html_e( 'Customize your email template and sending preferences.', 'formidable' ); ?></p>

<h3><?php esc_html_e( 'Styles', 'formidable' ); ?></h3>
<p><?php esc_html_e( 'Change how your emails look and feel.', 'formidable' ); ?></p>

<div id="frm-email-styles" class="frm_clearfix">
	<?php
	foreach ( $email_styles as $style_key => $style ) :
		$html_attrs = array(
			'class'          => 'frm-email-style frm-mb-md',
			'data-style-key' => $style_key,
		);

		if ( $style_key === $selected_style ) {
			$html_attrs['class'] .= ' frm-email-style--selected';
		}

		if ( empty( $style['selectable'] ) ) {
			$html_attrs['class'] .= ' frm-email-style--disabled';
		}
		?>
		<div <?php FrmAppHelper::array_to_html_params( $html_attrs, true ); ?>>
			<div class="frm-email-style__card">
				<a href="#" class="frm-email-style__click">
					<img src="<?php echo esc_url( $style['icon_url'] ); ?>" alt="<?php echo esc_attr( $style['name'] ); ?>">
				</a>

				<div class="frm-email-style__buttons">
					<?php
					/* translators: %s: Email style name. */
					$choose_label = sprintf( __( 'Choose %s email style', 'formidable' ), $style['name'] );
					/* translators: %s: Email style name. */
					$selected_label = sprintf( __( 'Selected %s email style', 'formidable' ), $style['name'] );
					/* translators: %s: Email style name. */
					$preview_label = sprintf( __( 'Preview %s email style', 'formidable' ), $style['name'] );

					// The Selected button uses aria-disabled instead of disabled so it stays focusable, giving focus somewhere to land after choosing.
					?>
					<button type="button" class="frm-email-style__button frm-button-primary" data-action="choose" aria-label="<?php echo esc_attr( $choose_label ); ?>">
						<?php esc_html_e( 'Choose', 'formidable' ); ?>
					</button>

					<button type="button" class="frm-email-style__button frm-button-primary" data-action="selected" aria-disabled="true" aria-label="<?php echo esc_attr( $selected_label ); ?>">
						<?php esc_html_e( 'Selected', 'formidable' ); ?>
					</button>

					<a href="<?php echo esc_url( FrmEmailStylesController::get_email_style_preview_url( $style_key ) ); ?>" class="frm-email-style__button frm-button-secondary" data-action="preview" target="_blank" aria-label="<?php echo esc_attr( $preview_label ); ?>">
						<?php esc_html_e( 'Preview', 'formidable' ); ?>
					</a>
				</div>
			</div><!-- End .frm-email-style__card -->

			<div class="frm-email-style__name">
				<?php if ( empty( $style['selectable'] ) ) : ?>
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon' ); ?>
				<?php endif; ?>
				<?php echo esc_html( $style['name'] ); ?>
			</div>
		</div><!-- End .frm-email-style -->

	<?php endforeach; ?>

	<input type="hidden" name="frm_email_style" id="frm-email-style-value" value="<?php echo esc_attr( $selected_style ); ?>">
</div>

<hr class="frm-mt-md frm-mb-md" />

<?php
/**
 * @since 6.25
 */
do_action( 'frm_email_styles_extra_settings' );
?>

<p class="frm-mb-md">
	<button id="frm-send-test-email" type="button" class="frm-button-secondary"><?php esc_html_e( 'Send a test email', 'formidable' ); ?></button>
</p>

<div id="frm-send-test-email-modal" class="frm_hidden frm-modal">
	<div class="metabox-holder">
		<div class="postbox frm_wrap">
			<a href="javascript:void(0)" class="dismiss" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
			</a>
			<div class="inside">
				<h3><?php esc_html_e( 'Send email test', 'formidable' ); ?></h3>

				<div>
					<label for="frm-test-email-address"><?php esc_html_e( 'Email address', 'formidable' ); ?></label>
					<input type="text" id="frm-test-email-address" class="widefat" aria-describedby="frm-test-email-help" autofocus />
					<p class="description" id="frm-test-email-help"><?php esc_html_e( 'Use commas to separate multiple emails.', 'formidable' ); ?></p>
				</div>

				<div id="frm-send-test-email-result" role="status" aria-live="polite" aria-atomic="true"></div>

				<p style="text-align: right;">
					<button type="button" class="frm-button-primary" id="frm-send-test-email-btn"><?php esc_html_e( 'Send Email', 'formidable' ); ?></button>
				</p>
			</div>
		</div>
	</div>
</div>
