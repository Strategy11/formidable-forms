<?php
/**
 * Welcome Tour's Checklist component.
 *
 * @since 6.25.1
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-checklist frm-flex-col frm-bg-white frm-rounded-12 frm-box-shadow-xxl">
	<div class="frm-checklist__header frm-bg-grey-800 frm-h-stack frm-justify-between frm-p-sm frm-cursor-pointer">
		<h2 class="frm-text-sm frm-text-white frm-m-0"><?php esc_html_e( 'Formidable Checklist', 'formidable' ); ?></h2>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown8_icon frm_svg12 frm-text-white frm-m-2xs', array( 'aria-hidden' => 'true' ) ); ?>
	</div>

	<?php if ( ! $is_tour_completed ) : ?>
		<div class="frm-checklist__progress-bar frm-shrink-0 frm-flex frm-bg-grey-200">
			<div class="frm-checklist__progress-fill frm-bg-primary-500"></div>
		</div>
	<?php endif; ?>

	<div class="frm-checklist__steps frm-grow frm-scrollbar-wrapper frm-slide-down">
		<?php include $steps_path; ?>
	</div>

	<?php if ( ! $is_tour_completed ) { ?>
		<button type="button" class="frm-checklist__dismiss frm-btn-unstyled frm-shrink-0 frm-flex-center frm-mt-auto">
			<span class="frm-text-grey-400"><?php esc_html_e( 'Dismiss Checklist', 'formidable' ); ?></span>
		</button>
	<?php } ?>
</div>
