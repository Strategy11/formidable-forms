<div class="general_settings metabox-holder tabs-panel frm_license_box <?php echo ($a == 'general_settings') ? 'frm_block' : 'frm_hidden'; ?>">
<?php if ( ! is_multisite() || is_super_admin() ) { ?>
    <div class="postbox">
        <div class="inside">
			<p class="alignright"><?php printf( __( '%1$sClick here%2$s to get it now', 'formidable' ), '<a href="'. esc_url( FrmAppHelper::make_affiliate_url( 'http://formidablepro.com' ) ) . '">', '</a>' ) ?> &#187;</p>
			<p><?php _e( 'Ready to take your forms to the next level?<br/>Formidable Forms will help you create views, manage data, and get reports.', 'formidable' ) ?></p>

			<p>Already signed up? <a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( 'https://formidablepro.com/knowledgebase/install-formidable-forms/' ) ) ?>" target="_blank"><?php _e( 'Click here', 'formidable' ) ?></a> to get installation instructions and download the pro version.</p>
        </div>
    </div>
<?php } ?>
</div>
