<?php
/**
 * Pages dropdown view
 *
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @var array  $args     Pages dropdown arguments
 * @var object $pages    List of WordPress pages
 * @var int    $selected Currently selected page ID
 */
?>
<select name="<?php echo esc_attr( $args['field_name'] ); ?>" id="<?php echo esc_attr( $args['field_name'] ); ?>" class="frm-pages-dropdown">
	<option value=""><?php echo esc_html( $args['placeholder'] ); ?></option>
	<?php foreach ( $pages as $page ) { ?>
		<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $selected, $page->ID ); ?>>
			<?php echo esc_html( $args['truncate'] ? FrmAppHelper::truncate( $page->post_title, $args['truncate'] ) : $page->post_title ); ?>
		</option>
	<?php } ?>
</select>
