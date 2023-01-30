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
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/email.jpg' ); ?>" alt="Email Address" height="202px" />
	<h2><?php esc_html_e( 'You don\'t have any messages', 'formidable' ); ?></h2>
	<p>
		<?php esc_html_e( 'Get the details about new updates, tips, sales, and more. We\'ll keep you in the loop.', 'formidable' ); ?>
		<?php esc_html_e( 'Want more news and email updates?', 'formidable' ); ?>
	</p>
<style>
	#_form_3_ { font-size:14px; line-height:1.6; font-family:arial, helvetica, sans-serif; margin:0; }
	._form_hide { display:none; visibility:hidden; }
	._form_show { display:block; visibility:visible; }
	#_form_3_ ._form_element { position:relative; }
	#_form_3_ input[type="text"]._has_error { border-color:#f37c7b; }
	#_form_3_ ._error { display:block; position:absolute; font-size:14px; z-index:10000001; }
	#_form_3_ ._error._above { padding-bottom:4px; bottom:39px; right:0; }
	#_form_3_ ._error._below { padding-top:4px; top:100%; right:0; }
	#_form_3_ ._error._above ._error-arrow { bottom:0; right:15px; border-left:5px solid transparent; border-right:5px solid transparent; border-top:5px solid #f37c7b; }
	#_form_3_ ._error._below ._error-arrow { top:0; right:15px; border-left:5px solid transparent; border-right:5px solid transparent; border-bottom:5px solid #f37c7b; }
	#_form_3_ ._error-inner { padding:8px 12px; background-color:#f37c7b; font-size:14px; font-family:arial, sans-serif; color:#fff; text-align:center; text-decoration:none; -webkit-border-radius:4px; -moz-border-radius:4px; border-radius:4px; }
	#_form_3_ ._error-inner._form_error { margin-bottom:5px; text-align:left; }
	#_form_3_ ._button-wrapper ._error-inner._form_error { position:static; }
	#_form_3_ ._error-inner._no_arrow { margin-bottom:10px; }
	#_form_3_ ._error-arrow { position:absolute; width:0; height:0; }
	#_form_3_ ._error-html { margin-bottom:10px; }
	#_form_3_ { position:relative; }
	#_form_3_:before,#_form_3_:after { content:" "; display:table; }
	#_form_3_:after { clear:both; }
	#_form_3_ ._form-thank-you { position:relative; left:0; right:0; text-align:center; font-size:18px; }
</style>
	<form method="POST" action="https://strategy1137274.activehosted.com/proc.php" id="_form_3_" class="_form _form_3 frm-fields frm-subscribe" novalidate>
		<input type="hidden" name="u" value="3" />
		<input type="hidden" name="f" value="3" />
		<input type="hidden" name="s" />
		<input type="hidden" name="c" value="0" />
		<input type="hidden" name="m" value="0" />
		<input type="hidden" name="act" value="sub" />
		<input type="hidden" name="v" value="2" />
		<div class="_form-content">
			<p>
				<input type="text" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" placeholder="<?php esc_attr_e( 'Type your email', 'formidable' ); ?>" required/>
			</p>
			<div class="_button-wrapper">
				<button id="_form_3_submit" type="submit" class="_submit button-primary frm-button-primary">
					<?php esc_html_e( 'Subscribe', 'formidable' ); ?>
				</button>
			</div>
			<div class="_clear-element"></div>
		</div>
		<div class="_form-thank-you" style="display:none;"></div>
		<?php do_action( 'frm_page_footer', array( 'table' => 'inbox' ) ); ?>
	</form>
</div>

	</div>
</div>
