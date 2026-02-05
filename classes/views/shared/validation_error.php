<?php
/**
 * Validation error view
 *
 * @since x.x
 *
 * @var array $args Arguments including id, errors, and class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<span id="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>">
	<?php
	if ( is_array( $args['errors'] ) ) {
		foreach ( $args['errors'] as $key => $msg ) {
			?>
			<span frm-error="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $msg ); ?></span>
			<?php
		}
	} else {
		echo '<span>' . esc_html( $args['errors'] ) . '</span>';
	}
	?>
</span>
