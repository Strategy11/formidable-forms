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
$frm_settings = FrmAppHelper::get_settings();

$selected_style = $frm_settings->email_style ? $frm_settings->email_style : 'classic';
?>
<p><?php esc_html_e( 'Customize your email template and sending preferences.', 'formidable' ); ?></p>

<h4><?php esc_html_e( 'Styles', 'formidable' ); ?></h4>
<p><?php esc_html_e( 'Change how your emails looks and feels.', 'formidable' ); ?></p>

<div id="frm-email-styles" class="frm_clearfix">
	<?php
	foreach ( $email_styles as $style_key => $style ) :
		$html_attrs = array(
			'class' => 'frm-email-style',
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
					<button type="button" class="frm-email-style__button frm-button-primary" data-action="choose">
						<?php esc_html_e( 'Choose', 'formidable' ); ?>
					</button>

					<button type="button" class="frm-email-style__button frm-button-primary" disabled="disabled">
						<?php esc_html_e( 'Selected', 'formidable' ); ?>
					</button>

					<a href="<?php echo esc_url( FrmEmailStylesController::get_email_style_preview_url( $style_key ) ); ?>" class="frm-email-style__button frm-button-secondary" data-action="preview" target="_blank">
						<?php esc_html_e( 'Preview', 'formidable' ); ?>
					</a>
				</div>
			</div><!-- End .frm-email-style__card -->

			<div class="frm-email-style__name">
				<?php echo esc_html( $style['name'] ); ?>
			</div>
		</div><!-- End .frm-email-style -->

	<?php endforeach; ?>

	<input type="hidden" name="frm_email_style" id="frm-email-style-value" value="<?php echo esc_attr( $selected_style ); ?>">
</div>

<hr class="frm-mt-md frm-mb-md" />

<?php
FrmTipsHelper::show_tip(
	array(
		'tip'   => __( 'Make every email match your brand — beautifully and effortlessly.', 'formidable' ),
		'call'  => __( 'Upgrade to PRO', 'formidable' ),
		'link'  => array(
			'url' => 'https://formidableforms.com/knowledgebase/email-styles/',
		),
	)
);
?>
