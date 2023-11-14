<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

the_generator( 'export' );
?>

<channel>
	<title><?php bloginfo_rss( 'name' ); ?></title>
	<pubDate><?php echo esc_html( gmdate( 'D, d M Y H:i:s +0000' ) ); ?></pubDate>

<?php
/**
 * @since 5.3
 */
do_action( 'frm_xml_export_before_types_loop' );

foreach ( $type as $tb_type ) {

	if ( ! isset( $tables[ $tb_type ] ) ) {
		do_action( 'frm_xml_import_' . $tb_type, $args );
		continue;
	}

	if ( ! isset( $records[ $tb_type ] ) ) {
		// No records.
		continue;
	}

	$item_ids = $records[ $tb_type ];

	if ( in_array( $tb_type, array( 'styles', 'actions' ), true ) ) {
		include dirname( __FILE__ ) . '/posts_xml.php';
	} elseif ( file_exists( dirname( __FILE__ ) . '/' . $tb_type . '_xml.php' ) ) {
		include dirname( __FILE__ ) . '/' . $tb_type . '_xml.php';
	} elseif ( FrmAppHelper::pro_is_installed() && file_exists( FrmProAppHelper::plugin_path() . '/classes/views/xml/' . $tb_type . '_xml.php' ) ) {
		include FrmProAppHelper::plugin_path() . '/classes/views/xml/' . $tb_type . '_xml.php';
	}

	unset( $item_ids, $records[ $tb_type ], $tb_type );
}

/**
 * @since 5.3
 */
do_action( 'frm_xml_export_after_types_loop' );
?>
</channel>
