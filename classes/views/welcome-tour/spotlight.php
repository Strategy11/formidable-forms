<?php
/**
 * Welcome Tour's Spotlight component.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( empty( $spotlight['target'] ) ) {
	return;
}
?>
<div class="frm-spotlight frm-h-stack-xs frm-force-hidden" data-target="<?php echo esc_attr( $spotlight['target'] ); ?>" data-position="<?php echo esc_attr( $spotlight['position'] ?? 'end' ); ?>">
	<div class="frm-spotlight__pointer frm-flex-center frm-mr-2xs frm-fadein-up-back"></div>

	<div class="frm-spotlight__content frm-flex-col frm-justify-center frm-gap-xs frmcenter frm-bg-white frm-p-sm frm-rounded-md frm-box-shadow-xxl frm-fadein-up-back">
		<?php if ( ! empty( $spotlight['title'] ) ) { ?>
			<h3 class="frm-text-xs frm-font-bold frm-text-grey-800 frm-m-0">
				<?php echo esc_html( $spotlight['title'] ); ?>
			</h3>
		<?php } ?>

		<?php if ( ! empty( $spotlight['description'] ) ) { ?>
			<p class="frm-text-xs frm-text-grey-700 frm-m-0">
				<?php echo esc_html( $spotlight['description'] ); ?>
			</p>
		<?php } ?>
	</div>
</div>
