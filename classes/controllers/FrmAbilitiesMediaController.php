<?php
/**
 * Discover editable media for file-backed Formidable experiences.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAbilitiesMediaController {
	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		FrmSolutionAbilitiesHelper::register(
			'list-media',
			__( 'List media available to the current editor', 'formidable' ),
			array(
				'properties' => array(
					'page'      => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'page_size' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 100,
						'default' => 24,
					),
					'search'    => array(
						'type'    => 'string',
						'default' => '',
					),
					'mime_type' => array(
						'type'    => 'string',
						'enum'    => array( 'image', 'application/pdf', 'audio', 'video' ),
						'default' => 'image',
					),
				),
			),
			array( self::class, 'listing' ),
			array( 'upload_files' ),
			array( true, false, true )
		);
		FrmSolutionAbilitiesHelper::register(
			'upload-media',
			__( 'Upload media through WordPress file validation', 'formidable' ),
			array(
				'properties' => array(
					'filename'       => array(
						'type'      => 'string',
						'minLength' => 1,
					),
					'title'          => array(
						'type'    => 'string',
						'default' => '',
					),
					'content_base64' => array(
						'type'      => 'string',
						'minLength' => 1,
						'maxLength' => 13981016,
					),
				),
				'required'   => array( 'filename', 'content_base64' ),
			),
			array( self::class, 'upload' ),
			array( 'upload_files' )
		);
	}

	/**
	 * @since x.x
	 *
	 * @param array $input Media type and pagination.
	 *
	 * @return array
	 */
	public static function listing( $input ) {
		$posts  = get_posts(
			array(
				's'              => $input['search'] ?? '',
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => $input['mime_type'],
				'posts_per_page' => $input['page_size'],
				'paged'          => $input['page'],
			)
		);
		$result = array();

		foreach ( $posts as $post ) {
			if ( current_user_can( 'edit_post', $post->ID ) ) {
				$result[] = array(
					'id'        => $post->ID,
					'title'     => $post->post_title,
					'url'       => wp_get_attachment_url( $post->ID ),
					'mime_type' => $post->post_mime_type,
				);
			}
		}

		return $result;
	}
	/**
	 * Decode a bounded upload and let WordPress enforce extension and MIME rules.
	 *
	 * @since x.x
	 *
	 * @param array $input Filename, title and base64 file bytes.
	 *
	 * @return array|WP_Error
	 */
	public static function upload( $input ) {
		$filename = sanitize_file_name( $input['filename'] );
		$bytes    = base64_decode( $input['content_base64'], true );

		if ( ! $filename || false === $bytes || '' === $bytes || strlen( $bytes ) > min( 10485760, wp_max_upload_size() ) ) {
			return new WP_Error( 'frm_media_upload', __( 'Provide a valid file within the site upload limit.', 'formidable' ) );
		}

		$type = wp_check_filetype( $filename, get_allowed_mime_types() );

		if ( empty( $type['type'] ) ) {
			return new WP_Error( 'frm_media_type', __( 'This file type is not allowed.', 'formidable' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$temporary = wp_tempnam( $filename );

		if ( ! $temporary ) {
			return new WP_Error( 'frm_media_temp', __( 'A temporary upload file could not be created.', 'formidable' ) );
		}

		global $wp_filesystem;

		if ( ! WP_Filesystem() || ! $wp_filesystem->put_contents( $temporary, $bytes ) ) {
			wp_delete_file( $temporary );
			return new WP_Error( 'frm_media_write', __( 'The upload could not be written.', 'formidable' ) );
		}

		$id = media_handle_sideload(
			array(
				'name'     => $filename,
				'tmp_name' => $temporary,
			),
			0,
			null,
			array( 'post_title' => sanitize_text_field( $input['title'] ) )
		);

		if ( is_wp_error( $id ) ) {
			wp_delete_file( $temporary );
			return $id;
		}

		return array(
			'id'        => $id,
			'title'     => get_the_title( $id ),
			'url'       => wp_get_attachment_url( $id ),
			'mime_type' => get_post_mime_type( $id ),
		);
	}
}
