<?php
/**
 * Template for plain text license expired email
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $args Content args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

esc_html_e( 'Hello', 'formidable' );

echo "\r\n";

esc_html_e( 'Your license is expired. You can go to the link below to renew.', 'formidable' );

echo "\r\n";

echo esc_url( $args['renew_url'] );
