<?php if ( isset( $delete_link ) ) { ?>
	<a href="<?php echo esc_url( $delete_link ); ?>" id="frm_delete_style" class="submitdelete deletion alignright" data-frmverify="<?php esc_attr_e( 'Permanently delete this style?', 'formidable' ); ?>">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_delete_icon', array( 'aria-hidden' => 'true' ) ); ?>
	</a>
<?php } ?>

<input type="submit" id="save_menu_header" class="button button-primary frm-button-primary menu-save" value="<?php esc_attr_e( 'Update', 'formidable' ); ?>"  />
<a href="#" class="button button-secondary frm-button-secondary" id="frm_reset_style" data-resetstyle="1" data-frmverify="<?php esc_attr_e( 'Reset this style back to the default? ', 'formidable' ); ?>">
	<?php esc_attr_e( 'Reset', 'formidable' ); ?>
</a>
