<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmStylesCardHelper {

	const PAGE_SIZE = 4;

	/**
	 * @var string
	 */
	private $view_file_path;

	/**
	 * @var WP_Post
	 */
	private $active_style;

	/**
	 * @var WP_Post
	 */
	private $default_style;

	/**
	 * @var int
	 */
	private $form_id;

	/**
	 * @var bool
	 */
	private $enabled;

	/**
	 * @var bool If this is true, a lock will be rendered with the style name when self::echo_style_card is called.
	 */
	private $locked;

	/**
	 * @param WP_Post    $active_style
	 * @param WP_Post    $default_style
	 * @param string|int $form_id
	 * @param bool       $enabled
	 */
	public function __construct( $active_style, $default_style, $form_id, $enabled ) {
		$this->view_file_path = FrmAppHelper::plugin_path() . '/classes/views/styles/_style-card.php';
		$this->active_style   = $active_style;
		$this->default_style  = $default_style;
		$this->form_id        = (int) $form_id;
		$this->enabled        = $enabled;
		$this->locked         = false;
	}

	/**
	 * Echo a style card for a specific target Style Post object.
	 *
	 * @since x.x
	 *
	 * @param stdClass|WP_Post $style
	 * @param bool             $hidden Used for pagination.
	 * @param Closure|false    $param_callback
	 * @return void
	 */
	private function echo_style_card( $style, $hidden = false, $param_callback = false ) {
		$is_default_style     = $style->ID === $this->default_style->ID;
		$is_active_style      = $style->ID === $this->active_style->ID;
		$is_locked            = $this->locked;
		$submit_button_params = $this->get_submit_button_params();
		$params               = $this->get_params_for_style_card( $style );

		if ( $is_default_style ) {
			$params['class'] .= ' frm-default-style-card';
		}
		if ( $is_active_style ) {
			$params['class'] .= ' frm-active-style-card';
		}
		if ( $hidden ) {
			$params['class'] .= ' frm_hidden';
		}
		if ( false !== $param_callback && is_callable( $param_callback ) ) {
			$params = $param_callback( $params );
		}

		include $this->view_file_path;
	}

	/**
	 * @since x.x
	 *
	 * @return array
	 */
	private function get_submit_button_params() {
		$frm_style            = new FrmStyle();
		$defaults             = $frm_style->get_defaults();
		$submit_button_styles = array(
			'font-size: ' . esc_attr( $defaults['submit_font_size'] ) . ' !important',
			'padding: ' . esc_attr( $defaults['submit_padding'] ) . ' !important',
		);
		return array(
			'type'     => 'submit',
			'disabled' => 'disabled',
			'class'    => 'frm_full_opacity',
			'value'    => esc_attr__( 'Submit', 'formidable' ),
			'style'    => implode( ';', $submit_button_styles ),
		);
	}

	/**
	 * Get params to use in the style card HTML element used in the style list.
	 *
	 * @since x.x
	 *
	 * @param stdClass|WP_Post $style
	 * @return array
	 */
	private function get_params_for_style_card( $style ) {
		if ( ! empty( $style->post_content['position'] ) ) {
			$label_position = $style->post_content['position'];
		} else {
			$frm_style      = new FrmStyle();
			$defaults       = $frm_style->get_defaults();
			$label_position = $defaults['position'];
		}

		$class_name = 'frm_style_' . $style->post_name;
		$params     = array(
			'class'               => 'frm-style-card',
			'style'               => self::get_style_param_for_card( $style ),
			'data-classname'      => $class_name,
			'data-style-id'       => $style->ID,
			'data-edit-url'       => esc_url( FrmStylesHelper::get_edit_url( $style, $this->form_id ) ),
			'data-label-position' => $label_position,
		);

		/**
		 * Filter params so Pro can add additional params, like data-delete-url.
		 *
		 * @since x.x
		 *
		 * @param array $params
		 * @param array $args {
		 *     @type WP_Post $style
		 * }
		 */
		return apply_filters( 'frm_style_card_params', $params, compact( 'style' ) );
	}

	/**
	 * @since x.x
	 *
	 * @param array $style API style data {
	 *     @type array  $settings
	 *     @type string $name
	 *     @type string $slug
	 * }
	 * @param bool  $hidden
	 * @return bool True if the template was valid and echoed.
	 */
	private function echo_card_template( $style, $hidden = false ) {
		if ( empty( $style['settings'] ) || ! is_array( $style['settings'] ) ) {
			return false;
		}

		// Use a better name than my sample form.
		$style_object               = new stdClass();
		$style_object->ID           = 0;
		$style_object->post_title   = $style['name'];
		$style_object->post_name    = 'frm_style_template'; // This name is referenced in Pro.
		$style_object->post_content = $style['settings'];

		$this->locked = empty( $style['url'] );

		// TODO can I use the frm_style_card_params filter instead of calling a callback?
		$param_callback = false;
		if ( $this->locked ) {
			/**
			 * Include additional params for locked templates.
			 *
			 * @param stdClass $style_object Object sent to echo_style_card function.
			 * @param array    $style        API data.
			 */
			$param_callback = function( $params ) use ( $style_object, $style ) {
				$params['class'] .= ' frm-locked-style';

				$params['data-upgrade'] = $style_object->post_title;
				$params['data-medium']  = 'styler-template';

				$item = array(
					'categories' => $style['categories'],
				);
				$params['data-requires'] = FrmFormsHelper::get_plan_required( $item );

				// WordPress requires that images are local files, so we may need to include those files in-plugin.
				// if ( ! empty( $style['icon'] ) && is_array( $style['icon'] ) ) {
				// 	$params['data-image'] = reset( $style['icon'] );
				// }
				return $params;
			};
		} else {
			$param_callback = function( $params ) use ( $style ) {
				$params['data-template-key'] = $style['slug'];
				return $params;
			};
		}

		$this->echo_style_card( $style_object, $hidden, $param_callback );
		return true;
	}

	/**
	 * Get the string to populate the style card's style attribute with.
	 * This is used to reset some style variables like font size, label padding, and field height, so the cards all look more similar in comparison.
	 * It's kept static as it only requires a $style as input and also gets called when resetting a style.
	 *
	 * @since x.x
	 *
	 * @param WP_Post|stdClass $style A new style (including duplicated styles) is not a WP_Post object.
	 *                                Template cards also use an stdClss instead of a WP_Post object.
	 * @return string
	 */
	public static function get_style_param_for_card( $style ) {
		$styles = array();

		// Add the background color setting for fieldsets to the card.
		if ( empty( $style->post_content['fieldset_bg_color'] ) ) {
			$background_color = '#fff';
		} else {
			$background_color = ( 0 === strpos( $style->post_content['fieldset_bg_color'], 'rgb' ) ? $style->post_content['fieldset_bg_color'] : '#' . $style->post_content['fieldset_bg_color'] );
		}
		$styles[] = '--preview-background-color: ' . $background_color;

		// Apply additional styles from the style.
		$rules_to_apply = self::get_style_keys_for_card();

		$frm_style      = new FrmStyle();
		$color_settings = $frm_style->get_color_settings();

		foreach ( $rules_to_apply as $key ) {
			if ( ! array_key_exists( $key, $style->post_content ) ) {
				// A template from the API may not include every style key. If something is missing, skip it.
				continue;
			}

			$value = $style->post_content[ $key ];

			if ( in_array( $key, $color_settings, true ) && $value && '#' !== $value[0] && false === strpos( $value, 'rgb' ) ) {
				$value = '#' . $value;
			}

			$styles[] = '--' . str_replace( '_', '-', $key ) . ':' . $value;
		}

		return implode( ';', $styles );
	}

	/**
	 * Get the keys we want to use from the style to use in the card.
	 * We don't use every style as we want the cards to look consistent, so size settings are left out.
	 * The card previews also only include a labelled text input and submit button so we don't need styles for other elements.
	 *
	 * @since x.x
	 *
	 * @return array<string>
	 */
	private static function get_style_keys_for_card() {
		return array(
			'fieldset_bg_color',
			'field_border_width',
			'field_border_style',
			'border_color',
			'submit_bg_color',
			'submit_border_color',
			'submit_border_width',
			'submit_border_radius',
			'submit_text_color',
			'submit_weight',
			'submit_width',
			'label_color',
			'text_color',
			'bg_color',
		);
	}

	/**
	 * Echo a card wrapper and its children style cards.
	 *
	 * @since x.x
	 *
	 * @param string $id     The ID of the card wrapper element.
	 * @param array  $styles
	 *
	 * @return void
	 */
	public function echo_card_wrapper( $id, $styles ) {
		$wrapper_style = $this->get_style_attribute_value_for_wrapper();

		// Begin card wrapper
		$card_wrapper_params = array(
			'id'    => $id,
			'class' => 'frm-style-card-wrapper with_frm_style',
			'style' => $wrapper_style,
		);
		if ( $this->enabled ) {
			$card_wrapper_params['class'] .= ' frm-styles-enabled';
		}

		$first_style         = reset( $styles );
		$is_template_wrapper = is_array( $first_style );
		?>
		<div <?php FrmAppHelper::array_to_html_params( $card_wrapper_params, true ); ?>>
			<?php
			if ( $is_template_wrapper ) {
				$this->echo_template_cards( $styles );
			} else {
				$this->echo_custom_cards( $styles );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Overwrite some styles. We want to make sure the sizes are normalized for the cards.
	 * The cards use some rules from the default style because of the with_frm_style class on the card wrapper.
	 * As these can be customized, apply some default values for the card previews instead.
	 * This is used in the card wrapper so it doesn't need to get added to each card.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private function get_style_attribute_value_for_wrapper() {
		$frm_style = new FrmStyle();
		$defaults  = $frm_style->get_defaults();

		$styles   = array();
		$styles[] = '--field-font-size: ' . $defaults['field_font_size'];
		$styles[] = '--field-height: ' . $defaults['field_height'];
		$styles[] = '--field-pad: ' . $defaults['field_pad'];
		$styles[] = '--font-size: ' . $defaults['font_size'];
		$styles[] = '--label-padding: ' . $defaults['label_padding'];
		$styles[] = '--form-align: ' . $defaults['form_align'];

		return implode( ';', $styles );
	}

	/**
	 * @since x.x
	 *
	 * @param array<array> $styles
	 * @return void
	 */
	private function echo_template_cards( $styles ) {
		$count = 0;
		array_walk(
			$styles,
			/**
			 * Echo a style card for a single template from API data.
			 *
			 * @param array|string $style
			 * @param string       $key   The key for the API data. It may be a numeric ID, or a key like "active_sub" or "expires".
			 * @param int          $count Used for pagination.
			 * @return void
			 */
			function( $style, $key ) use ( &$count ) {
				if ( ! is_numeric( $key ) ) {
					// Skip active_sub/expires keys.
					return;
				}

				$hidden = $count > ( self::PAGE_SIZE - 1 );
				if ( $this->echo_card_template( $style, $hidden ) ) {
					++$count;
				}
			}
		);

		$this->maybe_echo_card_pagination( $count );
	}

	/**
	 * @param array<WP_Post> $styles
	 * @return void
	 */
	private function echo_custom_cards( $styles ) {
		$count        = 0;
		$this->locked = false;
		array_walk(
			$styles,
			/**
			* Echo a style card for a single style in the $styles array.
			*
			* @param WP_Post $style
			* @param int     $count Used for pagination.
			* @return void
			*/
			function( $style ) use ( &$count ) {
				$hidden = $count > ( self::PAGE_SIZE - 1 );
				$this->echo_style_card( $style, $hidden );
				++$count;
			}
		);

		if ( ! FrmAppHelper::pro_is_installed() ) {
			$this->echo_upsell_card();
		}

		$this->maybe_echo_card_pagination( $count );
	}

	/**
	 * @since x.x
	 *
	 * @param int $count
	 * @return void
	 */
	private function maybe_echo_card_pagination( $count ) {
		if ( $count <= self::PAGE_SIZE ) {
			// Not enough cards to require pagination.
			return;
		}

		$number_of_pages = ceil( $count / self::PAGE_SIZE );
		?>
		<div class="frm-style-card-pagination frm_wrap">
			<?php for ( $index = 0; $index < $number_of_pages; ++$index ) { ?>
				<?php
				$anchor_parms = array( 'href' => '#' );
				if ( 0 === $index ) {
					$anchor_parms['class'] = 'frm-current-style-card-page';
				}
				?>
				<a <?php FrmAppHelper::array_to_html_params( $anchor_parms, true ); ?>><?php echo absint( $index + 1 ); ?></a>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	private function echo_upsell_card() {
		$upgrade_link = FrmAppHelper::admin_upgrade_link( 'styler-upsell-card' );
		?>
		<div id="frm_styles_upsell_card" class="frm-style-card">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/upgrade-custom-styles.svg" />
			<div><?php esc_html_e( 'Create styles and get access to premium templates', 'formidable' ); ?></div>
			<div>
				<a href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank"><?php esc_html_e( 'Upgrade now', 'formidable' ); ?></a>
			</div>
		</div>
		<?php
	}
}
