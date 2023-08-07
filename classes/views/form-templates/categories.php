<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<ul class="frm-form-templates-categories">
	<?php foreach ( $categories as $category ) { ?>
		<?php
		$classes       = '';
		$templates     = isset( $categorized_templates[ $category ] ) ? $categorized_templates[ $category ] : $all_templates;
		$count         = count( $templates );
		$category_slug = sanitize_title( $category );

		if ( 'all-templates' === $category_slug ) {
			echo '<li class="frm-form-templates-divider"></li>';
			$classes .= ' frm-current';
		}
		?>
		<li class="frm-form-templates-cat-item<?php echo esc_attr( $classes ); ?>" data-category="<?php echo esc_attr( $category_slug ); ?>">
			<span class="frm-form-templates-cat-text"><?php echo esc_html( $category ); ?></span>
			<span class="frm-form-templates-cat-count"><?php echo esc_html( $count ); ?></span>
		</li><!-- .frm-form-templates-cat-item -->
	<?php } ?>
</ul><!-- .frm-form-templates-categories -->
