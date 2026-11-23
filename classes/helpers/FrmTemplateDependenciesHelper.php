<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Resolve template requirements before importing any forms.
 *
 * @since x.x
 */
class FrmTemplateDependenciesHelper {

	/**
	 * Get missing dependencies for a validated template download URL.
	 *
	 * @since x.x
	 *
	 * @param string $url The template XML download URL.
	 *
	 * @return array[] Missing dependencies and their available actions.
	 */
	public static function get_missing_addons( $url ) {
		$api       = new FrmFormTemplateApi();
		$templates = $api->get_api_info();
		$path      = wp_parse_url( $url, PHP_URL_PATH );
		$required  = array();

		foreach ( $templates as $template ) {
			if ( ! is_array( $template ) || ! is_string( $template['url'] ?? null ) || $path !== wp_parse_url( $template['url'], PHP_URL_PATH ) ) {
				continue;
			}

			$required = self::get_required_addons( $template );
			break;
		}

		if ( ! $required ) {
			return array();
		}

		$api    = new FrmFormApi();
		$addons = array_filter(
			$api->get_api_info(),
			function ( $addon ) {
				return is_array( $addon ) && is_string( $addon['plugin'] ?? null ) && '' !== $addon['plugin'];
			}
		);

		$plugins = FrmAppHelper::get_plugins();
		$missing = array();

		foreach ( $required as $requirement ) {
			$addon     = self::find_addon( $requirement, $addons );
			$installed = $addon && isset( $plugins[ $addon['plugin'] ] );
			$variant   = $addon ? self::get_views_variant( $addon ) : '';
			$matches   = ! $variant || ! $installed || $variant === self::get_installed_views_variant( $plugins[ $addon['plugin'] ] );

			if ( $installed && $matches && is_plugin_active( $addon['plugin'] ) ) {
				continue;
			}

			$key             = $addon ? $addon['plugin'] . $variant : $requirement;
			$missing[ $key ] = self::get_dependency_action( $requirement, $addon, $installed, $matches );
		}

		return array_values( $missing );
	}

	/**
	 * Get the add-ons a template declares, without the gateways that ship in Lite.
	 *
	 * @since x.x
	 *
	 * @param array $template The API template record.
	 *
	 * @return string[] Add-on identifiers or legacy display names.
	 */
	public static function get_required_addons( $template ) {
		$required = array();

		foreach ( (array) ( $template['used_addons'] ?? array() ) as $name ) {
			// Basic payment gateways ship in Lite and do not need separate add-ons.
			if ( ! is_string( $name ) || '' === trim( $name ) || in_array( $name, array( 'Stripe', 'Square', 'PayPal Commerce' ), true ) ) {
				continue;
			}

			$required[] = $name;
		}

		return array_values( array_unique( $required ) );
	}

	/**
	 * Resolve API IDs, slugs, plugin basenames, and legacy display names.
	 *
	 * @since x.x
	 *
	 * @param string  $requirement The required add-on identifier.
	 * @param array[] $addons The add-on API catalog records that have a plugin file.
	 *
	 * @return array|false The matching add-on record.
	 */
	private static function find_addon( $requirement, $addons ) {
		$aliases = array(
			'digital-signatures' => 'signature',
			'ai-forms'           => 'ai',
			'datepicker-options' => 'dates',
		);
		$slug    = sanitize_title( $requirement );
		$slug    = $aliases[ $slug ] ?? $slug;

		foreach ( $addons as $id => $addon ) {
			$directory   = dirname( $addon['plugin'] );
			$identifiers = array( (string) $id, $addon['plugin'], $directory, preg_replace( '/^formidable-/', '', $directory ), $addon['slug'] ?? '', $addon['title'] ?? '' );

			foreach ( $identifiers as $identifier ) {
				if ( ! is_string( $identifier ) || '' === $identifier ) {
					continue;
				}

				$identifier_slug = sanitize_title( $identifier );
				$identifier_slug = $aliases[ $identifier_slug ] ?? $identifier_slug;

				if ( $slug === $identifier_slug ) {
					return $addon;
				}
			}
		}

		return false;
	}

	/**
	 * Identify the catalog product for the basename shared by both Views add-ons.
	 *
	 * @since x.x
	 *
	 * @param array $addon The catalog record.
	 *
	 * @return string The Views product slug or an empty string.
	 */
	private static function get_views_variant( $addon ) {
		$slug = $addon['slug'] ?? '';
		return in_array( $slug, array( 'views', 'visual-views' ), true ) ? $slug : '';
	}

	/**
	 * Identify the Views product by the installed plugin header, even when it is inactive.
	 *
	 * @since x.x
	 *
	 * @param array $plugin The installed plugin header data.
	 *
	 * @return string The Views product slug.
	 */
	private static function get_installed_views_variant( $plugin ) {
		return version_compare( $plugin['Version'], '5.0', '>=' ) ? 'visual-views' : 'views';
	}

	/**
	 * Describe an available activation, installation, or manual resolution.
	 *
	 * @since x.x
	 *
	 * @param string      $requirement The required add-on identifier.
	 * @param array|false $addon The matching catalog record.
	 * @param bool        $installed Whether the catalog plugin is installed.
	 * @param bool        $matches Whether the installed plugin is the required variant.
	 *
	 * @return array The dependency label and action.
	 */
	private static function get_dependency_action( $requirement, $addon, $installed, $matches ) {
		$result = array(
			'name'        => $addon['title'] ?? $requirement,
			'description' => $addon['excerpt'] ?? '',
			'action'      => '',
			'plugin'      => '',
			'url'         => admin_url( 'admin.php?page=formidable-addons' ),
			'message'     => __( 'Install and activate this add-on before importing the template.', 'formidable' ),
		);

		if ( ! current_user_can( 'activate_plugins' ) ) {
			$result['url']     = '';
			$result['message'] = __( 'Ask an administrator to activate this add-on before importing the template.', 'formidable' );
			return $result;
		}

		if ( ! $addon ) {
			return $result;
		}

		if ( ! $matches ) {
			$result['message'] = __( 'A different version of Views is installed. Manage Views on the Add-Ons page, then try again.', 'formidable' );
		} elseif ( $installed && current_user_can( 'activate_plugin', $addon['plugin'] ) ) {
			$result['action'] = 'frm_activate_addon';
			$result['plugin'] = $addon['plugin'];
		} elseif ( ! $installed && ! empty( $addon['url'] ) && current_user_can( 'install_plugins' ) ) {
			$result['action'] = 'frm_install_addon';
			$result['plugin'] = $addon['url'];
		} else {
			$result['message'] = __( 'This add-on is not available to install. Check your license or install it manually, then try again.', 'formidable' );
		}

		return $result;
	}
}
