<div id="form_show_entry_page" class="frm_wrap frm_single_entry_page">
	<div class="frm_forms">

		<div id="poststuff" class="frm_page_container">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			<?php
			FrmAppHelper::get_admin_header(
				array(
					'label'     => __( 'View Entry', 'formidable' ),
					'link_hook' => array(
						'hook'  => 'frm_entry_inside_h2',
						'param' => $form,
					),
					'form'      => $form,
				)
			);
			?>
			<div class="frm_form_fields">
				<div class="postbox">
					<h3 class="hndle"><span><?php esc_html_e( 'Entry', 'formidable' ) ?></span></h3>
					<div class="inside">
						<table class="form-table"><tbody>
							<?php
							$first_h3 = 'frm_first_h3';
							foreach ( $fields as $field ) {
								if ( in_array( $field->type, array( 'captcha', 'html', 'end_divider', 'form' ), true ) ) {
									continue;
								}

								if ( in_array( $field->type, array( 'break', 'divider' ), true ) ) {
									?>
						</tbody></table>
						<br/><h3 class="<?php echo esc_attr( $first_h3 ) ?>"><?php echo esc_html( $field->name ) ?></h3>
						<table class="form-table"><tbody>
							<?php
									$first_h3 = '';
								} else {
							?>
							<tr>
								<th scope="row"><?php echo esc_html( $field->name ) ?>:</th>
								<td>
									<?php
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
							<tr><th><?php esc_html_e( 'Parent Entry ID', 'formidable' ) ?>:</th>
								<td><?php echo absint( $entry->parent_item_id ) ?>
								</td></tr>
								<?php } ?>
							</tbody></table>
							<?php do_action( 'frm_show_entry', $entry ); ?>
						</div>
					</div>

					<?php do_action( 'frm_after_show_entry', $entry ); ?>
				</div>
			</div>
			<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/sidebar-show.php' ); ?>
		</div>
	</div>
</div>
</div>
<br/>
