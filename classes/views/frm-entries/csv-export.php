<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! $form || ! FrmAppHelper::pro_is_installed() || ! current_user_can( 'frm_view_entries' ) ) {
	return;
}
?>
<dialog id="frm-csv-export" class="frm_wrap" aria-labelledby="frm-csv-export-title" aria-describedby="frm-csv-export-description">
	<form method="dialog">
		<h2 id="frm-csv-export-title"><?php esc_html_e( 'Customize CSV export', 'formidable' ); ?></h2>
		<p id="frm-csv-export-description"><?php esc_html_e( 'Choose the columns to include. Drag columns or use the move buttons to change their order. Repeated values and comments include all rows.', 'formidable' ); ?></p>
		<p>
			<button type="button" class="button js-frm-csv-all"><?php esc_html_e( 'Select all', 'formidable' ); ?></button>
			<button type="button" class="button js-frm-csv-none"><?php esc_html_e( 'Deselect all', 'formidable' ); ?></button>
			<button type="button" class="button js-frm-csv-reset"><?php esc_html_e( 'Reset order', 'formidable' ); ?></button>
		</p>
		<ul class="frm-csv-columns"></ul>
		<p class="frm-csv-status" role="status" aria-live="polite"></p>
		<p><label><input type="checkbox" class="js-frm-csv-remember" /> <?php esc_html_e( 'Remember these columns for my exports of this form', 'formidable' ); ?></label></p>
		<div class="frm-csv-export-actions">
			<button value="cancel" class="button frm-button-secondary"><?php esc_html_e( 'Cancel', 'formidable' ); ?></button>
			<button type="button" class="button button-primary js-frm-csv-download"><?php esc_html_e( 'Download CSV', 'formidable' ); ?></button>
		</div>
	</form>
</dialog>
