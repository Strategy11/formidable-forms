/**
 * Configuration File.
 * Establishes links and options parameters to be utilized by the S11FloatingLinks class.
 *
 * @class S11FloatingLinks
 */

( ( wp ) => {

	/**
	 * WordPress dependencies
	 */
	const { __ } = wp.i18n;

	// Define a configuration variable for Formidable's floating links.
	const frmFloatingLinksConfig = {};

	/**
	 * SVG definitions for the icons
	 */
	// Icon for Upgrade link
	frmFloatingLinksConfig.upgradeIcon = `
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
			<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m12 4.75 1.75 5.5h5.5l-4.5 3.5 1.5 5.5-4.25-3.5-4.25 3.5 1.5-5.5-4.5-3.5h5.5L12 4.75Z"/>
		</svg>
	`;

	// Icon for Support link
	frmFloatingLinksConfig.supportIcon = `
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
			<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 12a7.25 7.25 0 1 1-14.5 0 7.25 7.25 0 0 1 14.5 0Z"/>
			<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.25 12a3.25 3.25 0 1 1-6.5 0 3.25 3.25 0 0 1 6.5 0ZM7 17l2.5-2.5M17 17l-2.5-2.5m-5-5L7 7m7.5 2.5L17 7"/>
		</svg>
	`;

	// Icon for Documentation link
	frmFloatingLinksConfig.documentationIcon = `
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
			<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m14.924 16.002.482 2.432c.106.537.682.895 1.286.8l1.64-.256c.604-.095 1.007-.607.9-1.145l-.481-2.431m-3.827.6-1.157-5.835c-.106-.538.297-1.05.9-1.145l1.64-.257c.605-.095 1.18.264 1.287.801l1.157 5.836m-3.827.6 3.827-.6M8.75 15.75v2.5a1 1 0 0 0 1 1h1.5a1 1 0 0 0 1-1v-2.5m-3.5 0v-8a1 1 0 0 1 1-1h1.5a1 1 0 0 1 1 1v8m-3.5 0h3.5m-7.5 0v2.5a1 1 0 0 0 1 1h1.5a1 1 0 0 0 1-1v-2.5m-3.5 0v-10a1 1 0 0 1 1-1h1.5a1 1 0 0 1 1 1v10m-3.5 0h3.5"/>
		</svg>
	`;

	// Icon for Notifications link
	frmFloatingLinksConfig.notificationsIcon = `
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
			<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.25 12v-2a5.25 5.25 0 1 0-10.5 0v2l-2 4.25h14.5l-2-4.25ZM9 16.75s0 2.5 3 2.5 3-2.5 3-2.5"/>
		</svg>
	`;

	/**
	 * Define links for the "free" version of the plugin
	 */
	frmFloatingLinksConfig.freeVersionLinks = [
		{
			title: __( 'Upgrade', 'formidable' ),
			icon: frmFloatingLinksConfig.upgradeIcon,
			url: 'https://formidableforms.com/lite-upgrade/',
			openInNewTab: true
		},
		{
			title: __( 'Support', 'formidable' ),
			icon: frmFloatingLinksConfig.supportIcon,
			url: 'https://wordpress.org/support/plugin/formidable/',
			openInNewTab: true
		},
		{
			title: __( 'Documentation', 'formidable' ),
			icon: frmFloatingLinksConfig.documentationIcon,
			url: 'https://formidableforms.com/knowledgebase/',
			openInNewTab: true
		}
	];

	/**
	 * Define links for the "pro" version of the plugin
	 */
	frmFloatingLinksConfig.proVersionLinks = [
		{
			title: __( 'Support & Docs', 'formidable' ),
			icon: frmFloatingLinksConfig.supportIcon,
			url: 'https://formidableforms.com/knowledgebase/',
			openInNewTab: true
		}
	];

	/**
	 * Define options
	 */
	frmFloatingLinksConfig.options = {
		hoverColor: '#4199FD',
		bgHoverColor: '#F5FAFF',
		logoIcon: `
			<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
				<g clip-path="url(#floatingLinksClipPath)">
					<path fill="#F15A24" d="M19.265 25.641h9.401v4.957h-9.401v-4.957Z"/>
					<path fill="#5E5E5F" d="M26.702 9.743H13.368a2.12 2.12 0 0 0-2.136 2.12V14.7h17.436V9.743h-1.966Zm-.171 7.864H11.249v12.991h4.957v-8.034h10.36a2.154 2.154 0 0 0 2.016-1.419 1.67 1.67 0 0 0 .103-.598v-2.94H26.53ZM20 40a20 20 0 0 1-6.748-38.827 20 20 0 0 1 14.526 37.254A19.847 19.847 0 0 1 20 40Zm0-37.35A17.35 17.35 0 0 0 7.727 32.272 17.358 17.358 0 0 0 32.275 7.726 17.232 17.232 0 0 0 20 2.666V2.65Z"/>
				</g>
				<defs>
					<clipPath id="floatingLinksClipPath">
					<path fill="#fff" d="M0 0h40v40H0z"/>
					</clipPath>
				</defs>
			</svg>
		`
	};

	// Determine the appropriate links and initialize the S11FloatingLinks class
	frmFloatingLinksConfig.links = s11FloatingLinksData.proIsInstalled ? frmFloatingLinksConfig.proVersionLinks : frmFloatingLinksConfig.freeVersionLinks;

	// Trigger the 'set_floating_links_config' action, passing the config
	wp.hooks.doAction( 'set_floating_links_config', frmFloatingLinksConfig );

})( window.wp );
