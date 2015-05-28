
<div class="postbox frm_with_icons" >
    <h3 class="hndle"><span><?php _e( 'Entry Details', 'formidable' ) ?></span></h3>
    <div class="inside">
        <?php if ( $entry->post_id ) { ?>
        <div class="misc-pub-section">
            <span class="dashicons dashicons-admin-post wp-media-buttons-icon"></span>
            <?php _e( 'Post', 'formidable' ) ?>:
            <b><?php echo get_the_title($entry->post_id) ?></b>
            <span><a href="<?php echo esc_url( admin_url('post.php?post='. $entry->post_id .'&action=edit') ) ?>"><?php _e( 'Edit', 'formidable' ) ?></a>
            <a href="<?php echo esc_url( get_permalink( $entry->post_id ) ) ?>"><?php _e( 'View', 'formidable' ) ?></a></span>
        </div>
        <?php } ?>

        <div class="misc-pub-section">
            <span class="dashicons dashicons-id wp-media-buttons-icon"></span>
			<?php _e( 'Entry ID', 'formidable' ) ?>:
			<b><?php echo absint( $entry->id ) ?></b>
        </div>

        <div class="misc-pub-section">
            <span class="dashicons dashicons-post-status wp-media-buttons-icon"></span>
            <?php _e( 'Entry Key', 'formidable' ) ?>:
			<b><?php echo sanitize_title( $entry->item_key ) ?></b>
        </div>

        <?php if ( FrmAppHelper::pro_is_installed() ) { ?>
        <?php if ( $entry->user_id ) { ?>
        <div class="misc-pub-section">
            <span class="dashicons dashicons-admin-users wp-media-buttons-icon"></span>
			<?php printf( __( 'Created by: %1$s', 'formidable' ), FrmProFieldsHelper::get_display_name( $entry->user_id, 'display_name', array( 'link' => true ) ) ); ?>
        </div>
        <?php } ?>

        <?php if ( $entry->updated_by && $entry->updated_by != $entry->user_id ) { ?>
        <div class="misc-pub-section">
            <span class="dashicons dashicons-admin-users wp-media-buttons-icon"></span>
			<?php printf( __( 'Updated by: %1$s', 'formidable' ), FrmProFieldsHelper::get_display_name( $entry->updated_by,  'display_name', array( 'link' => true ) ) ); ?>
        </div>
        <?php } ?>
        <?php } ?>

    </div>
</div>

<div class="postbox">
    <h3 class="hndle"><span><?php _e( 'User Information', 'formidable' ) ?></span></h3>
    <div class="inside">
        <div class="misc-pub-section">
            <?php _e( 'IP Address', 'formidable' ) ?>:
			<b><?php echo sanitize_text_field( $entry->ip ); ?></b>
        </div>

        <?php if ( isset( $browser ) ) { ?>
        <div class="misc-pub-section">
            <b><?php _e( 'Browser/OS', 'formidable' ) ?></b>:<br/>
			<?php echo wp_kses_post( $browser ); ?>
        </div>
        <?php } ?>

        <?php if ( isset($data['referrer']) ) { ?>
        <div class="misc-pub-section">
            <b><?php _e( 'Referrer', 'formidable' ) ?></b>:<br/>
			<?php echo wp_kses_post( str_replace( "\r\n", '<br/>', $data['referrer'] ) );  ?>
        </div>
        <?php } ?>

        <?php
        foreach ( (array) $data as $k => $d ) {
			if ( in_array( $k, array( 'browser', 'referrer' ) ) ) {
                continue;
            }
        ?>
        <div class="misc-pub-section">
			<b><?php echo sanitize_text_field( ucfirst( str_replace( '-', ' ', $k ) ) ); ?></b>:
			<?php echo wp_kses_post( implode( ', ', (array) $d ) ); ?>
        </div>
        <?php
            unset($k, $d);
        }
        ?>
    </div>
</div>
