<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto frm-mt-sm frm-italic">
	<?php esc_html_e( 'To create a custom input mask, you’ll need to use this specific set of symbols:', 'formidable' ); ?>
</p>
<p class="frm-mt-0 frm-text-md">
	<span class="frm-block frm-mb-2xs">9 - <?php esc_html_e( 'Numeric', 'formidable' ); ?> (0-9)</span>
	<span class="frm-block frm-mb-2xs">a - <?php esc_html_e( 'Alphabetical', 'formidable' ); ?> (a-z, A-Z)</span>
	<span class="frm-block frm-mb-2xs">* - <?php esc_html_e( 'Alphanumeric', 'formidable' ); ?> (0-9, a-z, A-Z)</span>
</p>
<span class="frm-hr"></span>
<p class="frm-h-stack-xs">
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_file_text2_icon frm_svg24' ); ?>
	<span>
	<?php
	printf(
		// translators: %1s: open anchor tag, %2s: close anchor tag
		esc_html__( 'More examples on our %1$sdocs page%2$s.', 'formidable' ),
		'<a class="frm-link-secondary" href="' . esc_url(
			FrmAppHelper::admin_upgrade_link(
				array(
					'medium'  => 'builder',
					'content' => 'inputmask',
				),
				'knowledgebase/format/'
			)
		) . '" target="_blank" rel="noopener">',
		'</a>'
	);
	?>
	</span>
</p>
