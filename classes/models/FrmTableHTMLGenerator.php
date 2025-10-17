<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.04
 */
class FrmTableHTMLGenerator {

	/**
	 * @var string
	 * @since 2.04
	 */
	private $type = '';

	/**
	 * @var array
	 * @since 2.04
	 */
	private $style_settings = array();

	/**
	 * @var bool
	 * @since 2.04
	 */
	private $use_inline_style = true;

	/**
	 * @var string
	 * @since 2.04
	 * @since 5.0.16 Changed scope from `private` to `protected`.
	 */
	protected $direction = 'ltr';

	/**
	 * @var bool
	 * @since 2.04
	 */
	private $odd = true;

	/**
	 * @var string
	 * @since 2.04
	 * @since 5.0.16 Changed scope from `private` to `protected`.
	 */
	protected $table_style = '';

	/**
	 * @var string
	 * @since 2.04
	 * @since 5.0.16 Changed scope from `private` to `protected`.
	 */
	protected $td_style = '';

	/**
	 * Cell padding.
	 *
	 * @since 6.25
	 *
	 * @var string
	 */
	protected $cell_padding = '7px 9px';

	/**
	 * Table width.
	 *
	 * @since 6.25
	 *
	 * @var string
	 */
	protected $width = '';

	/**
	 * Used to add a class in tables. Set in Pro.
	 *
	 * @var bool
	 * @since 5.4.2
	 */
	public $is_child = false;

	/**
	 * FrmTableHTMLGenerator constructor.
	 *
	 * @param string $type
	 * @param array  $atts
	 */
	public function __construct( $type, $atts = array() ) {

		$this->type = (string) $type;

		if ( isset( $atts['cell_padding'] ) ) {
			$this->cell_padding = $atts['cell_padding'];
		}

		if ( isset( $atts['width'] ) ) {
			$this->width = $atts['width'];
		}

		$this->init_style_settings( $atts );
		$this->init_use_inline_style( $atts );
		$this->init_direction( $atts );
		$this->init_table_style();
		$this->init_td_style();
	}

	/**
	 * Set the style_settings property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 * @return void
	 */
	private function init_style_settings( $atts ) {
		$style_settings       = array(
			'border_color' => 'dddddd',
			'bg_color'     => 'f7f7f7',
			'text_color'   => '444444',
			'font_size'    => '12px',
			'border_width' => '1px',
			'alt_bg_color' => 'ffffff',
		);
		$this->style_settings = apply_filters( 'frm_show_entry_styles', $style_settings );

		foreach ( $this->style_settings as $key => $setting ) {
			if ( isset( $atts[ $key ] ) && $atts[ $key ] !== '' ) {
				$this->style_settings[ $key ] = $atts[ $key ];
			}

			if ( $this->is_color_setting( $key ) ) {
				$this->style_settings[ $key ] = $this->get_color_markup( $this->style_settings[ $key ] );
			}
		}

		$this->style_settings['class'] = $atts['class'] ?? '';
	}

	/**
	 * Set the use_inline_style property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 * @return void
	 */
	private function init_use_inline_style( $atts ) {
		if ( isset( $atts['inline_style'] ) && ! $atts['inline_style'] ) {
			$this->use_inline_style = false;
		}
	}

	/**
	 * Set the direction property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 * @return void
	 */
	private function init_direction( $atts ) {
		if ( isset( $atts['direction'] ) && $atts['direction'] === 'rtl' ) {
			$this->direction = 'rtl';
		}
	}

	/**
	 * Set the table_style property
	 *
	 * @since 2.04
	 * @return void
	 */
	private function init_table_style() {
		if ( $this->use_inline_style === true ) {

			$this->table_style  = ' style="' . esc_attr( 'border-spacing:0;font-size:' . $this->style_settings['font_size'] . ';line-height:135%;' );
			$this->table_style .= esc_attr( 'border-bottom:' . $this->style_settings['border_width'] . ' solid ' . $this->style_settings['border_color'] . ';' ) . '"';

		}

		if ( ! empty( $this->style_settings['class'] ) ) {
			$this->table_style .= ' class="' . esc_attr( $this->style_settings['class'] ) . '"';
		}

		if ( ! empty( $this->width ) ) {
			$this->table_style .= ' width="' . esc_attr( $this->width ) . '"';
		}
	}

	/**
	 * Set the td_style property
	 *
	 * @since 2.04
	 * @return void
	 */
	private function init_td_style() {
		if ( $this->use_inline_style === true ) {

			$td_style_attributes  = 'text-align:' . ( $this->direction === 'rtl' ? 'right' : 'left' ) . ';';
			$td_style_attributes .= 'color:' . $this->style_settings['text_color'] . ';padding:' . $this->cell_padding . ';vertical-align:top;';
			$td_style_attributes .= 'border-top:' . $this->style_settings['border_width'] . ' solid ' . $this->style_settings['border_color'] . ';';

			$this->td_style = ' style="' . esc_attr( $td_style_attributes ) . '"';
		}
	}

