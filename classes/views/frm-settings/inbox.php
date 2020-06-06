<?php
foreach ( $messages as $key => $message ) {
	if ( ! isset( $message['icon'] ) ) {
		$message['icon'] = 'frm_tooltip_icon';
	}
	?>
	<section class="frm_inbox_card">
		<span class="frm_inbox_card_icon" aria-hidden="true">
			<?php FrmAppHelper::icon_by_class( 'frmfont ' . esc_attr( $message['icon'] ) ); ?>
		</span>
		<header>
			<h3>
				<?php echo esc_html( $message['subject'] ); ?>
			</h3>
			<span class="frm_inbox_date">
				<?php
				if ( ! isset( $message['read'] ) || ! isset( $message['read'][ get_current_user_id() ] ) ) {
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
