<?php

if ( ! $item_ids ) {
    return;
}

global $wp_query;
$wp_query->in_the_loop = true; // Fake being in the loop.

// fetch 20 posts at a time rather than loading the entire table into memory
while ( $next_posts = array_splice( $item_ids, 0, 20 ) ) {
	$posts = FrmDb::get_results( $wpdb->posts, array( 'ID' => $next_posts ) );

	// Begin Loop
	foreach ( $posts as $post ) {
		setup_postdata( $post );
		$is_sticky = is_sticky( $post->ID ) ? 1 : 0;
?>
	<view>
		<title><?php echo apply_filters( 'the_title_rss', $post->post_title ); ?></title>
		<link><?php the_permalink_rss() ?></link>
		<post_author><?php echo FrmXMLHelper::cdata( get_the_author_meta( 'login' ) ); ?></post_author>
		<description></description>
		<content><?php echo FrmXMLHelper::cdata( apply_filters( 'the_content_export', $post->post_content ) ); ?></content>
		<excerpt><?php echo FrmXMLHelper::cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) ); ?></excerpt>
		<post_id><?php echo esc_html( $post->ID ); ?></post_id>
		<post_date><?php echo esc_html( $post->post_date ); ?></post_date>
		<post_date_gmt><?php echo esc_html( $post->post_date_gmt ); ?></post_date_gmt>
		<comment_status><?php echo esc_html( $post->comment_status ); ?></comment_status>
		<ping_status><?php echo esc_html( $post->ping_status ); ?></ping_status>
		<post_name><?php echo esc_html( $post->post_name ); ?></post_name>
		<status><?php echo esc_html( $post->post_status ); ?></status>
		<post_parent><?php echo esc_html( $post->post_parent ); ?></post_parent>
		<menu_order><?php echo esc_html( $post->menu_order ); ?></menu_order>
		<post_type><?php echo esc_html( $post->post_type ); ?></post_type>
		<post_password><?php echo FrmXMLHelper::cdata( $post->post_password ); ?></post_password>
		<is_sticky><?php echo esc_html( $is_sticky ); ?></is_sticky>
<?php	if ( $post->post_type == 'attachment' ) : ?>
		<attachment_url><?php echo wp_get_attachment_url( $post->ID ); ?></attachment_url>
<?php 	endif; ?>
<?php
		$postmeta = FrmDb::get_results( $wpdb->postmeta, array( 'post_id' => $post->ID ) );
		foreach ( $postmeta as $meta ) :
			if ( apply_filters( 'wxr_export_skip_postmeta', false, $meta->meta_key, $meta ) ) {
				continue;
			}
		?>
		<postmeta>
			<meta_key><?php echo esc_html( $meta->meta_key ); ?></meta_key>
			<meta_value><?php echo FrmXMLHelper::cdata( $meta->meta_value ); ?></meta_value>
		</postmeta>
<?php	endforeach;

		$taxonomies = get_object_taxonomies( $post->post_type );
		if ( ! empty( $taxonomies ) ) {
			$terms = wp_get_object_terms( $post->ID, $taxonomies );

			foreach ( (array) $terms as $term ) {
				echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . FrmXMLHelper::cdata( $term->name ) . "</category>\n";
			}
		} ?>
	</view>
<?php
	}
}

if ( empty( $taxonomies ) ) {
    return;
}

global $frm_inc_tax;
if ( empty( $frm_inc_tax ) ) {
	$frm_inc_tax = array();
}

foreach ( (array) $terms as $term ) {
	if ( in_array( $term->term_id, $frm_inc_tax ) ) {
        return;
	}

    $frm_inc_tax[] = $term->term_id;
    $label = ($term->taxonomy == 'category' || $term->taxonomy == 'tag') ? $term->taxonomy : 'term'; ?>
	<term><term_id><?php echo esc_html( $term->term_id ) ?></term_id><term_taxonomy><?php echo esc_html( $term->taxonomy ); ?></term_taxonomy><?php
    if ( ! empty( $term->name ) ) {
        echo '<term_name>' . FrmXMLHelper::cdata( $term->name ) . '</term_name>';
    }
    if ( ! empty( $term->description ) ) {
    ?><term_description><?php echo FrmXMLHelper::cdata( $term->description ) ?></term_description><?php
    }
    ?><term_slug><?php echo esc_html( $term->slug ); ?></term_slug></term>
<?php
}
