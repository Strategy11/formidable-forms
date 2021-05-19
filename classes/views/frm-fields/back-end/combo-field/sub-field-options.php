<?php
/**
 * Sub field options
 *
 * @package Formidable
 * @since 4.10.02
 *
 * @var FrmFieldCombo $this            Field type object.
 * @var array         $field           Field array.
 * @var array         $sub_field       Sub field array.
 * @var array         $default_value   Default value of all sub fields.
 * @var array         $wrapper_classes CSS classes of wrapper element of subfield options.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_id  = $field['id'];
$field_key = $field['field_key'];
$uniq_str  = $sub_field['name'] . '_' . $field_id;
$labels    = $this->get_built_in_option_labels();
?>
<div
	class="<?php echo esc_attr( $wrapper_classes ); ?>"
	data-sub-field-name="<?php echo esc_attr( $sub_field['name'] ); ?>"
	data-field-id="<?php echo intval( $field_id ); ?>"
>
	<label id="<?php echo esc_attr( $uniq_str ); ?>" class="frm_primary_label">
		<?php echo esc_html( $sub_field['label'] ); ?>
	</label>

	<?php
	// Loop through $sub_field['options'] to show options.
	foreach ( $sub_field['options'] as $option ) {
		switch ( $option ) {
			case 'default_value':
				$input_name = sprintf( '%1$s_%2$s[%3$s]', $option, $field_id, $sub_field['name'] );
				$input_id   = $option . '_' . $uniq_str;
				?>
				<p class="frm6 frm_form_field">
					<span class="frm-with-right-icon">
						<?php
						FrmAppHelper::icon_by_class(
							'frm_icon_font frm_more_horiz_solid_icon frm-show-inline-modal',
							array(
								'data-open' => 'frm-smart-values-box',
							)
						);
						?>
						<input
							type="text"
							name="<?php echo esc_attr( $input_name ); ?>"
							id="<?php echo esc_attr( $input_id ); ?>"
							value="<?php echo esc_attr( isset( $default_value[ $sub_field['name'] ] ) ? $default_value[ $sub_field['name'] ] : '' ); ?>"
							data-changeme="field_<?php echo esc_attr( $field_key . '_' . $sub_field['name'] ); ?>"
							data-changeatt="value"
						/>
					</span>
					<label class="frm_description" for="<?php echo esc_attr( $input_id ); ?>">
						<?php echo esc_html( $labels[ $option ] ); ?>
					</label>
				</p>
				<?php
				break;

			// All simple text options with live update the form output can go here.
			case 'placeholder':
				$input_name  = sprintf( 'field_options[%1$s_%2$s][%3$s]', $option, $field_id, $sub_field['name'] );
				$input_id    = 'field_options_' . $option . '_' . $uniq_str;
				$input_value = FrmField::get_option( $field, $option );
				?>
				<p class="frm6 frm_form_field">
					<input
						type="text"
						name="<?php echo esc_attr( $input_name ); ?>"
						id="<?php echo esc_attr( $input_id ); ?>"
						value="<?php echo esc_attr( isset( $input_value[ $sub_field['name'] ] ) ? $input_value[ $sub_field['name'] ] : '' ); ?>"
						data-changeme="field_<?php echo esc_attr( $field_key . '_' . $sub_field['name'] ); ?>"
						data-changeatt="<?php echo esc_attr( $option ); ?>"
					/>
					<label class="frm_description" for="<?php echo esc_attr( $input_id ); ?>">
						<?php echo esc_html( $labels[ $option ] ); ?>
					</label>
				</p>
				<?php
				break;

			// All simple text options without live update the form output can go here.
			case 'desc':
				$input_name  = sprintf( 'field_options[%1$s_%2$s_%3$s]', $sub_field['name'], $option, $field_id );
				$input_id    = 'field_options_' . $option . '_' . $uniq_str;
				$input_value = FrmField::get_option( $field, $sub_field['name'] . '_' . $option );
				?>
				<p class="frm6 frm_form_field">
					<input
						type="text"
						name="<?php echo esc_attr( $input_name ); ?>"
						id="<?php echo esc_attr( $input_id ); ?>"
						value="<?php echo esc_attr( $input_value ); ?>"
						data-changeme="frm_field_<?php echo esc_attr( $field_id . '_' . $sub_field['name'] ); ?>_desc"
					/>
					<label class="frm_description" for="<?php echo esc_attr( $input_id ); ?>">
						<?php echo esc_html( $labels[ $option ] ); ?>
					</label>
				</p>
				<?php
				break;
		}
	}
	?>
	<div class="frm12"></div>
</div><!-- End .frm_sub_field_options -->
