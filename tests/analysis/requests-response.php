<?php
/**
 * Describe the WordPress Requests response used by HTTP test fixtures.
 * WordPress loads the real class when the tests run.
 *
 * @package Formidable
 *
 * @since x.x
 */

declare(strict_types=1);

namespace WpOrg\Requests;

if ( ! class_exists( Response::class ) ) {
	/**
	 * The HTTP response returned by the Requests library bundled with WordPress.
	 */
	class Response {
		/**
		 * Response body.
		 *
		 * @var string
		 */
		public $body = '';

		/**
		 * HTTP status code assigned by response fixtures.
		 *
		 * @var int
		 */
		public $status_code = 200;
	}
}

// WordPress stubs from before the Requests namespace change use this legacy alias.
if ( ! class_exists( '\Requests_Response' ) ) {
	class_alias( Response::class, 'Requests_Response' );
}
