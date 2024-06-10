<?php
/**
 * Add-Ons categories.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<ul class="frm-addons-categories frm-flex-col frm-gap-xs" aria-label="<?php esc_attr_e( 'Categories', 'formidable' ); ?>">
	<?php foreach ( $categories as $category_slug => $category_data ) { ?>
		<?php
		$classes    = 'frm-addons-cat-item frm-flex-box frm-justify-between frm-font-medium';
		$aria_label = sprintf(
			// translators: %1$s: category name, %2$d: number of items in the category
			esc_html__( '%1$s category, %2$d items', 'formidable' ),
			esc_html( $category_data['name'] ),
			esc_html( $category_data['count'] )
		);

		if ( 'all-addons' === $category_slug ) {
			echo '<li class="frm-addons-divider"></li>';
			$classes .= ' frm-current';
		}
		?>

		<li class="<?php echo esc_attr( $classes ); ?>" data-category="<?php echo esc_attr( $category_slug ); ?>" role="link" tabindex="0" aria-label="<?php echo esc_attr( $aria_label ); ?>">
			<span class="frm-addons-cat-text"><?php echo esc_html( $category_data['name'] ); ?></span>
			<span class="frm-addons-cat-count"><?php echo esc_html( $category_data['count'] ); ?></span>
		</li>
		<?php
	}//end foreach
	?>
</ul>
