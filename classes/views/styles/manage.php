<div class="nav-menus-php">
<div class="wrap">
    <?php FrmStylesHelper::style_menu('manage'); ?>

	<p><?php printf(__( 'Easily change which template your forms are using by making changes below.', 'formidable' ), '<a href="?page=formidable-styles&frm_action=new_style">', '</a>'); ?></p>

	<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

	<div id="menu-locations-wrap">
	<form method="post">
	    <input type="hidden" name="frm_action" value="manage_styles"/>
		<table class="widefat fixed" id="menu-locations-table">
			<thead>
			<tr>
				<th scope="col" class="manage-column column-locations"><?php _e( 'Form Title', 'formidable' ) ?></th>
				<th scope="col" class="manage-column column-menus"><?php _e( 'Assigned Style Templates', 'formidable' ) ?></th>
			</tr>
			</thead>

			<tbody class="menu-locations">
			    <?php
			    if ( $forms ) {
			        foreach ( $forms as $form ) {
			            $this_style = isset($form->options['custom_style']) ? (int) $form->options['custom_style'] : 1;
			            if ( 1 == $this_style ) {
			                // use the default style
			                $this_style = $default_style->ID;
			            }
			        ?>
				<tr id="menu-locations-row">
					<td class="menu-location-title"><strong><?php echo empty($form->name) ? __( '(no title)') : $form->name ?></strong></td>
					<td class="menu-location-menus">
					    <input type="hidden" name="prev_style[<?php echo esc_attr( $form->id ) ?>]" value="<?php echo esc_attr( $this_style ) ?>" />
					    <select name="style[<?php echo esc_attr( $form->id ) ?>]">
         		            <?php foreach ( $styles as $s ) { ?>
								<option value="<?php echo esc_attr( $s->ID ) ?>" <?php selected( $s->ID, $this_style ) ?>>
									<?php echo esc_html( $s->post_title . ( empty( $s->menu_order ) ? '' : ' (' . __( 'default', 'formidable' ) . ')' ) ) ?>
								</option>
         		            <?php } ?>
         		            <option value="" <?php selected(0, $this_style) ?>><?php _e( 'Styling disabled', 'formidable' ) ?></option>
         		        </select>

					</td><!-- .menu-location-menus -->
				</tr><!-- #menu-locations-row -->
				<?php
				    }
				} else {
				?>
				<tr>
				    <td><?php _e( 'No Forms Found', 'formidable' ) ?></td>
				</tr>
				<?php
				}
				?>
			</tbody>
		</table>
		<p class="button-controls"><input type="submit" name="nav-menu-locations" id="nav-menu-locations" class="button button-primary left" value="<?php esc_attr_e( 'Save Changes', 'formidable' ) ?>" /></p>
		<?php wp_nonce_field( 'frm_manage_style_nonce', 'frm_manage_style' ); ?>
	</form>
</div><!-- #menu-locations-wrap -->
</div><!-- /.wrap-->
