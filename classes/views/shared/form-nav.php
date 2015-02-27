<div id="titlediv">
<ul class="frm_form_nav">
<li class="first"><a<?php
if ( ( $current_page == 'formidable') && isset($_GET['frm_action']) && (in_array($_GET['frm_action'], array('edit', 'new', 'duplicate'))) ) {
    echo ' class="current_page"';
} ?> href="<?php echo admin_url('admin.php?page=formidable') ?>&amp;frm_action=edit&amp;id=<?php echo $id ?>"><?php _e('Build', 'formidable') ?></a> </li>
<li><a<?php
if ( ( $current_page == 'formidable') && isset($_GET['frm_action']) && $_GET['frm_action'] == 'settings' ) {
    echo ' class="current_page"';
} ?> href="<?php echo admin_url('admin.php?page=formidable') ?>&amp;frm_action=settings&amp;id=<?php echo $id ?>"><?php _e('Settings', 'formidable') ?></a> </li>
<li> <a<?php
if ( $current_page == 'formidable-entries' ) {
    echo ' class="current_page"';
} ?> href="<?php echo admin_url('admin.php?page=formidable') ?>-entries&amp;frm_action=list&amp;form=<?php echo $id ?>"><?php _e('Entries', 'formidable') ?></a></li>
<li> <a<?php
if ( $current_page == 'frm_display' || $pagenow == 'post.php' || $pagenow == 'post-new.php' || $current_page == 'formidable-entry-templates' ) {
    echo ' class="current_page"';
} ?> href="<?php echo esc_url((FrmAppHelper::pro_is_installed() ? admin_url('edit.php?post_type=frm_display') : admin_url( 'admin.php?page=formidable-entry-templates')) ."&form={$id}&show_nav=1"); ?>"><?php _e('Views', 'formidable') ?></a></li>
<li> <a<?php
if ( $current_page == 'formidable' && isset($_GET['frm_action']) && in_array($_GET['frm_action'], array('reports')) ) {
    echo ' class="current_page"';
} ?> href="<?php echo esc_url(admin_url('admin.php?page=formidable') . "&frm_action=reports&form=$id&show_nav=1") ?>"><?php _e('Reports', 'formidable') ?></a></li>
<?php FrmFormsHelper::form_switcher(); ?>
</ul>

<?php if($form && $title == 'show'){ ?>
    <input id="title" type="text" value="<?php echo esc_attr($form->name == '' ? __('(no title)') : $form->name) ?>" readonly="readonly" disabled="disabled" />
<?php } ?>
</div>
