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
	constructor() {
		super( ...arguments );
	}

	componentDidMount() {
	}

	render() {
		const {
			setAttributes,
			attributes,
		} = this.props;

		const {
			view_id,
			filter,
		} = attributes;

		return (
			<InspectorControls>
				<PanelBody
					title={ __( 'Select View' ) }
					initialOpen={ true }
				>
					<PanelRow>
						<ViewSelect
							view_id={ view_id }
							setAttributes={ setAttributes }/>
					</PanelRow>
					{ view_id &&
					<PanelRow>
						<ExternalLink href={ getSubDir() + `wp-admin\/post.php?post=${ view_id }&action=edit` }>
							{ __( 'Go to View', 'formidable' ) }
						</ExternalLink>
					</PanelRow> }
				</PanelBody>
				<PanelBody
					title={ __( 'Filter' ) }
					initialOpen={ false }>
					<RadioControl
						label={ __( 'Filter the View?' ) }
						selected={ filter }
						options={ [
							{ label: __( 'Limited (recommended)' ), value: 'limited' },
							{ label: __( 'Yes' ), value: '1' },
							{ label: __( 'No' ), value: '0' },
						] }
						help={ "Setting filter to limited sends View content through WordPress content filters to process shortcodes inside the View and add auto paragraphs." }
						onChange={ filter => {
							updateAttribute( 'filter', filter, setAttributes );
						}
						}

					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Shortcode' ) }
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
	attributes: PropTypes.object,//block attributes
	setAttributes: PropTypes.func,//setAttributes of block
};