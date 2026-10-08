<?php
/**
 * Add-Ons categories.
 *
 * @package Formidable
 *
 * @var array<string, array{name: string, count: int}> $categories Categories keyed by slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<ul role="group" class="frm-page-skeleton-categories frm-flex-col frm-gap-xs" aria-label="<?php esc_attr_e( 'Categories', 'formidable' ); ?>">
	<?php foreach ( $categories as $category_slug => $category_data ) { ?>
		<?php
		$classes    = 'frm-page-skeleton-cat frm-flex-box frm-justify-between frm-font-medium';
		$aria_label = sprintf(
			// translators: %1$s: category name, %2$d: number of items in the category
			esc_html__( '%1$s category, %2$d items', 'formidable' ),
			esc_html( $category_data['name'] ),
			esc_html( $category_data['count'] )
		);

		if ( 'all-items' === $category_slug || 'basic' === $category_slug ) {
			echo '<li aria-hidden="true" class="frm-page-skeleton-divider frm-mt-xs frm-mb-xs"></li>';
		}

		if ( 'all-items' === $category_slug ) {
			$classes .= ' frm-current';
		}
		$control_attrs = array(
			'aria-pressed' => 'all-items' === $category_slug ? 'true' : 'false',
		);
		?>

		<li class="<?php echo esc_attr( $classes ); ?>" data-category="<?php echo esc_attr( $category_slug ); ?>" tabindex="0" role="button"<?php FrmAppHelper::array_to_html_params( $control_attrs, true ); ?> aria-label="<?php echo esc_attr( $aria_label ); ?>">
			<span class="frm-page-skeleton-cat-text"><?php echo esc_html( $category_data['name'] ); ?></span>
			<span class="frm-page-skeleton-cat-count"><?php echo esc_html( $category_data['count'] ); ?></span>
		</li>
		<?php
	}//end foreach
	?>
</ul>
