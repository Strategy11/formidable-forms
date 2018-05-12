<div class="frmcenter frm_no_entries_form">
<?php if ( $form && isset( $form->options['no_save'] ) && $form->options['no_save'] ) { ?>
<h3><?php esc_html_e( 'This form is not set to save any entries.', 'formidable' ) ?></h3>
<p><?php printf( esc_html__( 'If you would like to save entries in this form, go to the %1$sform Settings%2$s page %3$s and uncheck the "Do not store any entries submitted from this form" box.', 'formidable' ), '<a href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $form->id ) ) . '">', '</a>', '</br>' ) ?></p>
<?php } elseif ( $form ) { ?>
<div class="frm_no_entries_header"><?php printf( esc_html__( 'No Entries for form: %s', 'formidable' ), esc_html( $form->name ) ); ?></div>
<p class="frm_no_entries_text"><?php printf( esc_html__( 'See the %1$sform documentation%2$s for instructions on publishing your form', 'formidable' ), '<a href="https://formidableforms.com/knowledgebase/publish-your-forms/" target="_blank">', '</a>' ); ?></p>
<?php } else { ?>
<div class="frm_no_entries_header"><?php esc_html_e( 'You have not created any forms yet.', 'formidable' ); ?></div>
<p class="frm_no_entries_text"><?php printf( esc_html__( 'To view entries, you must first %1$sbuild a form%2$s', 'formidable' ), '<a href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=new' ) ) . '">', '</a>' ); ?></p>
<?php } ?>
</div>
