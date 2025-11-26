/* global formidable_form_selector */

import FormidableIcon from '../common/components/icon';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Notice } = wp.components;

const UpgradeNotice = ( { text, buttonText, link } ) => (
	<Notice status="warning" isDismissible={ false }>
		{ text }
		<br />
		<a href={ link } target="_blank">
			{ buttonText }
		</a>
	</Notice>
);

registerBlockType( 'frm-modal/modal', {
	apiVersion: 3,
	title: formidable_form_selector.name + ' ' + __( 'Modal', 'formidable' ),
	description: __( 'Display a modal', 'formidable' ),
	icon: FormidableIcon,
	category: 'widgets',
	keywords: [
		'modal',
		'formidable',
	],

	edit: ( { setAttributes, attributes } ) => {
		const blockName = __( 'Bootstrap modal popup', 'formidable' );
		const imageStyles = {
			maxWidth: '504px',
			height: 'auto',
			borderRadius: '12px',
		};
		const imageWrapperStyles = {
			padding: '38px',
			textAlign: 'center',
			backgroundColor: '#f2f4f7',
			marginTop: '24px',
		};

		return (
			<div className="frm-block-intro-screen">
				<div className="frm-block-intro-content">
					<FormidableIcon></FormidableIcon>
					<div className="frm-block-title">{ blockName }</div>
					<div className="frm-block-selector-screen frm_pro_tip" style={ { alignSelf: 'stretch' } }>
						{ formidable_form_selector.modalAddon.hasAccess ? (
							<UpgradeNotice
								text={ __( 'This site does not have popup modals active.', 'formidable' ) }
								buttonText={ __( 'Install Formidable Modals', 'formidable' ) }
								link={ formidable_form_selector.modalAddon.link }
							/>
						) : (
							<UpgradeNotice
								text={ __( 'This site does not have popup modals.', 'formidable' ) }
								buttonText={ __( 'Upgrade Formidable Forms', 'formidable' ) }
								link={ formidable_form_selector.link }
							/>
						) }

						<div style={ imageWrapperStyles }>
							<img src={ formidable_form_selector.url + '/images/modal.png' } alt={ blockName } style={ imageStyles } />
						</div>
					</div>
				</div>
			</div>
		);
	},
} );
