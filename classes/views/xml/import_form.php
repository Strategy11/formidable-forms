<div class="wrap">
	<h1><?php esc_html_e( 'Import/Export', 'formidable' ); ?></h1>

	<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>
	<div id="poststuff" class="metabox-holder">
	<div id="post-body">
	<div id="post-body-content">

	<div class="postbox ">
	<h3 class="hndle"><span><?php esc_html_e( 'Import', 'formidable' ) ?></span></h3>
	<div class="inside">
		<p class="howto"><?php echo esc_html( apply_filters( 'frm_upload_instructions1', __( 'Upload your Formidable XML file to import forms into this site. If your imported form key and creation date match a form on your site, that form will be updated.', 'formidable' ) ) ) ?></p>
		<br/>
		<form enctype="multipart/form-data" method="post">
			<input type="hidden" name="frm_action" value="import_xml" />
			<?php wp_nonce_field( 'import-xml-nonce', 'import-xml' ); ?>
			<p>
				<label>
					<?php echo esc_html( apply_filters( 'frm_upload_instructions2', __( 'Choose a Formidable XML file', 'formidable' ) ) ) ?>
					(<?php echo esc_html( sprintf( __( 'Maximum size: %s', 'formidable' ), ini_get( 'upload_max_filesize' ) ) ); ?>)
				</label>
				<input type="file" name="frm_import_file" size="25" />
			</p>

			<?php do_action( 'frm_csv_opts', $forms ) ?>

			<p class="submit">
				<input type="submit" value="<?php esc_attr_e( 'Upload file and import', 'formidable' ) ?>" class="button-primary" />
			</p>
		</form>
		<?php FrmTipsHelper::pro_tip( 'get_import_tip' ); ?>
	</div>
	</div>

	<div class="postbox">
	<h3 class="hndle"><span><?php esc_html_e( 'Export', 'formidable' ) ?></span></h3>
	<div class="inside with_frm_style">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" id="frm_export_xml">
			<input type="hidden" name="action" value="frm_export_xml" />
			<?php wp_nonce_field( 'export-xml-nonce', 'export-xml' ); ?>

			<table class="form-table">
				<tr class="form-field">
					<th scope="row"><label for="format"><?php esc_html_e( 'Export Format', 'formidable' ); ?></label></th>
					<td>
						<select name="format">
						<?php foreach ( $export_format as $t => $type ) { ?>
							<option value="<?php echo esc_attr( $t ) ?>" data-support="<?php echo esc_attr( $type['support'] ) ?>" <?php echo isset( $type['count'] ) ? 'data-count="' . esc_attr( $type['count'] ) . '"' : ''; ?>>
								<?php echo esc_html( isset( $type['name'] ) ? $type['name'] : $t ) ?>
							</option>
						<?php } ?>
						</select>

						<ul class="frm_hidden csv_opts export-filters">
							<li>
							<label for="csv_format"><?php esc_html_e( 'Format', 'formidable' ) ?>:</label>
							<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'If your CSV special characters are not working correctly, try a different formatting option.', 'formidable' ) ?>"></span>
							<select name="csv_format">
								<?php foreach ( FrmCSVExportHelper::csv_format_options() as $format ) { ?>
								<option value="<?php echo esc_attr( $format ) ?>"><?php echo esc_html( $format ) ?></option>
								<?php } ?>
							</select>
							</li>

							<li>
								<label for="csv_col_sep"><?php esc_html_e( 'Column separation', 'formidable' ) ?>:</label>
								<input id="frm_csv_col_sep" name="csv_col_sep" value="," type="text" />
							</li>
						</ul>
					</td>
				</tr>

				<tr class="form-field" id="frm_csv_data_export">
					<th scope="row"><label><?php esc_html_e( 'Data to Export', 'formidable' ); ?></label></th>
					<td>
						<?php esc_html_e( 'Include the following in the export file', 'formidable' ); ?>:<br/>
						<?php foreach ( $export_types as $t => $type ) { ?>
							<label>
								<input type="checkbox" name="type[]" value="<?php echo esc_attr( $t ) ?>"/>
								<?php echo esc_html( $type ) ?>
							</label> &nbsp;
						<?php } ?>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row">
						<label><?php esc_html_e( 'Select Form(s)', 'formidable' ); ?></label>
					</th>
					<td>
						<select name="frm_export_forms[]" multiple="multiple">
						<?php foreach ( $forms as $form ) { ?>
							<option value="<?php echo esc_attr( $form->id ) ?>">
								<?php
								echo esc_html( '' === $form->name ? __( '(no title)' ) : $form->name );
								echo ' &mdash; ' . esc_html( $form->form_key );
								if ( $form->is_template && $form->default_template ) {
									echo ' ' . esc_html__( '(default template)', 'formidable' );
								} elseif ( $form->is_template ) {
									echo ' ' . esc_html__( '(template)', 'formidable' );
								} elseif ( $form->parent_form_id ) {
									echo ' ' . esc_html__( '(child)', 'formidable' );
								}
								?>
							</option>
						<?php } ?>
						</select>
						<p class="howto"><?php esc_html_e( 'Hold down the CTRL/Command button to select multiple forms', 'formidable' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" value="<?php esc_attr_e( 'Export Selection', 'formidable' ) ?>" class="button-primary" />
			</p>
		</form>

	</div>
	</div>

	</div>
	</div>
	</div>
</div>
