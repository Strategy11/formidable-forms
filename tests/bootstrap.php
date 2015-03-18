<?php

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

echo 'Welcome to the Formidable Forms Test Suite' . PHP_EOL;
echo 'Version: 1.0' . PHP_EOL . PHP_EOL;

$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( 'formidable/formidable.php' ),
);

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	require getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/bootstrap.php';
} else {
	require '../../../../tests/phpunit/includes/bootstrap.php';
}

if ( file_exists( dirname( __FILE__ )  . '/../vendor/autoload_52.php' ) ) {
	include( dirname( __FILE__ )  . '/../vendor/autoload_52.php' );
}

if ( version_compare( phpversion(), '5.3', '>=' ) ) {
	include( dirname( __FILE__ ) . '/../vendor/autoload.php' );
}

// include unit test base class
require_once dirname( __FILE__ ) . '/base/FrmUnitTest.php';
