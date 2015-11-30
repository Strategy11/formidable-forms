<div class="nav-menus-php">
<div class="wrap">
    <?php FrmStylesHelper::style_menu(); ?>

	<?php include(FrmAppHelper::plugin_path() .'/classes/views/shared/errors.php'); ?>

	<?php do_action( 'frm_style_switcher', $style, $styles ) ?>

	<form id="frm_styling_form" action="" name="frm_styling_form" method="post">
	    <input type="hidden" name="ID" value="<?php echo esc_attr( $style->ID ) ?>" />
		<input type="hidden" name="frm_action" value="save" />
        <textarea name="<?php echo esc_attr( $frm_style->get_field_name('custom_css') ) ?>" class="frm_hidden"><?php echo FrmAppHelper::esc_textarea( $style->post_content['custom_css'] ) ?></textarea>
		<?php wp_nonce_field( 'frm_style_nonce', 'frm_style' ); ?>

	<div id="nav-menus-frame">
	<div id="menu-settings-column" class="metabox-holder">
		<div class="clear"></div>

		<div class="styling_settings">
		    <input type="hidden" name="style_name" value="frm_style_<?php echo esc_attr( $style->post_name ) ?>" />
			<?php FrmStylesController::do_accordion_sections( FrmStylesController::$screen, 'side', compact('style', 'frm_style') ); ?>
		</div>

	</div><!-- /#menu-settings-column -->


	<div id="menu-management-liquid">
		<div id="menu-management">
				<div class="menu-edit blank-slate">
					<div id="nav-menu-header">
						<div class="major-publishing-actions">
							<label class="menu-name-label howto open-label" for="menu-name">
								<span><?php _e( 'Style Name', 'formidable' ) ?></span>
								<input id="menu-name" name="<?php echo esc_attr( $frm_style->get_field_name('post_title', '') ); ?>" type="text" class="menu-name regular-text menu-item-textbox" title="<?php esc_attr_e( 'Enter style name here', 'formidable' ) ?>" value="<?php echo esc_attr( $style->post_title ) ?>" />
							</label>

							<input name="prev_menu_order" type="hidden" value="<?php echo esc_attr($style->menu_order) ?>" />
							<label class="menu-name-label howto open-label default-style-box" for="menu_order">
							<span>
							<?php if ( $style->menu_order ) { ?>
							    <input name="<?php echo esc_attr( $frm_style->get_field_name('menu_order', '') ); ?>" type="hidden" value="1" />
							    <input id="menu_order" disabled="disabled" type="checkbox" value="1" <?php checked($style->menu_order, 1) ?> />
							<?php } else { ?>
								<input id="menu_order" name="<?php echo esc_attr( $frm_style->get_field_name('menu_order', '') ); ?>" type="checkbox" value="1" <?php checked($style->menu_order, 1) ?> />
							<?php } ?>
							    <?php _e( 'Make default style', 'formidable' ) ?></span>
							</label>

							<div class="publishing-action">
								<input type="submit" id="save_menu_header" class="button button-primary menu-save" value="<?php esc_attr_e( 'Save Style', 'formidable' ); ?>"  />
							</div><!-- END .publishing-action -->
						</div><!-- END .major-publishing-actions -->
					</div><!-- END .nav-menu-header -->
					<div id="post-body">
						<div id="post-body-content">

							<?php include( dirname(__FILE__) .'/_sample_form.php') ?>

						</div><!-- /#post-body-content -->
					</div><!-- /#post-body -->
					<div id="nav-menu-footer" class="submitbox">
						<div class="major-publishing-actions">
						    <?php if ( ! empty( $style->ID ) && empty( $style->menu_order ) ) { ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=destroy&id=' . $style->ID ) ); ?>" id="frm_delete_style" class="submitdelete deletion" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete that style?', 'formidable' ) ?>')"><?php _e( 'Delete Style', 'formidable' ) ?></a>
						    <?php } ?>
						    <?php
						    if ( $style->ID ) {
							    echo '<span class="howto"><span>.frm_style_'. esc_attr( $style->post_name ) .'</span></span>';
							} ?>
                            <div class="publishing-action">
                                <input type="button" value="<?php esc_attr_e( 'Reset to Default', 'formidable' ) ?>" class="button-secondary frm_reset_style" />
								<input type="submit" id="save_menu_header" class="button button-primary menu-save" value="<?php esc_attr_e( 'Save Style', 'formidable' ); ?>"  />
							</div><!-- END .publishing-action -->
						</div><!-- END .major-publishing-actions -->
					</div><!-- /#nav-menu-footer -->
				</div><!-- /.menu-edit -->
		</div><!-- /#menu-management -->
	</div><!-- /#menu-management-liquid -->
	</div><!-- /#nav-menus-frame -->
	</form>
</div>
</div>

<div id="this_css"></div>
