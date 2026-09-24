<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteDb {

	/**
	 * @var int
	 */
	public $db_version = 6;

	/**
	 * @var string
	 */
	public $db_opt_name = 'frm_trans_db_version';

	/**
	 * @var string
	 */
	public $table_name = '';

	/**
	 * @var string
	 */
	public $singular = '';

	/**
	 * @param mixed $old_db_version
	 *
	 * @return void
	 */
	public function upgrade( $old_db_version = false ) {
		if ( ! $old_db_version ) {
			$old_db_version = get_option( $this->db_opt_name );
		}

		if ( $this->db_version === (int) $old_db_version ) {
			return;
		}

		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$frm_db          = new FrmMigrate();
		$charset_collate = $frm_db->collation();

		/* Create/Upgrade Payments Table */
		$sql = "CREATE TABLE {$wpdb->prefix}frm_payments (
				id bigint(20) NOT NULL auto_increment,
				meta_value longtext default NULL,
				receipt_id varchar(100) default NULL,
				invoice_id varchar(100) default NULL,
				sub_id varchar(100) default NULL,
				item_id bigint(20) NOT NULL,
				action_id bigint(20) NOT NULL,
				amount decimal(12,2) NOT NULL default '0.00',
				status varchar(100) default NULL,
				begin_date date NOT NULL,
				expire_date date default NULL,
				paysys varchar(100) default NULL,
				created_at datetime NOT NULL,
				test TINYINT(1) NULL DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY item_id (item_id)
			) {$charset_collate};";

		dbDelta( $sql );

		/* Create/Upgrade Subscriptions Table */
		$sql = "CREATE TABLE {$wpdb->prefix}frm_subscriptions (
				id bigint(20) NOT NULL auto_increment,
				sub_id varchar(100) default NULL,
				meta_value longtext default NULL,
				item_id bigint(20) NOT NULL,
				action_id bigint(20) NOT NULL,
				amount decimal(12,2) NOT NULL default '0.00',
				first_amount decimal(12,2) NOT NULL default '0.00',
				interval_count bigint(20) default 1,
				time_interval varchar(100) default NULL,
				fail_count bigint(20) default 0,
				end_count bigint(20) default NULL,
				next_bill_date date default NULL,
				status varchar(100) default NULL,
				paysys varchar(100) default NULL,
				created_at datetime NOT NULL,
				test TINYINT(1) NULL DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY item_id (item_id)
			) {$charset_collate};";

		dbDelta( $sql );

		// SAVE DB VERSION.
		update_option( $this->db_opt_name, $this->db_version );

		$this->migrate_data( $old_db_version );
	}

	/**
	 * @param array $values
	 *
	 * @return int
	 */
	public function create( $values ) {
		global $wpdb;

		$values['action'] = 'create';
		$new_values       = array();
		$this->fill_values( $values, $new_values );

		$wpdb->insert( $wpdb->prefix . $this->table_name, $new_values );

		return $wpdb->insert_id;
	}

	/**
	 * @param int|string $id
	 * @param array      $values
	 *
	 * @return false|int
	 */
	public function update( $id, $values ) {
		global $wpdb;

		$values['action'] = 'update';
		$new_values       = array();
		$this->fill_values( $values, $new_values );

		return $wpdb->update( $wpdb->prefix . $this->table_name, $new_values, compact( 'id' ) );
	}

	/**
	 * @param int|string $id
	 *
	 * @return bool|int
	 */
	public function destroy( $id ) {
		FrmAppHelper::permission_check( 'administrator' );

		global $wpdb;
		$id = absint( $id );

		/**
		 * @param int $id
		 */
		do_action( 'frm_before_destroy_' . $this->singular, $id );

		return $wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE id=%d', $wpdb->prefix . $this->table_name, $id ) );
	}

	/**
	 * @param int|string $id
	 *
	 * @return array|object|null
	 */
	public function get_one( $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE id=%d',
				$wpdb->prefix . $this->table_name,
				$id
			)
		);
	}

	/**
	 * @param int|string $id
	 * @param string     $field
	 *
	 * @return object|null
	 */
	public function get_one_by( $id, $field = 'receipt_id' ) {
		if ( ! in_array( $field, array( 'receipt_id', 'sub_id', 'item_id' ), true ) ) {
			_doing_it_wrong( __METHOD__, 'Items can only be retrieved by receipt id or sub id.', '6.5' );
			return null;
		}

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE %i = %s ORDER BY created_at DESC',
				$wpdb->prefix . $this->table_name,
				$field,
				$id
			)
		);
	}

	/**
	 * @param string $value
	 * @param string $field
	 *
	 * @return array
	 */
	public function get_all_by( $value, $field = 'item_id' ) {
		if ( ! FrmTransLiteAppHelper::payments_table_exists() ) {
			// If no migrations have run yet return nothing.
			return array();
		}

		$field = sanitize_text_field( $field );

		if ( ! in_array( $field, array( 'receipt_id', 'sub_id', 'item_id' ), true ) ) {
			_doing_it_wrong( __METHOD__, 'Items can only be retrieved by item id or sub id.', '6.5' );
			return array();
		}

		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE %i = %s ORDER BY created_at DESC',
				$wpdb->prefix . $this->table_name,
				$field,
				$value
			)
		);
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 */
	public function get_all_for_user( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT
					*,
					e.id as entry_id,
					p.id as id
				FROM %i p
				LEFT JOIN %i e ON e.id = p.item_id
				WHERE e.user_id = %d
				ORDER BY p.created_at DESC',
				$wpdb->prefix . $this->table_name,
				$wpdb->prefix . 'frm_items',
				$user_id
			)
		);
	}

	/**
	 * @param int|string $id
	 *
	 * @return array
	 */
	public function get_all_for_entry( $id ) {
		return $this->get_all_by( $id, 'item_id' );
	}

	/**
	 * @return string
	 */
	public function get_count() {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $wpdb->prefix . $this->table_name ) );
	}

	/**
	 * @return array
	 */
	public function get_defaults() {
		return array();
	}

	/**
	 * @param array $values
	 * @param array $new_values
	 *
	 * @return void
	 */
	private function fill_values( $values, &$new_values ) {
		foreach ( $this->get_defaults() as $val => $default ) {
			if ( isset( $values[ $val ] ) ) {
				if ( $default['sanitize'] === 'float' ) {
					$new_values[ $val ] = (float) $values[ $val ];
				} elseif ( ! empty( $default['sanitize'] ) ) {
					$new_values[ $val ] = call_user_func( $default['sanitize'], $values[ $val ] );
				}
			} elseif ( $values['action'] === 'create' ) {
				$new_values[ $val ] = $default['default'];
			}
		}
	}

	/**
	 * Run database migrations starting from a previous DB version.
	 *
	 * @param int $old_db_version Previous database version.
	 *
	 * @return void
	 */
	private function migrate_data( $old_db_version ) {
		$migrations = array( 4 );

		foreach ( $migrations as $migration ) {
			if ( $this->db_version < $migration || $old_db_version >= $migration ) {
				continue;
			}

			$function_name = 'migrate_to_' . $migration;
			$this->$function_name();
		}
	}

	/**
	 * This migration checks for PayPal payments and sets a status value based on the completed status.
	 *
	 * @return void
	 */
	private function migrate_to_4() {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM %i LIKE %s', $wpdb->prefix . 'frm_payments', 'completed' ) );

		if ( ! $result ) {
			return;
		}

		$payments = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM %i WHERE completed is NOT NULL AND status is NULL', $wpdb->prefix . 'frm_payments' )
		);

		foreach ( $payments as $payment ) {
			$status = $payment->completed ? 'complete' : 'failed';
			$wpdb->update( $wpdb->prefix . 'frm_payments', compact( 'status' ), array( 'id' => $payment->id ) );
		}
	}
}
