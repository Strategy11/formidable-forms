# Formidable Forms
Contributors: [sswells](https://github.com/stephywells), [srwells](https://github.com/srwells), [jamie.wahlin](https://github.com/cwahlin)

Donate link: http://formidablepro.com/donate

Tags: admin, AJAX, captcha, contact, contact form, database, email, feedback, form, forms, javascript, jquery, page, plugin, poll, Post, spam, survey, template, widget, wpmu, form builder

Requires at least: 3.6

Tested up to: 4.3.1

Stable tag: 2.0.17

Beautiful forms in 60 seconds. The WordPress form builder that enables you to create forms with a simple drag-and-drop interface and in-place editing.

## Description
Build WordPress forms the fast and simple way with a simple drag-and-drop interface (and a visual form styler in version 2.0). Create custom Formidable forms or generate them from a template with this stunning WordPress form plugin.

[View Documentation](http://formidablepro.com/knowledgebase/ "View Documentation")

[Contribute on Github](https://github.com/Strategy11/formidable-forms "Contribute on Github")

### Features
* Create forms with 7 field types: text, email, url, paragraph text, radio, checkbox, and dropdown
* Create forms from existing templates or add your own. A contact form template is included.
* Send unlimited email notifications, including autoresponders to the form submitter
* Create a single styling template using our visual form styler
* View form submissions from the back-end
* Import and export forms with XML
* Send forms to the trash
* Generate shortcodes with an advanced shortcode UI
* Customize the HTML in your form for any layout you would like, or use our CSS classes to arrange your fields
* Integrate with the one-click reCAPTCHA and Akismet for Spam control
* Use placeholder default values in form fields that clear when clicked
* Direct links available for previews and emailing surveys with and without integration with your theme
* Change the name on the Formidable menu to anything you would like
* Insert your forms on a page, post, or widget using a shortcode [formidable id=x]
* Support for bugs. We want it to be perfect!

### Pro Features
* Access even more field types: Section headings (repeatable in 2.0), page breaks, file upload, rich text, number, phone number, date, time, scale, dynamic fields populated from other forms, hidden field, user ID field, password, HTML, and tags
* Conditionally send your email notifications based on values in your form
* Create multiple styling templates and assign them to any of your forms, and add instant Bootstrap styling
* Flexibly and powerfully view, edit, and delete entries from anywhere on your site, and specify who has permission to do so
* Generate graphs and stats based on your submitted data
* Create and edit WordPress posts from the front-end
* Use our add-ons for user registration, payment, and integration with other services like MailChimp, Aweber, Highrise, Twilio for SMS, WPML, and Zapier
* Logged-in users can save drafts and return later
* Integrate the Math Captcha plugin for alternative spam protection
* Generate custom calculations
* Hide and show fields conditionally based on other fields or the user's role
* Export and import entries with CSV
* Import our pre-built form/view demo templates as a starting point or a final product
* Support for all Formidable features. If you have questions or need guidance on how to set up your application, we are happy to help. We want to make you look fabulous for your clients, and allow you to take on big jobs.

Learn more at: http://formidablepro.com

## Installation
1. Go to your Plugins -> Add New page in your WordPress admin
2. Search for 'Formidable'
3. Click the 'Install Now' button
4. Activate the plugin through the 'Plugins' menu
5. Go to the Formidable menu
6. Click the 'Add New' button to create a new form
7. Insert your form with the shortcode [formidable id###x] in pages, posts, or text widgets. Alternatively use `<?php echo FrmFormsController::show_form(2, $key  '', $title###true, $descriptiontrue); ?>` in your template

## Screenshots
1. Create beautiful WordPress forms without any code.
2. Form creation page
3. Field Options and CSS Layout Classes
4. Field Options for checkbox fields
5. Entry Management page
6. Form Widget

## Frequently Asked Questions
### Q. Why aren't I getting any emails?

A. Try the following steps:

   1. Double check to make sure your email address is present and correct in the "Emails" tab on the form "Settings" page
   2. Make sure you are receiving other emails from your site (ie comment notifications, forgot password...)
   3. Check your SPAM box
   4. Try a different email address.
   5. Install WP Mail SMPT or another similar plugin and configure the SMTP settings
   6. If none of these steps fix the problem, let us know and we'll try to help you find the bottleneck.

### Q. How do I edit the field name?

A. The field and form names and descriptions are all changed with in-place edit. Just click on the text you would like to change, and it will turn into a text field.

### Q. Why isn't the form builder page working after I updated?

A. Try clearing your browser cache. As plugin modifications are made, frequent javascript and stylesheet changes are also made. However, the previous versions may be cached so you aren't using the modified files. After clearing your cache and you're still having issues, please let us know.

[See more FAQs](http://formidablepro.com/formidable-faqs/ "Formidable Form FAQs")

## Changelog
### 2.0.18
* PHP 7 updates
* Add frm_field_extra_html hook
* Prevent specific html entity from breaking email message
* Add filter for removing wpautop from form success message
* Fix HTML error on form builder page
* Change the "Licenses" submenu to "Plugin Licenses"
* **Pro Features:**
* Allow ? and * in Phone Number Format
* Remove child form from export options
* Fix LIKE conditional logic bug
* Some auto-update adjustments
* Add frm_search_any_terms filter
* Fix file upload issue in CSV export
* Fix issue with duplicate classes in HTML field
* Fix filtering with user_id=current in graphs
* Fix Dynamic List field with value like 9.99
* Make sure userID field doesn't lose value when conditionally hidden/shown

### 2.0.17
* **Pro Features:**
* Fix post update bug
