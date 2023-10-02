<?php
/**
 * Form Templates - Categories.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<ul class="frm-form-templates-categories">
	<?php foreach ( $categories as $category_slug => $category_data ) { ?>
		<?php
		$classes = 'frm-form-templates-cat-item';

		if ( 'all-templates' === $category_slug ) {
			echo '<li class="frm-form-templates-divider"></li>';
			$classes .= ' frm-current';
		}
		?>

		<li class="<?php echo esc_attr( $classes ); ?>" data-category="<?php echo esc_attr( $category_slug ); ?>">
			<span class="frm-form-templates-cat-text"><?php echo esc_html( $category_data['name'] ); ?></span>
			<span class="frm-form-templates-cat-count"><?php echo esc_html( $category_data['count'] ); ?></span>
		</li><!-- .frm-form-templates-cat-item -->
	<?php } ?>
</ul><!-- .frm-form-templates-categories -->
