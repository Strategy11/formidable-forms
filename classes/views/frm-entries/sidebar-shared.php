
<div class="postbox frm_with_icons" >
    <h3 class="hndle">
		<span><?php esc_html_e( 'Entry Details', 'formidable' ) ?></span>
		<?php if ( FrmAppHelper::get_param( 'frm_action' ) != 'show' ) { ?>
		<a href="?page=formidable-entries&amp;frm_action=show&amp;id=<?php echo absint( $entry->id ); ?>" class="alignright">
			<?php esc_html_e( 'View Entry', 'formidable' ) ?>
		</a>
		<?php } ?>
	</h3>
    <div class="inside">
		<?php include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/_sidebar-shared-pub.php' ); ?>

        <?php if ( $entry->post_id ) { ?>
        <div class="misc-pub-section frm_no_print">
            <span class="dashicons dashicons-admin-post wp-media-buttons-icon"></span>
            <?php esc_html_e( 'Post', 'formidable' ) ?>:
			<b><?php echo get_the_title( $entry->post_id ); ?></b>
			<span>
				<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $entry->post_id . '&action=edit' ) ) ?>">
					<?php esc_html_e( 'Edit', 'formidable' ) ?>
				</a>
				<a href="<?php echo esc_url( get_permalink( $entry->post_id ) ) ?>">
					<?php esc_html_e( 'View', 'formidable' ) ?>
				</a>
			</span>
        </div>
        <?php } ?>

        <div class="misc-pub-section">
            <span class="dashicons dashicons-id wp-media-buttons-icon"></span>
			<?php esc_html_e( 'Entry ID', 'formidable' ) ?>:
			<b><?php echo absint( $entry->id ) ?></b>
        </div>

        <div class="misc-pub-section">
            <span class="dashicons dashicons-post-status wp-media-buttons-icon"></span>
			<?php esc_html_e( 'Entry Key', 'formidable' ) ?>:
			<b><?php echo esc_html( $entry->item_key ) ?></b>
        </div>

        <?php if ( FrmAppHelper::pro_is_installed() ) { ?>
        <?php if ( $entry->user_id ) { ?>
        <div class="misc-pub-section">
            <span class="dashicons dashicons-admin-users wp-media-buttons-icon"></span>
			<?php
			printf(
				esc_html__( 'Created by: %1$s', 'formidable' ),
				FrmAppHelper::kses( FrmFieldsHelper::get_user_display_name( $entry->user_id, 'display_name', array( 'link' => true ) ), array( 'a' ) )
			); // WPCS: XSS ok.
			?>
        </div>
        <?php } ?>

        <?php if ( $entry->updated_by && $entry->updated_by != $entry->user_id ) { ?>
        <div class="misc-pub-section">
            <span class="dashicons dashicons-admin-users wp-media-buttons-icon"></span>
			<?php
			printf(
				esc_html__( 'Updated by: %1$s', 'formidable' ),
				FrmAppHelper::kses( FrmFieldsHelper::get_user_display_name( $entry->updated_by, 'display_name', array( 'link' => true ) ), array( 'a' ) )
			); // WPCS: XSS ok.
			?>
        </div>
        <?php } ?>
        <?php } ?>

    </div>
</div>

<div class="postbox">
    <h3 class="hndle"><span><?php esc_html_e( 'User Information', 'formidable' ) ?></span></h3>
    <div class="inside">
		<?php if ( ! empty( $entry->ip ) ) { ?>
		<div class="misc-pub-section">
			<?php esc_html_e( 'IP Address', 'formidable' ) ?>:
			<b><?php echo esc_html( $entry->ip ); ?></b>
		</div>
		<?php } ?>

        <?php if ( isset( $browser ) ) { ?>
        <div class="misc-pub-section">
			<b><?php esc_html_e( 'Browser/OS', 'formidable' ) ?></b>:<br/>
			<?php echo wp_kses_post( $browser ); ?>
        </div>
        <?php } ?>

		<?php if ( isset( $data['referrer'] ) ) { ?>
		<div class="misc-pub-section frm_force_wrap">
			<b><?php esc_html_e( 'Referrer', 'formidable' ) ?></b>:<br/>
			<?php echo wp_kses_post( str_replace( "\r\n", '<br/>', $data['referrer'] ) ); ?>
        </div>
        <?php } ?>

        <?php
        foreach ( (array) $data as $k => $d ) {
			if ( in_array( $k, array( 'browser', 'referrer' ) ) ) {
                continue;
            }
        ?>
        <div class="misc-pub-section">
			<b><?php echo esc_html( ucfirst( str_replace( '-', ' ', $k ) ) ); ?></b>:
			<?php echo wp_kses_post( implode( ', ', (array) $d ) ); ?>
        </div>
        <?php
			unset( $k, $d );
        }
        ?>
    </div>
</div>
