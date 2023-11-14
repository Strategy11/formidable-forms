<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap" id="frm_inbox_page">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'   => __( 'Inbox', 'formidable' ),
			'publish' => array( 'FrmInboxController::dismiss_all_button', compact( 'messages' ) ),
		)
	);
	?>
	<div id="post-body-content">
	<div id="frm_message_list">
<?php

$has_messages = ! empty( $messages );
foreach ( $messages as $key => $message ) {
	if ( ! isset( $message['icon'] ) ) {
		$message['icon'] = 'frm_tooltip_icon';
	}

	?>
	<section class="frm_inbox_card" data-message="<?php echo esc_attr( $key ); ?>">
		<span class="frm_inbox_card_icon" aria-hidden="true">
			<?php FrmAppHelper::icon_by_class( 'frmfont ' . esc_attr( $message['icon'] ) ); ?>
		</span>
		<header>
			<h3>
				<?php echo esc_html( $message['subject'] ); ?>
			</h3>
			<span class="frm_inbox_date">
				<?php
				if ( ! isset( $message['read'] ) || ! isset( $message['read'][ $user->ID ] ) ) {
					$inbox->mark_read( $key );
					?>
					<span class="frm_inbox_unread"></span>
					<?php
				}

				printf(
					/* translators: %s: Time stamp */
					esc_html__( '%s ago', 'formidable' ),
					esc_html( FrmAppHelper::human_time_diff( $message['created'] ) )
				);
	?>
			</span>
		</header>
		<div class="frm_inbox_body">
			<p><?php echo FrmAppHelper::kses( $message['message'], array( 'a', 'p', 'div', 'span', 'br' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		</div>
		<footer>
			<?php echo FrmAppHelper::kses( $message['cta'], array( 'a' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</footer>
	</section>
	<?php
}
if ( $has_messages ) {
	?>
	<div style="margin:20px"><?php do_action( 'frm_page_footer', array( 'table' => 'inbox' ) ); ?></div>
	<?php
}
?>
</div>

<div class="frm_no_items <?php echo esc_attr( $has_messages ? 'frm_hidden' : '' ); ?>" id="frm_empty_inbox">

	<?php
	FrmAppController::api_email_form(
		'subscribe',
		__( 'You don\'t have any messages', 'formidable' ),
		__( 'Get the details about new updates, tips, sales, and more. We\'ll keep you in the loop.', 'formidable' )
			. "\n" . __( 'Want more news and email updates?', 'formidable' )
	);
	?>

	<button id="frm-add-my-email-address" class="button-primary frm-button-primary"><?php esc_html_e( 'Subscribe', 'formidable' ); ?></button>
</div>

	</div>
</div>
