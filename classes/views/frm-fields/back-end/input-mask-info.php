<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php esc_html_e( 'To create a custom input mask, you’ll need to use this specific set of symbols:', 'formidable' ); ?>
</p>
<p>
	9 - <?php esc_html_e( 'Numeric', 'formidable' ); ?> (0-9)<br/>
	a - <?php esc_html_e( 'Alphabetical', 'formidable' ); ?> (a-z, A-Z)<br/>
	* - <?php esc_html_e( 'Alphanumeric', 'formidable' ); ?> (0-9, a-z, A-Z)<br/>
</p>
<p>
	<?php esc_html_e( 'Example:', 'formidable' ); ?> 1 (999)-999-9999
</p>
<p>
	<a href="<?php
		echo esc_url(
			FrmAppHelper::admin_upgrade_link(
				array(
					'medium' => 'builder',
					'content' => 'inputmask',
				),
				'knowledgebase/format/'
			)
		);
		?>" target="_blank" rel="noopener">
		<?php esc_html_e( 'See more examples and docs', 'formidable' ); ?>
	</a>
</p>
