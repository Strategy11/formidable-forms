/**
 * Text field
 */

const { __ } = wp.i18n;
const {
	Component,
} = wp.element;

export default class TextField extends Component {
	render() {
		return (
			<div className="frm_form_field form-field frm_focus_field frm_pos_container frm_top_container">
				<label className="frm_primary_label">Text field in active state <span className="frm_required">*</span></label>
				<input type="text" value="Active state will be seen when the field is clicked" />
			</div>
		);
	}
}