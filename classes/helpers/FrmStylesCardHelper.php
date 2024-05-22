<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmStylesCardHelper {

	const PAGE_SIZE = 3;

	/**
	 * @var string
	 */
	private $view_file_path;

	/**
	 * @var stdClass|WP_Post
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
	 * @var bool If this is true, a "NEW" pill is included beside the style name.
	 */
	private $is_new_template;

	/**
	 * @param stdClass|WP_Post $active_style
	 * @param WP_Post          $default_style
	 * @param int|string       $form_id
	 * @param bool             $enabled
	 */
	public function __construct( $active_style, $default_style, $form_id, $enabled ) {
		$this->view_file_path  = FrmAppHelper::plugin_path() . '/classes/views/styles/_style-card.php';
		$this->active_style    = $active_style;
		$this->default_style   = $default_style;
		$this->form_id         = (int) $form_id;
		$this->enabled         = $enabled;
		$this->locked          = false;
		$this->is_new_template = false;
	}

	/**
	 * Echo a style card for a specific target Style Post object.
	 *
	 * @since 6.0
	 *
	 * @param stdClass|WP_Post $style
	 * @param bool             $hidden Used for pagination.
	 * @return void
	 */
	private function echo_style_card( $style, $hidden = false ) {
		$params          = $this->get_params_for_style_card( $style, $hidden );
		$is_locked       = $this->locked;
		$is_new_template = $this->is_new_template;
		$is_active_style = $style->ID === $this->active_style->ID;

		include $this->view_file_path;
	}

	/**
	 * Get params to use in the style card HTML element used in the style list.
	 *
	 * @since 6.0
	 *
	 * @param stdClass|WP_Post $style
	 * @param bool             $hidden
	 * @return array
	 */
	private function get_params_for_style_card( $style, $hidden = false ) {
		if ( ! empty( $style->post_content['position'] ) ) {
			$label_position = $style->post_content['position'];
		} else {
			$frm_style      = new FrmStyle();
			$defaults       = $frm_style->get_defaults();
			$label_position = $defaults['position'];
		}

		$class_name = 'frm_style_' . $style->post_name;
		$params     = array(
			'class'               => 'frm-style-card frm-transition-ease',
			'style'               => self::get_style_param_for_card( $style ),
			'data-classname'      => $class_name,
			'data-style-id'       => $style->ID,
			'data-edit-url'       => esc_url( FrmStylesHelper::get_edit_url( $style, $this->form_id ) ),
			'data-label-position' => $label_position,
		);

		$is_active_style = $style->ID === $this->active_style->ID;
		if ( $is_active_style ) {
			$params['class'] .= ' frm-active-style-card frm-currently-set-style-card';
		}
		if ( $hidden ) {
			$params['class'] .= ' frm_hidden';
		}
		if ( self::has_dark_background( $style ) ) {
			$params['class'] .= ' frm-dark-style';
		}

		/**
		 * Filter params so Pro can add additional params, like data-delete-url.
		 *
		 * @since 6.0
		 *
		 * @param array $params
		 * @param array $args {
		 *     @type WP_Post $style
		 * }
		 */
		return apply_filters( 'frm_style_card_params', $params, compact( 'style' ) );
	}

	/**
	 * @param stdClass|WP_Post $style
	 * @return bool
	 */
	private static function has_dark_background( $style ) {
		$key = 'fieldset_bg_color';

		if ( empty( $style->post_content[ $key ] ) ) {
			return false;
		}

		$color = $style->post_content[ $key ];

		if ( 0 === strpos( $color, 'rgba' ) ) {
			preg_match_all( '/([\\d.]+)/', $color, $matches );

			if ( isset( $matches[1][3] ) && is_numeric( $matches[1][3] ) ) {
				// Consider a faded out rgba value as light even when the color is dark.
				$color_opacity = floatval( $matches[1][3] );
				if ( $color_opacity < 0.5 ) {
					return false;
				}
			}
		}

		$brightness = FrmStylesHelper::get_color_brightness( $color );
		return $brightness < 155;
	}

	/**
	 * @since 6.0
	 *
	 * @param array $style {
	 *     API style data.
	 *
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
		$style_object->post_content = $style['settings'];

		// An unlocked template uses a static "frm_style_template" in Pro.
		// A locked template however uses a slug to match the sandbox CSS.
		$style_object->post_name = isset( $style['url'] ) ? 'frm_style_template' : $style['slug'];

		$this->locked = empty( $style['url'] );

		if ( $this->locked ) {
			/**
			 * Set up a locked style card for the upgrade modal.
			 *
			 * @param array $params
			 * @param array $args {
			 *     @type stdClass|WP_Post $style
			 * }
			 * @param stdClass $style_object
			 * @param array    $style
			 */
			$param_filter = function ( $params, $args ) use ( $style_object, $style ) {
				if ( $args['style'] !== $style_object ) {
					return $params;
				}

				$params['class']           .= ' frm-locked-style';
				$params['data-upgrade-url'] = FrmAppHelper::admin_upgrade_link(
					array(
						'content' => 'upgrade',
						'medium'  => 'styler-card',
					),
					'/style-templates/' . $style['slug']
				);
				$params['data-requires']    = FrmFormsHelper::get_plan_required( $style );
				return $params;
			};
		} else {
			/**
			 * Include the template key for the preview in Pro.
			 *
			 * @param array $params
			 * @param array $style
			 * @return array
			 */
			$param_filter = function ( $params ) use ( $style ) {
				$params['data-template-key'] = $style['slug'];
				return $params;
			};
		}//end if

		$this->is_new_template = ! empty( $style['is_new'] );

		add_filter( 'frm_style_card_params', $param_filter, 10, $this->locked ? 2 : 1 );

		$this->echo_style_card( $style_object, $hidden );
		return true;
	}

	/**
	 * Get the string to populate the style card's style attribute with.
	 * This is used to reset some style variables like font size, label padding, and field height, so the cards all look more similar in comparison.
	 * It's kept static as it only requires a $style as input and also gets called when resetting a style.
	 *
	 * @since 6.0
	 *
	 * @param stdClass|WP_Post $style A new style (including duplicated styles) is not a WP_Post object.
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

		if ( empty( $style->post_content['submit_border_color'] ) ) {
			$style->post_content['submit_border_color'] = 'transparent';
		}

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

			$is_hex = in_array( $key, $color_settings, true ) && $value && '#' !== $value[0] && false === strpos( $value, 'rgb' ) && $value !== 'transparent';
			if ( $is_hex ) {
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
	 * @since 6.0
	 *
	 * @return array<string>
	 */
	private static function get_style_keys_for_card() {
		return array(
			'field_border_width',
			'field_border_style',
			'border_color',
			'border_radius',
			'submit_bg_color',
			'submit_border_color',
			'submit_border_width',
			'submit_border_radius',
			'submit_text_color',
			'label_color',
			'text_color',
			'bg_color',
		);
	}

	/**
	 * Echo a card wrapper and its children style cards.
	 *
	 * @since 6.0
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
	 * @since 6.0
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
	 * @since 6.0
	 *
	 * @param array<array> $styles
	 * @return void
	 */
	private function echo_template_cards( $styles ) {
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
			function ( $style, $key ) {
				if ( ! is_numeric( $key ) ) {
					// Skip active_sub/expires keys.
					return;
				}

				$this->echo_card_template( $style );
			}
		);
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
			function ( $style ) use ( &$count ) {
				$hidden = $count > self::PAGE_SIZE - 1;
				$this->echo_style_card( $style, $hidden );
				++$count;
			}
		);

		$this->maybe_echo_card_pagination( $count );
	}

	/**
	 * @since 6.0
	 *
	 * @param int $count
	 * @return void
	 */
	private function maybe_echo_card_pagination( $count ) {
		if ( $count <= self::PAGE_SIZE ) {
			// Not enough cards to require pagination.
			return;
		}
		?>
		<div class="frm-style-card-pagination frm_wrap">
			<a href="#" class="frm-show-all-styles">
				<?php
				printf(
					/* translators: %d: The number of styles */
					esc_html__( 'Show all (%d)', 'formidable' ),
					esc_html( $count - self::PAGE_SIZE )
				);
				?>
			</a>
		</div>
		<?php
	}

	/**
	 * @since 6.0
	 *
	 * @return array
	 */
	public function get_styles() {
		if ( is_callable( 'FrmProStylesController::get_styles_for_styler' ) ) {
			return FrmProStylesController::get_styles_for_styler( $this->active_style );
		}
		return array( $this->default_style );
	}

	/**
	 * Remove the default style from an array of styles.
	 *
	 * @param array $styles
	 * @return array
	 */
	public function filter_custom_styles( $styles ) {
		return array_filter(
			$styles,
			/**
			 * @param WP_Post $style
			 * @return bool
			 */
			function ( $style ) {
				return $this->default_style->ID !== $style->ID;
			}
		);
	}

	/**
	 * @return array
	 */
	public function get_template_info() {
		$style_api = new FrmStyleApi();
		return $style_api->get_api_info();
	}
}
