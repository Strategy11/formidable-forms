<?php
/**
 * Gated Content form action settings view
 *
 * @package Formidable
 *
 * @since x.x
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
$frm_gc_wrapper_id = 'frm_gc_settings_' . esc_attr( $this->number );

$frm_gc_pages = get_pages(
	array(
		'post_status' => array( 'publish', 'private' ),
		'sort_column' => 'post_title',
		'sort_order'  => 'ASC',
	)
);
?>

<div
	class="frm_gated_content_settings"
	id="<?php echo esc_attr( $frm_gc_wrapper_id ); ?>"
	data-item-count="<?php echo count( $frm_gc_items ); ?>"
>

	<?php // ── Section: Gated content items ─────────────────────────────── ?>
	<div class="frm_form_field frm_gc_items_section">
		<label><?php esc_html_e( 'Gated Content Items', 'formidable' ); ?></label>

		<ul class="frm_gc_items_list">
			<?php foreach ( $frm_gc_items as $frm_gc_idx => $frm_gc_item ) : ?>
				<?php
				$frm_gc_item_type    = isset( $frm_gc_item['type'] ) ? $frm_gc_item['type'] : 'page';
				$frm_gc_item_id      = isset( $frm_gc_item['id'] ) ? (int) $frm_gc_item['id'] : 0;
				$frm_gc_item_base    = $frm_gc_field_name_base . '[' . $frm_gc_idx . ']';
				$frm_gc_type_sel_id  = $frm_gc_wrapper_id . '_type_' . $frm_gc_idx;
				$frm_gc_page_sel_id  = $frm_gc_wrapper_id . '_id_page_' . $frm_gc_idx;
				?>
				<li class="frm_gc_item_row frm_grid_container">

					<?php // ── Col 1: Type (3/12) ──────────────────────── ?>
					<div class="frm3">
						<label for="<?php echo esc_attr( $frm_gc_type_sel_id ); ?>">
							<?php esc_html_e( 'Type', 'formidable' ); ?>
						</label>
						<select
							id="<?php echo esc_attr( $frm_gc_type_sel_id ); ?>"
							name="<?php echo esc_attr( $frm_gc_item_base . '[type]' ); ?>"
							class="frm-gc-item-type"
						>
							<?php foreach ( $frm_gc_types as $frm_gc_type_key => $frm_gc_type ) : ?>
								<option
									value="<?php echo esc_attr( $frm_gc_type_key ); ?>"
									<?php selected( $frm_gc_item_type, $frm_gc_type_key ); ?>
									<?php disabled( ! empty( $frm_gc_type['disabled'] ) ); ?>
								>
									<?php echo esc_html( $frm_gc_type['label'] ); ?>
									<?php if ( ! empty( $frm_gc_type['pro'] ) ) : ?>
										<?php esc_html_e( '(Pro)', 'formidable' ); ?>
									<?php endif; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div><!-- .frm3 -->

					<?php // ── Col 2: Type-specific settings (8/12) ───── ?>
					<div class="frm8 frm-gc-item-settings">

						<?php
						// Page type settings.
						// Only add name when this type is active — prevents duplicate [id] values
						// from being submitted when the user switches types via JS.
						?>
						<div
							class="frm-gc-type-settings"
							data-type="page"
							<?php echo 'page' !== $frm_gc_item_type ? 'hidden' : ''; ?>
						>
							<label for="<?php echo esc_attr( $frm_gc_page_sel_id ); ?>">
								<?php esc_html_e( 'WordPress page', 'formidable' ); ?>
							</label>
							<select
								id="<?php echo esc_attr( $frm_gc_page_sel_id ); ?>"
								data-frm-gc-field="id"
								<?php if ( 'page' === $frm_gc_item_type ) : ?>
									name="<?php echo esc_attr( $frm_gc_item_base . '[id]' ); ?>"
								<?php endif; ?>
							>
								<option value=""><?php esc_html_e( '— Select a page —', 'formidable' ); ?></option>
								<?php foreach ( $frm_gc_pages as $frm_gc_page ) : ?>
									<option
										value="<?php echo esc_attr( $frm_gc_page->ID ); ?>"
										<?php selected( $frm_gc_item_id, $frm_gc_page->ID ); ?>
									>
										<?php echo esc_html( $frm_gc_page->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div><!-- [data-type="page"] -->

						<?php
						/**
						 * Fires after the built-in type settings for an existing gated content item row.
						 *
						 * Pro and PDF plugins use this to render their own type-specific settings.
						 *
						 * Guidelines for hook callbacks:
						 * - Wrap each type's settings in `<div class="frm-gc-type-settings" data-type="{TYPE}">`.
						 * - Add the `hidden` attribute when `$active_type !== '{TYPE}'`.
						 * - Include a `<label for="{ID}">` and `id="{ID}"` on the select, where
						 *   `{ID}` follows the pattern `{$frm_gc_item_base}_id_{TYPE}` (replacing `[` and `]`
						 *   with `_`). JS will regenerate these for template-cloned rows.
						 * - Add `data-frm-gc-field="id"` to the select so JS can manage its name on type change.
						 * - Only add a `name` attribute when `$active_type === '{TYPE}'`.
						 *
						 * @since x.x
						 *
						 * @param string $frm_gc_item_type  Active type key for this item (e.g. 'page', 'frm_file').
						 * @param int    $frm_gc_idx        Zero-based index of this item in the items array.
						 * @param array  $frm_gc_item       Saved item data.
						 * @param string $frm_gc_item_base  Field name prefix for this item, e.g. `frm_form_action[X][post_content][items][0]`.
						 */
						do_action( 'frm_gated_content_item_settings', $frm_gc_item_type, $frm_gc_idx, $frm_gc_item, $frm_gc_item_base );
						?>
					</div><!-- .frm8 -->

					<?php // ── Col 3: Delete button (1/12) ─────────────── ?>
					<div class="frm1 frm-gc-item-delete">
						<?php // Hidden label spacer aligns the button with the selects in adjacent columns. ?>
						<label aria-hidden="true" style="visibility: hidden; display: block;">&nbsp;</label>
						<button
							type="button"
							class="frm_gc_remove_item button-link"
							style="color: var(--error-500);"
							aria-label="<?php esc_attr_e( 'Remove item', 'formidable' ); ?>"
						>
							<?php FrmAppHelper::icon_by_class( 'frmfont frm_minus1_icon frm_svg15' ); ?>
						</button>
					</div><!-- .frm1 -->

				</li>
			<?php endforeach; ?>
		</ul><!-- .frm_gc_items_list -->

		<?php
		// Template row — hidden, cloned by JS when "Add Item" is clicked.
		// Use data-frm-gc-field="KEY" on all form fields instead of a name attribute.
		// Use data-frm-gc-for="KEY" on labels instead of a for attribute.
		// JS assigns id, name, and for attributes after cloning using the item index counter.
		?>
		<template class="frm_gc_item_template">
			<li class="frm_gc_item_row frm_grid_container">

				<?php // ── Col 1: Type (3/12) ──────────────────────── ?>
				<div class="frm3">
					<label data-frm-gc-for="type">
						<?php esc_html_e( 'Type', 'formidable' ); ?>
					</label>
					<select data-frm-gc-field="type" class="frm-gc-item-type">
						<?php foreach ( $frm_gc_types as $frm_gc_type_key => $frm_gc_type ) : ?>
							<option
								value="<?php echo esc_attr( $frm_gc_type_key ); ?>"
								<?php disabled( ! empty( $frm_gc_type['disabled'] ) ); ?>
							>
								<?php echo esc_html( $frm_gc_type['label'] ); ?>
								<?php if ( ! empty( $frm_gc_type['pro'] ) ) : ?>
									<?php esc_html_e( '(Pro)', 'formidable' ); ?>
								<?php endif; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div><!-- .frm3 -->

				<?php // ── Col 2: Type-specific settings (8/12) ───── ?>
				<div class="frm8 frm-gc-item-settings">

					<?php // Default (first) type is visible; all others added by plugins must set hidden. ?>
					<div class="frm-gc-type-settings" data-type="page">
						<label data-frm-gc-for="id">
							<?php esc_html_e( 'WordPress page', 'formidable' ); ?>
						</label>
						<select data-frm-gc-field="id">
							<option value=""><?php esc_html_e( '— Select a page —', 'formidable' ); ?></option>
							<?php foreach ( $frm_gc_pages as $frm_gc_page ) : ?>
								<option value="<?php echo esc_attr( $frm_gc_page->ID ); ?>">
									<?php echo esc_html( $frm_gc_page->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div><!-- [data-type="page"] -->

					<?php
					/**
					 * Fires after the built-in type settings in the template item row.
					 *
					 * Pro and PDF plugins add their type-specific template settings here.
					 *
					 * Guidelines for hook callbacks:
					 * - Wrap each type's settings in `<div class="frm-gc-type-settings" data-type="{TYPE}" hidden>`.
					 * - Always include the `hidden` attribute — JS shows the active type after cloning.
					 * - Use `<label data-frm-gc-for="id">` (no for attribute) — JS sets it after cloning.
					 * - Use `data-frm-gc-field="id"` (no id or name attributes) on the select — JS assigns both.
					 *
					 * @since x.x
					 *
					 * @param array $frm_gc_types All registered type configurations.
					 */
					do_action( 'frm_gated_content_item_template_settings', $frm_gc_types );
					?>
				</div><!-- .frm8 -->

				<?php // ── Col 3: Delete button (1/12) ─────────────── ?>
				<div class="frm1 frm-gc-item-delete">
					<?php // Hidden label spacer aligns the button with the selects in adjacent columns. ?>
					<label aria-hidden="true" style="visibility: hidden; display: block;">&nbsp;</label>
					<button
						type="button"
						class="frm_gc_remove_item button-link"
						style="color: var(--error-500);"
						aria-label="<?php esc_attr_e( 'Remove item', 'formidable' ); ?>"
					>
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_minus1_icon frm_svg15' ); ?>
					</button>
				</div><!-- .frm1 -->

			</li>
		</template>

		<button
			type="button"
			class="frm_gc_add_item button-secondary frm-button-sm"
			data-field-name-base="<?php echo esc_attr( $frm_gc_field_name_base ); ?>"
		>
			+ <?php esc_html_e( 'Add Item', 'formidable' ); ?>
		</button>
	</div><!-- .frm_gc_items_section -->

	<?php // ── Section: Shortcode reference ─────────────────────────────── ?>
	<div class="frm_form_field frm_gc_shortcodes_section" style="margin-top: 20px;">
		<label><?php esc_html_e( 'Access Link Shortcodes', 'formidable' ); ?></label>
		<p class="frm_description">
			<?php esc_html_e( 'Add these shortcodes to a Confirmation or Send Email action to include the access link.', 'formidable' ); ?>
		</p>
		<?php
		$frm_gc_shortcodes = array(
			array(
				'code'   => '[frm_gated_content id="' . $frm_gc_action_id . '"]',
				'output' => __( 'Access links for all items', 'formidable' ),
			),
			array(
				'code'   => '[frm_gated_content id="' . $frm_gc_action_id . '" item="0"]',
				'output' => __( 'Access link for the first item (0-indexed)', 'formidable' ),
			),
			array(
				'code'   => '[frm_gated_content id="' . $frm_gc_action_id . '" item="0" show="url"]',
				'output' => __( 'URL only for the first item (no link tag)', 'formidable' ),
			),
			array(
				'code'   => '[frm_gated_content id="' . $frm_gc_action_id . '" show="access_token"]',
				'output' => __( 'Raw access token string', 'formidable' ),
			),
		);
		?>
		<table class="frm_gc_shortcode_table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Shortcode', 'formidable' ); ?></th>
					<th><?php esc_html_e( 'Output', 'formidable' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $frm_gc_shortcodes as $frm_gc_shortcode ) : ?>
					<tr>
						<td>
							<code><?php echo esc_html( $frm_gc_shortcode['code'] ); ?></code>
							<button
								type="button"
								class="frm_gc_copy_shortcode button-link"
								data-frm-copy="<?php echo esc_attr( $frm_gc_shortcode['code'] ); ?>"
								data-copied-label="<?php esc_attr_e( 'Copied!', 'formidable' ); ?>"
								aria-label="<?php esc_attr_e( 'Copy shortcode', 'formidable' ); ?>"
							>
								<svg class="frmsvg frm_svg14" aria-hidden="true" focusable="false">
									<use href="#frm_clone_icon"></use>
								</svg>
							</button>
						</td>
						<td><?php echo esc_html( $frm_gc_shortcode['output'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php
				/**
				 * Fires inside the shortcode reference table body for a gated content action.
				 *
				 * Pro plugins use this to append additional shortcode rows (e.g. show="expired_time").
				 * Each callback should output one or more `<tr>` elements.
				 *
				 * @since x.x
				 *
				 * @param int $frm_gc_action_id ID of the current gated content action post.
				 */
				do_action( 'frm_gated_content_shortcodes', $frm_gc_action_id );
				?>
			</tbody>
		</table>
	</div><!-- .frm_gc_shortcodes_section -->

</div><!-- .frm_gated_content_settings -->

<?php
unset( $frm_gc_items, $frm_gc_action_id, $frm_gc_field_name_base, $frm_gc_types, $frm_gc_wrapper_id, $frm_gc_pages, $frm_gc_page, $frm_gc_item, $frm_gc_idx, $frm_gc_item_type, $frm_gc_item_id, $frm_gc_item_base, $frm_gc_type, $frm_gc_type_key, $frm_gc_type_sel_id, $frm_gc_page_sel_id, $frm_gc_shortcodes, $frm_gc_shortcode );
