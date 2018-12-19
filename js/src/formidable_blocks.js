/* global formidable_simple-block-js, wp */
/*jshint es3: false, esversion: 6 */

'use strict';

const { __ } = wp.i18n;
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { SelectControl, ToggleControl, PanelBody, ServerSideRender, Placeholder } = wp.components;

const frmFormsIcon = createElement( 'svg', { width: 20, height: 20, viewBox: '0 0 599.68 601.37', className: 'cls-1' },
	createElement( 'path', { fill: 'currentColor', d: 'M299.84,601.37A300.26,300.26,0,0,1,0,300.68,299.84,299.84,0,1,1,511.85,513.3,297.44,297.44,0,0,1,299.84,601.37Zm0-563A261.94,261.94,0,0,0,38.26,300.68,261.58,261.58,0,1,0,484.8,115.2,259.47,259.47,0,0,0,299.84,38.37Z' } )
);

registerBlockType( 'formidable/simple-form', {
	title: 'Formidable Forms',
	description: 'Description',
	icon: frmFormsIcon,
	keywords: [ __( 'form' ) ],
	category: 'widgets',
	attributes: {
		formId: {
			type: 'string',
		},
		title: {
			type: 'boolean',
		},
		description: {
			type: 'boolean',
		},
		minimize: {
			type: 'boolean',
		},
	},
	edit( props ) {
		const { attributes: { formId = '', title = false, description = false, minimize = false }, setAttributes } = props;
		const formOptions = formidable_form_selector.forms.map( value => (
			{ value: value.ID, label: value.post_title }
		) );
		let jsx;

		formOptions.unshift( { value: '', label: 'Select a Form' } );

		function selectForm( value ) {
			setAttributes( { formId: value } );
		}

		function toggleMinimize( value ) {
			setAttributes( { minimize: value } );
		}

		function toggleTitle( value ) {
			setAttributes( { title: value } );
		}

		function toggleDescription( value ) {
			setAttributes( { description: value } );
		}

		jsx = [
			<InspectorControls key="formidable-form-selector-inspector-controls">
				<PanelBody title={ 'Form Settings' }>
					<SelectControl
						label={ 'Form' }
						value={ formId }
						options={ formOptions }
						onChange={ selectForm }
					/>
					<ToggleControl
						label={ 'Show Title' }
						checked={ title }
						onChange={ toggleTitle }
					/>
					<ToggleControl
						label={ 'Show Description' }
						checked={ description }
						onChange={ toggleDescription }
					/>
					<ToggleControl
						label={ 'Minimize HTML' }
						checked={ minimize }
						onChange={ toggleMinimize }
					/>
				</PanelBody>
			</InspectorControls>
		];

		if ( formId ) {
			jsx.push(
				<ServerSideRender
					key="formidable-form-selector-server-side-renderer"
					block="formidable/simple-form"
					attributes={ props.attributes }
				/>
			);
		} else {
			jsx.push(
				<Placeholder
					key="formidable-form-selector-wrap"
					className="formidable-form-selector-wrap">
					<img src={ formidable_form_selector.logo_url }/>
					<h3>Formidable Forms</h3>
					<SelectControl
						key="formidable-form-selector-select-control"
						value={ formId }
						options={ formOptions }
						onChange={ selectForm }
					/>
				</Placeholder>
			);
		}

		return jsx;
	},
	save() {
		return null;
	},
} );
