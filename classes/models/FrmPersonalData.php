<?php

class FrmPersonalData {

	private $limit = 200;

	private $page  = 1;

	public function __construct() {
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_data_eraser' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
	}

	/**
	 * Add options to the WordPress personal data exporter
	 *
	 * @since 3.02
	 * @param array $exporters
	 * @return array
	 */
	public function register_exporter( $exporters ) {
		$exporters['formidable-exporter'] = array(
			'exporter_friendly_name' => __( 'Formidable Forms Exporter', 'formidable' ),
			'callback'               => array( $this, 'export_data' ),
		);
		return $exporters;
	}

	/**
	 * Add options to the WordPress personal data eraser
	 *
	 * @since 3.02
	 * @param array $erasers
	 * @return array
	 */
	public function register_data_eraser( $erasers ) {
		$erasers['formidable-eraser'] = array(
			'eraser_friendly_name' => __( 'Formidable Forms entries' ),
			'callback'             => array( $this, 'erase_data' ),
		);

		return $erasers;
	}

	public function export_data( $email, $page = 1 ) {
		$this->page = absint( $page );

		$data_to_export = array(
			'data' => array(),
			'done' => true,
		);

		$entries = $this->get_user_entries( $email );
		if ( empty( $entries ) ) {
			return $data_to_export;
		}

		foreach ( (array) $entries as $entry ) {
			$data_to_export['data'][] = array(
				'group_id'    => 'formidable',
				'group_label' => __( 'Form Submissions', 'formidable' ),
				'item_id'     => esc_attr( 'entry-' . $entry ),
				'data'        => $this->prepare_entry_data( $entry ),
			);
		}

		$data_to_export['done'] = count( $entries ) < $this->limit;
		return $data_to_export;
	}

	/**
	 * @return array
	 */
	public function erase_data( $email, $page = 1 ) {
		$data_removed = array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);

		if ( empty( $email ) ) {
			return $data_removed;
		}

		$this->page = absint( $page );
		$entries = $this->get_user_entries( $email );
		if ( empty( $entries ) ) {
			return $data_removed;
		}

		// TODO: Add an option to anonymize the entries with wp_privacy_anonymize_data( 'email', 'e@e.com' );

		foreach ( (array) $entries as $entry ) {
			$removed = FrmEntry::destroy( $entry );

			if ( $removed ) {
				$data_removed['items_removed'] = true;
			} else {
				$data_removed['items_retained'] = true;
			}
		}

		$data_removed['done'] = count( $entries ) < $this->limit;

		return $data_removed;
	}

	/**
	 * Get all entries that were submitted by this user while logged in,
	 * or with a field value that matches the email address
	 *
	 * @param string $email
	 * @return array of entry ids
	 */
	private function get_user_entries( $email ) {
		$query_args = array(
			'order_by' => 'item_id ASC',
			'limit'    => $this->get_current_page(),
		);

		$user = get_user_by( 'email', $email );
		$entries_by_email = FrmDb::get_col( 'frm_item_metas', array( 'meta_value' => $email ), 'item_id', $query_args );

		if ( empty( $user ) ) {
			// no matching user, so return the entry ids we have
			return $entries_by_email;
		}

		$query_args['order_by'] = 'id ASC';

		$entries_by_user = FrmDb::get_col( 'frm_items', array( 'user_id' => $user->ID ), 'id', $query_args );

		$entry_ids = array_merge( (array) $entries_by_user, (array) $entries_by_email );
		$entry_ids = array_unique( array_filter( $entry_ids ) );
		return $entry_ids;
	}

	private function get_current_page() {
		$start = ( $this->page - 1 ) * $this->limit;
		return FrmDb::esc_limit( $start . ',' . $this->limit );
	}

	/**
	 * @param int $entry
	 * @return array
	 */
	private function prepare_entry_data( $entry ) {
		$entry = FrmEntry::getOne( $entry, true );

		$entry_data = array();
		foreach ( $entry->metas as $field_id => $meta ) {
			$field = FrmField::getOne( $field_id );

			$entry_data[] = array(
				'name'  => $field->name,
				'value' => FrmFieldsHelper::get_unfiltered_display_value(
					array(
						'field' => $field,
						'value' => $meta,
					)
				),
			);
		}

		return $entry_data;
	}
}
