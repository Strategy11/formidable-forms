<div class="clear"></div>
<?php
if ( isset( $message ) && '' !== $message ) {
	if ( FrmAppHelper::is_admin() ) {
		echo '<div id="message" class="updated frm_updated_message">';
		echo $message;
		echo '</div>';
	} else {
		echo $message;
	}
}

if ( isset( $errors ) && is_array( $errors ) && count( $errors ) > 0 ) {
?>
	<div class="error">
		<ul id="frm_errors">
			<?php
			foreach ( $errors as $error ) {
				echo '<li>' . FrmAppHelper::kses( $error, 'a' ) . '</li>';
			}
			?>
		</ul>
	</div>
<?php
}
