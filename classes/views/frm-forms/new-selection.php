<div class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2><?php _e('Build New Form', 'formidable') ?></h2>
    
    <div class="clear"></div>
    <div id="menu-management" class="nav-menus-php frm-menu-boxes">
        <div class="menu-edit">
        <div id="nav-menu-header"><div class="major-publishing-actions" style="padding:8px 0;">
            <div style="font-size:15px;background:transparent;" class="search"><?php _e('Pre-Built Form', 'formidable') ?></div>
        </div></div>
            
        <form method="get">
            <div id="post-body">
            <p><?php _e('Select a template to generate your form.', 'formidable'); ?></p>
            <input type="hidden" name="frm_action" value="duplicate" />
            <input type="hidden" name="page" value="formidable" />
            <select name="id">
                <?php foreach ($all_templates as $temp){ ?>
                <option value="<?php echo $temp->id ?>"><?php echo FrmAppHelper::truncate($temp->name, 40) ?></option>
                <?php } ?>
            </select><br/>
            </div>
            <div id="nav-menu-footer">
            <div class="major-publishing-actions"><input type="submit" class="button-primary" value="<?php _e('Create', 'formidable') ?>" /></div>

            <div class="clear"></div>
            </div>
        </form>
        </div>
    </div>
    
    <div id="menu-management" class="nav-menus-php frm-menu-boxes">
        <div class="menu-edit">
        <div id="nav-menu-header"><div class="major-publishing-actions" style="padding:8px 0;">
            <div style="font-size:15px;background:transparent;" class="search"><?php _e('Blank Form', 'formidable') ?></div>
        </div></div>
        
        <form method="get">
            <div id="post-body">
            <p style="padding-bottom:26px;"><?php _e('Start with a blank form and build anything.', 'formidable'); ?></p>
            <input type="hidden" name="frm_action" value="new" />
            <input type="hidden" name="page" value="formidable" />
            </div>
            <div id="nav-menu-footer">
            <div class="major-publishing-actions"><input type="submit" class="button-primary" value="<?php _e('Create', 'formidable') ?>" /></div>

            <div class="clear"></div>
            </div>
        </form>
        </div>
        
        <div class="clear"></div>
    </div>
    
    <div class="clear"></div>
</div>