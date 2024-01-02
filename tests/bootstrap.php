<?php

if ( function_exists( 'xdebug_disable' ) ) {
	// Disable xdebug backtrace.
	xdebug_disable();
}

echo 'Welcome to the Formidable Forms Test Suite' . PHP_EOL;
echo 'Version: 1.0' . PHP_EOL . PHP_EOL;

$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( 'formidable/formidable.php' ),
);

if ( ! defined( 'SCRIPT_DEBUG' ) ) {
	define( 'SCRIPT_DEBUG', false );
}

if ( file_exists( dirname( __FILE__ ) . '/../vendor/autoload.php' ) ) {
	include dirname( __FILE__ ) . '/../vendor/autoload.php';
}

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	require getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/bootstrap.php';
} else {
	require '../../../../tests/phpunit/includes/bootstrap.php';
}

require_once dirname( __FILE__ ) . '/base/frm_factory.php';

// include unit test base class
require_once dirname( __FILE__ ) . '/base/FrmUnitTest.php';
require_once dirname( __FILE__ ) . '/base/FrmAjaxUnitTest.php';
