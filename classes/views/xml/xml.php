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
foreach ( $type as $tb_type ) {

	if ( ! isset( $tables[ $tb_type ] ) ) {
		do_action( 'frm_xml_import_' . $tb_type, $args );
		continue;
	}

	//no records
	if ( ! isset( $records[ $tb_type ] ) ) {
		continue;
	}

	$item_ids = $records[ $tb_type ];
	if ( in_array( $tb_type, array( 'styles', 'actions' ), true ) ) {
		include( dirname( __FILE__ ) . '/posts_xml.php' );
	} elseif ( file_exists( dirname( __FILE__ ) . '/' . $tb_type . '_xml.php' ) ) {
		include( dirname( __FILE__ ) . '/' . $tb_type . '_xml.php' );
	} elseif ( FrmAppHelper::pro_is_installed() && file_exists( FrmProAppHelper::plugin_path() . '/classes/views/xml/' . $tb_type . '_xml.php' ) ) {
		include( FrmProAppHelper::plugin_path() . '/classes/views/xml/' . $tb_type . '_xml.php' );
	}

	unset( $item_ids, $records[ $tb_type ], $tb_type );
}

?>
</channel>
