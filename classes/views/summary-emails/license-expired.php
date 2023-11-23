<?php
/**
 * Template for license expired email
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $args Content args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_section_style( '' ) ); ?>">
	<p><?php esc_html_e( 'I hope you have loved the past year of using Formidable. It looks like your subscription has expired.', 'formidable' ); ?></p>

	<p><?php esc_html_e( 'Renewing your license grants you access to our legendary support services, form templates, Pro features, and updates for another year.', 'formidable' ); ?></p>

	<p><a href="<?php echo esc_url( $args['renew_url'] ); ?>" title=""><?php esc_html_e( 'Renew Now', 'formidable' ); ?></a></p>

	<p><?php esc_html_e( 'If you don\'t need Formidable Pro right now, that\'s ok. You can resubscribe in the future when needed.', 'formidable' ); ?></p>
</div>
