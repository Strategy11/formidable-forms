describe( 'Add-Ons page', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable-addons' );
		cy.viewport( 1280, 720 );
	} );

	it( 'should validate all add-on cards', () => {
		cy.get( '#frm_top_bar' ).should( 'contain', 'Formidable Add-Ons' );
		cy.get( '#frm-publishing > .button' ).should( 'contain', 'Upgrade' );
		cy.log( 'Target the upgrade banner and perform all checks within it' );
		cy.get( '#frm-upgrade-banner' ).within( () => {
			cy.get( 'h4' ).should( 'contain.text', 'Unlock Add-on library' );
			cy.get( 'p.frm-m-0' ).should( 'contain.text', 'Upgrade to Pro and access our library of add-ons to supercharge your forms.' );
			cy.get( 'a.frm-cta-link' )
				.should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=upgrade-cta' )
				.and( 'contain.text', 'Upgrade to PRO' );
		} );
		cy.get( '#addon-search-input' ).should( 'exist' );

		cy.log( 'Validate add-ons categories' );
		cy.get( 'li[data-category="all-items"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'All Add-Ons' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 43 );
			} );
		} );

		cy.get( 'li[data-category="automation"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Automation' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 4 );
			} );
		} );

		cy.get( 'li[data-category="crm"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'CRM' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 3 );
			} );
		} );

		cy.get( 'li[data-category="data-collection"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Data Collection' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 6 );
			} );
		} );

		cy.get( 'li[data-category="data-management"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Data Management' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 6 );
			} );
		} );

		cy.get( 'li[data-category="ecommerce"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Ecommerce' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 4 );
			} );
		} );

		cy.get( 'li[data-category="email-sms-marketing"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Email & SMS Marketing' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 9 );
			} );
		} );

		cy.get( 'li[data-category="form-design-display"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Form Design & Display' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 4 );
			} );
		} );

		cy.get( 'li[data-category="form-functionality"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Form Functionality' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 5 );
			} );
		} );

		cy.get( 'li[data-category="marketing"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Marketing' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 1 );
			} );
		} );

		cy.get( 'li[data-category="multilingual"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Multilingual' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 2 );
			} );
		} );

		cy.get( 'li[data-category="utilities"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'Utilities' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 3 );
			} );
		} );

		cy.log( 'Validate add-ons category plans' );

		cy.get( 'li[data-category="basic"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'basic' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 2 );
			} );
		} );

		cy.get( 'li[data-category="plus"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'plus' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 14 );
			} );
		} );

		cy.get( 'li[data-category="business"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'business' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 37 );
			} );
		} );

		cy.get( 'li[data-category="elite"]' ).within( () => {
			cy.get( '.frm-page-skeleton-cat-text' ).should( 'have.text', 'elite' );
			cy.get( '.frm-page-skeleton-cat-count' ).invoke( 'text' ).then( text => {
				const count = parseInt( text );
				expect( count ).to.be.at.least( 43 );
			} );
		} );

		cy.log( 'Formidable Forms Pro card' );
		cy.get( 'li[data-slug="formidable-pro"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Formidable Forms Pro' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Create calculators, surveys, smart forms, and data-driven applications. Build directories, real estate listings, job boards, and much more.' );
			cy.get( 'a[aria-label="View Docs"]' ).should( 'have.attr', 'href', 'https://formidableforms.com/knowledgebase/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates' )
				.and( 'have.attr', 'target', '_blank' )
				.invoke( 'removeAttr', 'target' ).click();
		} );

		cy.origin( 'https://formidableforms.com', () => {
			cy.get( 'h1' ).should( 'have.text', 'Docs & Support' );
		} );

		cy.visit( '/wp-admin/admin.php?page=formidable-addons' );

		cy.log( 'Digital Signatures card' );
		cy.get( 'li[data-slug="signature"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Digital Signatures' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );

			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Add an electronic signature to your WordPress form. The visitor may write their signature with a trackpad/mouse or type it.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=signature' )
					.and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' )
				.and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'PayPal Standard card' );
		cy.get( 'li[data-slug="paypal-standard"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'PayPal Standard' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_paypal_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Collect instant payments and recurring payments to automate your online business. Calculate a total and send customers on to PayPal.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=paypal-standard' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Formidable API card' );
		cy.get( 'li[data-slug="formidable-api"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Formidable API' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Add a full forms API for forms, form fields, views, and entries. Then send submissions to other sites with REST APIs.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=formidable-api' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Twilio WordPress SMS card' );
		cy.get( 'li[data-slug="twilio"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Twilio WordPress SMS' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_twilio_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Allow users to text their votes for polls created by Formidable Forms, or send SMS notifications when entries are submitted or updated.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=twilio' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Bootstrap card' );
		cy.get( 'li[data-slug="bootstrap"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Bootstrap' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_bootstrap_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Instantly add Bootstrap styling to all your Formidable forms.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=bootstrap' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'AWeber card' );
		cy.get( 'li[data-slug="aweber"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'AWeber' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_aweber_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'AWeber is a powerful email marketing service. Subscribe contacts to an AWeber mailing list when they submit your WordPress contact forms.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=aweber' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'WP Multilingual card' );
		cy.get( 'li[data-slug="wp-multilingual"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'WP Multilingual' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Translate your forms into multiple languages using the Formidable-integrated WPML plugin.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=wp-multilingual' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Locations card' );
		cy.get( 'li[data-slug="locations"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Locations' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Populate fields with Countries, States/Provinces, U.S. Counties, and U.S. Cities. This data can then be used in dependent Data from Entries fields.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=locations' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Zapier card' );
		cy.get( 'li[data-slug="zapier"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Zapier' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_zapier_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Connect with hundreds of applications through Zapier. Automatically insert a Google spreadsheet row, tweet, or upload to Dropbox.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=zapier' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'User Flow card' );
		cy.get( 'li[data-slug="user-tracking"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'User Flow' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Track the pages a user visits and the time spent on each page prior to submitting a form.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=user-tracking' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Mailchimp card' );
		cy.get( 'li[data-slug="mailchimp"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Mailchimp' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_mailchimp_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Get on the path to more leads in minutes. Add and update leads in a Mailchimp mailing list when a form is submitted.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=mailchimp' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'User Registration card' );
		cy.get( 'li[data-slug="user-registration"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'User Registration' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Give new users access to your site quickly and painlessly. Plus edit profiles and login from the front end.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=user-registration' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'WooCommerce card' );
		cy.get( 'li[data-slug="woocommerce"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'WooCommerce' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_woocommerce_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Are your WooCommerce product forms too basic? Add custom fields to a product form and collect more data when it is added to the cart.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=woocommerce' ).and( 'contain.text', 'Elite' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Highrise card' );
		cy.get( 'li[data-slug="highrise"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Highrise' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_highrise_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Capture leads in your WordPress contact forms, and save them in your Highrise CRM account too.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=highrise' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Bootstrap Modal card' );
		cy.get( 'li[data-slug="bootstrap-modal"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Bootstrap Modal' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_bootstrap_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Open forms, views, other shortcodes, or sections of content in a Bootstrap popup.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=bootstrap-modal' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Polylang card' );
		cy.get( 'li[data-slug="polylang"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Polylang' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_polylang_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Create bilingual or multilingual forms with help from Polylang.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=polylang' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Form Action Automation card' );
		cy.get( 'li[data-slug="autoresponder"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Form Action Automation' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Schedule email notifications, SMS messages, and API actions.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=autoresponder' ).and( 'contain.text', 'Elite' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Logs card' );
		cy.get( 'li[data-slug="logs"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Logs' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).invoke( 'text' ).then( text => {
				const normalizedText = text.replace( /\s+/g, ' ' ).trim();
				expect( normalizedText ).to.contain( 'See your API requests along with their responses from add-ons including Zapier, Formidable API Webhooks, Salesforce and more.' );
			} );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=logs' ).and( 'contain.text', 'Basic' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Datepicker Options card' );
		cy.get( 'li[data-slug="datepicker-options"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Datepicker Options' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).invoke( 'text' ).then( text => {
				const normalizedText = text.replace( /\s+/g, ' ' ).trim();
				expect( normalizedText ).to.contain( 'Add more options to date fields in your forms for so only the dates you choose can be chosen.' );
			} );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=datepicker-options' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Salesforce card' );
		cy.get( 'li[data-slug="salesforce"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Salesforce' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_salesforcealt_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Add new contacts and leads into your Salesforce CRM directly from the WordPress forms on your site.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=salesforce' ).and( 'contain.text', 'Elite' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'MailPoet Newsletters card' );
		cy.get( 'li[data-slug="mailpoet-newsletters"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'MailPoet Newsletters' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_mailpoet_icon' );
			cy.get( 'p.frm-line-clamp-2' ).invoke( 'text' ).then( text => {
				const normalizedText = text.replace( /\s+/g, ' ' ).trim();
				expect( normalizedText ).to.contain( 'Send WordPress newsletters from your own site with MailPoet. And use Formidable to for your newsletter signup forms.' );
			} );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=mailpoet-newsletters' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'ActiveCampaign card' );
		cy.get( 'li[data-slug="activecampaign-wordpress-plugin"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'ActiveCampaign' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_activecampaign_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Add contacts to any ActiveCampaign list from your WordPress forms.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=activecampaign-wordpress-plugin' ).and( 'contain.text', 'Elite' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'GetResponse card' );
		cy.get( 'li[data-slug="getresponse-wordpress-plugin"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'GetResponse' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_getresponse_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Collect leads in WordPress forms and automatically add them in GetResponse. Then trigger automatic emails and other GetResponse marketing automations.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=getresponse-wordpress-plugin' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Quiz Maker card' );
		cy.get( 'li[data-slug="quiz-maker"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Quiz Maker' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Turn your forms into automated quizzes. Add questions and submit the quiz key. Then all the grading is done for you.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=quiz-maker' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Constant Contact card' );
		cy.get( 'li[data-slug="constant-contact"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Constant Contact' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_constant_contact_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Setup WordPress forms to create leads automatically in Constant Contact. Just select a list and match up form fields.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=constant-contact' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Campaign Monitor card' );
		cy.get( 'li[data-slug="campaign-monitor"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Campaign Monitor' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_campaignmonitor_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Save time by automatically sending leads from WordPress forms to Campaign Monitor.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=campaign-monitor' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Export View to CSV card' );
		cy.get( 'li[data-slug="export-view"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Export View to CSV' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Easily create custom CSV files and allow users to export their data from the front-end of your site.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=export-view' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Visual Views card' );
		cy.get( 'li[data-slug="visual-views"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Visual Views' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Create WordPress web apps to display your form submissions in grids, tables, calendars, and more.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=visual-views' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Surveys and Polls card' );
		cy.get( 'li[data-slug="surveys"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Surveys and Polls' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Transform your WordPress site into a data collection machine with our user-friendly survey form builder.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=surveys' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Landing Pages card' );
		cy.get( 'li[data-slug="landing-pages"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Landing Pages' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Create beautiful landing pages fast and rake in new leads.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=landing-pages' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Conversational Forms card' );
		cy.get( 'li[data-slug="conversational-forms"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Conversational Forms' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Ask one question at a time to humanize forms and boost their conversion rates.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=conversational-forms' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Geolocation card' );
		cy.get( 'li[data-slug="geolocation"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Geolocation' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Get more accurate data and make forms faster to complete with address autocomplete.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=geolocation' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'PDFs card' );
		cy.get( 'li[data-slug="pdfs"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'PDFs' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Create PDFs from form entries automatically. Email them or let visitors download PDFs from your site.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=pdfs' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Google Sheets card' );
		cy.get( 'li[data-slug="google-sheets"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Google Sheets' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_googlesheets_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Send form entries to a Google spreadsheet as a backup or for extra processing.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=google-sheets' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'ACF Forms card' );
		cy.get( 'li[data-slug="acf-forms"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'ACF Forms' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_acfforms_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Sync custom fields between Formidable and Advanced Custom Fields or ACF Pro.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=acf-forms' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'AI Forms card' );
		cy.get( 'li[data-slug="ai"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'AI Forms' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm-ai-form-icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Get back your time by autogenerating a response from ChatGPT and inserting it into a field.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=ai' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Form Abandonment card' );
		cy.get( 'li[data-slug="abandonment"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Form Abandonment' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).invoke( 'text' ).then( text => {
				const normalizedText = text.replace( /\s+/g, ' ' ).trim();
				expect( normalizedText ).to.contain( "Capture form data before it's submitted to save more leads and optimize forms. Plus, auto save drafts and allow logged out editing." );
			} );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=abandonment' ).and( 'contain.text', 'Business' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'Charts card' );
		cy.get( 'li[data-slug="charts"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'Charts' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_logo_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Transform form data into insightful graphs with ease.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=charts' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.log( 'ConvertKit card' );
		cy.get( 'li[data-slug="convertkit"]' ).within( () => {
			cy.get( '.frm-font-medium.frm-truncate' ).should( 'contain.text', 'ConvertKit' );
			cy.get( 'svg.frmsvg > use' ).should( 'have.attr', 'xlink:href', '#frm_convertkit_icon' );
			cy.get( 'p.frm-line-clamp-2' ).should( 'contain.text', 'Bring automation into your email marketing plan for the power to say "welcome" to your subscribers the moment they opt-in to your list.' );
			cy.contains( 'Plan required:' ).within( () => {
				cy.get( 'a' ).should( 'have.attr', 'href', 'https://formidableforms.com/lite-upgrade/?utm_source=plugin&utm_medium=lite&utm_campaign=form-templates&utm_content=convertkit' ).and( 'contain.text', 'Plus' );
			} );
			cy.get( 'a[aria-label="Upgrade Now"]' ).should( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'href' ).and( 'include', 'https://formidableforms.com/lite-upgrade/' );
		} );

		cy.get( 'div.frm-addons-request-addon' ).should( 'exist' ).within( () => {
			cy.get( 'span' ).should( 'have.text', 'Not finding what you need?' );
			cy.get( 'a.frm-font-semibold' ).should( 'have.text', 'Request Add-On' )
				.and( 'have.attr', 'href', 'https://connect.formidableforms.com/add-on-request/' )
				.and( 'have.attr', 'target', '_blank' );
		} );
	} );

	it( 'should search for add-ons', () => {
		cy.log( 'Search for valid add-ons by name' );
		cy.get( '#addon-search-input' ).type( 'PayPal Standard' );
		cy.get( '.plugin-card-paypal-standard' ).should( 'contain', 'PayPal Standard' );

		cy.log( 'Search for valid add-ons by description' );
		cy.get( '#addon-search-input' ).clear().type( 'Add an electronic signature to your WordPress form. The visitor may write their signature with a trackpad/mouse or type it.' );
		cy.get( '.plugin-card-signature' ).should( 'contain', 'Digital Signatures' );

		cy.log( 'Search for non valid add-ons' );
		cy.get( '#addon-search-input' ).clear().type( 'Non valid add-on' );
		cy.get( '#frm-page-skeleton-empty-state > img' ).should( 'exist' );
		cy.get( '.frm-page-skeleton-title' ).should( 'contain', 'No add-ons found' );
		cy.get( '.frm-page-skeleton-text' ).should( 'contain', "Sorry, we didn't find any add-ons that match your criteria." );
		cy.get( '#frm-page-skeleton-empty-state > .button' ).should( 'contain', 'Request Add-On' ).click();
	} );
} );
