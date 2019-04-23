<input type="submit" id="save_menu_header" class="button button-primary frm-button-primary menu-save" value="<?php esc_attr_e( 'Save Style', 'formidable' ); ?>"  />
<a href="#" class="button button-secondary frm-button-secondary frm_reset_style" data-frmverify="<?php esc_attr_e( 'Really? ', 'formidable' ); ?>">
	<?php esc_attr_e( 'Reset', 'formidable' ); ?>
</a>

<?php if ( isset( $delete_link ) ) { ?>
	<a href="<?php echo esc_url( $delete_link ); ?>" id="frm_delete_style" class="submitdelete deletion" data-frmverify="<?php esc_attr_e( 'Are you sure?', 'formidable' ); ?>">
		<i class="frm_icon_font frm_delete_icon"></i>
	</a>
<?php } ?>
