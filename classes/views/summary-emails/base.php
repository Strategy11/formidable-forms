<?php
/**
 * Base template for summary emails
 *
 * @since 6.7
 * @package Formidable
 *
 * @var array $args Content args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_settings = FrmAppHelper::get_settings();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, minimal-ui, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="format-detection" content="telephone=no, date=no, email=no, address=no">
	<meta name="x-apple-disable-message-reformatting">
	<title></title>
	<!--[if (mso 16)]> <style type="text/css"> a { text-decoration: none; } </style> <![endif]-->
	<!--[if gte mso 9]> <style> sup { font-size: 100% !important; } </style> <![endif]-->
	<!--[if gte mso 9]> <xml> <o:OfficeDocumentSettings> <o:AllowPNG></o:AllowPNG> <o:PixelsPerInch>96</o:PixelsPerInch> </o:OfficeDocumentSettings> </xml> <![endif]-->
	<!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]-->
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />

	<!-- Some clients doesn't fully support <style> tag -->
	<style type="text/css">
		.frm_inbox_dismiss { display: none; }

		.frm-button-primary { <?php echo esc_attr( FrmEmailSummaryHelper::get_button_style() ); ?> }
	</style>
</head>

<body style="background-color: #f2f4f7; color: #475467; font-family: 'Inter', Arial, sans-serif; padding: 4.375em 0;">
	<div style="background-color: #fff; max-width: 42.5em; border-radius: 1em; margin: auto;">
		%%INNER_CONTENT%%

		<div style="<?php echo esc_attr( FrmEmailSummaryHelper::get_section_style( '' ) ); ?>padding-top: 0;">
			<table style="background-color: #FEF7F4; color: #501E0C; border-radius: 1em; line-height: 1.5; padding: 1em; width: 100%;">
				<tr>
					<td><?php esc_html_e( 'Need help? Get in touch with our team', 'formidable' ); ?></td>
					<td align="right">
						<a href="<?php echo esc_url( $args['support_url'] ); ?>" style="color: #F15A24; font-weight: 700; text-decoration: none;"><?php esc_html_e( 'Contact support', 'formidable' ); ?></a>
					</td>
				</tr>
			</table>
		</div>

		<div style="<?php echo esc_attr( FrmEmailSummaryHelper::get_section_style() ); ?>">
			<a href="<?php echo esc_url( $args['unsubscribe_url'] ); ?>" style="color: currentColor; font-size: 0.75em; line-height: 1.33; font-weight: 500;"><?php esc_html_e( 'Unsubscribe', 'formidable' ); ?></a>
		</div>
	</div>
</body>
</html>
