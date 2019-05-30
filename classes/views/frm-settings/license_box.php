<p>You're using Formidable Forms Lite - no license needed. Enjoy! 🙂</p>
<p>
		<?php
		printf(
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
			esc_html__( 'To unlock more features consider %1$supgrading to PRO%2$s.', 'formidable' ),
			'<a href="' . esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( 'settings-license' ) ) ) . '">',
			'</a>'
		);
		?>
</p>
<p>
<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( 'settings-license', 'knowledgebase/install-formidable-forms/' ) ) ); ?>" target="_blank">
	<?php esc_html_e( 'Already purchased?', 'formidable' ); ?>
</a>
</p>
<div class="clear"></div>
