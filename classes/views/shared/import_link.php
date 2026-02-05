<?php
/**
 * Import link view
 *
 * @since 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @var string $type Button type
 */
?>
<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-import' ) ); ?>" class="button frm-button-<?php echo esc_attr( $type ); ?> frm_animate_bg">
	<?php esc_html_e( 'Import', 'formidable' ); ?>
</a>
