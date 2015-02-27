<div class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2><?php _e('Import/Export', 'formidable'); ?></h2>

    <?php include(FrmAppHelper::plugin_path() .'/classes/views/shared/errors.php'); ?>
    <div id="poststuff" class="metabox-holder">
    <div id="post-body">
    <div id="post-body-content">

    <div class="postbox ">
    <h3 class="hndle"><span><?php _e('Import', 'formidable') ?></span></h3>
    <div class="inside">
        <p class="howto"><?php echo apply_filters('frm_upload_instructions1', __('Upload your Formidable XML file to import forms into this site. If your imported form key and creation date match a form on your site, that form will be updated.', 'formidable')) ?></p>
        <br/>
        <form enctype="multipart/form-data" method="post">
            <input type="hidden" name="frm_action" value="import_xml" />
            <?php wp_nonce_field('import-xml-nonce', 'import-xml'); ?>
            <p><label><?php echo apply_filters('frm_upload_instructions2', __('Choose a Formidable XML file', 'formidable')) ?> (<?php printf(__('Maximum size: %s', 'formidable'), ini_get('upload_max_filesize')) ?>)</label>
            <input type="file" name="frm_import_file" size="25" />
            </p>
            
            <?php do_action('frm_csv_opts', $forms) ?>

            <p class="submit">
                <input type="submit" value="<?php _e('Upload file and import', 'formidable') ?>" class="button-primary" />
            </p>
        </form>
    </div>
    </div>
    
    
    <div class="postbox">
    <h3 class="hndle"><span><?php _e('Export', 'formidable') ?></span></h3>
    <div class="inside with_frm_style">
        <form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" id="frm_export_xml">
            <input type="hidden" name="action" value="frm_export_xml" />
            <?php wp_nonce_field('export-xml-nonce', 'export-xml'); ?>
            
            <table class="form-table">
                <?php if (count($export_format) == 1) { 
                    reset($export_format); ?>
                <tr><td colspan="2"><input type="hidden" name="format" value="<?php echo key($export_format) ?>" /></td></tr>
                <?php } else { ?>
                <tr class="form-field">
                    <th scope="row"><label for="format"><?php _e('Export Format', 'formidable'); ?></label></th>
                    <td>
                        <select name="format">
                        <?php foreach ( $export_format as $t => $type ){ ?>
                            <option value="<?php echo $t ?>" data-support="<?php echo esc_attr($type['support']) ?>" <?php echo isset($type['count']) ? 'data-count="'. esc_attr($type['count']) .'"' : ''; ?>><?php echo isset($type['name']) ? $type['name'] : $t ?></option>
                        <?php } ?>
                        </select>
                        
                        <ul class="frm_hidden csv_opts export-filters">
                            <li>
                            <label for="csv_format"><?php _e('Format', 'formidable') ?>:</label>
                            <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php _e('If your CSV special characters are not working correctly, try a different formatting option.', 'formidable') ?>"></span>
                            <select name="csv_format">
                            <option value="UTF-8" <?php selected($csv_format, 'UTF-8') ?>>UTF-8</option>
                            <option value="ISO-8859-1" <?php selected($csv_format, 'ISO-8859-1'); ?>>ISO-8859-1</option>
                            <option value="windows-1256" <?php selected($csv_format, 'windows-1256'); ?>>windows-1256</option>
                            <option value="windows-1251" <?php selected($csv_format, 'windows-1251'); ?>>windows-1251</option>
                            <option value="macintosh" <?php selected($csv_format, 'macintosh'); ?>><?php _e('Macintosh', 'formidable') ?></option>
                            </select>
                            </li>
                        
                            <li><label for="csv_col_sep"><?php _e('Column separation', 'formidable') ?>:</label>
                            <input name="csv_col_sep" value="," type="text" style="width:45px;" /></li>
                        </ul>
                    </td>
                </tr>
                <?php } ?>
                
                <?php if (count($export_types) == 1) { 
                    reset($export_types); ?>
                <tr><td colspan="2"><input type="hidden" name="type[]" value="<?php echo key($export_types) ?>" /></td></tr>
                <?php } else { ?>
                <tr class="form-field">
                    <th scope="row"><label><?php _e('Data to Export', 'formidable'); ?></label></th>
                    <td>
                        <?php _e('Include the following in the export file', 'formidable'); ?>:<br/>
                        <?php foreach ( $export_types as $t => $type ){ ?>
                        <label><input type="checkbox" name="type[]" value="<?php echo $t ?>"/> <?php echo $type ?></label> &nbsp;
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                <tr class="form-field">
                    <th scope="row"><label><?php _e('Select Form(s)', 'formidable'); ?></label></th>
                    <td>
                        <select name="frm_export_forms[]" multiple="multiple" class="frm_chzn">
                        <?php foreach($forms as $form){ ?>
                            <option value="<?php echo $form->id ?>"><?php 
                        echo ($form->name == '') ? '(no title)' : $form->name;
                        echo ' &mdash; '. $form->form_key;
                        if ( $form->is_template && $form->default_template ) {
                            echo ' '. __('(default template)', 'formidable');
                        } else if ( $form->is_template ) { 
                            echo ' '. __('(template)', 'formidable');
                        }
                        ?></option>
                        <?php } ?>
                        </select>
                        <p class="howto"><?php _e('Hold down the CTRL/Command button to select multiple forms', 'formidable'); ?></p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" value="<?php _e('Export Selection', 'formidable') ?>" class="button-primary" />
            </p>
        </form>

    </div>
    </div>


    </div>
    </div>
    </div>
</div>
