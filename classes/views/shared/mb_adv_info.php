<div id="taxonomy-linkcategory" class="categorydiv <?php echo esc_attr( $class ); ?>">
	<ul id="frm-nav-tabs" class="frm-nav-tabs <?php echo esc_attr( $settings_tab ? '' : 'frm-compact-nav' ); ?>">
		<li class="frm-tabs">
			<a href="#frm-insert-fields-box" id="frm_insert_fields_tab">
				<?php esc_html_e( 'Fields', 'formidable' ); ?>
			</a>
		</li>
		<?php if ( ! empty( $cond_shortcodes ) ) { ?>
		<li class="hide-if-no-js">
			<a href="#frm-conditionals">
				<?php esc_html_e( 'Conditionals', 'formidable' ); ?>
			</a>
		</li>
		<?php } ?>
		<li class="hide-if-no-js">
			<a href="#frm-adv-info-tab">
				<?php esc_html_e( 'Advanced', 'formidable' ); ?>
			</a>
		</li>
		<?php if ( $settings_tab ) { ?>
		<li id="frm_html_tab" class="hide-if-no-js frm_hidden">
			<a href="#frm-html-tags" id="frm_html_tags_tab">
				<?php esc_html_e( 'HTML Tags', 'formidable' ); ?>
			</a>
		</li>
		<?php } ?>
	</ul>

	<div id="frm-insert-fields-box" class="tabs-panel">
		<ul class="subsubsub">
			<li><a href="javascript:void(0)" class="current frmids"><?php esc_html_e( 'IDs', 'formidable' ); ?></a> |</li>
			<li><a href="javascript:void(0)" class="frmkeys"><?php esc_html_e( 'Keys', 'formidable' ); ?></a></li>
		</ul>
		<ul class="alignleft">
			<li><?php esc_html_e( 'Fields from your form', 'formidable' ); ?>:</li>
		</ul>

		<?php
		FrmAppHelper::show_search_box(
			array(
				'input_id'    => 'field',
				'placeholder' => __( 'Search', 'formidable' ),
				'tosearch'    => 'frm-customize-list',
			)
		);
		?>

		<ul class="frm_code_list frm_customize_field_list frm-full-hover">
		<?php
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $f ) {
				if ( FrmField::is_no_save_field( $f->type ) ) {
					continue;
				}

				if ( $f->type == 'data' && ( ! isset( $f->field_options['data_type'] ) || $f->field_options['data_type'] == 'data' || $f->field_options['data_type'] == '' ) ) {
					continue;
				}

				FrmFormsHelper::insert_opt_html(
					array(
						'id'   => $f->id,
						'key'  => $f->field_key,
						'name' => $f->name,
						'type' => $f->type,
						'class' => 'frm-customize-list',
					)
				);

				do_action( 'frm_field_code_tab', array( 'field' => $f ) );

				if ( $f->type == 'user_id' ) {
					$uid = $f->id;
				}
				unset( $f );
			}
		}
		?>
		</ul>
	</div>

	<?php if ( ! empty( $cond_shortcodes ) ) { ?>
	<div id="frm-conditionals" class="tabs-panel">
		<ul class="subsubsub">
			<li><a href="javascript:void(0)" class="current frmids"><?php esc_html_e( 'IDs', 'formidable' ); ?></a> |</li>
			<li><a href="javascript:void(0)" class="frmkeys"><?php esc_html_e( 'Keys', 'formidable' ); ?></a></li>
		</ul>
		<?php
		FrmAppHelper::show_search_box(
			array(
				'input_id'    => 'field',
				'placeholder' => __( 'Search', 'formidable' ),
				'tosearch'    => 'frm-conditional-list',
			)
		);
		?>
		<ul class="frm_code_list frm-full-hover">
			<?php
			if ( ! empty( $fields ) ) {
				foreach ( $fields as $f ) {
					if ( FrmField::is_no_save_field( $f->type ) || ( $f->type == 'data' && ( ! isset( $f->field_options['data_type'] ) || $f->field_options['data_type'] == 'data' || $f->field_options['data_type'] == '' ) ) ) {
						continue;
					}

					FrmFormsHelper::insert_opt_html(
						array(
							'id'        => 'if ' . $f->id . ']' . __( 'Conditional text here', 'formidable' ) . '[/if ' . $f->id,
							'id_label'  => 'if ' . $f->id,
							'key'       => 'if ' . $f->field_key . ']' . __( 'Conditional text here', 'formidable' ) . '[/if ' . $f->field_key,
							'key_label' => 'if ' . $f->field_key,
							'name'      => $f->name,
							'type'      => $f->type,
							'class'     => 'frm-conditional-list',
						)
					);

					unset( $f );
				}
			}
			?>
		</ul>

		<p class="howto"><?php esc_html_e( 'Click a button below to insert sample logic into your view', 'formidable' ); ?></p>
		<ul class="frm_code_list">
			<?php
			$col = 'one';
			foreach ( $cond_shortcodes as $skey => $sname ) {
				?>
			<li class="frm_col_<?php echo esc_attr( $col ); ?>">
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
		<?php
		FrmAppHelper::show_search_box(
			array(
				'input_id'    => 'advanced',
				'placeholder' => __( 'Search', 'formidable' ),
				'tosearch'    => 'frm-advanced-list',
			)
		);
		?>
		<ul class="frm_code_list frm-full-hover">
		<?php
		foreach ( $entry_shortcodes as $skey => $sname ) {
			if ( empty( $skey ) ) {
				echo '<li class="clear frm_block"></li>';
				continue;
			}

			$classes = 'frm-advanced-list';
			$classes .= ( in_array( $skey, array( 'siteurl', 'sitename', 'entry_count' ) ) ) ? ' show_before_content show_after_content' : '';
			$classes .= ( strpos( $skey, 'default-' ) === 0 ) ? ' hide_frm_not_email_subject' : '';

			FrmFormsHelper::insert_code_html(
				array(
					'code'  => $skey,
					'label' => $sname,
					'class' => $classes,
				)
			);

			unset( $skey, $sname, $classes );
		}
		?>
		</ul>
		<?php

		foreach ( $advanced_helpers as $helper_type => $helper ) {
			if ( 'user_id' === $helper_type && ! isset( $uid ) ) {
				continue;
			}

			if ( isset( $helper['heading'] ) && ! empty( $helper['heading'] ) ) {
				?>
				<p class="howto"><?php echo esc_html( $helper['heading'] ); ?></p>
				<?php
			}
			?>
			<ul class="frm_code_list frm-full-hover">
			<?php
			foreach ( $helper['codes'] as $code => $code_label ) {
				if ( isset( $uid ) ) {
					$code = str_replace( '|user_id|', $uid, $code );
				} else {
					$code = str_replace( '|user_id|', 'x', $code );
				}
				$include_x = strpos( $code, ' ' ) ? '' : 'x ';

				if ( ! is_array( $code_label ) ) {
					$code_label = array(
						'label' => $code_label,
					);
				}

				FrmFormsHelper::insert_code_html(
					array(
						'code'  => $include_x . $code,
						'label' => $code_label['label'],
						'title' => isset( $code_label['title'] ) ? $code_label['title'] : '',
						'class' => 'frm-advanced-list',
					)
				);

				unset( $code );
			}
			?>
			</ul>
			<?php
		}
		?>
	</div>

	<?php
	if ( $settings_tab ) {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/mb_html_tab.php' );
	}
	?>
</div>
