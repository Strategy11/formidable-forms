<div id="frm-publishing" class="<?php echo esc_attr( 'draft' === $values['status'] ? 'frm-is-draft' : '' ); ?>">

			<?php if ( 'settings' == FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' ) ) { ?>
				<button class="frm_submit_form frm_submit_settings_btn button-primary frm-button-primary frm_button_submit" type="button" id="frm_submit_side_top" >
					<?php esc_html_e( 'Update', 'formidable' ); ?>
				</button>
			<?php } else { ?>
				<button class="frm_submit_form frm_submit_<?php echo ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_'; ?>ajax button-primary frm-button-primary frm_button_submit" type="button" id="frm_submit_side_top">
					<?php esc_html_e( 'Update', 'formidable' ); ?>
				</button>
			<?php } ?>
			<div id="frm-preview-action">
				<?php if ( ( ! isset( $hide_preview ) || ! $hide_preview ) && isset( $values['form_key'] ) ) { ?>
					<div class="preview dropdown">
						<a href="#" id="frm-previewDrop" class="frm-dropdown-toggle button frm-button-secondary" data-toggle="dropdown"><?php esc_html_e( 'Preview', 'formidable' ); ?> <b class="caret"></b></a>

						<ul class="frm-dropdown-menu <?php echo esc_attr( is_rtl() ? 'pull-left' : 'pull-right' ); ?>" role="menu" aria-labelledby="frm-previewDrop">
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
						</ul>
					</div>
				<?php } ?>
			</div>

	<div class="clear"></div>
</div>
<div class="clear"></div>
