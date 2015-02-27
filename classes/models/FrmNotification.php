<?php
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

class FrmNotification{
    function __construct(){
        add_action('frm_after_create_entry', array(&$this, 'entry_created'), 10, 2);
    }
    
    function entry_created($entry_id, $form_id){
        if (apply_filters('frm_stop_standard_email', false, $entry_id)) return;
        global $frm_entry, $frm_entry_meta;
        
        $entry = $frm_entry->getOne($entry_id, true);
        $frm_form = new FrmForm();
        $form = $frm_form->getOne($form_id);
        $values = $frm_entry_meta->getAll("it.item_id = $entry_id", " ORDER BY fi.field_order");
        
        
        if(isset($form->options['notification']))
            $notification = reset($form->options['notification']);
        else
            $notification = $form->options;
        
        // Set the from and to email names and addresses
        $to_email = $notification['email_to']; 
        if ( empty($to_email) ) {
            $to_email = '[admin_email]';
        }

        $to_emails = explode(',', $to_email);
        
        $reply_to = $reply_to_name = '';
        
        foreach ($values as $value) {
            $val = apply_filters('frm_email_value', maybe_unserialize($value->meta_value), $value, $entry);
            if (is_array($val))
                $val = implode(', ', $val);

            if(isset($notification['reply_to']) and (int)$notification['reply_to'] == $value->field_id and is_email($val))
                $reply_to = $val;

            if(isset($notification['reply_to_name']) and (int)$notification['reply_to_name'] == $value->field_id)
                $reply_to_name = $val;
        }
        
            
        if ( empty($reply_to) && $notification['reply_to'] == 'custom' ) {
            $reply_to = $notification['cust_reply_to'];
        }
        
        if ( empty($reply_to_name) && $notification['reply_to_name'] == 'custom' ){
            $reply_to_name = $notification['cust_reply_to_name'];
        }
        
        // Set the email message
        $plain_text = (isset($notification['plain_text']) && $notification['plain_text']) ? true : false;
        $mail_body = isset($notification['email_message']) ? $notification['email_message'] : '';
        
        $mail_body = FrmEntriesHelper::replace_default_message($mail_body, array(
            'id' => $entry->id, 'entry' => $entry, 'plain_text' => $plain_text,
            'user_info' => (isset($notification['inc_user_info']) ? $notification['inc_user_info'] : false),
        ) );
        
        // Set the subject
        $subject = isset($notification['email_subject']) ? $notification['email_subject'] : '';
        if ( empty($subject) ) {
            $frm_blogname = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );
            $subject = sprintf(__('%1$s Form submitted on %2$s', 'formidable'), $form->name, $frm_blogname);
        }
        
        // Send the emails now
        foreach ( (array) $to_emails as $to_email) {
            $this->send_notification_email(trim($to_email), $subject, $mail_body, $reply_to, $reply_to_name, $plain_text);
        }
    }
  
    function send_notification_email($to_email, $subject, $message, $reply_to='', $reply_to_name='', $plain_text=true, $attachments=array()){
        $content_type   = ($plain_text) ? 'text/plain' : 'text/html';
        $reply_to_name  = ($reply_to_name == '') ? wp_specialchars_decode( get_option('blogname'), ENT_QUOTES ) : $reply_to_name; //senders name
        $reply_to       = ($reply_to == '' or $reply_to == '[admin_email]') ? get_option('admin_email') : $reply_to; //senders e-mail address
        
        if($to_email == '[admin_email]')
            $to_email = get_option('admin_email');
        
        $charset        = get_option('blog_charset');
        $recipient      = $to_email; //recipient
        $header         = array();
        $header[]       = 'From: "'. $reply_to_name .'" <'. $reply_to .'>';
        $header[]       = 'Reply-To: '. $reply_to;
        $header[]       = 'Content-Type: '. $content_type .'; charset="'. $charset . '"';
        $subject        = wp_specialchars_decode(strip_tags(stripslashes($subject)), ENT_QUOTES );
        
        $message        = do_shortcode($message);
        $message        = wordwrap($message, 70, "\r\n"); //in case any lines are longer than 70 chars
        if($plain_text)
            $message    = wp_specialchars_decode(strip_tags($message), ENT_QUOTES );

        $header         = apply_filters('frm_email_header', $header, compact('to_email', 'subject'));
        
        if ( apply_filters('frm_encode_subject', 1, $subject ) ) {
            $subject = '=?'. $charset .'?B?'. base64_encode($subject) .'?=';
        }
        
        remove_filter('wp_mail_from', 'bp_core_email_from_address_filter' );
        remove_filter('wp_mail_from_name', 'bp_core_email_from_name_filter');
        
        if (!wp_mail($recipient, $subject, $message, $header, $attachments)){
            $header = 'From: "'. $reply_to_name .'" <'. $reply_to .'>'. "\r\n";
            mail($recipient, $subject, $message, $header);
        }

        do_action('frm_notification', $recipient, $subject, $message);
    }

}
