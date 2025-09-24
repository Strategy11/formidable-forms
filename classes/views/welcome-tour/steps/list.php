<?php
/**
 * Welcome Tour's Individual step view.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! is_array( $steps ) ) {
	return;
}

foreach ( $steps as $key => $step ) {
	?>
	<div id="frm-checklist__step-<?php echo esc_attr( $key ); ?>" class="frm-checklist__step">
		<div class="frm-checklist__step-title">
			<span class="frm-text-sm frm-p-xs"><?php echo esc_html( $step['title'] ); ?></span>
		</div>
		<div class="frm-checklist__step-description">
			<span class="frm-text-xs frm-p-xs"><?php echo esc_html( $step['description'] ); ?></span>
		</div>
	</div>
	<?php
}
