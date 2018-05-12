<div id="frm-html-tags" class="tabs-panel">
	<p class="howto">
		<?php esc_html_e( 'Use the buttons below to help customize your form HTML.', 'formidable' ) ?>
	</p>

    <ul class="frm_code_list">
    <?php
    $col = 'one';
	$entry_shortcodes = array(
		'id'            => __( 'Field ID', 'formidable' ),
		'key'           => __( 'Field Key', 'formidable' ),
		'field_name'    => __( 'Field Name', 'formidable' ),
		'description'   => __( 'Field Description', 'formidable' ),
		'label_position' => __( 'Label Position', 'formidable' ),
		'required_label' => __( 'Required Label', 'formidable' ),
		'input'         => __( 'Input Field', 'formidable' ),
		'input opt=1'   => array(
			'label'     => __( 'Single Option', 'formidable' ),
			'title'     => __( 'Show a single radio or checkbox option by replacing 1 with the order of the option', 'formidable' ),
		),
		'input label=0' => __( 'Hide Option Label', 'formidable' ),
		'required_class' => array(
			'label'     => __( 'Required Class', 'formidable' ),
			'title'     => __( 'Add class name if field is required', 'formidable' ),
		),
		'error_class'   => array(
			'label'     => __( 'Error Class', 'formidable' ),
			'title'     => __( 'Add class name if field has an error on form submit', 'formidable' ),
		),
	);

	foreach ( $entry_shortcodes as $skey => $sname ) {
    ?>
	<li class="frm_col_<?php echo esc_attr( $col ) ?>">
		<a href="javascript:void(0)" class="show_field_custom_html frmbutton button frm_insert_code <?php echo is_array( $sname ) ? 'frm_help' : ''; ?>" data-code="<?php echo esc_attr( $skey ) ?>" <?php echo is_array( $sname ) ? 'title="' . esc_attr( $sname['title'] ) . '"' : ''; ?>><?php echo esc_html( is_array( $sname ) ? $sname['label'] : $sname ); ?></a>
	</li>
    <?php
		$col = ( 'one' === $col ) ? 'two' : 'one';
		unset( $skey, $sname );
	}
	?>
    </ul>

    <ul class="frm_code_list frm_clear">
        <?php
		$col = 'one';
		$form_codes = array(
			'form_name'  => __( 'Form Name', 'formidable' ),
			'form_description' => __( 'Form Description', 'formidable' ),
			'form_key'   => __( 'Form Key', 'formidable' ),
			'deletelink' => __( 'Delete Entry Link', 'formidable' ),
		);
		foreach ( $form_codes as $skey => $sname ) {
		?>
        <li class="frm_col_<?php echo esc_attr( $col ) ?>">
			<a href="javascript:void(0)" class="show_before_html show_after_html frmbutton button frm_insert_code" data-code="<?php echo esc_attr( $skey ); ?>"><?php echo esc_html( $sname ); ?></a>
    	</li>
		<?php
			$col = ( 'one' === $col ) ? 'two' : 'one';
		}

		foreach ( array(
			'button_label' => __( 'Button Label', 'formidable' ),
			'button_action' => __( 'Button Hook', 'formidable' ),
		) as $skey => $sname ) {
        ?>
        <li class="frm_col_<?php echo esc_attr( $col ) ?>">
			<a href="javascript:void(0)" class="show_submit_html frmbutton button frm_insert_code" data-code="<?php echo esc_attr( $skey ); ?>"><?php echo esc_html( $sname ); ?></a>
    	</li>
        <?php
			$col = ( 'one' === $col ) ? 'two' : 'one';
		}
		?>
    </ul>
</div>
