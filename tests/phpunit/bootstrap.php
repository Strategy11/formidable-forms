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

if ( file_exists( __DIR__ . '/../vendor/autoload.php' ) ) {
	include __DIR__ . '/../vendor/autoload.php';
}

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	require getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/bootstrap.php';
} else {
	require '../../../../tests/phpunit/includes/bootstrap.php';
}

require_once __DIR__ . '/base/frm_factory.php';

// include unit test base class
require_once __DIR__ . '/base/FrmUnitTest.php';
require_once __DIR__ . '/base/FrmAjaxUnitTest.php';

// include our Stripe unit helper base class
require_once __DIR__ . '/stripe/FrmStrpLiteUnitTest.php';

// Ensure that the plugin has been installed and activated.
FrmUnitTest::frm_install();
