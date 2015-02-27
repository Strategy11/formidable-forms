<div style="text-align:center;padding:60px 0;">
<?php
if ( $form && isset($form->options['no_save']) && $form->options['no_save'] ) { ?>
<h3><?php _e('This form is not set to save any entries.', 'formidable') ?></h3>
<p>If you would like entries in this form to be saved, go to the <a href="<?php echo esc_url(admin_url('admin.php?page=formidable') . '&frm_action=settings&id='. $form->id) ?>">form Settings</a> page and uncheck the "Do not store any entries submitted from this form" box.</p>
<?php
} else if ( $form ) {
?>
<div style="font-size:20px;margin-bottom:10px;"><?php printf(__('No Entries for form: %s', 'formidable'), $form->name); ?></div>
<p style="line-height:200%;"><?php printf( __('For instructions on publishing your form see %1$sthis page%2$s <br/> or click "%3$sAdd New%4$s" above to add an entry from here (Requires License)', 'formidable'), '<a href="https://formidablepro.com/knowledgebase/publish-your-forms/" target="_blank">', '</a>', '<a href="'. admin_url('admin.php?page=formidable-entries&frm_action=new&form='. $form->id) .'">', '</a>'); ?></p>
<?php
} else {
?>
<div style="font-size:20px;margin-bottom:10px;"><?php _e('You have not created any forms yet.', 'formidable'); ?></div>
<p style="line-height:200%;"><?php printf( __('To view entries, you must first %1$sbuild a form%2$s', 'formidable'), '<a href="'. admin_url('admin.php?page=formidable&frm_action=new-selection') .'">', '</a>'); ?></p>
<?php
} ?>
</div>
