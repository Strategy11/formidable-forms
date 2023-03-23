<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Import/Export', 'formidable' ),
		)
	);
	?>
	<div class="wrap">
		<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

		<h2 class="frm-h2"><?php esc_html_e( 'Import', 'formidable' ); ?></h2>
		<p class="howto"><?php echo esc_html( apply_filters( 'frm_upload_instructions1', __( 'Upload your Formidable XML file to import forms into this site. If your imported form key and creation date match a form on your site, that form will be updated.', 'formidable' ) ) ); ?></p>
		<br/>
		<form enctype="multipart/form-data" method="post" class="frm-fields">
			<input type="hidden" name="frm_action" value="import_xml" />
			<?php wp_nonce_field( 'import-xml-nonce', 'import-xml' ); ?>
			<p>
				<label>
					<?php echo esc_html( apply_filters( 'frm_upload_instructions2', __( 'Choose a Formidable XML file', 'formidable' ) ) ); ?>
					(<?php
					/* translators: %s: File size */
					echo esc_html( sprintf( __( 'Maximum size: %s', 'formidable' ), ini_get( 'upload_max_filesize' ) ) );
					?>)
				</label>
				<br/>
				<input type="file" name="frm_import_file" size="25" />
			</p>

			<?php do_action( 'frm_csv_opts', $forms ); ?>

			<p class="submit">
				<input type="submit" value="<?php esc_attr_e( 'Upload file and import', 'formidable' ); ?>" class="button-primary frm-button-primary" />
			</p>
		</form>
		<?php FrmFormMigratorsHelper::maybe_show_download_link(); ?>
		<?php FrmTipsHelper::pro_tip( 'get_import_tip' ); ?>

		<?php do_action( 'frm_import_settings' ); ?>

		<h2 class="frm-h2"><?php esc_html_e( 'Export', 'formidable' ); ?></h2>
		<p class="howto">
			<?php echo esc_html( __( 'Export your forms, entries, views, and styles so you can easily import them on another site.', 'formidable' ) ); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" id="frm_export_xml" class="frm-fields frm_grid_container">
			<input type="hidden" name="action" value="frm_export_xml" />
			<?php wp_nonce_field( 'export-xml-nonce', 'export-xml' ); ?>

			<p class="frm4 frm_form_field">
				<label for="format"><?php esc_html_e( 'Export Format', 'formidable' ); ?></label>
				<select name="format">
					<?php foreach ( $export_format as $t => $type ) { ?>
						<option value="<?php echo esc_attr( $t ); ?>" data-support="<?php echo esc_attr( $type['support'] ); ?>" <?php echo isset( $type['count'] ) ? 'data-count="' . esc_attr( $type['count'] ) . '"' : ''; ?>>
							<?php echo esc_html( isset( $type['name'] ) ? $type['name'] : $t ); ?>
						</option>
					<?php } ?>
				</select>
			</p>

			<p class="frm_hidden csv_opts export-filters frm4 frm_form_field">
				<label for="csv_format" class="frm_help" title="<?php esc_attr_e( 'If your CSV special characters are not working correctly, try a different formatting option.', 'formidable' ); ?>">
					<?php esc_html_e( 'CSV Encoding Format', 'formidable' ); ?>
				</label>
				<select name="csv_format">
					<?php foreach ( FrmCSVExportHelper::csv_format_options() as $format ) { ?>
						<option value="<?php echo esc_attr( $format ); ?>">
							<?php echo esc_html( $format ); ?>
						</option>
					<?php } ?>
				</select>
			</p>

			<p class="frm_hidden csv_opts export-filters frm4 frm_form_field">
				<label for="csv_col_sep">
					<?php esc_html_e( 'Column Separation', 'formidable' ); ?>
				</label>
				<input id="frm_csv_col_sep" name="csv_col_sep" value="," type="text" />
			</p>

			<p id="frm_csv_data_export" class="xml_opts">
				<label><?php esc_html_e( 'Include the following in the export file', 'formidable' ); ?></label>
				<?php foreach ( $export_types as $t => $type ) { ?>
					<label class="frm_inline_label">
						<input type="checkbox" name="type[]" value="<?php echo esc_attr( $t ); ?>"/>
						<?php echo esc_html( $type ); ?>
					</label> &nbsp;
				<?php } ?>
			</p>

			<div class="frm-table-box">
			<p class="alignleft frm-mb-sm">
				<label class="xml_opts">
					<?php esc_html_e( 'Select Form(s)', 'formidable' ); ?>
				</label>
				<label class="csv_opts frm_hidden">
					<?php esc_html_e( 'Select a Form', 'formidable' ); ?>
				</label>
			</p>
			<?php
			FrmAppHelper::show_search_box(
				array(
					'input_id'    => 'template',
					'placeholder' => __( 'Search Forms', 'formidable' ),
					'tosearch'    => 'frm-row',
				)
			);
			?>
			<div class="frm-scroll-box">
				<table class="widefat striped frm-border frm-mt-0">
					<thead>
						<tr>
							<td class="column-cb check-column"></td>
							<td><?php esc_html_e( 'Form Title', 'formidable' ); ?></td>
							<td><?php esc_html_e( 'ID / Form Key', 'formidable' ); ?></td>
							<td><?php esc_html_e( 'Type', 'formidable' ); ?></td>
							<td class="column-entries"><?php esc_html_e( 'Entries', 'formidable' ); ?></td>
							<td class="column-entries"><?php esc_html_e( 'Style', 'formidable' ); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $forms as $form ) { ?>
							<tr class="frm-row <?php echo ! empty( $form->parent_form_id ) ? esc_attr( 'frm-is-repeater' ) : ''; ?>">
								<td>
									<input type="checkbox" name="frm_export_forms[]" value="<?php echo esc_attr( $form->id ); ?>" id="export_form_<?php echo esc_attr( $form->id ); ?>" />
								</td>
								<td>
									<label for="export_form_<?php echo esc_attr( $form->id ); ?>">
										<?php echo esc_html( '' === $form->name ? __( '(no title)', 'formidable' ) : $form->name ); ?>
									</label>
								</td>
								<td>
									<?php echo esc_html( $form->id ); ?> / <?php echo esc_html( $form->form_key ); ?>
								</td>
								<td>
									<?php
									if ( $form->is_template ) {
										esc_html_e( 'Template', 'formidable' );
									} elseif ( $form->parent_form_id ) {
										echo esc_html(
											sprintf(
												/* translators: %1$s: Form name */
												__( 'Child Form (%1$s)', 'formidable' ),
												$form->parent_form_id
											)
										);
									} else {
										esc_html_e( 'Form', 'formidable' );
									}
									?>
								</td>
								<td class="column-entries">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-entries&form=' . absint( $form->id ) ) ); ?>" target="_blank">
										<?php echo absint( FrmEntry::getRecordCount( $form->id ) ); ?>
									</a>
								</td>
								<td class="column-entries">
									<?php
									$style = isset( $form->options['custom_style'] ) ? $form->options['custom_style'] : 1;
									if ( empty( $style ) ) {
										echo '0';
									} else {
										echo '1';
									}
									?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			</div>

			<p class="submit">
				<input type="submit" value="<?php esc_attr_e( 'Export Selection', 'formidable' ); ?>" class="button-primary frm-button-primary" />
			</p>
		</form>

		<?php do_action( 'frm_page_footer', array( 'table' => 'export' ) ); ?>
	</div>
</div>
