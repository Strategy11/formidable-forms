<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-inbox-wrapper">
	<div class="frm-tabs-navs">
		<ul class="frm-flex-box">
			<li class="frm-active"><?php echo esc_html__( 'Inbox', 'formidable' ); ?></li>
			<li><?php echo esc_html__( 'Dismissed', 'formidable' ); ?></li>
		</ul>
	</div>
	<div class="frm-tabs-delimiter">
		<span class="frm-tabs-active-underline"></span>
	</div>
	<div class="frm-tabs-container">
		<div class="frm-tabs-slide-track frm-flex-box">
			<div class="frm-active frm-scrollbar-wrapper">
				<div>
					<div id="frm_empty_inbox" class="<?php echo esc_attr( $subscribe_inbox_classnames ); ?>">
						<?php
							FrmAppController::api_email_form(
								'subscribe',
								__( 'You don\'t have any messages', 'formidable' ),
								__( 'Get the details about new updates, tips, sales, and more. We\'ll keep you in the loop.', 'formidable' )
							);
							?>
						<button id="frm-add-my-email-address" class="button-primary frm-button-primary"><?php esc_html_e( 'Subscribe', 'formidable' ); ?></button>
					</div>

					<?php foreach ( $template['unread'] as $key => $message ) : ?>
						<div class="frm-inbox-message-container frm-flex-col" data-message="<?php echo esc_attr( $key ); ?>" >
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
								<a href="#" class="frm_inbox_dismiss">Dismiss</a>
							</div>
							<p><?php echo wp_kses_post( $message['message'] ); ?></p>
							<?php echo wp_kses( $message['cta'], array( 'a' => array( 'href' => true ) ) ); ?>
						</div>
					<?php endforeach; ?>
				</div>

			</div>

			<div class="frm-scrollbar-wrapper">
				<div class="frm-dismissed-inbox-messages">
				<?php foreach ( $template['dismissed'] as $key => $message ) : ?>
					<div class="frm-inbox-message-container frm-flex-col">
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
								<h3>
									<?php echo esc_html( $message['subject'] ); ?>
								</h3>
							</div>
						</div>
						<p><?php echo wp_kses_post( $message['message'] ); ?></p>
						<?php echo wp_kses( $message['cta'], array( 'a' => array( 'href' => true ) ) ); ?>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>
