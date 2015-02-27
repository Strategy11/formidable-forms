<?php

class FrmStatisticsController{

    public static function menu(){
        add_submenu_page('formidable', 'Formidable | '. __('Views', 'formidable'), '<span style="opacity:.5;filter:alpha(opacity=50);">'. __('Views', 'formidable') .'</span>', 'administrator', 'formidable-entry-templates', 'FrmStatisticsController::list_displays');
    }

    public static function list_reports(){
        add_filter('frm_form_stop_action_reports', '__return_true');
        $form = FrmAppHelper::get_param('form', false);
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-statistics/list.php');
    }

    public static function list_displays(){
        $form = FrmAppHelper::get_param('form', false);
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-statistics/list_displays.php');
    }

}
