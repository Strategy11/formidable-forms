<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<span class="<?php echo esc_attr( $component_class ); ?> frm-colorpicker " <?php echo esc_attr( $component_attr ); ?>>
	<?php include __DIR__ . '/colorpicker-input.php'; ?>
</span>
