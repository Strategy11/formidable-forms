<div id="taxonomy-linkcategory" class="categorydiv <?php echo esc_attr( $class ) ?>">
	<ul id="category-tabs" class="category-tabs frm-category-tabs">
		<li class="tabs"><a href="#frm-insert-fields-box" id="frm_insert_fields_tab"><?php esc_html_e( 'Fields', 'formidable' ); ?></a></li>
		<?php if ( ! empty( $cond_shortcodes ) ) { ?>
		<li class="hide-if-no-js"><a href="#frm-conditionals"><?php esc_html_e( 'Conditionals', 'formidable' ); ?></a></li>
		<?php } ?>
		<li class="hide-if-no-js"><a href="#frm-adv-info-tab"><?php esc_html_e( 'Advanced', 'formidable' ); ?></a></li>
		<?php if ( $settings_tab ) { ?>
		<li id="frm_html_tab" class="hide-if-no-js frm_hidden"><a href="#frm-html-tags" id="frm_html_tags_tab" ><?php esc_html_e( 'HTML Tags', 'formidable' ); ?></a></li>
		<?php } ?>
	</ul>

	<div id="frm-insert-fields-box" class="tabs-panel">
		<ul class="subsubsub">
			<li><a href="javascript:void(0)" class="current frmids"><?php esc_html_e( 'IDs', 'formidable' ) ?></a> |</li>
			<li><a href="javascript:void(0)" class="frmkeys"><?php esc_html_e( 'Keys', 'formidable' ) ?></a></li>
		</ul>
		<ul class="alignleft"><li><?php esc_html_e( 'Fields from your form', 'formidable' ) ?>:</li>
