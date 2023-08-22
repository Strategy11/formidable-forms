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
<div>
	<p><?php printf( esc_html__( 'Hello %s', 'formidable' ), esc_html( $args['admin_display_name'] ) ); ?></p>

	<p><?php esc_html_e( 'Your license is expired. You can use the button below to renew.', 'formidable' ); ?></p>

	<p><a href="<?php esc_url( $args['renew_url'] ); ?>" title=""><?php esc_html_e( 'Renew now', 'formidable' ); ?></a></p>
</div>
