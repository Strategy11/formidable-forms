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
import FormShortcode from "./formshortcode";
import Link from "../common/components/link";
import Toggle from '../common/components/toggle';

import { updateAttribute } from "../common/utilities/values";

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
                              link_text={ __( 'Go to form' ) }
                              add_sub_dir={ true }
                        />
                    </PanelRow> }
                </PanelBody>
                <PanelBody
                    title={ __( 'Options' ) }
                    initialOpen={ false }
                >
                    <Toggle
                        label={ __( 'Display form title?' ) }
                        setAttributes={ setAttributes }
                        attribute_name={'title'}
                        attribute_value={title}
                    />
                    <Toggle
                        label={ __( 'Display form description?' ) }
                        setAttributes={ setAttributes }
                        attribute_name={'description'}
                        attribute_value={description}
                    />
                    <Toggle
                        label={ __( 'Minimize?' ) }
                        setAttributes={ setAttributes }
                        attribute_name={'minimize'}
                        attribute_value={minimize}
                    />
                </PanelBody>
                <PanelBody
                    title={ __( 'Shortcode' ) }
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
    attributes: PropTypes.object,//block attributes
    setAttributes: PropTypes.func,//setAttributes of block
};