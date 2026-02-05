<?php
/**
 * Solutions page options view
 *
 * @since x.x
 *
 * @var array $pages Array of page information including type, label, and name
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<h3>Choose New Page Title</h3>

<?php foreach ( $pages as $page ) : ?>
	<p>
		<label for="pages_<?php echo esc_html( $page['type'] ); ?>">
			<?php echo esc_html( $page['label'] ); ?>
		</label>
		<input type="text" name="pages[<?php echo esc_html( $page['type'] ); ?>]" value="<?php echo esc_attr( $page['name'] ); ?>" id="pages_<?php echo esc_html( $page['type'] ); ?>" required /><?php // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong ?>
	</p>
<?php endforeach; ?>
