<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
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
		<?php
		if ( count( $fields ) > 8 ) {
			FrmAppHelper::show_search_box(
				array(
					'input_id'    => 'field',
					'placeholder' => __( 'Search', 'formidable' ),
					'tosearch'    => 'frm-customize-list',
				)
			);
		}
		?>

		<ul class="subsubsub">
			<li><a href="javascript:void(0)" class="current frmids"><?php esc_html_e( 'IDs', 'formidable' ); ?></a> |</li>
			<li><a href="javascript:void(0)" class="frmkeys"><?php esc_html_e( 'Keys', 'formidable' ); ?></a></li>
		</ul>
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

	<?php
	$show_logic = ! empty( $cond_shortcodes ) && ! empty( $fields );
	if ( $show_logic ) {
		?>
	<div id="frm-conditionals" class="tabs-panel">
		<div class="frmcenter">
			<label class="frm_toggle frm_toggle_long">
				<input type="checkbox" value="id" checked="checked" id="frm-id-key-condition" />
				<span class="frm_toggle_slider"></span>
				<span class="frm_toggle_on">
					<?php esc_html_e( 'ID', 'formidable' ); ?>
				</span>
				<span class="frm_toggle_off">
					<?php esc_html_e( 'Key', 'formidable' ); ?>
				</span>
			</label>
		</div>

		<div class="frm_grid_container frm-fields">
			<div class="frm1 frm_form_field" style="line-height:27px;">
				<label for="frm-id-condition">
					<?php esc_html_e( 'IF', 'formidable' ); ?>
				</label>
				</div>

			<div class="frm11 frm_form_field">
				<select id="frm-id-condition" class="frm-build-logic">
					<option value="x">
						<?php esc_html_e( 'Select a Field', 'formidable' ); ?>
					</option>
					<?php
					foreach ( $fields as $f ) {
						?>
						<option value="<?php echo esc_attr( $f->id ); ?>">
							<?php echo esc_html( $f->name ); ?>
						</option>
						<?php
					}
					?>
				</select>
				<select id="frm-key-condition" class="frm_hidden frm-build-logic">
					<option value="x">
						<?php esc_html_e( 'Select a Field', 'formidable' ); ?>
					</option>
					<?php
					foreach ( $fields as $f ) {
						?>
						<option value="<?php echo esc_attr( $f->field_key ); ?>">
							<?php echo esc_html( $f->name ); ?>
						</option>
						<?php
					}
					?>
				</select>
			</div>

			<div class="frm1 frm_form_field"></div>
			<div class="frm11 frm_form_field">
				<select id="frm-is-condition" class="frm-build-logic">
					<?php
					foreach ( $cond_shortcodes as $skey => $sname ) {
						?>
						<option value="<?php echo esc_attr( $skey ); ?>">
							<?php echo esc_html( $sname ); ?>
						</option>
						<?php
						unset( $skey, $sname );
					}
					?>
				</select>
			</div>

			<div class="frm1 frm_form_field"></div>
			<div class="frm11 frm_form_field">
				<input id="frm-text-condition" type="text" value="" placeholder="<?php esc_attr_e( 'A blank value', 'formidable' ); ?>" class="frm-build-logic" />
			</div>
			<h3 class="frm-with-line">
				<span><?php esc_html_e( 'Click to Insert', 'formidable' ); ?></span>
			</h3>
			<ul class="frm_code_list frm-full-hover frmcenter">
				<li>
					<a href="#" id="frm-insert-condition" class="frm_insert_code" data-code="if x equals='']<?php esc_attr_e( 'Conditional content here', 'formidable' ); ?>[/if x">
						[if x equals=""][/if x]
					</a>
				</li>
			</ul>
			<br/>
		</div>
	</div>
		<?php
	}
	?>

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

		foreach ( $advanced_helpers as $helper_type => $helper ) {
			if ( 'user_id' === $helper_type && ! isset( $uid ) ) {
				continue;
			}

			if ( isset( $helper['heading'] ) && ! empty( $helper['heading'] ) ) {
				?>
				<li style="padding:0 25px;">
					<p class="howto"><?php echo esc_html( $helper['heading'] ); ?></p>
				</li>
				<?php
			}

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
		}
		?>
		</ul>
	</div>

	<?php
	if ( $settings_tab ) {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/mb_html_tab.php' );
	}
	?>
</div>
