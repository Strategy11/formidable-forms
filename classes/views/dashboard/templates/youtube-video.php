<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="<?php echo esc_attr( $classes ); ?>">
	<iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $template['id'] ); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture;" allowfullscreen></iframe>
</div>
