<?php
/**
 * Gated Content form action settings view
 *
 * @package Formidable
 *
 * @since 6.33
 *
 * @var object                $instance Form action post object. Settings in $instance->post_content.
 * @var array                 $args     Contains `form`, `action_key`, `values`.
 * @var FrmGatedContentAction $this     Action class instance. Use get_field_name() / get_field_id().
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_gc_items           = $instance->post_content['items'] ?? array();
$frm_gc_action_id       = (int) $instance->ID;
$frm_gc_field_name_base = $this->get_field_name( 'items' );
$frm_gc_types           = FrmGatedContentAction::get_types();

// Unique wrapper ID per action instance — prevents JS collisions when multiple
// gated content actions exist on the same form.
$frm_gc_wrapper_id = 'frm_gc_settings_' . $this->number;

// array<string, WP_Post[]> — posts grouped by item type key (e.g. 'page', 'post').
$frm_gc_posts = FrmGatedContentAction::get_posts();

$frm_gc_total_posts      = array_sum( array_map( 'count', $frm_gc_posts ) );
$frm_gc_use_autocomplete = $frm_gc_total_posts > 50;

if ( $frm_gc_use_autocomplete ) {
	wp_enqueue_script( 'jquery-ui-autocomplete' );

	// Pre-encode one autocomplete source per type for reuse in all item rows.
	$frm_gc_posts_source = array();

	foreach ( $frm_gc_posts as $frm_gc_type_key => $frm_gc_type_posts ) {
		$frm_gc_posts_source[ $frm_gc_type_key ] = FrmGatedContentAction::get_posts_autocomplete_source( $frm_gc_type_posts );
	}
	unset( $frm_gc_type_key, $frm_gc_type_posts );
}
?>

<div
	class="frm_gated_content_settings frm-mt-sm"
	id="<?php echo esc_attr( $frm_gc_wrapper_id ); ?>"
	data-item-count="<?php echo count( $frm_gc_items ); ?>"
>

	<?php /* ── Section: Gated content items ─────────────────────────────── */ ?>
	<div class="frm_form_field frm_gc_items_section">
		<h3 class="frm-mb-sm"><?php esc_html_e( 'Gated Content Items', 'formidable' ); ?></h3>

		<ul class="frm_gc_items_list">
			<?php foreach ( $frm_gc_items as $frm_gc_idx => $frm_gc_item ) : ?>
				<?php
				$frm_gc_item_type   = $frm_gc_item['type'] ?? 'page';
				$frm_gc_item_id     = isset( $frm_gc_item['id'] ) ? (int) $frm_gc_item['id'] : 0;
				$frm_gc_item_base   = $frm_gc_field_name_base . '[' . $frm_gc_idx . ']';
				$frm_gc_type_sel_id = $frm_gc_wrapper_id . '_type_' . $frm_gc_idx;
				$is_template        = false;
				include __DIR__ . '/_gated_content_item_row.php';
				?>
			<?php endforeach; ?>
		</ul><!-- .frm_gc_items_list -->

		<?php
		// Template row — hidden, cloned by JS when "Add Item" is clicked.
		// JS assigns id, name, and for attributes after cloning using the item index counter.
		?>
		<template class="frm_gc_item_template">
			<?php
			$frm_gc_item_type   = 'page';
			$frm_gc_item_id     = 0;
			$frm_gc_item_base   = '';
			$frm_gc_type_sel_id = '';
			$frm_gc_idx         = 0;
			$frm_gc_item        = array();
			$is_template        = true;
			include __DIR__ . '/_gated_content_item_row.php';
			?>
		</template>

		<button
			type="button"
			class="frm_gc_add_item button-secondary frm-button-sm"
			data-field-name-base="<?php echo esc_attr( $frm_gc_field_name_base ); ?>"
		>
			+ <?php esc_html_e( 'Add Item', 'formidable' ); ?>
		</button>
	</div><!-- .frm_gc_items_section -->

	<?php /* ── Section: Shortcode reference ─────────────────────────────── */ ?>
	<?php include __DIR__ . '/_gated_content_shortcodes.php'; ?>

</div><!-- .frm_gated_content_settings -->

<?php
unset( $frm_gc_items, $frm_gc_action_id, $frm_gc_field_name_base, $frm_gc_types, $frm_gc_wrapper_id, $frm_gc_posts, $frm_gc_total_posts, $frm_gc_item, $frm_gc_idx, $frm_gc_item_type, $frm_gc_item_id, $frm_gc_item_base, $frm_gc_type_sel_id, $frm_gc_use_autocomplete, $frm_gc_posts_source );
