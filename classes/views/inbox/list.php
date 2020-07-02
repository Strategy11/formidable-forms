<div class="frm_wrap" id="frm_inbox_page">
	<div class="frm_page_container">
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
			<p><?php echo FrmAppHelper::kses( $message['message'], array( 'a', 'p', 'div', 'span', 'br' ) ); // WPCS: XSS ok. ?></p>
		</div>
		<footer>
			<?php echo FrmAppHelper::kses( $message['cta'], array( 'a' ) ); // WPCS: XSS ok. ?>
		</footer>
	</section>
	<?php
}
?>
</div>

<div class="frm_no_items <?php echo esc_attr( $has_messages ? 'frm_hidden' : '' ); ?>" id="frm_empty_inbox">
	<h2><?php esc_html_e( 'You don\'t have any messages', 'formidable' ); ?></h2>
	<p>
		<?php esc_html_e( 'Get the details about new updates, tips, sales, and more. We\'ll keep you in the loop.', 'formidable' ); ?>
		<?php esc_html_e( 'Want more news and email updates?', 'formidable' ); ?>
	</p>
	<form target="_blank" action="//formidablepro.us1.list-manage.com/subscribe/post?u=a4a913790ffb892daacc6f271&id=7e7df15967" method="post" selector="newsletter-form" accept-charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>" class="frm-fields frm-subscribe">
		<p>
			<input type="text" name="EMAIL" value="<?php echo esc_attr( $user->user_email ); ?>" selector="newsletter-email" placeholder="<?php esc_attr_e( 'Email', 'formidable' ); ?>"/>
		</p>
		<input type="hidden" name="group[4505]" value="4" />
		<p>
			<button type="submit" class="button-primary frm-button-primary">
				<?php esc_html_e( 'Subscribe', 'formidable' ); ?>
			</button>
		</p>
	</form>
</div>

	</div>
	</div>
</div>
