<div id="form_entries_page" class="wrap">
	<h1><?php _e( 'Entries', 'formidable' ); ?>
        <?php do_action('frm_entry_inside_h2', $form); ?>
	</h1>

	<?php require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

    <form id="posts-filter" method="get">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <?php FrmAppController::get_form_nav($form, true, 'hide'); ?>
            </div>
            <div id="postbox-container-1" class="postbox-container">
                <input type="hidden" name="page" value="formidable-entries" />
				<input type="hidden" name="form" value="<?php echo esc_attr( $form ? $form->id : '' ); ?>" />
                <input type="hidden" name="frm_action" value="list" />
                <?php $wp_list_table->search_box( __( 'Search', 'formidable' ), 'entry' ); ?>
            </div>
            <div class="clear"></div>
            </div>
            <?php if ( $form ) { ?>
            <div id="titlediv" class="frm-on-bottom">
				<input id="title" type="text" value="<?php echo esc_attr( $form->name == '' ? __( '(no title)' ) : $form->name ) ?>" readonly="readonly" disabled="disabled" />
            </div>
            <?php } ?>

			<?php FrmTipsHelper::pro_tip( 'get_entries_tip' ); ?>

            <?php $wp_list_table->display(); ?>

        </div>
    </form>

</div>
