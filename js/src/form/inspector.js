/**
 * Inspector controls for Formidable Simple Form block
 */

const { __ } = wp.i18n;
const { Component } = wp.element;
const {
	InspectorControls,
} = wp.editor;
const {
	PanelBody,
	PanelRow,
	ToggleControl,
	ExternalLink,
} = wp.components;

import PropTypes from 'prop-types';
import FormSelect from './formselect';
import FormShortcode from './formshortcode';
import {
	updateAttribute,
	getSubDir
} from '../common/utilities/values';

export default class Inspector extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {
		const {
			setAttributes,
			attributes,
		} = this.props;

		const {
			form_id,
			title,
			description,
			minimize,
		} = attributes;

		return (
			<InspectorControls>
				<PanelBody
					title={ __( 'Select Form', 'formidable' ) }
					initialOpen={ true }
				>
					<PanelRow>
						<FormSelect
							form_id={ form_id }
							setAttributes={ setAttributes }
						/>
					</PanelRow>
					{ form_id &&
					<PanelRow>
						<ExternalLink
							href={ getSubDir() + `wp-admin\/admin.php?page=formidable&frm_action=edit&id=${ form_id }` }>
							{ __( 'Go to form', 'formidable' ) }
						</ExternalLink>
					</PanelRow> }
				</PanelBody>
				<PanelBody
					title={ __( 'Options', 'formidable' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show Form Title', 'formidable' ) }
						checked={ title }
						onChange={ response => {
							updateAttribute( 'title', response ? '1' : '', setAttributes )
						} }
					/>
					<ToggleControl
						label={ __( 'Show Form Description', 'formidable' ) }
						checked={ description }
						onChange={ response => {
							updateAttribute( 'description', response ? '1' : '', setAttributes )
						} }
					/>
					<ToggleControl
						label={ __( 'Minimize HTML', 'formidable' ) }
						checked={ minimize }
						onChange={ response => {
							updateAttribute( 'minimize', response ? '1' : '', setAttributes )
						} }
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Shortcode', 'formidable' ) }
					initialOpen={ false }
				>
					<PanelRow>
						<FormShortcode { ...this.props.attributes } />
					</PanelRow>
				</PanelBody>
			</InspectorControls>
		);
	}
}

Inspector.propTypes = {
	attributes: PropTypes.object, //block attributes
	setAttributes: PropTypes.func, //setAttributes of block
};
