<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 4.05
 */
class FrmInbox extends FrmFormApi {

	protected $cache_key;

	private $option = 'frm_inbox';

	private $messages = false;

	public function __construct( $for_parent = null ) {
		$this->set_cache_key();
		$this->set_messages();
	}

	/**
	 * @since 4.05
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_inbox_cache';
	}

	/**
	 * @since 4.05
	 */
	protected function api_url() {
		return 'https://formidableforms.com/wp-json/inbox/v1/message/';
	}

	/**
	 * @since 4.05
	 */
	public function get_messages( $filter = false ) {
		$messages = $this->messages;
		if ( $filter === 'filter' ) {
			$this->filter_messages( $messages );
		}
		return $messages;
	}

	/**
	 * @since 4.05
	 */
	public function set_messages() {
		$this->messages = get_option( $this->option );
		if ( empty( $this->messages ) ) {
			$this->messages = array();
		}

		$this->add_api_messages();

		/**
		 * Messages are in an array.
		 */
		$this->messages = apply_filters( 'frm_inbox', $this->messages );
	}

	/**
	 * @since 4.05
	 */
	private function add_api_messages() {
		$api = $this->get_api_info();
		if ( empty( $api ) ) {
			return;
		}

		foreach ( $api as $message ) {
			$this->add_message( $message );
		}
	}

	/**
	 * @param array $message
	 */
	public function add_message( $message ) {
		if ( isset( $this->messages[ $message['key'] ] ) && ! isset( $message['force'] ) ) {
			// Don't replace messages unless required.
			return;
		}

		if ( isset( $this->messages[ $message['key'] ] ) ) {
			// Move up and mark as new.
			unset( $this->messages[ $message['key'] ] );
		}

		$this->fill_message( $message );
		$this->messages[ $message['key'] ] = $message;

		$this->update_list();

		$this->clean_messages();
	}

	private function fill_message( &$message ) {
		$defaults = array(
			'time'    => time(),
			'message' => '',
			'subject' => '',
			'icon'    => 'frm_tooltip_icon',
			'cta'     => '',
			'expires' => false,
			'who'     => 'all', // use 'free', 'personal', 'business', 'elite', 'grandfathered'
			'type'    => '',
		);

		$message = array_merge( $defaults, $message );

		$message['created'] = $message['time'];
		unset( $message['time'] );
	}

	private function clean_messages() {
		$removed  = false;
		foreach ( $this->messages as $t => $message ) {
			$read    = isset( $message['read'] ) && ! empty( $message['read'] ) && isset( $message['read'][ get_current_user_id() ] ) && $message['read'][ get_current_user_id() ] < strtotime( '-1 month' );
			$dismissed = isset( $message['dismissed'] ) && ! empty( $message['dismissed'] ) && isset( $message['dismissed'][ get_current_user_id() ] ) && $message['dismissed'][ get_current_user_id() ] < strtotime( '-1 week' );
			$expired = $this->is_expired( $message );
			if ( $read || $expired || $dismissed ) {
				unset( $this->messages[ $t ] );
				$removed = true;
			}
		}

		if ( $removed ) {
			$this->update_list();
		}
	}

	private function filter_messages( &$messages ) {
		$user_id = get_current_user_id();
		foreach ( $messages as $k => $message ) {
			$dismissed = isset( $message['dismissed'] ) && isset( $message['dismissed'][ $user_id ] );
			if ( $this->is_expired( $message ) || $dismissed ) {
				unset( $messages[ $k ] );
			} elseif ( ! $this->is_for_user( $message ) ) {
				unset( $messages[ $k ] );
			}
		}
		$messages = apply_filters( 'frm_filter_inbox', $messages );
	}

	private function is_expired( $message ) {
		return isset( $message['expires'] ) && ! empty( $message['expires'] ) && $message['expires'] < time();
	}

	/**
	 * Show different messages for different accounts.
	 */
	private function is_for_user( $message ) {
		if ( ! isset( $message['who'] ) || $message['who'] === 'all' ) {
			return true;
		}

		return in_array( $this->get_user_type(), (array) $message['who'] );
	}

	private function get_user_type() {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			return 'free';
		}

		return FrmAddonsController::license_type();
	}

	/**
	 * @param string $key
	 */
	public function mark_read( $key ) {
		if ( ! isset( $this->messages[ $key ] ) ) {
			return;
		}

		if ( ! isset( $this->messages[ $key ]['read'] ) ) {
			$this->messages[ $key ]['read'] = array();
		}
		$this->messages[ $key ]['read'][ get_current_user_id() ] = time();

		$this->update_list();
	}

	/**
	 * @param string $key
	 *
	 * @since 4.05.02
	 */
	public function mark_unread( $key ) {
		$is_read = isset( $this->messages[ $key ] ) && isset( $this->messages[ $key ]['read'] ) && isset( $this->messages[ $key ]['read'][ get_current_user_id() ] );
		if ( $is_read ) {
			unset( $this->messages[ $key ]['read'][ get_current_user_id() ] );
			$this->update_list();
		}
	}

	/**
	 * @param string $key
	 */
	public function dismiss( $key ) {
		if ( $key === 'all' ) {
			$this->dismiss_all();
			return;
		}

		if ( ! isset( $this->messages[ $key ] ) ) {
			return;
		}

		if ( ! isset( $this->messages[ $key ]['dismissed'] ) ) {
			$this->messages[ $key ]['dismissed'] = array();
		}
		$this->messages[ $key ]['dismissed'][ get_current_user_id() ] = time();

		$this->update_list();
	}

	/**
	 * @since 4.06
	 */
	private function dismiss_all() {
		$user_id = get_current_user_id();
		foreach ( $this->messages as $key => $message ) {
			if ( ! isset( $message['dismissed'] ) ) {
				$this->messages[ $key ]['dismissed'] = array();
			}

			if ( ! isset( $message['dismissed'][ $user_id ] ) ) {
				$this->messages[ $key ]['dismissed'][ $user_id ] = time();
			}
		}
		$this->update_list();
	}

	public function unread() {
		$messages = $this->get_messages( 'filter' );
		$user_id  = get_current_user_id();
		foreach ( $messages as $t => $message ) {
			if ( isset( $message['read'] ) && isset( $message['read'][ $user_id ] ) ) {
				unset( $messages[ $t ] );
			}
		}
		return $messages;
	}

	public function unread_html() {
		$html = '';
		$count = count( $this->unread() );
		if ( $count ) {
			$html = ' <span class="update-plugins frm_inbox_count"><span class="plugin-count">' . absint( $count ) . '</span></span>';

			/**
			 * @since 4.06.01
			 */
			$html = apply_filters( 'frm_inbox_badge', $html );
		}
		return $html;
	}

	/**
	 * @since 4.05.02
	 */
	public function remove( $key ) {
		if ( isset( $this->messages[ $key ] ) ) {
			unset( $this->messages[ $key ] );
			$this->update_list();
		}
	}

	private function update_list() {
		update_option( $this->option, $this->messages, 'no' );
	}
}
