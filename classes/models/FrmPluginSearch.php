<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPluginSearch {

	/**
	 * PSH slug name.
	 *
	 * @var string
	 */
	public static $slug = 'frm-plugin-search';

	/**
	 * Option name that holds dismissed suggestions.
	 *
	 * @var string
	 */
	protected static $dismissed_opt = 'frm_dismissed_hints';

	public function __construct() {
		add_action( 'current_screen', array( $this, 'start' ) );
	}

	/**
	 * Add actions and filters only if this is the plugin installation screen and it's the first page.
	 *
	 * @param object $screen WP Screen object.
	 *
	 * @since 4.12
	 */
	public function start( $screen ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'plugin-install' === $screen->base && ( ! isset( $_GET['paged'] ) || 1 === intval( $_GET['paged'] ) ) ) {
			add_filter( 'plugins_api_result', array( $this, 'inject_suggestion' ), 10, 3 );
			add_filter( 'self_admin_url', array( $this, 'plugin_details' ) );
			add_filter( 'plugin_install_action_links', array( $this, 'insert_related_links' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_plugins_search_script' ) );
			$this->maybe_dismiss();
		}
	}

	/**
	 * Intercept the plugins API response and add in an appropriate card.
	 *
	 * @param object $result Plugin search results.
	 * @param string $action unused.
	 * @param object $args Search args.
	 */
	public function inject_suggestion( $result, $action, $args ) {
		// Looks like a search query; it's matching time.
		if ( empty( $args->search ) ) {
			return $result;
		}

		$addon_list = $this->get_addons();

		// Lowercase, trim, remove punctuation/special chars, decode url, remove 'formidable'.
		$normalized_term = $this->search_to_array( $args->search );
		if ( empty( $normalized_term ) ) {
			// Don't add anything extra.
			return $result;
		}

		$matching_addon = $this->matching_addon( $addon_list, $normalized_term );

		if ( empty( $matching_addon ) || ! $this->should_display_hint( $matching_addon ) ) {
			return $result;
		}

		$inject    = (array) $this->get_plugin_data();
		$overrides = array(
			'plugin-search'       => true, // Helps to determine if that an injected card.
			'name'                => sprintf(
				/* translators: Formidable addon name */
				esc_html_x( 'Formidable %s', 'Formidable Addon Name', 'formidable' ),
				$addon_list[ $matching_addon ]['name']
			),
			'addon'               => $addon_list[ $matching_addon ]['slug'],
			'short_description'   => $addon_list[ $matching_addon ]['excerpt'],
			'slug'                => self::$slug,
			'version'             => $addon_list[ $matching_addon ]['version'],
		);

		// Splice in the base addon data.
		$inject = array_merge( $inject, $addon_list[ $matching_addon ], $overrides );

		// Add it to the top of the list.
		array_unshift( $result->plugins, $inject );

		return $result;
	}

	/**
	 * Search for any addons that match the searched terms.
	 *
	 * @since 4.12
	 *
	 * @return int
	 */
	private function matching_addon( $addon_list, $normalized_term ) {
		$matching_addon = null;

		// Try to match a passed search term with addon's search terms.
		foreach ( $addon_list as $addon_id => $addon_opts ) {
			if ( ! is_array( $addon_opts ) || empty( $addon_opts['excerpt'] ) ) {
				continue;
			}

			/*
			* Does the site's current plan support the feature?
			*/
			$is_supported_by_plan = ! empty( $addon_opts['url'] );

			if ( ! isset( $addon_opts['search_terms'] ) ) {
				$addon_opts['search_terms'] = '';
			}

			$addon_terms = $this->search_to_array( $addon_opts['search_terms'] . ', ' . $addon_opts['name'] );

			$matches = ! empty( array_intersect( $addon_terms, $normalized_term ) );

			if ( $matches && $is_supported_by_plan ) {
				$matching_addon = $addon_id;
				break;
			}
		}

		return $matching_addon;
	}

	/**
	 * @since 4.12
	 *
	 * @return array
	 */
	private function get_addons() {
		$api    = new FrmFormApi();
		return $api->get_api_info();
	}

	/**
	 * Get the plugin repo's data to populate the fields with.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	private function get_plugin_data() {
		$data = get_transient( 'formidable_plugin_data' );

		if ( false !== $data && ! is_wp_error( $data ) ) {
			return $data;
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		$data = plugins_api(
			'plugin_information',
			array(
				'slug'   => 'formidable',
				'is_ssl' => is_ssl(),
				'fields' => array(
					'banners'         => true,
					'reviews'         => true,
					'active_installs' => true,
					'versions'        => false,
					'sections'        => false,
				),
			)
		);
		set_transient( 'formidable_plugin_data', $data, DAY_IN_SECONDS );

		return $data;
	}

	/**
	 * Modify URL used to fetch to plugin information so it pulls Formidable plugin page.
	 *
	 * @param string $url URL to load in dialog pulling the plugin page from wporg.
	 *
	 * @since 4.12
	 *
	 * @return string The URL with 'formidable' instead of 'frm-plugin-search'.
	 */
	public function plugin_details( $url ) {
		return false !== stripos( $url, 'tab=plugin-information&amp;plugin=' . self::$slug )
			? 'plugin-install.php?tab=plugin-information&amp;plugin=formidable&amp;TB_iframe=true&amp;width=600&amp;height=550'
			: $url;
	}

	/**
	 * @since 4.12
	 */
	private function maybe_dismiss() {
		$addon = FrmAppHelper::get_param( 'frm-dismiss', '', 'get', 'absint' );
		if ( ! empty( $addon ) ) {
			$this->add_to_dismissed_hints( $addon );
		}
	}

	/**
	 * Returns a list of previously dismissed hints.
	 *
	 * @since 4.12
	 *
	 * @return array List of dismissed hints.
	 */
	protected function get_dismissed_hints() {
		$dismissed_hints = get_option( self::$dismissed_opt );
		return ! empty( $dismissed_hints ) && is_array( $dismissed_hints ) ? $dismissed_hints : array();
	}

	/**
	 * Save the hint in the list of dismissed hints.
	 *
	 * @since 4.12
	 *
	 * @param string $hint The hint id, which is a Formidable addon slug.
	 *
	 * @return bool Whether the card was added to the list and hence dismissed.
	 */
	protected function add_to_dismissed_hints( $hint ) {
		$hints = array_merge( $this->get_dismissed_hints(), array( $hint ) );
		return update_option( self::$dismissed_opt, $hints, 'no' );
	}

	/**
	 * Checks that the addon slug passed should be displayed.
	 *
	 * A feature hint will be displayed if it has not been dismissed before or if 2 or fewer other hints have been dismissed.
	 *
	 * @since 7.2.1
	 *
	 * @param string $hint The hint id, which is a Formidable addon slug.
	 *
	 * @return bool True if $hint should be displayed.
	 */
	protected function should_display_hint( $hint ) {
		$dismissed_hints = $this->get_dismissed_hints();

		// If more than 3 hints have been dismissed, then show no more.
		if ( 3 < count( $dismissed_hints ) ) {
			return false;
		}

		return ! in_array( $hint, $dismissed_hints, true );
	}

	/**
	 * Take a raw search query and return something a bit more standardized and
	 * easy to work with.
	 *
	 * @param  string $term The raw search term.
	 * @return string A simplified/sanitized version.
	 */
	private function sanitize_search_term( $term ) {
		$term = strtolower( urldecode( $term ) );

		// remove non-alpha/space chars.
		$term = preg_replace( '/[^a-z ]/', '', $term );

		// remove strings that don't help matches.
		$term = trim( str_replace( array( 'formidable', 'free', 'wordpress', 'wp ', 'plugin' ), '', $term ) );

		return $term;
	}

	/**
	 * @since 4.12
	 *
	 * @return array
	 */
	private function search_to_array( $terms ) {
		$terms = $this->sanitize_search_term( $terms );
		return array_filter( explode( ',', $terms ) );
	}

	/**
	 * Put some more appropriate links on our custom result cards.
	 *
	 * @param array $links Related links.
	 * @param array $plugin Plugin result information.
	 */
	public function insert_related_links( $links, $plugin ) {
		if ( self::$slug !== $plugin['slug'] ) {
			return $links;
		}

		// By the time this filter is applied, self_admin_url was already applied and we don't need it anymore.
		remove_filter( 'self_admin_url', array( $this, 'plugin_details' ) );

		$links = array();
		$is_installed = $this->is_installed( $plugin['plugin'] );
		$is_active    = is_plugin_active( $plugin['plugin'] );
		$has_access   = ! empty( $plugin['url'] );

		// Plugin installed, active, feature not enabled; prompt to enable.
		if ( ! $is_active && $is_installed ) {
			if ( current_user_can( 'activate_plugins' ) ) {
				$activate_url = add_query_arg(
					array(
						'action' => 'activate',
						'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $plugin['plugin'] ),
						'plugin'   => $plugin['plugin'],
					),
					admin_url( 'plugins.php' )
				);
				$links['frm_get_started'] = '<a href="' . esc_url( $activate_url ) . '" class="button activate-now" aria-label="Activate ' . esc_attr( $plugin['name'] ) . '">' . __( 'Activate', 'formidable' ) . '</a>';
			}
		} elseif ( ! $is_active && isset( $plugin['url'] ) ) {
			// Go to the add-ons page to install.
			$links[] = '<a
				class="button-secondary"
				href="' . esc_url( admin_url( 'admin.php?page=formidable-addons' ) ) . '"
				>' . __( 'Install Now', 'formidable' ) . '</a>';

		} elseif ( ! empty( $plugin['link'] ) ) {
			// Add link pointing to a relevant doc page in formidable.com.
			$links[] = '<a
				class="button-primary frm-plugin-search__learn-more"
				href="' . esc_url( FrmAppHelper::admin_upgrade_link( 'plugin-learn-more', $plugin['link'] ) ) . '"
				target="_blank"
				data-addon="' . esc_attr( $plugin['addon'] ) . '"
				>' . esc_html__( 'Learn more', 'formidable' ) . '</a>';
		}

		// Dismiss link.
		$dismiss = add_query_arg( array( 'frm-dismiss' => $plugin['id'] ) );
		$links[] = '<a
			href="' . $dismiss . '"
			class="frm-plugin-search__dismiss"
			data-addon="' . esc_attr( $plugin['addon'] ) . '"
			>' . esc_html__( 'Hide this suggestion', 'formidable' ) . '</a>';

		return $links;
	}

	protected function is_installed( $plugin ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		return isset( $all_plugins[ $plugin ] );
	}

	/**
	 * Load the search scripts and CSS for PSH.
	 */
	public function load_plugins_search_script() {
		wp_enqueue_script( self::$slug, FrmAppHelper::plugin_url() . '/js/plugin-search.js', array(), FrmAppHelper::plugin_version(), true );
		wp_localize_script(
			self::$slug,
			'frmPlugSearch',
			array(
				'legend' => esc_html__(
					'This suggestion was made by Formidable Forms, the form builder and application plugin already installed on your site.',
					'formidable'
				),
			)
		);
	}
}
