<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCSVExportHelper {

	protected static $separator        = ', ';
	protected static $column_separator = ',';
	protected static $line_break       = 'return';
	protected static $charset          = 'UTF-8';
	protected static $to_encoding      = 'UTF-8';
	protected static $wp_date_format   = 'Y-m-d H:i:s';
	protected static $comment_count    = 0;
	protected static $form_id          = 0;
	protected static $headings         = array();
	protected static $fields           = array();
	protected static $entry;
	protected static $has_parent_id;
	protected static $fields_by_repeater_id;

	public static function csv_format_options() {
		$formats = array( 'UTF-8', 'ISO-8859-1', 'windows-1256', 'windows-1251', 'macintosh' );
		$formats = apply_filters( 'frm_csv_format_options', $formats );

		return $formats;
	}

	public static function generate_csv( $atts ) {
		global $frm_vars;
		$frm_vars['prevent_caching'] = true;

		self::$fields  = $atts['form_cols'];
		self::$form_id = $atts['form']->id;
		self::set_class_paramters();
		self::set_has_parent_id( $atts['form'] );

		$filename = apply_filters( 'frm_csv_filename', gmdate( 'ymdHis', time() ) . '_' . sanitize_title_with_dashes( $atts['form']->name ) . '_formidable_entries.csv', $atts['form'] );
		unset( $atts['form'], $atts['form_cols'] );

		self::print_file_headers( $filename );
		unset( $filename );

		$comment_count       = FrmDb::get_count(
			'frm_item_metas',
			array(
				'item_id'         => $atts['entry_ids'],
				'field_id'        => 0,
				'meta_value like' => '{',
			),
			array(
				'group_by' => 'item_id',
				'order_by' => 'count(*) DESC',
				'limit'    => 1,
			)
		);
		self::$comment_count = $comment_count;

		self::prepare_csv_headings();

		// fetch 20 posts at a time rather than loading the entire table into memory
		while ( $next_set = array_splice( $atts['entry_ids'], 0, 20 ) ) {
			self::prepare_next_csv_rows( $next_set );
		}
	}

	private static function set_class_paramters() {
		self::$separator      = apply_filters( 'frm_csv_sep', self::$separator );
		self::$line_break     = apply_filters( 'frm_csv_line_break', self::$line_break );
		self::$wp_date_format = apply_filters( 'frm_csv_date_format', self::$wp_date_format );
		self::get_csv_format();
		self::$charset = get_option( 'blog_charset' );

		$col_sep = ( isset( $_POST['csv_col_sep'] ) && ! empty( $_POST['csv_col_sep'] ) ) ? sanitize_text_field( wp_unslash( $_POST['csv_col_sep'] ) ) : self::$column_separator;

		self::$column_separator = apply_filters( 'frm_csv_column_sep', $col_sep );
	}

	private static function set_has_parent_id( $form ) {
		self::$has_parent_id = $form->parent_form_id > 0;
	}

	private static function print_file_headers( $filename ) {
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="' . esc_attr( $filename ) . '"' );
		header( 'Content-Type: text/csv; charset=' . self::$charset, true );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', mktime( gmdate( 'H' ) + 2, gmdate( 'i' ), gmdate( 's' ), gmdate( 'm' ), gmdate( 'd' ), gmdate( 'Y' ) ) ) . ' GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );

		do_action(
			'frm_csv_headers',
			array(
				'form_id' => self::$form_id,
				'fields'  => self::$fields,
			)
		);
	}

	public static function get_csv_format() {
		$csv_format        = FrmAppHelper::get_post_param( 'csv_format', 'UTF-8', 'sanitize_text_field' );
		$csv_format        = apply_filters( 'frm_csv_format', $csv_format );
		self::$to_encoding = $csv_format;
	}

	private static function prepare_csv_headings() {
		$headings = array();
		self::csv_headings( $headings );
		$headings       = apply_filters(
			'frm_csv_columns',
			$headings,
			self::$form_id,
			array(
				'fields' => self::$fields,
			)
		);
		self::$headings = $headings;

		self::print_csv_row( $headings );
	}

	private static function field_headings( $col ) {
		$field_headings  = array();
		$separate_values = array( 'user_id', 'file', 'data', 'date' );
		if ( isset( $col->field_options['separate_value'] ) && $col->field_options['separate_value'] && ! in_array( $col->type, $separate_values, true ) ) {
			$field_headings[ $col->id . '_label' ] = strip_tags( $col->name . ' ' . __( '(label)', 'formidable' ) );
		}

		$field_headings[ $col->id ] = strip_tags( $col->name );
		$field_headings             = apply_filters(
			'frm_csv_field_columns',
			$field_headings,
			array(
				'field' => $col,
			)
		);

		return $field_headings;
	}

	private static function csv_headings( &$headings ) {
		$fields_by_repeater_id = array();
		$repeater_ids          = array();

		foreach ( self::$fields as $col ) {
			if ( self::is_the_child_of_a_repeater( $col ) ) {
				$repeater_id                           = $col->field_options['in_section'];
				$headings[ 'repeater' . $repeater_id ] = array(); // set a placeholder to maintain order for repeater fields

				if ( ! isset( $fields_by_repeater_id[ $repeater_id ] ) ) {
					$fields_by_repeater_id[ $repeater_id ] = array();
					$repeater_ids[]                        = $repeater_id;
				}

				$fields_by_repeater_id[ $repeater_id ][] = $col;
			} else {
				$headings += self::field_headings( $col );
			}
		}
		unset( $repeater_id, $col );

		if ( $repeater_ids ) {
			$where         = array( 'field_id' => $repeater_ids );
			$repeater_meta = FrmDb::get_results( 'frm_item_metas', $where, 'field_id, meta_value' );
			$max           = array_fill_keys( $repeater_ids, 0 );

			foreach ( $repeater_meta as $row ) {
				$start  = strpos( $row->meta_value, 'a:' ) + 2;
				$end    = strpos( $row->meta_value, ':{' );
				$length = substr( $row->meta_value, $start, $end - $start );

				if ( $length > $max[ $row->field_id ] ) {
					$max[ $row->field_id ] = $length;
				}
			}
			unset( $start, $end, $length, $row, $repeater_meta, $where );

			$flat = array();
			foreach ( $headings as $key => $heading ) {
				if ( is_array( $heading ) ) {
					$repeater_id = str_replace( 'repeater', '', $key );

					$repeater_headings = array();
					foreach ( $fields_by_repeater_id[ $repeater_id ] as $col ) {
						$repeater_headings += self::field_headings( $col );
					}

					for ( $i = 0; $i < $max[ $repeater_id ]; $i ++ ) {
						foreach ( $repeater_headings as $repeater_key => $repeater_name ) {
							$flat[ $repeater_key . '[' . $i . ']' ] = $repeater_name;
						}
					}
				} else {
					$flat[ $key ] = $heading;
				}
			}

			self::$fields_by_repeater_id = $fields_by_repeater_id;

			unset( $key, $heading, $max, $repeater_headings, $repeater_id, $fields_by_repeater_id );

			$headings = $flat;
			unset( $flat );
		}

		if ( self::$comment_count ) {
			for ( $i = 0; $i < self::$comment_count; $i ++ ) {
				$headings[ 'comment' . $i ]            = __( 'Comment', 'formidable' );
				$headings[ 'comment_user_id' . $i ]    = __( 'Comment User', 'formidable' );
				$headings[ 'comment_created_at' . $i ] = __( 'Comment Date', 'formidable' );
			}
			unset( $i );
		}

		$headings['created_at'] = __( 'Timestamp', 'formidable' );
		$headings['updated_at'] = __( 'Last Updated', 'formidable' );
		$headings['user_id']    = __( 'Created By', 'formidable' );
		$headings['updated_by'] = __( 'Updated By', 'formidable' );
		$headings['is_draft']   = __( 'Draft', 'formidable' );
		$headings['ip']         = __( 'IP', 'formidable' );
		$headings['id']         = __( 'ID', 'formidable' );
		$headings['item_key']   = __( 'Key', 'formidable' );
		if ( self::has_parent_id() ) {
			$headings['parent_id'] = __( 'Parent ID', 'formidable' );
		}
	}

	/**
	 * @param object $field
	 * @return bool
	 */
	private static function is_the_child_of_a_repeater( $field ) {
		if ( $field->form_id === self::$form_id || empty( $field->field_options['in_section'] ) ) {
			return false;
		}

		$section_id = $field->field_options['in_section'];
		$section    = FrmField::getOne( $section_id );

		if ( ! $section ) {
			return false;
		}

		return FrmField::is_repeating_field( $section );
	}

	private static function has_parent_id() {
		return self::$has_parent_id;
	}

	private static function prepare_next_csv_rows( $next_set ) {
		// order by parent_item_id so children will be first
		$where   = array(
			'or'             => 1,
			'id'             => $next_set,
			'parent_item_id' => $next_set,
		);
		$entries = FrmEntry::getAll( $where, ' ORDER BY parent_item_id DESC', '', true, false );

		foreach ( $entries as $k => $entry ) {
			self::$entry = $entry;
			unset( $entry );

			if ( self::$entry->form_id !== self::$form_id ) {
				self::add_repeat_field_values_to_csv( $entries );
			} else {
				self::prepare_csv_row();
			}
		}
	}

	private static function prepare_csv_row() {
		$row = array();
		self::add_field_values_to_csv( $row );
		self::add_entry_data_to_csv( $row );
		$row = apply_filters(
			'frm_csv_row',
			$row,
			array(
				'entry'         => self::$entry,
				'date_format'   => self::$wp_date_format,
				'comment_count' => self::$comment_count,
			)
		);
		self::print_csv_row( $row );
	}

	private static function add_repeat_field_values_to_csv( &$entries ) {
		if ( isset( self::$entry->metas ) ) {
			// add child entries to the parent
			foreach ( self::$entry->metas as $meta_id => $meta_value ) {
				if ( ! is_numeric( $meta_id ) || '' === $meta_value ) {
					// if the hook is being used to include field keys in the metas array,
					// we need to skip the keys and only process field ids
					continue;
				}

				if ( ! isset( $entries[ self::$entry->parent_item_id ] ) ) {
					$entries[ self::$entry->parent_item_id ]        = new stdClass();
					$entries[ self::$entry->parent_item_id ]->metas = array();
				}

				if ( ! isset( $entries[ self::$entry->parent_item_id ]->metas[ $meta_id ] ) ) {
					$entries[ self::$entry->parent_item_id ]->metas[ $meta_id ] = array();
				} elseif ( ! is_array( $entries[ self::$entry->parent_item_id ]->metas[ $meta_id ] ) ) {
					// if the data is here, it should be an array but if this field has collected data
					// both while inside and outside of the repeating section, it's possible this is a string
					$entries[ self::$entry->parent_item_id ]->metas[ $meta_id ] = (array) $entries[ self::$entry->parent_item_id ]->metas[ $meta_id ];
				}

				//add the repeated values
				$entries[ self::$entry->parent_item_id ]->metas[ $meta_id ][] = $meta_value;
			}

			self::$entry->metas                              = self::fill_missing_repeater_metas( self::$entry->metas, $entries );
			$entries[ self::$entry->parent_item_id ]->metas += self::$entry->metas;
		}

		// add the embedded form id
		if ( ! isset( $entries[ self::$entry->parent_item_id ]->embedded_fields ) ) {
			$entries[ self::$entry->parent_item_id ]->embedded_fields = array();
		}
		$entries[ self::$entry->parent_item_id ]->embedded_fields[ self::$entry->id ] = self::$entry->form_id;
	}

	/**
	 * When an empty field is saved, it isn't saved as a meta value
	 * The export needs all of the meta to be filled in, so we put blank strings for every missing repeater child
	 *
	 * @param array $metas
	 * @param array $entries
	 * @return array
	 */
	private static function fill_missing_repeater_metas( $metas, &$entries ) {
		$field_ids = array_keys( $metas );
		$field_id  = end( $field_ids );
		$field     = self::get_field( $field_id );

		if ( ! $field || empty( $field->field_options['in_section'] ) ) {
			return $metas;
		}

		$repeater_id = $field->field_options['in_section'];
		if ( ! isset( self::$fields_by_repeater_id[ $repeater_id ] ) ) {
			return $metas;
		}

		foreach ( self::$fields_by_repeater_id[ $repeater_id ] as $repeater_child ) {
			if ( ! isset( $metas[ $repeater_child->id ] ) ) {
				$metas[ $repeater_child->id ]                                            = '';
				$entries[ self::$entry->parent_item_id ]->metas[ $repeater_child->id ][] = '';
			}
		}

		return $metas;
	}

	private static function get_field( $field_id ) {
		$field_id = (int) $field_id;
		foreach ( self::$fields as $field ) {
			if ( $field_id === (int) $field->id ) {
				return $field;
			}
		}
		return false;
	}

	private static function add_field_values_to_csv( &$row ) {
		foreach ( self::$fields as $col ) {
			$field_value = isset( self::$entry->metas[ $col->id ] ) ? self::$entry->metas[ $col->id ] : false;

			FrmAppHelper::unserialize_or_decode( $field_value );
			self::add_array_values_to_columns( $row, compact( 'col', 'field_value' ) );

			$field_value = apply_filters(
				'frm_csv_value',
				$field_value,
				array(
					'field'     => $col,
					'entry'     => self::$entry,
					'separator' => self::$separator,
				)
			);

			if ( ! empty( $col->field_options['separate_value'] ) ) {
				$sep_value = FrmEntriesHelper::display_value(
					$field_value,
					$col,
					array(
						'type'              => $col->type,
						'post_id'           => self::$entry->post_id,
						'show_icon'         => false,
						'entry_id'          => self::$entry->id,
						'sep'               => self::$separator,
						'embedded_field_id' => ( isset( self::$entry->embedded_fields ) && isset( self::$entry->embedded_fields[ self::$entry->id ] ) ) ? 'form' . self::$entry->embedded_fields[ self::$entry->id ] : 0,
					)
				);

				$row[ $col->id . '_label' ] = $sep_value;
				unset( $sep_value );
			}

			$row[ $col->id ] = $field_value;

			unset( $col, $field_value );
		}
	}

	/**
	 * @since 2.0.23
	 */
	private static function add_array_values_to_columns( &$row, $atts ) {
		if ( is_array( $atts['field_value'] ) ) {
			foreach ( $atts['field_value'] as $key => $sub_value ) {
				$column_key = $atts['col']->id . '_' . $key;
				if ( ! is_numeric( $key ) && isset( self::$headings[ $column_key ] ) ) {
					$row[ $column_key ] = $sub_value;
				}
			}
		}
	}

	private static function add_entry_data_to_csv( &$row ) {
		$row['created_at'] = FrmAppHelper::get_formatted_time( self::$entry->created_at, self::$wp_date_format, ' ' );
		$row['updated_at'] = FrmAppHelper::get_formatted_time( self::$entry->updated_at, self::$wp_date_format, ' ' );
		$row['user_id']    = self::$entry->user_id;
		$row['updated_by'] = self::$entry->updated_by;
		$row['is_draft']   = self::$entry->is_draft ? '1' : '0';
		$row['ip']         = self::$entry->ip;
		$row['id']         = self::$entry->id;
		$row['item_key']   = self::$entry->item_key;
		if ( self::has_parent_id() ) {
			$row['parent_id'] = self::$entry->parent_item_id;
		}
	}

	private static function print_csv_row( $rows ) {
		$sep = '';

		foreach ( self::$headings as $k => $heading ) {
			if ( isset( $rows[ $k ] ) ) {
				$row = $rows[ $k ];
			} else {
				$row = '';
				// array indexed data is not at $rows[ $k ]
				if ( $k[ strlen( $k ) - 1 ] === ']' ) {
					$start = strrpos( $k, '[' );
					$key   = substr( $k, 0, $start ++ );
					$index = substr( $k, $start, strlen( $k ) - 1 - $start );

					if ( isset( $rows[ $key ] ) && isset( $rows[ $key ][ $index ] ) ) {
						$row = $rows[ $key ][ $index ];
					}

					unset( $start, $key, $index );
				}
			}

			if ( is_array( $row ) ) {
				// implode the repeated field values
				$row = implode( self::$separator, FrmAppHelper::array_flatten( $row, 'reset' ) );
			}

			$val = self::encode_value( $row );
			if ( 'return' !== self::$line_break ) {
				$val = str_replace( array( "\r\n", "\r", "\n" ), self::$line_break, $val );
			}

			echo $sep . '"' . $val . '"'; // WPCS: XSS ok.
			$sep = self::$column_separator;

			unset( $k, $row );
		}
		echo "\n";
	}

	public static function encode_value( $line ) {
		if ( '' === $line ) {
			return $line;
		}

		$convmap = false;

		switch ( self::$to_encoding ) {
			case 'macintosh':
				// this map was derived from the differences between the MacRoman and UTF-8 Charsets
				// Reference:
				//   - http://www.alanwood.net/demos/macroman.html
				$convmap = array( 256, 304, 0, 0xffff, 306, 337, 0, 0xffff, 340, 375, 0, 0xffff, 377, 401, 0, 0xffff, 403, 709, 0, 0xffff, 712, 727, 0, 0xffff, 734, 936, 0, 0xffff, 938, 959, 0, 0xffff, 961, 8210, 0, 0xffff, 8213, 8215, 0, 0xffff, 8219, 8219, 0, 0xffff, 8227, 8229, 0, 0xffff, 8231, 8239, 0, 0xffff, 8241, 8248, 0, 0xffff, 8251, 8259, 0, 0xffff, 8261, 8363, 0, 0xffff, 8365, 8481, 0, 0xffff, 8483, 8705, 0, 0xffff, 8707, 8709, 0, 0xffff, 8711, 8718, 0, 0xffff, 8720, 8720, 0, 0xffff, 8722, 8729, 0, 0xffff, 8731, 8733, 0, 0xffff, 8735, 8746, 0, 0xffff, 8748, 8775, 0, 0xffff, 8777, 8799, 0, 0xffff, 8801, 8803, 0, 0xffff, 8806, 9673, 0, 0xffff, 9675, 63742, 0, 0xffff, 63744, 64256, 0, 0xffff );
				break;
			case 'ISO-8859-1':
				$convmap = array( 256, 10000, 0, 0xffff );
		}

		if ( is_array( $convmap ) ) {
			$line = mb_encode_numericentity( $line, $convmap, self::$charset );
		}

		if ( self::$to_encoding !== self::$charset ) {
			$line = iconv( self::$charset, self::$to_encoding . '//IGNORE', $line );
		}

		return self::escape_csv( $line );
	}

	/**
	 * Escape a " in a csv with another "
	 *
	 * @since 2.0
	 * @param mixed $value
	 * @return mixed
	 */
	public static function escape_csv( $value ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}

		if ( '=' === $value[0] ) {
			// escape the = to prevent vulnerability
			$value = "'" . $value;
		}
		$value = str_replace( '"', '""', $value );

		return $value;
	}
}
