<div class="frm_wrap">
	<form method="post">
	<div class="frm_page_container">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => __( 'Manage Styles', 'formidable' ),
			'hide_title'  => true,
			'publish'     => array( 'FrmStylesHelper::save_button', array() ),
			'nav'         => FrmStylesHelper::get_style_menu( 'manage' ),
		)
	);
	?>

	<div class="columns-2">
	<div id="post-body-content">

		<div class="frm-inner-content">

	<h2 class="frm-h2">
		<?php esc_html_e( 'Manage Styles', 'formidable' ); ?>
	</h2>
	<p class="howto">
		<?php printf( esc_html__( 'Easily change which style your forms are using by making changes below.', 'formidable' ), '<a href="?page=formidable-styles&frm_action=new_style">', '</a>' ); ?>
	</p>

	<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

		<input type="hidden" name="frm_action" value="manage_styles"/>
		<table class="widefat fixed striped">
			<thead>
			<tr>
				<th scope="col" class="column-locations">
					<?php esc_html_e( 'Form Title', 'formidable' ); ?>
				</th>
				<th scope="col">
					<?php esc_html_e( 'Assigned Style Templates', 'formidable' ); ?>
				</th>
			</tr>
			</thead>

			<tbody>
				<?php
				if ( $forms ) {
					foreach ( $forms as $form ) {
						$this_style = isset( $form->options['custom_style'] ) ? (int) $form->options['custom_style'] : 1;
						if ( 1 === $this_style ) {
							// use the default style
							$this_style = $default_style->ID;
						}
						?>
				<tr>
					<td class="menu-location-title">
						<strong><?php echo esc_html( empty( $form->name ) ? __( '(no title)', 'formidable' ) : $form->name ); ?></strong>
					</td>
					<td>
						<input type="hidden" name="prev_style[<?php echo esc_attr( $form->id ); ?>]" value="<?php echo esc_attr( $this_style ); ?>" />
						<select name="style[<?php echo esc_attr( $form->id ); ?>]">
							<?php foreach ( $styles as $s ) { ?>
								<option value="<?php echo esc_attr( $s->ID ); ?>" <?php selected( $s->ID, $this_style ); ?>>
									<?php echo esc_html( $s->post_title . ( empty( $s->menu_order ) ? '' : ' (' . __( 'default', 'formidable' ) . ')' ) ); ?>
								</option>
							<?php } ?>
							<option value="" <?php selected( 0, $this_style ); ?>>
								<?php esc_html_e( 'Styling disabled', 'formidable' ); ?>
							</option>
						</select>

					</td>
				</tr>
						<?php
					}
				} else {
					?>
				<tr>
					<td><?php esc_html_e( 'No Forms Found', 'formidable' ); ?></td>
				</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<p>
			<input type="submit" name="submit" class="button button-primary frm-button-primary" value="<?php esc_attr_e( 'Update', 'formidable' ); ?>" />
		</p>
		<?php wp_nonce_field( 'frm_manage_style_nonce', 'frm_manage_style' ); ?>

</div>
</div>
</div>
</form>
</div>
