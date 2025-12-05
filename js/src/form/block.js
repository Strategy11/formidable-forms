/**
 * BLOCK: formidable/simple-form
 *
 * Block to display selected Formidable form with limited setting options
 */
import FormShortcode from './formshortcode';
import Inspector from './inspector';
import FormidableIcon from '../common/components/icon';
import FormSelect from './formselect';
import { cssHideAdvancedSettings } from '../common/utilities/values';

const { useBlockProps } = wp.blockEditor;
const { Fragment } = wp.element;
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Notice } = wp.components;
const { serverSideRender: ServerSideRender } = wp;

function Edit( { setAttributes, attributes, isSelected } ) {
	const { formId } = attributes;
	const forms = formidable_form_selector.forms;
	const blockProps = useBlockProps();

	if ( forms.length === 0 ) {
		return (
			<div { ...blockProps }>
				<Notice status="warning" isDismissible={ false }>
					{ __( 'This site does not have any forms.', 'formidable' ) }
				</Notice>
			</div>
		);
	}

	if ( ! formId ) {
		return (
			<div { ...blockProps }>
				<div className="frm-block-intro-screen">
					<div className="frm-block-intro-content">
						<FormidableIcon></FormidableIcon>
						<div className="frm-block-title">{ formidable_form_selector.name }</div>
						<div className="frm-block-selector-screen">
							<FormSelect
								formId={ formId }
								setAttributes={ setAttributes }
								forms={ forms }
							/>
						</div>
					</div>
				</div>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<Inspector
				attributes={ attributes }
				setAttributes={ setAttributes }
				forms={ forms }
			/>
			{ isSelected && <style>{ cssHideAdvancedSettings }</style> }
			<ServerSideRender
				block="formidable/simple-form"
				attributes={ attributes }
			></ServerSideRender>
		</div>
	);
}

registerBlockType( 'formidable/simple-form', {
	apiVersion: 3,
	title: formidable_form_selector.name,
	description: __( 'Display a Form', 'formidable' ),
	icon: FormidableIcon,
	category: 'widgets',
	keywords: [
		__( 'contact forms', 'formidable' ),
		'formidable',
	],

	edit: Edit,

	save: function( props ) {
		const {
			attributes,
		} = props;
		const { formId } = attributes;
		return (
			( formId === undefined )
				? ''
				: <Fragment>
					<FormShortcode { ...attributes } />
				</Fragment>
		);
	},
} );
