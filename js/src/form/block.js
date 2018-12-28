/**
 * BLOCK: formidable/simple-form
 *
 * Block to display selected Formidable form with limited setting options
 */
import FormShortcode from './formshortcode';
import Inspector from './inspector';
import FormidableIcon from './icon';
import FormSelect from './formselect';

const { Fragment } = wp.element;
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { ServerSideRender, Notice } = wp.components;

registerBlockType( 'formidable/simple-form', {
	title: __( 'Formidable Form', 'formidable' ),
	description: __( 'Display a form', 'formidable' ),
	icon: FormidableIcon,
	category: 'widgets',
	keywords: [
		__( 'contact form', 'formidable' ),
	],

	edit: function( { setAttributes, attributes } ) {
		const { formId } = attributes;

		const forms = formidable_form_selector.forms;

		if ( forms.length === 0 ) {
			return (
				<Notice status="warning" isDismissible={ false }>
					{ __( 'This site does not have any forms.', 'formidable' ) }
				</Notice>
			);
		}

		if ( ! formId ) {
			return (
				<div className="frm-block-intro-screen">
					<div className="frm-block-intro-content">
						<FormidableIcon></FormidableIcon>
						<div className="frm-block-title">{ __( 'Formidable Forms', 'formidable' ) }</div>
						<div className="frm-block-selector-screen">
							<FormSelect
								formId={ formId }
								setAttributes={ setAttributes }
								forms={ forms }
							/>
						</div>
					</div>
				</div>
			);
		}

		return (
			<Fragment>
				<Inspector
					attributes={ attributes }
					setAttributes={ setAttributes }
					forms={ forms }
				/>
				<ServerSideRender
					block="formidable/simple-form"
					attributes={ attributes }
				></ServerSideRender>
			</Fragment>
		);
	},

	save: function( props ) {
		const {
			attributes,
		} = props;
		const { formId } = attributes;
		return (
			( formId === undefined ) ?
				'' :
				<Fragment>
					<FormShortcode { ...attributes } />
				</Fragment>
		);
	},
} );
