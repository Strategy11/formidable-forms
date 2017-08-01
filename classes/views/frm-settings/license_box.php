<div class="general_settings metabox-holder tabs-panel frm_license_box <?php echo ($a == 'general_settings') ? 'frm_block' : 'frm_hidden'; ?>">
<?php if ( ! is_multisite() || current_user_can( 'setup_network' ) ) { ?>
    <div class="postbox">
        <div class="inside">
			<p><?php _e( 'Get 15 more field types, including multiple file upload fields and cascading lookup fields. PLUS multi-page forms, calculations, repeatable sections, confirmation fields, conditional logic, front-end editing, views, data management, and graph & stat reporting.', 'formidable' ) ?>
			<?php printf( __( '%1$sClick here%2$s to go Pro.', 'formidable' ), '<a href="' . esc_url( FrmAppHelper::make_affiliate_url( 'https://formidableforms.com' ) ) . '">', '</a>' ) ?>
			</p>

			<p>Already have a Pro license? <a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( 'https://formidableforms.com/knowledgebase/install-formidable-forms/' ) ) ?>" target="_blank"><?php _e( 'Click here', 'formidable' ) ?></a> to get installation instructions and download the pro version.</p>
        </div>
    </div>
<?php } ?>
</div>
