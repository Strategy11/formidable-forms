<div class="nav-menus-php">
<div class="wrap">
	<?php FrmStylesHelper::style_menu( 'custom_css' ); ?>

    <p><?php esc_html_e( 'You can add custom css here or in your theme style.css', 'formidable' ) ?></p>

	<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

	<form method="post" id="template">
	    <input type="hidden" name="ID" value="<?php echo esc_attr( $style->ID ) ?>" />
		<input type="hidden" name="<?php echo esc_attr( $frm_style->get_field_name( 'post_title', '' ) ); ?>" value="<?php echo esc_attr( $style->post_title ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( $frm_style->get_field_name( 'menu_order', '' ) ); ?>" value="<?php echo esc_attr( $style->menu_order ); ?>" />
        <input type="hidden" name="style_name" value="frm_style_<?php echo esc_attr( $style->post_name ) ?>" />
		<input type="hidden" name="frm_action" value="save_css" />
        <?php wp_nonce_field( 'frm_custom_css_nonce', 'frm_custom_css' ); ?>

		<textarea name="<?php echo esc_attr( $frm_style->get_field_name( 'custom_css' ) ); ?>" id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( 'false' === wp_get_current_user()->syntax_highlighting ? '' : 'hide-if-js' ); ?>"><?php echo FrmAppHelper::esc_textarea( $style->post_content['custom_css'] ); // WPCS: XSS ok. ?></textarea>

		<?php
		if ( ! empty( $settings ) && $id == 'frm_codemirror_box' ) {
			wp_add_inline_script(
				'code-editor',
				sprintf(
					'jQuery( function() { wp.codeEditor.initialize( "' . esc_attr( $id ) . '", %s ); } );',
					wp_json_encode( $settings )
				)
			);
		}

        foreach ( $style->post_content as $k => $v ) {
            if ( $k == 'custom_css' ) {
                continue;
            }
        ?>
		<input type="hidden" value="<?php echo esc_attr( $v ); ?>" name="<?php echo esc_attr( $frm_style->get_field_name( $k ) ); ?>" />    
		<?php
		}
		?>
		<p class="button-controls"><input type="submit" name="nav-menu-locations" id="nav-menu-locations" class="button button-primary left" value="<?php esc_attr_e( 'Save Changes', 'formidable' ) ?>" /></p>

    </form>
</div>
</div>
