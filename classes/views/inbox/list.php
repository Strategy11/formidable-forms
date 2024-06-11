<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-inbox-wrapper">
	<div class="frm-tabs-navs">
		<ul class="frm-flex-box">
			<li class="frm-active">
				<?php
				esc_html_e( 'Inbox', 'formidable' );
				echo wp_kses_post( FrmInboxController::get_notice_count( false ) );
				?>
			</li>
			<li><?php esc_html_e( 'Dismissed', 'formidable' ); ?></li>
		</ul>
	</div>
	<div class="frm-tabs-delimiter">
		<span class="frm-tabs-active-underline"></span>
	</div>
	<div class="frm-tabs-container">
		<div class="frm-tabs-slide-track frm-flex-box">
			<?php foreach ( $template as $key => $template_item ) : ?>
				<?php if ( in_array( $key, array( 'unread', 'dismissed' ), true ) ) : ?>
					<?php $tab_classname = 'dismissed' === $key ? 'frm-dismissed-inbox-messages' : ''; ?>

					<div class="frm-scrollbar-wrapper <?php echo 'unread' === $key ? 'frm-active' : ''; ?>">
						<div class="<?php echo esc_attr( $tab_classname ); ?>">
							<?php if ( 'unread' === $key ) : ?>
								<div id="frm_empty_inbox" class="<?php echo esc_attr( $subscribe_inbox_classnames ); ?>">
									<?php
										FrmAppController::api_email_form(
											'subscribe',
											__( 'You don\'t have any new messages', 'formidable' ),
											__( 'Get the details about new updates, tips, sales, and more. We\'ll keep you in the loop.', 'formidable' )
										);
									?>
									<button id="frm-add-my-email-address" class="button-primary frm-button-primary"><?php esc_html_e( 'Subscribe', 'formidable' ); ?></button>
								</div>
							<?php endif; ?>

							<?php foreach ( $template_item as $tab_key => $message ) : ?>
								<div class="frm-inbox-message-container frm-flex-col" data-message="<?php echo esc_attr( $tab_key ); ?>" >
									<div class="frm-inbox-message-heading frm-flex-box frm-justify-between">
										<div class="frm-flex-col">
											<span>
												<?php
												printf(
													/* translators: %s: Time stamp */
													esc_html__( '%s ago', 'formidable' ),
													esc_html( FrmAppHelper::human_time_diff( $message['created'] ) )
												);
												?>
											</span>
											<h3><?php echo esc_html( $message['subject'] ); ?></h3>
										</div>
										<?php if ( 'unread' === $key ) : ?>
											<a href="#" class="frm_inbox_dismiss"><?php esc_html_e( 'Dismiss', 'formidable' ); ?></a>
										<?php endif; ?>
									</div>
									<p><?php echo wp_kses_post( $message['message'] ); ?></p>
									<?php echo wp_kses( $message['cta'], array( 'a' => array( 'href' => true ) ) ); ?>
								</div>
							<?php endforeach; ?>

						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>
