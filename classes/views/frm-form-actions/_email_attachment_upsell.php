<?php
/**
 * Show a disabled preview of the Pro email attachment settings.
 *
 * Each control opens the upgrade modal with its own utm_medium so we can tell
 * which attachment type people are upgrading for.
 *
 * @since 6.35
 *
 * @package Formidable
 *
 * @var string $action_key The unique key for the current form action.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$file_params = array(
	'class'        => 'button frm-button-secondary frm-button-sm frm_noallow frm_show_upgrade',
	'data-upgrade' => __( 'Email attachments', 'formidable' ),
	'data-message' => __( 'Attach a file of your choice to every email this form sends.', 'formidable' ),
	'data-medium'  => 'email-attachment-file',
);

$toggle_upsells = array(
	array(
		'id'           => 'frm_attach_csv_' . $action_key,
		'label'        => __( 'Attach CSV export of entry to email', 'formidable' ),
		'data-upgrade' => __( 'CSV email attachments', 'formidable' ),
		'data-message' => __( 'Attach a CSV export of each new entry to the email.', 'formidable' ),
		'data-medium'  => 'email-attachment-csv',
	),
	array(
		'id'           => 'frm_attach_pdf_' . $action_key,
		'label'        => __( 'Attach PDF of entry to email', 'formidable' ),
		'data-upgrade' => __( 'Forms to PDF', 'formidable' ),
		'data-message' => __( 'Attach a PDF of each new entry to the email.', 'formidable' ),
		'data-medium'  => 'email-attachment-pdf',
	),
);

$pdf_addon = FrmAddonsController::install_link( 'pdfs' );
$pdf_plan  = FrmFormsHelper::get_plan_required( $pdf_addon );

if ( $pdf_plan ) {
	$toggle_upsells[1]['data-requires'] = $pdf_plan;
}
?>
<!-- Start: Email attachment upsell -->
<div class="frm_email_row">
	<h3>
		<?php esc_html_e( 'Attachment', 'formidable' ); ?>
	</h3>

	<p class="frm-mb-md">
		<a href="javascript:void(0)"<?php FrmAppHelper::array_to_html_params( $file_params, true ); ?>>
			<?php esc_html_e( 'Add Attachment', 'formidable' ); ?>
		</a>
	</p>

	<?php
	foreach ( $toggle_upsells as $upsell ) {
		$toggle_id     = $upsell['id'];
		$toggle_params = $upsell;
		unset( $toggle_params['id'], $toggle_params['label'] );
		$toggle_params['class'] = 'frm-h-stack-xs frm-mb-md frm_show_upgrade';
		?>
		<div <?php FrmAppHelper::array_to_html_params( $toggle_params, true ); ?>>
			<?php
			FrmHtmlHelper::toggle(
				$toggle_id,
				$toggle_id,
				array(
					'div_class' => 'with_frm_style frm_toggle',
					'checked'   => false,
					'echo'      => true,
					'disabled'  => true,
				)
			);
			?>
			<label id="<?php echo esc_attr( $toggle_id ); ?>_label" for="<?php echo esc_attr( $toggle_id ); ?>" class="frm_noallow">
				<?php echo esc_html( $upsell['label'] ); ?>
			</label>
		</div>
		<?php
	}//end foreach
	?>
</div>
<!-- End: Email attachment upsell -->
