import FormidableIcon from '../common/components/icon';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Notice } = wp.components;

const UpgradeNotice = ( { text, buttonText, link } ) => (
	<Notice status="warning" isDismissible={ false }>
		{ text }
		<br/>
		<a href={ link } target="_blank">
			{ buttonText }
		</a>
	</Notice>
);

registerBlockType( 'formidable-modal/modal', {
	title: __( 'Formidable Modal', 'formidable' ),
	description: __( 'Display a Calculator Form', 'formidable' ),
	icon: {
		src: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 599.68 601.37" width="120" height="120">
			<path className="cls-1 orange" d="M289.6 384h140v76h-140z"></path>
			<path className="cls-1"
				  d="M400.2 147h-200c-17 0-30.6 12.2-30.6 29.3V218h260v-71zM397.9 264H169.6v196h75V340H398a32.2 32.2 0 0 0 30.1-21.4 24.3 24.3 0 0 0 1.7-8.7V264z"></path>
			<path className="cls-1"
				  d="M299.8 601.4A300.3 300.3 0 0 1 0 300.7a299.8 299.8 0 1 1 511.9 212.6 297.4 297.4 0 0 1-212 88zm0-563A262 262 0 0 0 38.3 300.7a261.6 261.6 0 1 0 446.5-185.5 259.5 259.5 0 0 0-185-76.8z"></path>
		</svg>
	},
	category: 'widgets',
	keywords: [
		'modal',
		'formidable',
	],

	edit: ( { setAttributes, attributes } ) => {
		const hasAccess = -1 !== [ 'elite', 'business', 'personal', 'grandfathered' ].indexOf( formidable_form_selector.licenseType );
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
						{ hasAccess ? (
							<UpgradeNotice
								text={ __( 'This site does not have popup modals active.', 'formidable' ) }
								buttonText={ __( 'Install Formidable Modals', 'formidable' ) }
								link="https://formidableforms.com/features/bootstrap-modal-forms/"
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
