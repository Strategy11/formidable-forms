<?php
/**
 * WordPress pages used to publish connected Formidable experiences.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAbilitiesPagesController {
	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		$properties = array(
			'title'   => array(
				'type'      => 'string',
				'minLength' => 1,
			),
			'content' => array( 'type' => 'string' ),
			'slug'    => array( 'type' => 'string' ),
			'status'  => array(
				'type'    => 'string',
				'enum'    => array( 'draft', 'private', 'publish' ),
				'default' => 'draft',
			),
		);
		FrmSolutionAbilitiesHelper::register(
			'create-page',
			__( 'Create a page for a Formidable experience', 'formidable' ),
			array(
				'properties' => $properties,
				'required'   => array( 'title' ),
			),
			array( self::class, 'create' ),
			array( 'edit_pages' )
		);
		$properties['id'] = array(
			'type'    => 'integer',
			'minimum' => 1,
		);
		unset( $properties['status']['default'] );
		FrmSolutionAbilitiesHelper::register(
			'update-page',
			__( 'Update a Formidable experience page', 'formidable' ),
			array(
				'properties' => $properties,
				'required'   => array( 'id' ),
			),
			array( self::class, 'update' ),
			array( 'edit_pages' ),
			array( false, false, true )
		);
		FrmSolutionAbilitiesHelper::register(
			'get-page',
			__( 'Read a Formidable experience page', 'formidable' ),
			array(
				'properties' => array( 'id' => $properties['id'] ),
				'required'   => array( 'id' ),
			),
			array( self::class, 'get' ),
			array( 'edit_pages' ),
			array( true, false, true )
		);
		FrmSolutionAbilitiesHelper::register(
			'list-pages',
			__( 'List pages for Formidable experiences', 'formidable' ),
			array(
				'properties' => array(
					'search'    => array( 'type' => 'string' ),
					'page'      => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'page_size' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 100,
						'default' => 20,
					),
				),
			),
			array( self::class, 'listing' ),
			array( 'edit_pages' ),
			array( true, false, true )
		);
	}

	/**
	 * @since x.x
	 *
	 * @param array $input Page properties.
	 *
	 * @return array|WP_Error
	 */
	public static function create( $input ) {
		return self::save( $input, 0 );
	}

	/**
	 * @since x.x
	 *
	 * @param array $input Page ID and changed properties.
	 *
	 * @return array|WP_Error
	 */
	public static function update( $input ) {
		$page = self::get( $input );
		return is_wp_error( $page ) ? $page : self::save( $input, (int) $input['id'] );
	}

	/**
	 * @since x.x
	 *
	 * @param array $input Requested page ID.
	 *
	 * @return array|WP_Error
	 */
	public static function get( $input ) {
		$page = get_post( $input['id'] ?? 0 );

		if ( ! $page || 'page' !== $page->post_type || 'trash' === $page->post_status || ! current_user_can( 'edit_post', $page->ID ) ) {
			return new WP_Error( 'frm_page_access', __( 'The page is unavailable or you cannot edit it.', 'formidable' ) );
		}

		return array(
			'id'      => $page->ID,
			'title'   => $page->post_title,
			'content' => $page->post_content,
			'slug'    => $page->post_name,
			'status'  => $page->post_status,
			'url'     => get_permalink( $page ),
		);
	}

	/**
	 * @since x.x
	 *
	 * @param array $input Search and pagination.
	 *
	 * @return array
	 */
	public static function listing( $input ) {
		$pages  = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'draft', 'publish', 'private' ),
				's'              => sanitize_text_field( $input['search'] ?? '' ),
				'posts_per_page' => min( 100, max( 1, (int) ( $input['page_size'] ?? 20 ) ) ),
				'paged'          => max( 1, (int) ( $input['page'] ?? 1 ) ),
				'orderby'        => 'ID',
				'order'          => 'DESC',
			)
		);
		$result = array();

		foreach ( $pages as $page ) {
			if ( current_user_can( 'edit_post', $page->ID ) ) {
				$result[] = self::get( array( 'id' => $page->ID ) );
			}
		}

		return $result;
	}

	/**
	 * Save through WordPress, including per-page edit and publish checks.
	 *
	 * @since x.x
	 *
	 * @param array $input Changed page properties.
	 * @param int   $id Existing page ID, or zero.
	 *
	 * @return array|WP_Error
	 */
	private static function save( $input, $id ) {
		$values = array( 'post_type' => 'page' );

		if ( $id ) {
			$values['ID'] = $id;
		} else {
			$values['post_status'] = 'draft';
		}

		foreach ( array(
			'title'   => 'post_title',
			'content' => 'post_content',
			'slug'    => 'post_name',
			'status'  => 'post_status',
		) as $key => $column ) {
			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}

			$value = $input[ $key ];

			if ( 'content' !== $key ) {
				$value = sanitize_text_field( $value );
			} elseif ( ! current_user_can( 'unfiltered_html' ) ) {
				$value = wp_kses_post( $value );
			}

			if ( 'status' === $key && in_array( $value, array( 'publish', 'private' ), true ) && ! current_user_can( 'publish_pages' ) ) {
				return new WP_Error( 'frm_page_publish', __( 'You cannot publish pages.', 'formidable' ) );
			}

			$values[ $column ] = $value;
		}

		$saved = $id ? wp_update_post( wp_slash( $values ), true ) : wp_insert_post( wp_slash( $values ), true );
		return is_wp_error( $saved ) ? $saved : self::get( array( 'id' => $saved ) );
	}
}
