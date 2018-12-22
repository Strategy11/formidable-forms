/**
 * Inspector controls for View selector
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const {
	InspectorControls,
} = wp.editor;
const {
	ExternalLink,
	PanelBody,
	PanelRow,
	RadioControl,
} = wp.components;

import PropTypes from 'prop-types';
import ViewShortcode from './viewshortcode';
import ViewSelect from './viewselect';

import {
	updateAttribute,
	getSubDir,
} from '../common/utilities/values';

export default class Inspector extends Component {
	render() {
		const {
			setAttributes,
			attributes,
			views,
		} = this.props;

		const {
			viewId,
			filter,
		} = attributes;

		return (
			<InspectorControls>
				<PanelBody
					title={ __( 'Select View', 'formidable' ) }
					initialOpen={ true }
				>
					<PanelRow>
						<ViewSelect
							viewId={ viewId }
							setAttributes={ setAttributes }
							views={ views }
						/>
					</PanelRow>
					{ viewId &&
					<PanelRow>
						<ExternalLink href={ getSubDir() + `wp-admin\/post.php?post=${ viewId }&action=edit` }>
							{ __( 'Go to View', 'formidable' ) }
						</ExternalLink>
					</PanelRow> }
				</PanelBody>
				<PanelBody
					title={ __( 'Filter', 'formidable' ) }
					initialOpen={ false }>
					<RadioControl
						label={ __( 'Filter the View?', 'formidable' ) }
						selected={ filter }
						options={ [
							{ label: __( 'Limited (recommended)', 'formidable' ), value: 'limited' },
							{ label: __( 'Yes', 'formidable' ), value: '1' },
							{ label: __( 'No', 'formidable' ), value: '0' },
						] }
						help={ __( 'Setting filter to limited sends View content through WordPress content filters to process shortcodes inside the View and add auto paragraphs.', 'formidable' ) }
						onChange={ newFilter => {
							updateAttribute( 'filter', newFilter, setAttributes );
						}
						}

					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Shortcode', 'formidable' ) }
					initialOpen={ false }
				>
					<PanelRow>
						<ViewShortcode { ...this.props.attributes } />
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
