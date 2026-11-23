<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Restricts stylesheet reads to the active theme and its parent.
 *
 * @since x.x
 */
class FrmStylesThemeFilesHelper {

	/**
	 * Validate stylesheet entries before they are cached or read.
	 *
	 * @since x.x
	 *
	 * @param mixed $stylesheets Filtered stylesheet entries.
	 *
	 * @return array Valid entries with an additional resolved_path key.
	 */
	public static function validate( $stylesheets ) {
		if ( ! is_array( $stylesheets ) ) {
			return array();
		}

		$directories = self::get_directories();
		$validated   = array();

		foreach ( $stylesheets as $stylesheet ) {
			if ( ! is_array( $stylesheet ) || ! is_string( $stylesheet['base_url'] ?? null ) ) {
				continue;
			}

			$path = self::resolve_path( $stylesheet['path'] ?? null, $directories );

			if ( '' === $path ) {
				continue;
			}

			$stylesheet['resolved_path'] = $path;
			$validated[]                 = $stylesheet;
		}

		return $validated;
	}

	/**
	 * Resolve the active theme directories before comparing filesystem paths.
	 *
	 * @since x.x
	 *
	 * @return string[]
	 */
	public static function get_directories() {
		$directories = array();

		foreach ( array_unique( array( get_stylesheet_directory(), get_template_directory() ) ) as $directory ) {
			$resolved = realpath( $directory );

			if ( false !== $resolved ) {
				$directories[] = rtrim( $resolved, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
			}
		}

		return $directories;
	}

	/**
	 * Resolve a readable CSS file inside the active theme or its parent.
	 *
	 * @since x.x
	 *
	 * @param mixed    $path        Candidate stylesheet path.
	 * @param string[] $directories Allowed directories with trailing slashes.
	 *
	 * @return string Empty when the path is outside the theme or unreadable.
	 */
	public static function resolve_path( $path, $directories ) {
		if ( ! is_string( $path ) || ! str_ends_with( strtolower( $path ), '.css' ) || str_contains( $path, "\0" ) ) {
			return '';
		}

		$resolved = realpath( $path );

		if ( false === $resolved || ! str_ends_with( strtolower( $resolved ), '.css' ) ) {
			return '';
		}

		foreach ( $directories as $directory ) {
			if ( str_starts_with( $resolved, $directory ) && is_file( $resolved ) && is_readable( $resolved ) ) {
				return $resolved;
			}
		}

		return '';
	}
}
