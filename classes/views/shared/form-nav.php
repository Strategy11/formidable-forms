<div id="titlediv">
<ul class="frm_form_nav">
<?php
$class = ' class="first"';
if ( current_user_can( 'frm_edit_forms' ) ) { ?>
	<li<?php echo $class ?>><a<?php FrmAppHelper::select_current_page( 'formidable', $current_page, array( 'edit', 'new', 'duplicate' ) ); ?> href="<?php echo esc_url( admin_url('admin.php?page=formidable&frm_action=edit&id='. $id) ) ?>"><?php _e( 'Build', 'formidable' ) ?></a> </li>
<?php
	$class = '';
}

if ( current_user_can( 'frm_edit_forms' ) ) { ?>
	<li<?php echo $class ?>><a<?php FrmAppHelper::select_current_page( 'formidable', $current_page, array( 'settings' ) ); ?> href="<?php echo esc_url( admin_url('admin.php?page=formidable&frm_action=settings&id='. $id) ) ?>"><?php _e( 'Settings', 'formidable' ) ?></a> </li>
<?php
	$class = '';
} ?>
	<li<?php echo $class ?>> <a<?php FrmAppHelper::select_current_page( 'formidable-entries', $current_page ); ?> href="<?php echo esc_url( admin_url('admin.php?page=formidable-entries&frm_action=list&form='. $id) ) ?>"><?php _e( 'Entries', 'formidable' ) ?></a></li>
<?php
if ( current_user_can( 'frm_edit_displays' ) ) { ?>
	<li> <a<?php
if ( $current_page == 'frm_display' || $pagenow == 'post.php' || $pagenow == 'post-new.php' || $current_page == 'formidable-entry-templates' ) {
    echo ' class="current_page"';
} ?> href="<?php echo esc_url( (FrmAppHelper::pro_is_installed() ? admin_url('edit.php?post_type=frm_display') : admin_url( 'admin.php?page=formidable-entry-templates')) .'&form='. $id .'&show_nav=1' ); ?>"><?php _e( 'Views', 'formidable' ) ?></a></li>
<?php
}

if ( current_user_can( 'frm_view_reports' ) ) { ?>
	<li> <a<?php FrmAppHelper::select_current_page( 'formidable', $current_page, array( 'reports' ) ); ?> href="<?php echo esc_url( admin_url('admin.php?page=formidable&frm_action=reports&form='. $id .'&show_nav=1') ) ?>"><?php _e( 'Reports', 'formidable' ) ?></a></li>
<?php
}

FrmFormsHelper::form_switcher();
?>
</ul>

<?php if ( $form && $title == 'show' ) { ?>
    <input id="title" type="text" value="<?php echo esc_attr($form->name == '' ? __( '(no title)') : $form->name) ?>" readonly="readonly" disabled="disabled" />
<?php } ?>
</div>
