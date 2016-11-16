<?php

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

	public static function csv_format_options() {
		$formats = array( 'UTF-8', 'ISO-8859-1', 'windows-1256', 'windows-1251', 'macintosh' );
		$formats = apply_filters( 'frm_csv_format_options', $formats );
		return $formats;
	}

	public static function generate_csv( $atts ) {
		global $frm_vars;
		$frm_vars['prevent_caching'] = true;

		self::$fields = $atts['form_cols'];
		self::$form_id = $atts['form']->id;
		self::set_class_paramters();

		$filename = apply_filters( 'frm_csv_filename', date( 'ymdHis', time() ) . '_' . sanitize_title_with_dashes( $atts['form']->name ) . '_formidable_entries.csv', $atts['form'] );
		unset( $atts['form'], $atts['form_cols'] );

		self::print_file_headers( $filename );
		unset( $filename );

		$comment_count = FrmDb::get_count(
			'frm_item_metas',
			array( 'item_id' => $atts['entry_ids'], 'field_id' => 0, 'meta_value like' => '{' ),
			array( 'group_by' => 'item_id', 'order_by' => 'count(*) DESC', 'limit' => 1 )
		);
		self::$comment_count = $comment_count;

		self::prepare_csv_headings();

		// fetch 20 posts at a time rather than loading the entire table into memory
		while ( $next_set = array_splice( $atts['entry_ids'], 0, 20 ) ) {
			self::prepare_next_csv_rows( $next_set );
		}
	}

	private static function set_class_paramters() {
		self::$separator = apply_filters( 'frm_csv_sep', self::$separator );
		self::$line_break = apply_filters( 'frm_csv_line_break', self::$line_break );
		self::$wp_date_format = apply_filters( 'frm_csv_date_format', self::$wp_date_format );
		self::get_csv_format();
		self::$charset = get_option( 'blog_charset' );

		$col_sep = ( isset( $_POST['csv_col_sep'] ) && ! empty( $_POST['csv_col_sep'] ) ) ? sanitize_text_field( $_POST['csv_col_sep'] ) : self::$column_separator;
		self::$column_separator = apply_filters( 'frm_csv_column_sep', $col_sep );
	}

	private static function print_file_headers( $filename ) {
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="' . esc_attr( $filename ) . '"' );
		header( 'Content-Type: text/csv; charset=' . self::$charset, true );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', mktime( date( 'H' ) + 2, date( 'i' ), date( 's' ), date( 'm' ), date( 'd' ), date('Y' ) ) ) . ' GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );

		do_action( 'frm_csv_headers', array( 'form_id' => self::$form_id, 'fields' => self::$fields ) );
	}

	public static function get_csv_format() {
		$csv_format = FrmAppHelper::get_post_param( 'csv_format', 'UTF-8', 'sanitize_text_field' );
		$csv_format = apply_filters( 'frm_csv_format', $csv_format );
		self::$to_encoding = $csv_format;
	}

	private static function prepare_csv_headings() {
		$headings = array();
		self::csv_headings( $headings );
		$headings = apply_filters( 'frm_csv_columns', $headings, self::$form_id, array( 'fields' => self::$fields ) );
		self::$headings = $headings;

		self::print_csv_row( $headings );
	}

	private static function csv_headings( &$headings ) {
		foreach ( self::$fields as $col ) {
			$field_headings = array();
			if ( isset( $col->field_options['separate_value'] ) && $col->field_options['separate_value'] && ! in_array( $col->type, array( 'user_id', 'file', 'data', 'date' ) ) ) {
				$field_headings[ $col->id . '_label' ] = strip_tags( $col->name . ' ' . __( '(label)', 'formidable' ) );
			}

			$field_headings[ $col->id ] = strip_tags( $col->name );
			$field_headings = apply_filters( 'frm_csv_field_columns', $field_headings, array( 'field' => $col ) );
			$headings += $field_headings;
		}

		if ( self::$comment_count ) {
			for ( $i = 0; $i < self::$comment_count; $i++ ) {
				$headings[ 'comment' . $i ] = __( 'Comment', 'formidable' );
				$headings[ 'comment_user_id' . $i ] = __( 'Comment User', 'formidable' );
				$headings[ 'comment_created_at' . $i ] = __( 'Comment Date', 'formidable' );
			}
			unset($i);
		}

		$headings['created_at'] = __( 'Timestamp', 'formidable' );
		$headings['updated_at'] = __( 'Last Updated', 'formidable' );
		$headings['user_id'] = __( 'Created By', 'formidable' );
		$headings['updated_by'] = __( 'Updated By', 'formidable' );
		$headings['is_draft'] = __( 'Draft', 'formidable' );
		$headings['ip'] = __( 'IP', 'formidable' );
		$headings['id'] = __( 'ID', 'formidable' );
		$headings['item_key'] = __( 'Key', 'formidable' );
	}

	private static function prepare_next_csv_rows( $next_set ) {
		// order by parent_item_id so children will be first
		$entries = FrmEntry::getAll( array( 'or' => 1, 'id' => $next_set, 'parent_item_id' => $next_set ), ' ORDER BY parent_item_id DESC', '', true, false );

		foreach ( $entries as $k => $entry ) {
			self::$entry = $entry;
			unset( $entry );

			if ( self::$entry->form_id != self::$form_id ) {
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
		$row = apply_filters( 'frm_csv_row', $row, array( 'entry' => self::$entry, 'date_format' => self::$wp_date_format, 'comment_count' => self::$comment_count ) );
		self::print_csv_row( $row );
	}

	private static function add_repeat_field_values_to_csv( &$entries ) {
		if ( isset( self::$entry->metas ) ) {
			// add child entries to the parent
			foreach ( self::$entry->metas as $meta_id => $meta_value ) {
				if ( ! is_numeric( $meta_id ) || $meta_value == '' ) {
					// if the hook is being used to include field keys in the metas array,
					// we need to skip the keys and only process field ids
					continue;
				}

				if ( ! isset( $entries[ self::$entry->parent_item_id ]->metas[ $meta_id ] ) ) {
					$entries[ self::$entry->parent_item_id ]->metas[ $meta_id ] = array();
				} else if ( ! is_array( $entries[ self::$entry->parent_item_id ]->metas[ $meta_id ] ) ) {
					// if the data is here, it should be an array but if this field has collected data
					// both while inside and outside of the repeating section, it's possible this is a string
					$entries[ self::$entry->parent_item_id ]->metas[ $meta_id ] = (array) $entries[ self::$entry->parent_item_id ]->metas[ $meta_id ];
				}

				//add the repeated values
				$entries[ self::$entry->parent_item_id ]->metas[ $meta_id ][] = $meta_value;
			}
			$entries[ self::$entry->parent_item_id ]->metas += self::$entry->metas;
		}

		// add the embedded form id
		if ( ! isset( $entries[ self::$entry->parent_item_id ]->embedded_fields ) ) {
			$entries[ self::$entry->parent_item_id ]->embedded_fields = array();
		}
		$entries[ self::$entry->parent_item_id ]->embedded_fields[ self::$entry->id ] = self::$entry->form_id;
	}

	private static function add_field_values_to_csv( &$row ) {
		foreach ( self::$fields as $col ) {
			$field_value = isset( self::$entry->metas[ $col->id ] ) ? self::$entry->metas[ $col->id ] : false;

			$field_value = maybe_unserialize( $field_value );
			self::add_array_values_to_columns( $row, compact( 'col', 'field_value' ) );

			$field_value = apply_filters( 'frm_csv_value', $field_value, array( 'field' => $col, 'entry' => self::$entry, 'separator' => self::$separator ) );

			if ( isset( $col->field_options['separate_value'] ) && $col->field_options['separate_value'] ) {
				$sep_value = FrmEntriesHelper::display_value( $field_value, $col, array(
					'type' => $col->type, 'post_id' => self::$entry->post_id, 'show_icon' => false,
					'entry_id' => self::$entry->id, 'sep' => self::$separator,
					'embedded_field_id' => ( isset( self::$entry->embedded_fields ) && isset( self::$entry->embedded_fields[ self::$entry->id ] ) ) ? 'form' . self::$entry->embedded_fields[ self::$entry->id ] : 0,
				) );
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
		$row['user_id'] = self::$entry->user_id;
		$row['updated_by'] = self::$entry->updated_by;
		$row['is_draft'] = self::$entry->is_draft ? '1' : '0';
		$row['ip'] = self::$entry->ip;
		$row['id'] = self::$entry->id;
		$row['item_key'] = self::$entry->item_key;
	}

	private static function print_csv_row( $rows ) {
		$sep = '';

		foreach ( self::$headings as $k => $heading ) {
			$row = isset( $rows[ $k ] ) ? $rows[ $k ] : '';
			if ( is_array( $row ) ) {
				// implode the repeated field values
				$row = implode( self::$separator, FrmAppHelper::array_flatten( $row, 'reset' ) );
			}

			$val = self::encode_value( $row );
			if ( self::$line_break != 'return' ) {
				$val = str_replace( array( "\r\n", "\r", "\n" ), self::$line_break, $val );
			}

			echo $sep . '"' . $val . '"';
			$sep = self::$column_separator;

			unset( $k, $row );
		}
		echo "\n";
	}

	public static function encode_value( $line ) {
		if ( $line == '' ) {
			return $line;
		}

        $convmap = false;

		switch ( self::$to_encoding ) {
            case 'macintosh':
				// this map was derived from the differences between the MacRoman and UTF-8 Charsets
				// Reference:
				//   - http://www.alanwood.net/demos/macroman.html
                $convmap = array(
                    256, 304, 0, 0xffff,
                    306, 337, 0, 0xffff,
                    340, 375, 0, 0xffff,
                    377, 401, 0, 0xffff,
                    403, 709, 0, 0xffff,
                    712, 727, 0, 0xffff,
                    734, 936, 0, 0xffff,
                    938, 959, 0, 0xffff,
                    961, 8210, 0, 0xffff,
                    8213, 8215, 0, 0xffff,
                    8219, 8219, 0, 0xffff,
                    8227, 8229, 0, 0xffff,
                    8231, 8239, 0, 0xffff,
                    8241, 8248, 0, 0xffff,
                    8251, 8259, 0, 0xffff,
                    8261, 8363, 0, 0xffff,
                    8365, 8481, 0, 0xffff,
                    8483, 8705, 0, 0xffff,
                    8707, 8709, 0, 0xffff,
                    8711, 8718, 0, 0xffff,
                    8720, 8720, 0, 0xffff,
                    8722, 8729, 0, 0xffff,
                    8731, 8733, 0, 0xffff,
                    8735, 8746, 0, 0xffff,
                    8748, 8775, 0, 0xffff,
                    8777, 8799, 0, 0xffff,
                    8801, 8803, 0, 0xffff,
                    8806, 9673, 0, 0xffff,
                    9675, 63742, 0, 0xffff,
                    63744, 64256, 0, 0xffff,
                );
            break;
            case 'ISO-8859-1':
                $convmap = array( 256, 10000, 0, 0xffff );
            break;
        }

		if ( is_array( $convmap ) ) {
			$line = mb_encode_numericentity( $line, $convmap, self::$charset );
		}

		if ( self::$to_encoding != self::$charset ) {
			$line = iconv( self::$charset, self::$to_encoding . '//IGNORE', $line );
		}

		return self::escape_csv( $line );
    }

	/**
	 * Escape a " in a csv with another "
	 * @since 2.0
	 */
	public static function escape_csv( $value ) {
		if ( $value[0] == '=' ) {
			// escape the = to prevent vulnerability
			$value = "'" . $value;
		}
		$value = str_replace( '"', '""', $value );
		return $value;
	}
}
