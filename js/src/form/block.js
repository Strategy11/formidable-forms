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
	description: __( 'Display a Formidable form', 'formidable' ),
	icon: FormidableIcon,
	category: 'widgets',
	keywords: [
		__( 'contact form', 'formidable' ),
	],

	edit: function( { setAttributes, attributes } ) {
		const { form_id } = attributes;

		const forms = formidable_form_selector.forms;

		if ( forms.length === 0 ) {
			return (
				<Notice status={ 'warning' } isDismissible={ false }>
					{ __( 'This site doesn\'t have any Formidable forms.', 'formidable' ) }
				</Notice>
			);
		}

		if ( ! form_id ) {
			return (
				<div className={ 'frm-block-intro-screen' }>
					<div className={ 'frm-block-intro-content' }>
						<FormidableIcon></FormidableIcon>
						<div className={ 'frm-block-title' }>{ __( 'Formidable Forms', 'formidable' ) }</div>
						<div className={ 'frm-block-selector-screen' }>
							<FormSelect
								form_id={ form_id }
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
		const { form_id } = attributes;
		return (
			( form_id === undefined ) ? '' :
				<Fragment>
				<FormShortcode { ...attributes } />
			</Fragment>

		);
	},
} );