	/**
	 * Removes border CSS from HTML.
	 *
	 * @since 6.25
	 *
	 * @param string $html The HTML.
	 * @param string $position The border position. Default is `top`.
	 * @return string
	 */
	public function remove_border( $html, $position = 'top' ) {
		$search = sprintf(
			'border-%1$s:%2$s solid %3$s;',
			$position,
			$this->style_settings['border_width'],
			$this->style_settings['border_color']
		);

		return str_replace( $search, '', $html );
	}

	/**
	 * Determine if setting is for a color, e.g. text color, background color, or border color
	 *
	 * @since 2.05
	 * @param string $setting_key Name of setting.
	 *
	 * @return bool
	 */
	private function is_color_setting( $setting_key ) {
		return strpos( $setting_key, 'color' ) !== false;
	}

	/**
	 * Get color markup from color setting value
	 *
	 * @since 2.05
	 * @param string $color_markup value of a color setting, with format #FFFFF, FFFFFF, or white.
	 *
	 * @return string
	 */
	private function get_color_markup( $color_markup ) {
		$color_markup = trim( $color_markup );

		// Check if each character in string is valid hex digit
		if ( FrmAppHelper::ctype_xdigit( $color_markup ) ) {
			$color_markup = '#' . $color_markup;
		}

		return $color_markup;
	}

	/**
	 * Get the table row background color
	 *
	 * @since 2.04
	 *
	 * @return string
	 */
	private function table_row_background_color() {
		return ( $this->odd ? $this->style_settings['bg_color'] : $this->style_settings['alt_bg_color'] );
	}

	/**
	 * Get the table row style
	 *
	 * @since 2.04
	 * @since 5.0.16 Changed scope from `private` to `protected`.
	 * @since 6.25    Changed scope from `protected` to `public`.
	 *
	 * @return string
	 */
	public function tr_style() {

		if ( $this->type === 'shortcode' ) {
			$tr_style = ' style="[frm-alt-color]"';
		} elseif ( $this->use_inline_style ) {
			$tr_style = ' style="background-color:' . $this->table_row_background_color() . ';"';
		} else {
			$tr_style = '';
		}

		return $tr_style;
	}

	/**
	 * Gets table style.
	 *
	 * @since 6.25
	 *
	 * @return string
	 */
	public function get_table_style() {
		return $this->table_style;
	}

	/**
	 * Gets td style.
	 *
	 * @since 6.25
	 *
	 * @return string
	 */
	public function get_td_style() {
		return $this->td_style;
	}

	/**
	 * Switch the odd property from true to false or false to true
	 *
	 * @since 2.04
	 * @since 5.0.16 Changed scope from `private` to `protected`.
	 *
	 * @return void
	 */
	protected function switch_odd() {
		if ( $this->type !== 'shortcode' ) {
			$this->odd = ! $this->odd;
		}
	}

	/**
	 * Generate a table header
	 *
	 * @since 2.04
	 *
	 * @return string
	 */
	public function generate_table_header() {
		return '<table cellspacing="0"' . $this->table_style . '><tbody>' . "\r\n";
	}

	/**
	 * Generate a table footer
	 *
	 * @since 2.04
	 *
	 * @return string
	 */
	public function generate_table_footer() {
		return '</tbody></table>';
	}

	/**
	 * Generate a two cell row for an HTML table
	 *
	 * @since 2.04
	 *
	 * @param string $label The label.
	 * @param string $value The value.
	 *
	 * @return string
	 */
	public function generate_two_cell_table_row( $label, $value ) {
		$row  = '<tr' . $this->tr_style();
		$row .= $this->add_row_class( $value === '' );
		$row .= '>';

		$label = '<th scope="row"' . $this->td_style . '>' . wp_kses_post( $label ) . '</th>';
		$value = '<td' . $this->td_style . '>' . wp_kses_post( $value ) . '</td>';

		if ( 'rtl' == $this->direction ) {
			$row .= $value;
			$row .= $label;
		} else {
			$row .= $label;
			$row .= $value;
		}

		$row .= '</tr>' . "\r\n";

		$this->switch_odd();

		return $row;
	}

	/**
	 * Generate a single cell row for an HTML table
	 *
	 * @since 2.04
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function generate_single_cell_table_row( $value ) {
		$row  = '<tr' . $this->tr_style();
		$row .= $this->add_row_class();
		$row .= '>';
		$row .= '<td colspan="2"' . $this->td_style . '>' . $value . '</td>';
		$row .= '</tr>' . "\r\n";

		$this->switch_odd();

		return $row;
	}

	/**
	 * Add classes to the tr.
	 *
	 * @since 5.4.2
	 *
	 * @param bool $empty If the value in the row is blank.
	 *
	 * @return string
	 */
	protected function add_row_class( $empty = false ) {
		$class = '';
		if ( $empty ) {
			// Only add this class on two cell rows.
			$class .= ' frm-empty-row';
		}
		if ( $this->is_child ) {
			$class .= ' frm-child-row';
		}
		if ( $class ) {
			$class = ' class="' . trim( $class ) . '"';
		}
		return $class;
	}
}
