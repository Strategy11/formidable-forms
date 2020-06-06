<?php
/**
 * @since 4.05
 */
class FrmInbox extends FrmFormApi {

	protected $cache_key;

	private $option = 'frm_inbox';

	private $messages = false;

	public function __construct( $for_parent = null ) {
		$this->set_cache_key();
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
	public function get_messages() {
		if ( $this->messages === false ) {
			$this->set_messages();
		}
		return $this->messages;
	}

	/**
	 * @since 4.05
	 */
	public function set_messages( $skip = '' ) {
		$this->messages = get_option( $this->option );
		if ( empty( $this->messages ) ) {
			$this->messages = array();
		}

		if ( $skip !== 'skip' ) {
			$this->add_api_messages();
		}

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

	public function clean_messages() {
		$this->set_messages();

		$removed  = false;
		foreach ( $this->messages as $t => $message ) {
			$read    = isset( $message['read'] ) && ! empty( $message['read'] );
			$expired = isset( $message['expires'] ) && ! empty( $message['expires'] ) && $message['expires'] < time();
			if ( $read && $expired ) {
				unset( $this->messages[ $t ] );
				$removed = true;
			}
		}

		if ( $removed ) {
			$this->update_list();
		}
	}

	/**
	 * @param array $message
	 */
	public function add_message( $message ) {
		$this->set_messages( 'skip' );
		$time = isset( $message['time'] ) ? $message['time'] : time();

		if ( isset( $this->messages[ $message['key'] ] ) && ! isset( $message['force'] ) ) {
			// Don't replace messages unless required.
			return;
		}

		$this->messages[ $message['key'] ] = array(
			'created' => $time,
			'message' => $message['message'],
			'subject' => $message['subject'],
			'icon'    => isset( $message['icon'] ) ? $message['icon'] : 'frm_tooltip_icon',
			'cta'     => $message['cta'],
			'expires' => isset( $message['expires'] ) ? $message['expires'] : false,
		);

		$this->update_list();
	}

	/**
	 * @param string $timestamp in time format.
	 */
	public function mark_read( $key ) {
		$this->set_messages();
		if ( ! isset( $this->messages[ $key ] ) ) {
			return;
		}

		if ( ! isset( $this->messages[ $key ]['read'] ) ) {
			$this->messages[ $key ]['read'] = array();
		}
		$this->messages[ $key ]['read'][ get_current_user_id() ] = time();

		$this->update_list();
	}

	public function unread() {
		$messages = $this->get_messages();
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
			$html = '<span class="update-plugins frm_inbox_count"><span class="plugin-count">' . absint( $count ) . '</span></span>';
		}
		return $html;
	}

	private function update_list() {
		update_option( $this->option, $this->messages, 'no' );
	}
}
