<div class="general_settings metabox-holder tabs-panel frm_license_box <?php echo esc_attr( 'general_settings' === $a ? 'frm_block' : 'frm_hidden' ); ?>">
<?php if ( ! is_multisite() || current_user_can( 'setup_network' ) ) { ?>
    <div class="postbox">
        <div class="inside">
			<p><?php esc_html_e( 'Get more field types including multiple file upload and cascading lookups. PLUS multi-page forms, calculations, repeatable sections, confirmation fields, conditional logic, front-end editing, views, data management, and graph & stat reporting.', 'formidable' ); ?>
			<?php printf( esc_html__( '%1$sClick here%2$s to go Pro.', 'formidable' ), '<a href="' . esc_url( FrmAppHelper::make_affiliate_url( 'https://formidableforms.com' ) ) . '">', '</a>' ); ?>
			</p>

			<p>Already have a Pro license? <a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( 'https://formidableforms.com/knowledgebase/install-formidable-forms/' ) ) ?>" target="_blank"><?php esc_html_e( 'Click here', 'formidable' ); ?></a> to get installation instructions and download the pro version.</p>
        </div>
    </div>
<?php } ?>
</div>
