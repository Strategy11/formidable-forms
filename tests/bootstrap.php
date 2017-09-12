<?php

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

echo 'Welcome to the Formidable Forms Test Suite' . PHP_EOL;
echo 'Version: 1.0' . PHP_EOL . PHP_EOL;

$GLOBALS['wp_tests_options'] = array(
	'active_plugins'     => array( 'formidable/formidable.php', 'easy-table/easy-table.php' ),
	'frmpro-credentials' => array( 'license' => '87fu-uit7-896u-ihy8' ),
	'frmpro-authorized'  => true,
);

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	require getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/bootstrap.php';
} else {
	require '../../../../tests/phpunit/includes/bootstrap.php';
}

if ( file_exists( dirname( __FILE__ )  . '/../vendor/autoload_52.php' ) ) {
	include( dirname( __FILE__ )  . '/../vendor/autoload_52.php' );
}

if ( version_compare( phpversion(), '5.3', '>=' ) && file_exists( dirname( __FILE__ )  . '/../vendor/autoload.php' ) ) {
	include( dirname( __FILE__ ) . '/../vendor/autoload.php' );
}

require_once dirname( __FILE__ ) . '/base/frm_factory.php';

// include unit test base class
require_once dirname( __FILE__ ) . '/base/FrmUnitTest.php';
require_once dirname( __FILE__ ) . '/base/FrmAjaxUnitTest.php';

/**************************************************************
 * Custom functions for testing apply_filters
 *************************************************************/

// Tested in test_FrmProDisplaysController::test_before_content_with_custom_filter
function dynamic_frm_stats($content, $display, $show, $atts){
	$view_id = FrmProDisplay::get_id_by_key( 'dynamic-view' );
	if ( $display->ID == $view_id ){//Change 1066 to the ID of your View
		$entries = $atts['entry_ids'];
		$total = 0;
		$field_id = FrmField::get_id_by_key( 'number-field' );
		foreach($entries as $entry){
			$total += FrmProEntriesController::get_field_value_shortcode(array( 'field_id' => $field_id, 'entry' => $entry ) );
		}
		$content = str_replace('[sum_number-field]', $total, $content);
	}
	return $content;
}

// Tested in test_FrmProDisplaysController::test_row_num_custom_filter
function frm_get_row_num($new_content, $entry, $shortcodes, $display, $show, $odd, $atts){
	$view_id = FrmProDisplay::get_id_by_key( 'dynamic-view' );
	if ( $display->ID == $view_id ) {
		if ( isset($_GET['frm-page-' . $display->ID]) ) {
			$page_num = absint( $_GET['frm-page-' . $display->ID] );
			$page_size = $display->frm_page_size;
			$prev_total = ($page_num - 1) * $page_size;
			$current_count = $atts['count'] + $prev_total;
			$new_content = str_replace('[row_num]', $current_count, $new_content);
		} else {
			$new_content = str_replace('[row_num]', $atts['count'], $new_content);
		}
	}
	return $new_content;
}

// Tested in test_FrmProDisplaysController::test_record_count_and_total_count_with_filter
function frm_get_current_entry_num_out_of_total($content, $entry, $shortcodes, $display, $show, $odd, $atts) {
	$view_id = FrmProDisplay::get_id_by_key( 'dynamic-view' );
	if ( isset( $atts['pagination'] ) && $atts['count'] == 1 && $display->ID == $view_id ) {
		$current_page_num = isset($_GET['frm-page-' . $display->ID]) ? absint( $_GET['frm-page-' . $display->ID ] ) : 1;
		$page_size = $display->frm_page_size;
		$start_num = ( ( $current_page_num - 1 ) * $page_size ) + 1;
		$end_num = $start_num + $atts['total_count'] - 1;
		$content = 'Viewing entry ' . $start_num . ' to ' . $end_num .' (of ' . $atts['record_count'] .' entries)' . $content;
	}
	return $content;
}
