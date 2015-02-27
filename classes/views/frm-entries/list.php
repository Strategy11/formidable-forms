<div id="form_entries_page" class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2>
		<?php _e('Entries', 'formidable'); ?>
		<a href="#" class="add-new-h2" style="visibility:hidden;"><?php _e('Add New', 'formidable'); ?></a>
	</h2>

    <?php if($form) FrmAppController::get_form_nav($form->id, true); ?>
	
	<?php require(FrmAppHelper::plugin_path() .'/classes/views/shared/errors.php'); ?>
    <?php FrmAppController::update_message('view, search, export, and bulk delete your saved entries'); ?>

    <?php if(!$form or $entry_count){ ?>
    <img src="<?php echo FrmAppHelper::plugin_url() ?>/screenshot-5.png" alt="Entries List" style="max-width:100%"/>
    <?php }else{ ?>
    <table class="wp-list-table widefat post fixed" cellspacing="0">
        <thead>
            <tr><th class="manage-column" scope="col"> </th></tr>
        </thead>
        <tbody>
            <tr><td>
            <?php include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/no_entries.php'); ?>
            </td></tr>
        </tbody>
        <tfoot>
            <tr><th class="manage-column" scope="col"> </th></tr>
        </tfoot>
    </table>
    <?php } ?>
</div>

