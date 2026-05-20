<?php
/**
 * Gated Content item row partial
 *
 * Renders a single <li class="frm_gc_item_row"> used both for existing saved
 * items (PHP foreach loop) and for the hidden <template> element cloned by JS
 * when "Add Item" is clicked.
 *
 * When $is_template is true:
 * - Labels use data-frm-gc-for instead of for.
 * - Selects use data-frm-gc-field instead of id/name — JS assigns both after cloning.
 * - No selected() calls — JS selects the saved value after cloning.
 * - The post type div is always visible (post is the default type).
 *
 * @package Formidable
 *
 * @since x.x
 *
 * @var bool         $is_template         True when rendering the JS clone template.
 * @var string       $frm_gc_item_type    Active type key ('post', 'frm_file', …). Always 'post' for template.
 * @var int          $frm_gc_item_id      Saved item ID. 0 for template rows.
 * @var string       $frm_gc_item_base    Field name prefix. Empty for template rows.
 * @var string       $frm_gc_type_sel_id  Unique element ID for the type select. Empty for template rows.
 * @var string       $frm_gc_post_sel_id  Unique element ID for the post select. Empty for template rows.
 * @var int          $frm_gc_idx          Zero-based item index. 0 for template rows.
 * @var array        $frm_gc_item         Saved item data. Empty array for template rows.
 * @var array        $frm_gc_types        All registered type configurations.
 * @var WP_Post[]    $frm_gc_posts        Posts available as gated content targets.
 * @var bool         $frm_gc_use_autocomplete Whether to render autocomplete inputs.
 * @var string       $frm_gc_posts_source JSON-encoded autocomplete source. Only set when $frm_gc_use_autocomplete.
 * @var string       $frm_gc_wrapper_id   Unique wrapper element ID.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<li class="frm_gc_item_row frm_grid_container">

	<?php /* ── Col 1: Type (4/12) ──────────────────────── */ ?>
	<div class="frm4">
		<div class="frm_form_field frm-mt-xs frm-mb-xs">
			<?php if ( $is_template ) : ?>
				<label data-frm-gc-for="type">
			<?php else : ?>
				<label for="<?php echo esc_attr( $frm_gc_type_sel_id ); ?>">
			<?php endif; ?>
				<?php esc_html_e( 'Type', 'formidable' ); ?>
			</label>
			<select
				<?php if ( $is_template ) : ?>
					data-frm-gc-field="type"
				<?php else : ?>
					id="<?php echo esc_attr( $frm_gc_type_sel_id ); ?>"
					name="<?php echo esc_attr( $frm_gc_item_base . '[type]' ); ?>"
				<?php endif; ?>
				class="frm-gc-item-type"
			>
				<?php foreach ( $frm_gc_types as $frm_gc_type_key => $frm_gc_type ) : ?>
					<option
						value="<?php echo esc_attr( $frm_gc_type_key ); ?>"
						<?php if ( ! $is_template ) : ?>
							<?php selected( $frm_gc_item_type, $frm_gc_type_key ); ?>
						<?php endif; ?>
						<?php disabled( ! empty( $frm_gc_type['disabled'] ) ); ?>
					>
						<?php echo esc_html( $frm_gc_type['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div><!-- .frm_form_field -->
	</div><!-- .frm4 -->

	<?php /* ── Col 2: Type-specific settings + delete (8/12) ── */ ?>
	<div class="frm8 frm-gc-item-settings">

		<?php
		// Post type settings.
		// Existing rows: only add name when this type is active — prevents duplicate
		// [id] values being submitted when the user switches types via JS.
		// Template rows: JS assigns id, name, and for attributes after cloning.
		// Post is always the default visible type, so no hidden attribute is needed here.
		?>
		<div
			class="frm-gc-type-settings"
			data-type="post"
			<?php echo ! $is_template && 'post' !== $frm_gc_item_type ? 'hidden' : ''; ?>
		>
			<div class="frm_form_field frm-mt-xs frm-mb-xs">
				<?php if ( $is_template ) : ?>
					<label data-frm-gc-for="id">
				<?php else : ?>
					<label for="<?php echo esc_attr( $frm_gc_post_sel_id ); ?>">
				<?php endif; ?>
					<?php esc_html_e( 'WordPress post', 'formidable' ); ?>
				</label>
				<?php if ( $frm_gc_use_autocomplete ) : ?>
					<?php
					$frm_gc_selected_title = '';

					if ( ! $is_template ) {
						foreach ( $frm_gc_posts as $frm_gc_post ) {
							if ( $frm_gc_item_id === $frm_gc_post->ID ) {
								$frm_gc_selected_title = $frm_gc_post->post_title;
								break;
							}
						}
					}
					?>
					<input type="text" class="frm-custom-search"
						<?php if ( ! $is_template ) : ?>
							id="<?php echo esc_attr( $frm_gc_post_sel_id ); ?>"
						<?php endif; ?>
						data-source="<?php echo esc_attr( $frm_gc_posts_source ); ?>"
						placeholder="<?php esc_attr_e( '— Select a post —', 'formidable' ); ?>"
						value="<?php echo esc_attr( $frm_gc_selected_title ); ?>"
					/>
					<input type="hidden"
						data-frm-gc-field="id"
						class="frm_autocomplete_value_input"
						<?php if ( ! $is_template && 'post' === $frm_gc_item_type ) : ?>
							name="<?php echo esc_attr( $frm_gc_item_base . '[id]' ); ?>"
						<?php endif; ?>
						value="<?php echo esc_attr( ! $is_template && $frm_gc_item_id ? $frm_gc_item_id : '' ); ?>"
					/>
				<?php else : ?>
					<select
						<?php if ( ! $is_template ) : ?>
							id="<?php echo esc_attr( $frm_gc_post_sel_id ); ?>"
							<?php if ( 'post' === $frm_gc_item_type ) : ?>
								name="<?php echo esc_attr( $frm_gc_item_base . '[id]' ); ?>"
							<?php endif; ?>
						<?php endif; ?>
						data-frm-gc-field="id"
					>
						<option value=""><?php esc_html_e( '— Select a post —', 'formidable' ); ?></option>
						<?php foreach ( $frm_gc_posts as $frm_gc_post ) : ?>
							<option
								value="<?php echo esc_attr( $frm_gc_post->ID ); ?>"
								<?php if ( ! $is_template ) : ?>
									<?php selected( $frm_gc_item_id, $frm_gc_post->ID ); ?>
								<?php endif; ?>
							>
								<?php echo esc_html( $frm_gc_post->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div><!-- .frm_form_field -->
		</div><!-- [data-type="post"] -->

		<?php
		$filter_args = array(
			'is_template' => $is_template,
			'types'       => $frm_gc_types,
		);

		if ( ! $is_template ) {
			$filter_args['active_type'] = $frm_gc_item_type;
			$filter_args['idx']         = $frm_gc_idx;
			$filter_args['item']        = $frm_gc_item;
			$filter_args['item_base']   = $frm_gc_item_base;
			$filter_args['wrapper_id']  = $frm_gc_wrapper_id;
		}

		/**
		 * Fires after the built-in type settings for a gated content item row.
		 *
		 * Fires for both existing (PHP-rendered) rows and the JS-cloned <template> row.
		 * Use $args['is_template'] to distinguish between the two.
		 *
		 * Guidelines for hook callbacks:
		 * - Wrap each type's settings in `<div class="frm-gc-type-settings" data-type="{TYPE}">`.
		 * - In template mode: always add `hidden`; use `data-frm-gc-for` on labels; omit id/name on inputs.
		 * - In existing-row mode: add `hidden` when `$args['active_type'] !== '{TYPE}'`; use real for/id/name.
		 * - Add `data-frm-gc-field="{KEY}"` to each input so JS manages its name on type change.
		 *
		 * @since x.x
		 *
		 * @param array $args {
		 *
		 *     @type bool   $is_template True when rendering inside the JS <template> element.
		 *     @type array  $types       All registered type configurations.
		 *     @type string $active_type Active type key for this item (existing rows only).
		 *     @type int    $idx         Zero-based item index (existing rows only).
		 *     @type array  $item        Saved item data (existing rows only).
		 *     @type string $item_base   Field name prefix for this item (existing rows only).
		 *     @type string $wrapper_id  Unique wrapper element ID (existing rows only).
		 * }
		 */
		do_action( 'frm_gated_content_item_type_settings', $filter_args );
		?>

		<div class="frm-gc-item-delete">
			<button
				type="button"
				class="frm_gc_remove_item button-link"
				style="color: var(--error-500);"
				aria-label="<?php esc_attr_e( 'Remove item', 'formidable' ); ?>"
			>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_minus1_icon frm_svg15' ); ?>
			</button>
		</div><!-- .frm-gc-item-delete -->
	</div><!-- .frm8 -->

</li>
