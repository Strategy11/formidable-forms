<?php
/**
 * Template for license expired email
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $args Content args.
 */

?>
<div style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_section_style() ); ?>">
	<p><?php esc_html_e( 'Hello', 'formidable' ); ?></p>

	<p><?php esc_html_e( 'Your license is expired. You can use the button below to renew.', 'formidable' ); ?></p>

	<p><a href="<?php esc_url( $args['renew_url'] ); ?>" title=""><?php esc_html_e( 'Renew now', 'formidable' ); ?></a></p>
</div>