</ul>
		<ul class="frm_code_list" id="frm_customize_search">
			<li><input type="search" id="frm_field_search" name="frm_field_search" placeholder="<?php esc_html_e( 'Search', 'formidable' ) ?>"></li>
		</ul>
		<ul class="frm_code_list frm_full_width frm_customize_field_list">
		<?php
		if ( ! empty( $fields ) ) {
			global $wpdb;
			$linked_forms[] = array();

			foreach ( $fields as $f ) {
				if ( FrmField::is_repeating_field( $f ) ) {
					$repeat_field = $f->id;
				}

				if ( FrmField::is_no_save_field( $f->type ) ) {
					continue;
				}

				if ( $f->type == 'data' && ( ! isset( $f->field_options['data_type'] ) || $f->field_options['data_type'] == 'data' || $f->field_options['data_type'] == '' ) ) {
					continue;
				}

				FrmAppHelper::insert_opt_html(
					array(
						'id'   => $f->id,
						'key'  => $f->field_key,
						'name' => $f->name,
						'type' => $f->type,
					)
				);

				if ( $f->type == 'data' ) {
					//get all fields from linked form
					if ( isset( $f->field_options['form_select'] ) && is_numeric( $f->field_options['form_select'] ) ) {
						$linked_form = FrmDb::get_var( $wpdb->prefix . 'frm_fields', array( 'id' => $f->field_options['form_select'] ), 'form_id' );
                        if ( ! in_array( $linked_form, $linked_forms ) ) {
                            $linked_forms[] = $linked_form;
							$linked_fields = FrmField::getAll(
								array(
									'fi.type not' => FrmField::no_save_fields(),
									'fi.form_id'  => $linked_form,
								)
							);
                            $ldfe = '';
							if ( $linked_fields ) {
								foreach ( $linked_fields as $linked_field ) {
									FrmAppHelper::insert_opt_html(
										array(
											'id'   => $f->id . ' show=' . $linked_field->id,
											'key'  => $f->field_key . ' show=' . $linked_field->field_key,
											'name' => $linked_field->name,
											'type' => $linked_field->type,
										)
									);

                                    $ldfe = $linked_field->id;
									unset( $linked_field );
                                }
                            }
                        }
                    }
                    $dfe = $f->id;
        	    }
				unset( $f );
                }
			}
			?>
        </ul>

		<p class="howto">
			<?php esc_html_e( 'Click a button below to insert extra values from form entries or your site settings.', 'formidable' ) ?>
		</p>

        <?php esc_html_e( 'Helpers', 'formidable' ) ?>:
        <ul class="frm_code_list">
        <?php
        $col = 'one';
		foreach ( $entry_shortcodes as $skey => $sname ) {
			if ( empty( $skey ) ) {
                 $col = 'one';
                 echo '<li class="clear frm_block"></li>';
                 continue;
            }

			$classes = ( in_array( $skey, array( 'siteurl', 'sitename', 'entry_count' ) ) ) ? 'show_before_content show_after_content' : '';
			$classes .= ( strpos( $skey, 'default-' ) === 0 ) ? 'hide_frm_not_email_subject' : '';
        ?>
        <li class="frm_col_<?php echo esc_attr( $col ) ?>">
			<a href="javascript:void(0)" class="frmbutton button <?php echo esc_attr( $classes ); ?> frm_insert_code" data-code="<?php echo esc_attr( $skey ) ?>">
				<?php echo esc_html( $sname ) ?>
			</a>
        </li>
        <?php
            $col = ( $col == 'one' ) ? 'two' : 'one';
			unset( $skey, $sname, $classes );
        }
        ?>
        </ul>
		<div class="clear"></div>
	</div>

	<?php if ( ! empty( $cond_shortcodes ) ) { ?>
	<div id="frm-conditionals" class="tabs-panel">
	    <ul class="subsubsub">
	        <li><a href="javascript:void(0)" class="current frmids"><?php esc_html_e( 'IDs', 'formidable' ) ?></a> |</li>
	        <li><a href="javascript:void(0)" class="frmkeys"><?php esc_html_e( 'Keys', 'formidable' ) ?></a></li>
	    </ul>
	    <ul class="alignleft"><li><?php esc_html_e( 'Fields from your form', 'formidable' ) ?>:</li></ul>
	    <ul class="frm_code_list frm_full_width">
			<?php
			if ( ! empty( $fields ) ) {
		        foreach ( $fields as $f ) {
					if ( FrmField::is_no_save_field( $f->type ) || ( $f->type == 'data' && ( ! isset( $f->field_options['data_type'] ) || $f->field_options['data_type'] == 'data' || $f->field_options['data_type'] == '' ) ) ) {
                        continue;
                }
            ?>
                <li>
                    <a href="javascript:void(0)" class="frmids alignright frm_insert_code" data-code="if <?php echo esc_attr( $f->id ) ?>]<?php esc_attr_e( 'Conditional text here', 'formidable' ) ?>[/if <?php echo esc_attr( $f->id ) ?>">[if <?php echo (int) $f->id ?>]</a>
					<a href="javascript:void(0)" class="frmkeys alignright frm_insert_code" data-code="if <?php echo esc_attr( $f->field_key ); ?>]something[/if <?php echo esc_attr( $f->field_key ); ?>">[if <?php echo FrmAppHelper::truncate( $f->field_key, 10 ); // WPCS: XSS ok. ?>]</a>
					<a href="javascript:void(0)" class="frm_insert_code" data-code="<?php echo esc_attr( $f->id ); ?>"><?php echo FrmAppHelper::truncate( $f->name, 60 ); // WPCS: XSS ok. ?></a>
                </li>
                <?php

				if ( $f->type == 'user_id' ) {
					$uid = $f;
				} else if ( $f->type == 'file' ) {
					$file = $f;
				}
				unset( $f );
			}
		}
		?>
        </ul>

        <p class="howto"><?php esc_html_e( 'Click a button below to insert sample logic into your view', 'formidable' ) ?></p>
        <ul class="frm_code_list">
        <?php
        $col = 'one';
		foreach ( $cond_shortcodes as $skey => $sname ) {
	    ?>
	    <li class="frm_col_<?php echo esc_attr( $col ) ?>">
			<a href="javascript:void(0)" class="frmbutton button frm_insert_code" data-code="if x <?php echo esc_attr( $skey ); ?>][/if x"><?php echo esc_html( $sname ); ?></a>
	    </li>
	    <?php
	        $col = ( $col == 'one' ) ? 'two' : 'one';
			unset( $skey, $sname );
	    }
        ?>
        </ul>
	</div>
	<?php } ?>

	<div id="frm-adv-info-tab" class="tabs-panel">
		<p class="howto">
			<?php esc_html_e( 'Customize the field values with the following parameters. Click to see a sample.', 'formidable' ) ?>
		</p>
		<ul class="frm_code_list">
        <?php
        $col = 'one';
		foreach ( $adv_shortcodes as $skey => $sname ) {
	    ?>
	    <li class="frm_col_<?php echo esc_attr( $col ) ?>">
			<a href="javascript:void(0)" class="frmbutton button frm_insert_code <?php echo is_array( $sname ) ? 'frm_help' : ''; ?>" data-code="x <?php echo esc_attr( $skey ) ?>" <?php echo is_array( $sname ) ? 'title="' . esc_attr( $sname['title'] ) . '"' : ''; ?>>
				<?php echo esc_html( is_array( $sname ) ? $sname['label'] : $sname ); ?>
			</a>
	    </li>
		<?php
			$col = ( $col == 'one' ) ? 'two' : 'one';
	        unset( $skey, $sname );
		}
		?>
		<?php if ( isset( $file ) ) { ?>
        <li class="frm_col_<?php echo esc_attr( $col ) ?>">
			<a href="javascript:void(0)" class="frmbutton button frm_insert_code" data-code="<?php echo esc_attr( $file->id ); ?> show_image=1"><?php esc_html_e( 'Show image', 'formidable' ); ?></a>
	    </li>
		<?php $col = ( $col == 'one' ? 'two' : 'one' ); ?>
		<li class="frm_col_<?php echo esc_attr( $col ) ?>">
			<a href="javascript:void(0)" class="frmbutton button frm_insert_code" data-code="<?php echo esc_attr( $file->id ); ?> show=id"><?php esc_html_e( 'Image ID', 'formidable' ); ?></a>
	    </li>
		<?php $col = ( $col == 'one' ? 'two' : 'one' ); ?>
		<li class="frm_col_<?php echo esc_attr( $col ) ?>">
			<a href="javascript:void(0)" class="frmbutton button frm_insert_code" data-code="<?php echo esc_attr( $file->id ); ?> show_filename=1"><?php esc_html_e( 'Image Name', 'formidable' ); ?></a>
	    </li>
	    <?php } ?>
        </ul>

        <div class="clear"></div>
        <?php

		if ( isset( $uid ) && ! empty( $user_fields ) ) {
			$col = 'one';
			?>
        <p class="howto"><?php esc_html_e( 'Insert user information', 'formidable' ); ?></p>
        <ul class="frm_code_list">
        <?php foreach ( $user_fields as $uk => $uf ) { ?>
            <li class="frm_col_<?php echo esc_attr( $col ) ?>">
				<a href="javascript:void(0)" class="frmbutton button frm_insert_code" data-code="<?php echo esc_attr( $uid->id . ' show="' . $uk . '"' ) ?>"><?php echo esc_html( $uf ) ?></a>
    	    </li>
			<?php
			$col = ( $col == 'one' ) ? 'two' : 'one';
			unset( $uf, $uk );
		}
		unset( $uid );
		?>
        </ul>
		<?php
        }

		if ( isset( $repeat_field ) ) {
		?>
        <div class="clear"></div>
        <p class="howto"><?php esc_html_e( 'Repeating field options', 'formidable' ) ?></p>
            <ul class="frm_code_list">
        	    <li class="frm_col_one">
					<a href="javascript:void(0)" class="frmbutton button frm_insert_code" data-code="<?php echo esc_attr( 'foreach ' . $repeat_field . '][/foreach' ) ?>"><?php esc_html_e( 'For Each', 'formidable' ); ?></a>
        	    </li>
            </ul>
        <?php
        }

		if ( isset( $dfe ) ) {
        ?>

        <div class="clear"></div>
        <p class="howto"><?php esc_html_e( 'Dynamic field options', 'formidable' ); ?></p>
            <ul class="frm_code_list">
        	    <li class="frm_col_one">
					<a href="javascript:void(0)" class="frmbutton button frm_insert_code" data-code="<?php echo esc_attr( $dfe . ' show="created-at"' ) ?>"><?php esc_html_e( 'Creation Date', 'formidable' ); ?></a>
        	    </li>
				<?php if ( isset( $ldfe ) ) { ?>
        	    <li class="frm_col_two">
					<a href="javascript:void(0)" class="frmbutton button frm_insert_code" data-code="<?php echo esc_attr( $dfe . ' show="' . $ldfe . '"' ) ?>"><?php esc_html_e( 'Field From Entry', 'formidable' ); ?></a>
        	    </li>
        	    <?php } ?>
            </ul>
        <?php } ?>

	</div>

    <?php
    if ( $settings_tab ) {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/mb_html_tab.php' );
    }
    ?>
</div>
