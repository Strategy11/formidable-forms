<?php

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
	 */
	private $direction = 'ltr';

	/**
	 * @var bool
	 * @since 2.04
	 */
	private $odd = true;

	/**
	 * @var string
	 * @since 2.04
	 */
	private $table_style = '';

	/**
	 * @var string
	 * @since 2.04
	 */
	private $td_style = '';


	/**
	 * FrmTableHTMLGenerator constructor.
	 *
	 * @param string $type
	 * @param array $atts
	 */
	public function __construct( $type, $atts = array() ) {

		$this->type = (string) $type;
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
	 */
	private function init_style_settings( $atts ) {
		$this->style_settings = apply_filters( 'frm_show_entry_styles', array(
			'border_color' => 'dddddd',
			'bg_color'     => 'f7f7f7',
			'text_color'   => '444444',
			'font_size'    => '12px',
			'border_width' => '1px',
			'alt_bg_color' => 'ffffff',
		) );

		foreach ( $this->style_settings as $key => $setting ) {
			if ( isset( $atts[ $key ] ) && $atts[ $key ] !== '' ) {
				$this->style_settings[ $key ] = str_replace( '#', '', $atts[ $key ] );
			}
		}
	}

	/**
	 * Set the use_inline_style property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
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
	 */
	private function init_table_style() {
		if ( $this->use_inline_style === true ) {

			$this->table_style = ' style="' . esc_attr( 'font-size:' . $this->style_settings['font_size'] . ';line-height:135%;' );
			$this->table_style .= esc_attr( 'border-bottom:' . $this->style_settings['border_width'] . ' solid #' . $this->style_settings['border_color'] . ';' ) . '"';

		}
	}

	/**
	 * Set the td_style property
	 *
	 * @since 2.04
	 */
	private function init_td_style() {
		if ( $this->use_inline_style === true ) {

			$td_style_attributes = 'text-align:' . ( $this->direction == 'rtl' ? 'right' : 'left' ) . ';';
			$td_style_attributes .= 'color:#' . $this->style_settings['text_color'] . ';padding:7px 9px;vertical-align:top;';
			$td_style_attributes .= 'border-top:' . $this->style_settings['border_width'] . ' solid #' . $this->style_settings[ 'border_color' ] . ';';

			$this->td_style = ' style="' . $td_style_attributes . '"';
		}
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
	 *
	 * @return string
	 */
	private function tr_style() {

		if ( $this->type === 'shortcode' ) {
			$tr_style = ' style="[frm-alt-color]"';
		} else if ( $this->use_inline_style ) {
			$tr_style = ' style="background-color:#' . $this->table_row_background_color() . ';"';
		} else {
			$tr_style = '';
		}

		return $tr_style;
	}

	/**
	 * Switch the odd property from true to false or false to true
	 *
	 * @since 2.04
	 */
	private function switch_odd() {
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
	 * @param string $label
	 * @param string $value
	 *
	 * @return string
	 */
	public function generate_two_cell_table_row( $label, $value ) {
		$row = '<tr' . $this->tr_style() . '>';

		if ( 'rtl' == $this->direction ) {
			$first = $value;
			$second = $label;
		} else {
			$first = $label;
			$second = $value;
		}

		$row .= '<td' . $this->td_style . '>' . $first . '</td>';
		$row .= '<td' . $this->td_style . '>' . $second . '</td>';

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
		$row = '<tr' . $this->tr_style() . '>';
		$row .= '<td colspan="2"' . $this->td_style . '>' . $value . '</td>';
		$row .= '</tr>' . "\r\n";

		$this->switch_odd();

		return $row;
	}


	/**
	 * Generate a two cell row of shortcodes for an HTML table
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function generate_two_cell_shortcode_row( $field, $value = null ) {
		$row = '[if ' . $field->id . ']';

		$label = '[' . $field->id . ' show=field_label]';

		if ( $value === null ) {
			$value = '[' . $field->id . ']';
		}

		$row .= $this->generate_two_cell_table_row( $label, $value );

		$row .= '[/if ' . $field->id . ']' . "\r\n";

		return $row;
	}

	/**
	 * Generate a sinle cell row of shortcodes for an HTML table
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function generate_single_cell_shortcode_row( $field, $value ) {
		$row = '[if ' . $field->id . ']';
		$row .= $this->generate_single_cell_table_row( $value );
		$row .= '[/if ' . $field->id . ']' . "\r\n";

		return $row;
	}

}