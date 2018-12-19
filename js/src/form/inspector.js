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
} = wp.components;

import PropTypes from 'prop-types';
import FormSelect from './formselect';
import FormShortcode from './formshortcode';
import Link from '../common/components/link';
import Toggle from '../common/components/toggle';

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
                        <Link href={ `wp-admin\/admin.php?page=formidable&frm_action=edit&id=${ form_id }` }
                              link_text={ __( 'Go to form', 'formidable' ) }
                              add_sub_dir={ true }
                        />
                    </PanelRow> }
                </PanelBody>
                <PanelBody
                    title={ __( 'Options', 'formidable' ) }
                    initialOpen={ false }
                >
                    <Toggle
                        label={ __( 'Show Form Title', 'formidable' ) }
                        setAttributes={ setAttributes }
                        attribute_name={ 'title' }
                        attribute_value={ title }
                    />
                    <Toggle
                        label={ __( 'Show Form Description', 'formidable' ) }
                        setAttributes={ setAttributes }
                        attribute_name={ 'description' }
                        attribute_value={ description }
                    />
                    <Toggle
                        label={ __( 'Minimize HTML', 'formidable' ) }
                        setAttributes={ setAttributes }
                        attribute_name={ 'minimize' }
                        attribute_value={ minimize }
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
