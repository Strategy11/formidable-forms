/**
 * BLOCK: formidable/calculator
 *
 * Block to display selected Formidable calculator form
 */
import FormidableIcon from '../common/components/icon';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Notice } = wp.components;
const { useBlockProps } = wp.blockEditor;

function Edit( { setAttributes, attributes } ) {
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

	return (
		<div { ...blockProps }>
			<div className="frm-block-intro-screen">
				<div className="frm-block-intro-content">
					<FormidableIcon></FormidableIcon>
					<div className="frm-block-title">{ __( 'Calculator Form', 'formidable' ) }</div>
					<div className="frm-block-selector-screen frm_pro_tip">
						<Notice status="warning" isDismissible={ false }>
							{ __( 'This site does not have any calculator forms.', 'formidable' ) }
							<br />
							<a href={ formidable_form_selector.link } target="_blank">
								{ __( 'Upgrade Formidable Forms.', 'formidable' ) }
							</a>
						</Notice>
						<img src={ formidable_form_selector.url + '/images/conversion-calc.jpg' } alt={ __( 'Calculator Form', 'formidable' ) } />
					</div>
				</div>
			</div>
		</div>
	);
}

const FrmCalcIcon = wp.element.createElement(
	'svg',
	{
		width: 20,
		height: 20
	},
	wp.element.createElement( 'path',
		{
			d: 'M16.9 0H3a2 2 0 0 0-1.9 1.9V18a2 2 0 0 0 2 1.9h13.7a2 2 0 0 0 1.9-1.9V2a2 2 0 0 0-2-1.9zm0 18.1H3v-10H17v10zm0-11.9H3V2H17v4.3zM5.5 12.6H7c.3 0 .5-.3.5-.5v-1.5c0-.3-.3-.5-.5-.5H5.5c-.3 0-.5.3-.5.5V12c0 .3.3.5.5.5zm7.5 3.8h1.5c.3 0 .5-.3.5-.6v-5.2c0-.3-.3-.5-.5-.5H13c-.3 0-.5.3-.5.5v5.3c0 .2.3.4.5.4zm-7.5 0H7c.3 0 .5-.3.5-.6v-1.4c0-.3-.3-.6-.5-.6H5.5c-.3 0-.5.3-.5.6v1.4c0 .3.3.6.5.6zm3.8-3.8h1.4c.3 0 .6-.3.6-.5v-1.5c0-.3-.3-.5-.6-.5H9.3c-.3 0-.6.3-.6.5V12c0 .3.3.5.6.5zm0 3.8h1.4c.3 0 .6-.3.6-.6v-1.4c0-.3-.3-.6-.6-.6H9.3c-.3 0-.6.3-.6.6v1.4c0 .3.3.6.6.6z',
		}
	)
);

registerBlockType( 'formidable/calculator', {
	apiVersion: 3,
	title: __( 'Calculator Form', 'formidable' ),
	description: __( 'Display a Calculator Form', 'formidable' ),
	icon: FrmCalcIcon,
	category: 'widgets',
	keywords: [
		'calculation',
		'formidable',
	],

	edit: Edit,
} );
