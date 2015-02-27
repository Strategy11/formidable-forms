<div class="general_settings metabox-holder tabs-panel" style="min-height:0px;border-bottom:none;display:<?php echo ($a == 'general_settings') ? 'block' : 'none'; ?>;">
<?php if (!is_multisite() or is_super_admin()){ ?>
    <div class="postbox">
        <div class="inside">
            <p class="alignright"><?php printf(__('%1$sClick here%2$s to get it now', 'formidable'), '<a href="http://formidablepro.com">', '</a>') ?> &#187;</p>
            <p><?php _e('Ready to take your forms to the next level?<br/>Formidable Pro will help you style forms, manage data, and get reports.', 'formidable') ?></p>
            
            <p>Already signed up? <a href="http://formidablepro.com/knowledgebase/manually-install-formidable-pro/" target="_blank"><?php _e('Click here', 'formidable') ?></a> to get installation instructions and download the pro version.</p>
        </div>
    </div>
<?php } ?>
</div>