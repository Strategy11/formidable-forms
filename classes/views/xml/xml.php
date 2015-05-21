<?php the_generator( 'export' ); ?>

<channel>
	<title><?php bloginfo_rss( 'name' ); ?></title>
	<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>

<?php
foreach ( $type as $tb_type ) {

    if ( ! isset( $tables[ $tb_type ] ) ) {
        do_action('frm_xml_import_'. $tb_type, $args);
        continue;
    }

    //no records
    if ( ! isset( $records[ $tb_type ] ) ) {
        continue;
    }

	$item_ids = $records[ $tb_type ];
	if ( in_array( $tb_type, array( 'styles', 'actions' ) ) ) {
        include(dirname(__FILE__) .'/posts_xml.php');
    } else if ( file_exists(dirname(__FILE__) .'/'. $tb_type .'_xml.php') ) {
        include(dirname(__FILE__) .'/'. $tb_type .'_xml.php');
	} else if ( file_exists( FrmAppHelper::plugin_path() . '/pro/classes/views/xml/' . $tb_type . '_xml.php' ) ) {
        include( FrmAppHelper::plugin_path() .'/pro/classes/views/xml/'. $tb_type .'_xml.php' );
    }

	unset( $item_ids, $records[ $tb_type ], $tb_type );
}

?>
</channel>
