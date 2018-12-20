/**
 * BLOCK: formidable/simple-form
 *
 * Block to display selected Formidable form with limited setting options
 */

import FormShortcode from './formshortcode';
import Inspector from './inspector';
import Icon from './icon';
import FormidableIcon from './icon';
import { filterForms } from '../common/utilities/values';
import FormSelect from './formselect';

const { Fragment } = wp.element;
const { data } = wp;
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

	edit: function ( { className, isSelected, setAttributes, attributes } ) {

		const { form_id } = attributes;

		let forms = formidable_form_selector.forms;
		let filtered_forms_object = filterForms( forms );

		if ( Object.keys( filtered_forms_object ).length === 0 ) {
			return (
				<Notice status={ 'warning' } isDismissible={ false }>
					{ __( "This site doesn't have any Formidable forms.", 'formidable' ) }
				</Notice>
			)
		}

		if ( ! form_id ) {
			return (
				<div className={ "frm-block-intro-screen" }>
					<div className={ "frm-block-intro-content" }>
						<Icon></Icon>
						<div className={ "frm-block-title" }>{ __( 'Formidable Forms' ) }</div>
						<div className={ "frm-block-selector-screen" }>
							<FormSelect
								form_id={ form_id }
								setAttributes={ setAttributes }
							/>
						</div>
					</div>
				</div>
			)
		}

		return (
			<Fragment>
				<Inspector attributes={ attributes } setAttributes={ setAttributes }/>
				<ServerSideRender
					block="formidable/simple-form"
					attributes={ attributes }
				></ServerSideRender>
			</Fragment>
		)
	},

	save: function ( props ) {
		const {
			className,
			attributes
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