<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( isset( $message ) && '' !== $message ) {
	if ( FrmAppHelper::is_admin() ) {
		echo '<div class="frm_updated_message">';
		echo FrmAppHelper::kses( $message, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	} else {
		echo FrmAppHelper::maybe_kses( $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( ! isset( $show_messages ) ) {
	$show_messages = array();
}
$show_messages = apply_filters( 'frm_message_list', $show_messages );
if ( is_array( $show_messages ) && count( $show_messages ) > 0 ) {
	?>
	<div class="frm_warning_style" role="alert">
		<ul id="frm_messages">
			<?php
			foreach ( $show_messages as $m ) {
				echo '<li>' . FrmAppHelper::kses( $m, array( 'a', 'br', 'span', 'p' ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</ul>
	</div>
	<?php
}

if ( isset( $warnings ) && is_array( $warnings ) && count( $warnings ) > 0 ) {
	?>
	<div class="frm_warning_style inline" role="alert">
		<div class="frm_warning_heading"> <?php echo esc_html__( 'Warning:', 'formidable' ); ?></div>
		<ul id="frm_warnings">
			<?php
			foreach ( $warnings as $warning ) {
				echo '<li>' . FrmAppHelper::kses( $warning, array( 'a', 'br' ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</ul>
	</div>
	<?php
}

if ( isset( $errors ) && is_array( $errors ) && count( $errors ) > 0 ) {
	?>
	<div class="frm_error_style inline" role="alert">
		<ul id="frm_errors">
			<?php
			foreach ( $errors as $error ) {
				echo '<li>' . FrmAppHelper::kses( $error, array( 'a', 'br' ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</ul>
	</div>
	<?php
}
