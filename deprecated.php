<?php

// 2.0
if ( ! isset($frm_vars['pro_is_installed']) ) {
    $frm_vars['pro_is_installed'] = false;
}

// Instansiate Models
global $frmdb;
global $frm_field;
global $frm_form;
global $frm_entry;
global $frm_entry_meta;

$frmdb              = new FrmDb();
$frm_field          = new FrmField();
$frm_form           = new FrmForm();
$frm_entry          = new FrmEntry();
$frm_entry_meta     = new FrmEntryMeta();
