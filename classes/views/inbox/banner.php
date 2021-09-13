<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_banner">
	<?php if ( ! empty( $message['emoji'] ) ) { ?>
		<span class="frm-banner-emoji"><?php echo esc_html( $message['emoji'] ); ?></span>
	<?php } ?>
	<span class="frm-banner-title"><?php echo esc_html( $message['subject'] ); ?></span>
	<span class="frm-banner-content"><?php echo esc_html( $message['banner'] ); ?></span>
	<span class="frm-banner-cta"><?php echo FrmAppHelper::kses( $message['cta'], 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<?php /* TODO: dismiss icon */ ?>
</div>
