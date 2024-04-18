<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( 'settings' === FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' ) ) {
	$class = 'frm_submit_settings_btn';
} else {
	$class = 'frm_submit_' . ( ! empty( $values['ajax_load'] ) ? '' : 'no_' ) . 'ajax';
}
?>
<button class="frm_submit_form button-primary frm-button-primary frm_button_submit <?php echo esc_attr( $class ); ?>" type="button" id="frm_submit_side_top" >
	<?php FrmFormsHelper::publish_box_button_text(); ?>
</button>

<div id="frm-preview-action">
	<?php if ( ( ! isset( $hide_preview ) || ! $hide_preview ) && isset( $values['form_key'] ) ) { ?>
		<div class="preview dropdown">
			<a href="#" id="frm-previewDrop" class="frm-dropdown-toggle button frm-button-secondary" role="button">
				<?php esc_html_e( 'Preview', 'formidable' ); ?>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon frm_svg13', array( 'aria-hidden' => 'true' ) ); ?>
			</a>
			<ul class="frm-dropdown-menu frm-p-1 <?php echo esc_attr( is_rtl() ? 'dropdown-menu-left' : 'dropdown-menu-right' ); ?>" role="menu" aria-labelledby="frm-previewDrop">
				<li>
					<a href="<?php echo esc_url( FrmFormsHelper::get_direct_link( $values['form_key'] ) ); ?>" target="_blank">
						<?php esc_html_e( 'On Blank Page', 'formidable' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( FrmFormsHelper::get_direct_link( $values['form_key'] ) . '&theme=1' ); ?>" target="_blank">
						<?php esc_html_e( 'In Theme', 'formidable' ); ?>
					</a>
				</li>
				<?php if ( FrmAppHelper::show_landing_pages() ) { ?>
					<li>
						<?php FrmFormsController::landing_page_preview_option(); ?>
					</li>
				<?php } ?>
			</ul>
		</div>
		<?php
	}//end if
	?>
</div>

<a id="frm-embed-action" href="#" role="button" class="frm_submit_form frm-button-tertiary frm_button_submit">
	<?php esc_html_e( 'Embed', 'formidable' ); ?>
</a>
