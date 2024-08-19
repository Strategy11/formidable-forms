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
	// Define a callback function to add 'data-action' attribute to allowed HTML tags
	$add_data_action_callback = function ( $allowed_html ) {
		$allowed_html['span']['data-action'] = true;
		return $allowed_html;
	};
	?>
	<div class="frm_warning_style" role="alert">
		<ul id="frm_messages">
			<?php
			// Add the callback function to the 'frm_striphtml_allowed_tags' filter
			add_filter( 'frm_striphtml_allowed_tags', $add_data_action_callback );
			foreach ( $show_messages as $m ) {
				echo '<li>' . FrmAppHelper::kses( $m, array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			// Remove the callback function from the 'frm_striphtml_allowed_tags' filter
			remove_filter( 'frm_striphtml_allowed_tags', $add_data_action_callback );
			?>
		</ul>
	</div>
	<?php
}//end if

if ( ! empty( $warnings ) && is_array( $warnings ) ) {
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

if ( ! empty( $errors ) && is_array( $errors ) ) {
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

if ( ! empty( $notes ) && is_array( $notes ) ) {
	foreach ( $notes as $note ) {
		?>
		<div class="frm_note_style">
			<?php
			if ( is_string( $note ) ) {
				echo esc_html( $note );
			} elseif ( is_callable( $note ) ) {
				// If $note is a function call it so we can handle cases where we don't want to wrap the whole note in esc_html.
				$note();
			}
			?>
		</div>
		<?php
	}
}
