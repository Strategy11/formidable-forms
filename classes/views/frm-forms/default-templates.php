<?php FrmFormsHelper::get_template_dropdown($all_templates); ?>

<h3><?php _e('Default Templates', 'formidable') ?></h3>
<table class="widefat post fixed" id="posts-filter" cellspacing="0">
    <thead>
    <tr>
        <th class="manage-column" width="30%"><?php _e('Name') ?></th>
        <th class="manage-column"><?php _e('Description') ?></th>
    </tr>
    </thead>
<?php if(empty($default_templates)){ ?>
    <tr><td colspan="2"><?php _e('No Templates Found', 'formidable') ?></td></tr>
<?php
}else{
    $alternate = false;
    foreach($default_templates as $form){
        $alternate = (!$alternate) ? 'alternate' : false;
    ?>
    <tr class="<?php echo $alternate ?>">
        <td class="post-title">
            <a class="row-title" href="<?php echo $url = FrmFormsHelper::get_direct_link($form->form_key, $form); ?>" title="<?php _e('View', 'formidable')?> <?php echo esc_attr($form->name) ?>" target="blank"><?php echo $form->name; ?></a><br/>
            <div class="row-actions">
                <?php if(current_user_can('frm_edit_forms')){ ?>
                <span><a href="?page=formidable&amp;frm_action=duplicate&amp;id=<?php echo $form->id; ?>"><?php _e('Create Form from Template', 'formidable') ?></a></span> |
                <?php } ?>
                <?php global $frm_settings; 
                if ($frm_settings->preview_page_id > 0)
                   $url = add_query_arg('form', $form->form_key, get_permalink($frm_settings->preview_page_id));
                ?>
                <span><a href="<?php echo $url ?>" target="blank"><?php _e('View', 'formidable') ?></a></span>
            </div>
        </td>
        <td><?php echo $form->description ?></td>
    </tr>
      <?php
    }
  }
?>
    <tfoot>
    <tr>
        <th class="manage-column"><?php _e('Name') ?></th>
        <th class="manage-column"><?php _e('Description') ?></th>
    </tr>
    </tfoot>
</table>

<br/><br/><h3><?php _e('Custom Templates', 'formidable') ?></h3>
