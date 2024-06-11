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

	private static $messages = false;

	/**
	 * @var array
	 */
	private static $banner_messages;

	public function __construct( $for_parent = null ) {
		$this->set_cache_key();

		if ( false === self::$messages ) {
			$this->set_messages();
		}
	}

	/**
	 * @since 4.05
	 *
	 * @return void
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_inbox_cache';
	}

	/**
	 * @since 4.05
	 *
	 * @return string
	 */
	protected function api_url() {
		return 'https://formidableforms.com/wp-json/inbox/v1/message/';
	}

	/**
	 * @since 4.05
	 *
	 * @param array|false $filter
	 */
	public function get_messages( $filter = false ) {
		$messages = self::$messages;
		if ( $filter === 'filter' ) {
			$this->filter_messages( $messages );
		}
		return $messages;
	}

	/**
	 * @since 4.05
	 *
	 * @return void
	 */
	public function set_messages() {
		self::$messages = get_option( $this->option );
		if ( ! is_array( self::$messages ) ) {
			self::$messages = array();
		}

		$this->add_api_messages();

		/**
		 * Messages are in an array.
		 */
		self::$messages = apply_filters( 'frm_inbox', self::$messages );
	}

	/**
	 * @since 4.05
	 *
	 * @return void
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
	 * @param array|string $message
	 *
	 * @return void
	 */
	public function add_message( $message ) {
		if ( ! is_array( $message ) || ! isset( $message['key'] ) ) {
			// if the API response is invalid, $message may not be an array.
			// if there are no messages from the API, it is returning a "No Entries Found" item with no key, so check for a key as well.
			return;
		}

		if ( isset( self::$messages[ $message['key'] ] ) && ! isset( $message['force'] ) ) {
			// Don't replace messages unless required.
			return;
		}

		if ( $this->is_expired( $message ) ) {
			return;
		}

		if ( isset( self::$messages[ $message['key'] ] ) ) {
			// Move up and mark as new.
			unset( self::$messages[ $message['key'] ] );
		}

		$this->fill_message( $message );
		self::$messages[ $message['key'] ] = $message;

		$this->update_list();

		$this->clean_messages();
	}

	/**
	 * @param array $message
	 *
	 * @return void
	 */
	private function fill_message( &$message ) {
		$defaults = array(
			'time'    => time(),
			'message' => '',
			'subject' => '',
			'icon'    => 'frm_tooltip_icon',
			'cta'     => '',
			'expires' => false,
			// Use 'free', 'personal', 'business', 'elite', 'grandfathered'.
			'who'     => 'all',
			'type'    => '',
		);

		$message = array_merge( $defaults, $message );

		$message['created'] = $message['time'];
		unset( $message['time'] );
	}

	/**
	 * @return void
	 */
	private function clean_messages() {
		$removed = false;
		foreach ( self::$messages as $t => $message ) {
			$read      = ! empty( $message['read'] ) && isset( $message['read'][ get_current_user_id() ] ) && $message['read'][ get_current_user_id() ] < strtotime( '-1 month' );
			$dismissed = ! empty( $message['dismissed'] ) && isset( $message['dismissed'][ get_current_user_id() ] ) && $message['dismissed'][ get_current_user_id() ] < strtotime( '-1 week' );
			$expired   = $this->is_expired( $message );
			if ( $read || $expired || $dismissed ) {
				unset( self::$messages[ $t ] );
				$removed = true;
			}
		}

		if ( $removed ) {
			$this->update_list();
		}
	}

	/**
	 * @param array  $messages
	 * @param string $type
	 * @return void
	 */
	public function filter_messages( &$messages, $type = 'unread' ) {
		$user_id = get_current_user_id();
		foreach ( $messages as $k => $message ) {
			$dismissed = isset( $message['dismissed'] ) && isset( $message['dismissed'][ $user_id ] );
			if ( empty( $k ) || $this->is_expired( $message ) || ( $type === 'dismissed' ) !== $dismissed ) {
				unset( $messages[ $k ] );
			} elseif ( ! $this->is_for_user( $message ) ) {
				unset( $messages[ $k ] );
			}
		}
		$messages = apply_filters( 'frm_filter_inbox', $messages );
	}

	/**
	 * @param array $message
	 *
	 * @return bool
	 */
	private function is_expired( $message ) {
		return ! empty( $message['expires'] ) && $message['expires'] < time();
	}

	/**
	 * Show different messages for different accounts.
	 *
	 * @param array $message
	 * @return bool
	 */
	private function is_for_user( $message ) {
		if ( ! isset( $message['who'] ) || $message['who'] === 'all' ) {
			return true;
		}
		$who = (array) $message['who'];
		if ( in_array( 'all', $who, true ) || in_array( 'everyone', $who, true ) ) {
			return true;
		}
		if ( in_array( $this->get_user_type(), $who, true ) ) {
			return true;
		}
		/**
		 * Allow for other special inbox cases in other add-ons.
		 *
		 * @since 6.8.1
		 *
		 * @param bool  $is_for_user
		 * @param array $who
		 * @param array $message
		 */
		return (bool) apply_filters( 'frm_inbox_message_is_for_user', false, $who, $message );
	}

	private function get_user_type() {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			return 'free';
		}

		return FrmAddonsController::license_type();
	}

	/**
	 * @param string $key
	 *
	 * @return void
	 */
	public function mark_read( $key ) {
		if ( ! $key || ! isset( self::$messages[ $key ] ) ) {
			return;
		}

		if ( ! isset( self::$messages[ $key ]['read'] ) ) {
			self::$messages[ $key ]['read'] = array();
		}
		self::$messages[ $key ]['read'][ get_current_user_id() ] = time();

		$this->update_list();
	}

	/**
	 * @since 4.05.02
	 *
	 * @param string $key
	 * @return void
	 */
	public function mark_unread( $key ) {
		$is_read = isset( self::$messages[ $key ] ) && isset( self::$messages[ $key ]['read'] ) && isset( self::$messages[ $key ]['read'][ get_current_user_id() ] );
		if ( $is_read ) {
			unset( self::$messages[ $key ]['read'][ get_current_user_id() ] );
			$this->update_list();
		}
	}

	/**
	 * @param string $key
	 *
	 * @return void
	 */
	public function dismiss( $key ) {
		if ( $key === 'all' ) {
			$this->dismiss_all();
			return;
		}

		if ( ! isset( self::$messages[ $key ] ) ) {
			return;
		}

		if ( ! isset( self::$messages[ $key ]['dismissed'] ) ) {
			self::$messages[ $key ]['dismissed'] = array();
		}
		self::$messages[ $key ]['dismissed'][ get_current_user_id() ] = time();

		$this->update_list();
	}

	/**
	 * @since 4.06
	 *
	 * @return void
	 */
	private function dismiss_all() {
		$user_id = get_current_user_id();
		foreach ( self::$messages as $key => $message ) {
			if ( ! isset( $message['dismissed'] ) ) {
				self::$messages[ $key ]['dismissed'] = array();
			}

			if ( ! isset( $message['dismissed'][ $user_id ] ) ) {
				self::$messages[ $key ]['dismissed'][ $user_id ] = time();
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

	/**
	 * @since 6.8.4 The $filtered parameter was added.
	 *
	 * @param bool $filtered
	 * @return string
	 */
	public function unread_html( $filtered = true ) {
		$count = count( $this->unread() );
		if ( ! $count ) {
			return '';
		}

		$html = ' <span class="update-plugins frm_inbox_count"><span class="plugin-count">' . absint( $count ) . '</span></span>';

		if ( ! $filtered ) {
			return $html;
		}

		/**
		 * @since 4.06.01
		 *
		 * @param string $html
		 */
		return (string) apply_filters( 'frm_inbox_badge', $html );
	}

	/**
	 * @since 4.05.02
	 *
	 * @param string $key
	 * @return void
	 */
	public function remove( $key ) {
		if ( isset( self::$messages[ $key ] ) ) {
			unset( self::$messages[ $key ] );
			$this->update_list();
		}
	}

	/**
	 * @return void
	 */
	private function update_list() {
		update_option( $this->option, self::$messages, 'no' );
	}

	/**
	 * Show a banner message if one is available.
	 *
	 * @return bool True if a banner is available and shown.
	 */
	public static function maybe_show_banner() {
		if ( empty( self::$banner_messages ) ) {
			return false;
		}
		$message = end( self::$banner_messages );
		$cta     = self::get_prepared_banner_cta( $message['cta'] );

		require FrmAppHelper::plugin_path() . '/classes/views/inbox/banner.php';
		return true;
	}

	/**
	 * Make sure that the CTA uses utm_medium=banner.
	 *
	 * @since 6.8.4
	 *
	 * @param string $cta
	 * @return string
	 */
	private static function get_prepared_banner_cta( $cta ) {
		$cta = str_replace( 'button-secondary', 'button-primary', $cta );
		return preg_replace_callback(
			'/href=("|\')(.*?)("|\')/',
			/**
			 * Replace a single href attribute in the CTA.
			 *
			 * @param array $matches The regex results for a single match.
			 * @return string
			 */
			function ( $matches ) {
				$url   = $matches[2];
				$parts = parse_url( $url );

				if ( '#' === $url ) {
					return 'href="#"';
				}

				$query = array();
				if ( isset( $parts['query'] ) ) {
					parse_str( $parts['query'], $query );
				}
				$query['utm_medium'] = 'banner';
				$parts['query']      = http_build_query( $query );
				return 'href="' . $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '?' . $parts['query'] . '"';
			},
			$cta
		);
	}

	/**
	 * @return void
	 */
	public static function maybe_disable_screen_options() {
		self::$banner_messages = self::get_banner_messages();
		if ( self::$banner_messages ) {
			// disable screen options tab when displaying banner messages because it gets in the way of the banner.
			add_filter( 'screen_options_show_screen', '__return_false' );
		}
	}

	/**
	 * Get all message with a "banner" mesage key defined.
	 *
	 * @return array
	 */
	private static function get_banner_messages() {
		return self::get_messages_with_key( 'banner' );
	}

	/**
	 * Get all messages with a "slidein" message key defined.
	 *
	 * @since 6.8.4
	 *
	 * @return array
	 */
	private static function get_slidein_messages() {
		return self::get_messages_with_key( 'slidein' );
	}

	/**
	 * Get all messages with a $key message key defined.
	 *
	 * @since 6.8.4
	 *
	 * @param string $key The key we are checking for (ie. banner or slidein).
	 * @return array
	 */
	private static function get_messages_with_key( $key ) {
		$inbox = new self();
		return array_filter(
			$inbox->get_messages( 'filter' ),
			function ( $message ) use ( $key ) {
				return ! empty( $message[ $key ] );
			}
		);
	}

	/**
	 * Check if there is at least one slidein message.
	 *
	 * @since 6.8.4
	 *
	 * @return bool
	 */
	public static function has_a_slidein_message() {
		return (bool) self::get_slidein_messages();
	}

	/**
	 * Get the array used for frmGlobal.inboxSlideIn
	 *
	 * @since 6.8.4
	 *
	 * @return array|false
	 */
	public static function get_inbox_slide_in_value_for_js() {
		$messages = self::get_slidein_messages();
		if ( ! $messages ) {
			return false;
		}

		$message = reset( $messages );

		/**
		 * Extend the keys in the global JS object.
		 * This is used in Pro to include images.
		 *
		 * @since 6.8.4
		 *
		 * @param array $keys
		 */
		$keys_to_return = apply_filters(
			'frm_inbox_slidein_js_vars',
			array( 'key', 'slidein', 'subject', 'cta' )
		);

		return array_reduce(
			$keys_to_return,
			function ( $total, $key ) use ( $message ) {
				$total[ $key ] = isset( $message[ $key ] ) ? $message[ $key ] : '';
				return $total;
			},
			array()
		);
	}
}
