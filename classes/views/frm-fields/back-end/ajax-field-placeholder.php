<li id="frm_field_id_<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $li_classes ) ?> frm_field_loading" data-fid="<?php echo esc_attr( $field['id'] ) ?>" data-formid="<?php echo esc_attr( 'divider' == $field['type'] ? $field['form_select'] : $field['form_id'] ); ?>" data-ftype="<?php echo esc_attr( $display['type'] ) ?>">
<img src="<?php echo FrmAppHelper::plugin_url() ?>/images/ajax_loader.gif" alt="<?php esc_attr_e( 'Loading', 'formidable' ) ?>" />
<span class="frm_hidden_fdata frm_hidden"><?php echo htmlspecialchars( json_encode( $field ) ) ?></span>
</li>