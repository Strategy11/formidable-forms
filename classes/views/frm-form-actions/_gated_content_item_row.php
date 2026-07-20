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
 * @since 6.33
 *
 * @var bool         $is_template         True when rendering the JS clone template.
 * @var string       $frm_gc_item_type    Active type key ('post', 'frm_file', …). Always 'post' for template.
 * @var int          $frm_gc_item_id      Saved item ID. 0 for template rows.
 * @var string       $frm_gc_item_base    Field name prefix. Empty for template rows.
 * @var string       $frm_gc_type_sel_id  Unique element ID for the type select. Empty for template rows.
 * @var int          $frm_gc_idx          Zero-based item index. 0 for template rows.
 * @var array        $frm_gc_item         Saved item data. Empty array for template rows.
 * @var array        $frm_gc_types        All registered type configurations.
 * @var array<string, WP_Post[]> $frm_gc_posts        Posts grouped by item type key.
 * @var bool                    $frm_gc_use_autocomplete Whether to render autocomplete inputs.
 * @var array<string, string>   $frm_gc_posts_source JSON-encoded autocomplete source per type. Only set when $frm_gc_use_autocomplete.
 * @var string       $frm_gc_wrapper_id   Unique wrapper element ID.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<li class="frm_gc_item_row frm_grid_container">

	<?php /* ── Col 1: Type (6/12) ──────────────────────── */ ?>
	<div class="frm6">
		<div class="frm_form_field frm-mt-xs frm-mb-xs">
			<?php
			$frm_gc_label_atts = $is_template
				? array( 'data-frm-gc-for' => 'type' )
				: array( 'for' => $frm_gc_type_sel_id );
			?>
			<label <?php FrmAppHelper::array_to_html_params( $frm_gc_label_atts, true ); ?>>
				<?php esc_html_e( 'Type', 'formidable' ); ?>
			</label>
			<?php
			$frm_gc_type_sel_atts = array( 'class' => 'frm-gc-item-type' );

			if ( $is_template ) {
				$frm_gc_type_sel_atts['data-frm-gc-field'] = 'type';
			} else {
				$frm_gc_type_sel_atts['id']   = $frm_gc_type_sel_id;
				$frm_gc_type_sel_atts['name'] = $frm_gc_item_base . '[type]';
			}
			?>
			<select <?php FrmAppHelper::array_to_html_params( $frm_gc_type_sel_atts, true ); ?>>
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

	<?php /* ── Col 2: Type-specific settings + delete (6/12) ── */ ?>
	<div class="frm6 frm-gc-item-settings">

		<?php
		// Build args for inner/outer type-settings hooks once (all vars are available here).
		$action_args = array(
			'is_template' => $is_template,
			'types'       => $frm_gc_types,
		);

		if ( ! $is_template ) {
			$action_args['active_type'] = $frm_gc_item_type;
			$action_args['idx']         = $frm_gc_idx;
			$action_args['item']        = $frm_gc_item;
			$action_args['item_base']   = $frm_gc_item_base;
			$action_args['wrapper_id']  = $frm_gc_wrapper_id;
		}

		// One settings div per post-type-backed item type.
		// Existing rows: only the active type's div is visible; only it gets a name attribute.
		// Template rows: JS shows/hides divs on type change and assigns id/name after cloning.
		foreach ( $frm_gc_types as $frm_gc_pt_key => $frm_gc_pt_config ) :
			if ( ! post_type_exists( $frm_gc_pt_key ) ) {
				continue;
			}
			$frm_gc_pt_posts    = $frm_gc_posts[ $frm_gc_pt_key ] ?? array();
			$frm_gc_pt_sel_id   = $is_template ? '' : $frm_gc_wrapper_id . '_id_' . $frm_gc_pt_key . '_' . $frm_gc_idx;
			$frm_gc_pt_source   = $frm_gc_use_autocomplete ? ( $frm_gc_posts_source[ $frm_gc_pt_key ] ?? '[]' ) : '';
			$frm_gc_pt_is_first = ! isset( $frm_gc_first_pt_rendered );

			if ( $frm_gc_pt_is_first ) {
				$frm_gc_first_pt_rendered = true;
			}
			$frm_gc_pt_hidden = ! $is_template && $frm_gc_item_type !== $frm_gc_pt_key;

			$frm_gc_div_atts = array(
				'class'     => 'frm-gc-type-settings',
				'data-type' => $frm_gc_pt_key,
			);

			if ( $frm_gc_pt_hidden ) {
				$frm_gc_div_atts['hidden'] = '';
			}
		?>
		<div <?php FrmAppHelper::array_to_html_params( $frm_gc_div_atts, true ); ?>>
			<div class="frm_form_field frm-mt-xs frm-mb-xs">
				<?php
				$frm_gc_pt_label_atts = $is_template
					? array( 'data-frm-gc-for' => 'id' )
					: array( 'for' => $frm_gc_pt_sel_id );
				?>
				<label <?php FrmAppHelper::array_to_html_params( $frm_gc_pt_label_atts, true ); ?>>
					<?php echo esc_html( $frm_gc_pt_config['label'] ); ?>
				</label>
				<?php if ( $frm_gc_use_autocomplete ) : ?>
					<?php
					$frm_gc_pt_selected_title = '';

					if ( ! $is_template && $frm_gc_item_type === $frm_gc_pt_key ) {
						foreach ( $frm_gc_pt_posts as $frm_gc_post ) {
							if ( $frm_gc_item_id === $frm_gc_post->ID ) {
								$frm_gc_pt_selected_title = $frm_gc_post->post_title;
								break;
							}
						}
					}

					$frm_gc_ac_text_atts = array(
						'type'        => 'text',
						'class'       => 'frm-custom-search',
						'data-source' => $frm_gc_pt_source,
						'placeholder' => __( '— Select —', 'formidable' ),
						'value'       => $frm_gc_pt_selected_title,
					);

					if ( ! $is_template ) {
						$frm_gc_ac_text_atts['id'] = $frm_gc_pt_sel_id;
					}
					?>
					<input <?php FrmAppHelper::array_to_html_params( $frm_gc_ac_text_atts, true ); ?> />
					<?php
					$frm_gc_ac_hidden_atts = array(
						'type'              => 'hidden',
						'data-frm-gc-field' => 'id',
						'class'             => 'frm_autocomplete_value_input',
						'value'             => ! $is_template && $frm_gc_item_type === $frm_gc_pt_key && $frm_gc_item_id ? (string) $frm_gc_item_id : '',
					);

					if ( ! $is_template && $frm_gc_item_type === $frm_gc_pt_key ) {
						$frm_gc_ac_hidden_atts['name'] = $frm_gc_item_base . '[id]';
					}
					?>
					<input <?php FrmAppHelper::array_to_html_params( $frm_gc_ac_hidden_atts, true ); ?> />
				<?php else : ?>
					<?php
					$frm_gc_sel_atts = array( 'data-frm-gc-field' => 'id' );

					if ( ! $is_template ) {
						$frm_gc_sel_atts['id'] = $frm_gc_pt_sel_id;

						if ( $frm_gc_item_type === $frm_gc_pt_key ) {
							$frm_gc_sel_atts['name'] = $frm_gc_item_base . '[id]';
						}
					}
					?>
					<select <?php FrmAppHelper::array_to_html_params( $frm_gc_sel_atts, true ); ?>>
						<option value=""><?php esc_html_e( '— Select —', 'formidable' ); ?></option>
						<?php foreach ( $frm_gc_pt_posts as $frm_gc_post ) : ?>
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
			<?php
			/**
			 * Fires inside a post-type-backed type settings div, after the primary ID field.
			 *
			 * Use this to append extra fields (e.g. an entry selector) inside the same
			 * wrapper as the primary post selector. Fires for both existing rows and the
			 * JS <template> row.
			 *
			 * @since 6.33
			 *
			 * @param string $type     Post-type key for this settings div (e.g. 'frm_display').
			 * @param array  $row_args Same keys as the outer `frm_gated_content_item_type_settings` hook.
			 */
			do_action( 'frm_gated_content_type_settings_inner', $frm_gc_pt_key, $action_args );
			?>
		</div><!-- [data-type="<?php echo esc_attr( $frm_gc_pt_key ); ?>"] -->
		<?php endforeach; ?>
		<?php unset( $frm_gc_pt_key, $frm_gc_pt_config, $frm_gc_pt_posts, $frm_gc_pt_sel_id, $frm_gc_pt_source, $frm_gc_pt_is_first, $frm_gc_first_pt_rendered, $frm_gc_pt_hidden, $frm_gc_pt_selected_title, $frm_gc_post, $frm_gc_div_atts, $frm_gc_pt_label_atts, $frm_gc_ac_text_atts, $frm_gc_ac_hidden_atts, $frm_gc_sel_atts ); ?>

		<?php
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
		 * @since 6.33
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
		do_action( 'frm_gated_content_item_type_settings', $action_args );
		?>

		<div class="frm-gc-item-delete">
			<button
				type="button"
				class="frm_gc_remove_item button-link"
				aria-label="<?php esc_attr_e( 'Remove item', 'formidable' ); ?>"
			>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_minus1_icon frm_svg15' ); ?>
			</button>
		</div><!-- .frm-gc-item-delete -->
	</div><!-- .frm6 -->

</li>
