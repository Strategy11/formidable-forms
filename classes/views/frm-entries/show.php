<div id="form_show_entry_page" class="frm_wrap frm_single_entry_page">
	<div class="frm_page_container">

		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label'       => __( 'View Entry', 'formidable' ),
				'form'        => $form,
				'hide_title'  => true,
				'close'       => '?page=formidable-entries&form=' . $form->id,
			)
		);
		?>

		<div id="frm-bar-two">
			<?php FrmFormsHelper::form_switcher( $form->name ); ?>
			<h2><?php esc_html_e( 'Form Entries', 'formidable' ); ?></h2>
		</div>

		<div class="columns-2">

		<div id="post-body-content" class="frm-fields">

			<div class="frm_form_fields">
				<div class="postbox">
					<h3 class="hndle">
						<span><?php esc_html_e( 'Entry', 'formidable' ); ?></span>
						<span class="frm-sub-label">
							<?php echo esc_html( $entry->id ); ?>
						</span>
					</h3>
					<table class="frm-alt-table">
						<tbody>
							<?php
							$first_h3 = 'frm_first_h3';
							foreach ( $fields as $field ) {
								if ( in_array( $field->type, array( 'captcha', 'html', 'end_divider', 'form' ), true ) ) {
									continue;
								}

								if ( in_array( $field->type, array( 'break', 'divider' ), true ) ) {
									?>
						</tbody>
					</table>
					<br/>
					<h3 class="<?php echo esc_attr( $first_h3 ); ?>"><?php echo esc_html( $field->name ); ?></h3>
					<table class="frm-alt-table">
						<tbody>
									<?php
									$first_h3 = '';
								} else {

									$embedded_field_id = ( $entry->form_id !== $field->form_id ) ? 'form' . $field->form_id : 0;
									$atts = array(
										'type'          => $field->type,
										'post_id'       => $entry->post_id,
										'show_filename' => true,
										'show_icon'     => true,
										'entry_id'      => $entry->id,
										'embedded_field_id' => $embedded_field_id,
									);
									$display_value = FrmEntriesHelper::prepare_display_value( $entry, $field, $atts );
									$empty_class = trim( $display_value ) === '' ? 'frm-empty-value frm_hidden' : '';
									?>
							<tr class="<?php echo esc_attr( $empty_class ); ?>">
								<th scope="row"><?php echo esc_html( $field->name ); ?></th>
								<td>
									<?php

									echo $display_value; // WPCS: XSS ok.

									if ( is_email( $display_value ) && ! in_array( $display_value, $to_emails ) ) {
										$to_emails[] = $display_value;
									}
									?>
								</td>
							</tr>
									<?php
								}
							}
							?>

						<?php if ( $entry->parent_item_id ) { ?>
							<tr>
								<th><?php esc_html_e( 'Parent Entry ID', 'formidable' ); ?></th>
								<td><?php echo absint( $entry->parent_item_id ); ?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
						<?php do_action( 'frm_show_entry', $entry ); ?>
					</div>

					<?php do_action( 'frm_after_show_entry', $entry ); ?>
				</div>
			</div>

			<div class="postbox-container frm-right-panel">
				<?php
				do_action( 'frm_show_entry_sidebar', $entry );
				FrmEntriesController::entry_sidebar( $entry );
				?>
			</div>
		</div>
	</div>
</div>
<br/>
