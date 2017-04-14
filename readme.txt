=== Formidable Forms - Form Builder for WordPress ===
Contributors: formidableforms, sswells, srwells, jamie.wahlin
Tags: form, contact form, form builder, custom form, forms, form maker, form creator
Requires at least: 3.8
Tested up to: 4.7.3
Stable tag: 2.03.06

The best WordPress form plugin. Simple drag & drop form building, visual form styling, and unlimited email notifications. 

== Description ==
= WordPress Form Builder Plugin =
Formidable Forms is a flexible and free WordPress form plugin. Easily create contact forms, polls and surveys, or lead generation forms. Start with pre-built form templates or create totally custom forms. From the smallest sidebar opt-in form to large job application forms, Formidable is built to do it all.

Create professional contact forms without any code. Use the built-in visual styler to instantly customize the look and feel of your forms. Additionally,  changing the form layout is simple with included layout classes. If you need more advanced customizations, you have complete access to edit the form HTML and CSS.

[View form builder Documentation](https://formidableforms.com/knowledgebase/ "View form builder Documentation")

= Features =
* Create unlimited forms with all the essential field types: single line text, email, URL, paragraph text, radio, checkbox, dropdown, and reCaptcha.
* Create forms from pre-built templates or add your own. A free contact form template is included.
* Send unlimited email notifications.
* Create a single styling template using the visual form styler.
* View form submissions from the back-end.
* Import and export Formidable forms and templates.
* Send forms to the trash.
* Publish forms with an easy-to-use shortcode UI.
* Customize the form’s success message and submit button text.
* Use our ready-made CSS classes (or your own custom CSS classes) to arrange your fields.
* Create multi-column forms easily.
* Integrate with the one-click reCAPTCHA and Akismet for Spam control.
* Use placeholder default values in form fields that clear when clicked.
* Direct links available for previews and emailing surveys with and without integration with your theme.
* Change the name on the ‘Forms’ admin menu to anything you would like for white labeling.
* Every submission is saved to your database. Even if an email fails, you won’t lose anything.

Formidable Forms Pro is a premium upgrade that adds multi-page forms, conditional logic, payment integrations and data management. Not only can you collect data, but you can also display it on the front-end of your site. Add the ability to input, display, edit and filter data on the front end without any additional plugins. Formidable Forms is a powerful solution for purchase forms, member directories, user registration, and more.

= Pro Features =
* Over 30 field types: page breaks, section headings, repeating field groups, file uploads, rich text, number, phone number, date, time, scale, dynamic fields populated from other forms, hidden fields, user ID fields, password, HTML, tags, address, and more.
* Multi-Page forms: Create beautiful paged forms with rootline and progress indicators. Use conditional logic on page breaks for branching forms.
* Conditional logic: show or hide fields in your form based on user selections or the role of the user.
* Email routing: conditionally send multiple email notifications based on values in your form.
* Calculations: create basic and complex calculations, even combine text from multiple fields.
* Styling Templates: Create multiple styling templates and assign them to any of your forms. Need Bootstrap form styling? We’ve got you covered.
* Entry management: Flexibly and powerfully display, edit, and delete entries from anywhere on your site, and specify who has permission to do so.
* Views: unique to Formidable Forms is the core ability to display data in custom format.
* Graphs and stats based on your submitted data.
* Create and edit WordPress posts, pages, and even custom post types from front-end forms.
* Front-end editing: allow users to edit their entries and posts from the front-end of your site.
* Saved Drafts: logged-in users can save form progress and return later.
* Vast add-on library: user registration, form action automation, signature, a form API, and integration with other services like PayPal, Stripe, Authorize.net, MailChimp, Aweber, Highrise, Twilio for SMS, WPML, Polylang, Bootstrap, and Zapier.
* Form permission settings: limit form visibility based on user role.
* Conditionally redirect after submission.
* Prefill or prepopulate forms with user meta.
* Export and import entries with CSV.
* Limit number of entries per user, IP, or cookie.
* Import our pre-built form/view demo templates as a starting point or a final product.
* World Class Support: if you have questions or need guidance on how to set up your application, we are happy to help. Our goal with Formidable Forms is to help you take on bigger projects, earn more clients, and grow your business.

Learn more at [Formidable Forms](https://formidableforms.com/ "Formidable Forms")

[Contribute on Github](https://github.com/Strategy11/formidable-forms "Contribute on Github")

== Installation ==
1. Go to your Plugins -> Add New page in your WordPress admin
2. Search for 'Formidable'
3. Click the 'Install Now' button
4. Activate the plugin through the 'Plugins' menu
5. Go to the Formidable menu
6. Click the 'Add New' button to create a new form
7. Insert your forms on a page, post, or widget using a shortcode [formidable id=x], Alternatively use `<?php echo FrmFormsController::show_form(2, $key = '', $title=true, $description=true); ?>` in your template

== Screenshots ==
1. Build professional WordPress forms without any code.
2. Form builder page
3. Field Options and CSS Layout Classes
4. Field Options for checkbox fields
5. Entry Management page
6. Form Widget

== Frequently Asked Questions ==
= Q. Why am I not I getting any emails? =

A. Try the following steps:

   1. Double check to make sure your email address is present and correct in the "Emails" tab on the form "Settings" page
   2. Make sure you are receiving other emails from your site (ie comment notifications, forgot password...)
   3. Check your SPAM box
   4. Try a different email address.
   5. Install WP Mail SMPT or another similar plugin and configure the SMTP settings
   6. If none of these steps fix the problem, let us know and we'll try to help you find the bottleneck.

= Q. How do I edit the field name? =

A. The field and form names and descriptions are all changed with in-place edit. Just click on the text you would like to change, and it will turn into a text field.

[See more FAQs](https://formidableforms.com/formidable-faqs/ "Formidable Form FAQs")

== Changelog ==

= 2.03.06 =
* **Pro Version** *
* Fix: Add nonce check for uploads
* Fix: Decrease maximum number of orphaned files that can be deleted at one time
* Fix: Carry page titles across on import

= 2.03.05 =
* New: Add Honeypot spam protection
* Enhancement: Add frm_form_attributes hook.
* Enhancement: Make field value dropdown code available in free version
* Enhancement: Add deprecated notice for old globals such as $frm_form, $frm_entry, $frm_entry_meta, and $frmdb
* Fix: Set default menu name to Formidable
* Fix: Allow Date column to be toggled on form listing page
* **Pro Version** *
* New: Add Honeypot spam protection on file uploads.
* New: Add option to get oldest or newest unique values in Views.
* New: Add custom frmDrawChart event for customizing graphs.
* Enhancement: Delete temporary files after 6 hours.
* Enhancement: Add more comparison types for Lookup field queries. Affects frm_set_comparison_type_for_lookup hook options.
* Enhancement: Add frm_pro_value_selector_options hook for customizing options available in field value dropdown.
* Enhancement: Trigger frmLookupOptionsLoaded event when options are loaded in Lookup field.
* Fix: Separate multiple files with comma for Zapier.
* Fix: Start and end date not applying to repeating date fields.
* Fix: Do not clear hidden form field in conditionally hidden Repeatable Section.
* Fix: Create queue for fields watching Lookups so the value set is always the correct value.
* Fix: If a field doesn't have separate values, simplify the options array to include only the key and displayed value.
* Fix: Delete child entries when Repeatable Section is conditionally hidden and entry is updated.

= 2.03.04 =
* Fix: Allow quotes within shortcodes in email settings
* Fix: Check if an option is "other" a little more reliably. Instead of checking for 'other' anywhere in the option key, check for other_ at the beginning.
* Fix: Correctly use default version number for jquery ui URL if query string is not included
* Fix: Increase room for ids in the database. Increase from 11 to 20 to match the WordPress DB fields
* Fix: Resolve a conflict with themes adding display:block; as the default for all input elements that is causing checkboxes and radio buttons to look bad
* Code: Email code refactoring
* **Pro Version** *
* Fix: text calculations using a single dropdown time field
* Fix: issue with duplicate headings after a repeating section in the default email message and the frm-show-entry shortcode
* Fix: Prevent blank lines when headings are excluded in the default email message and the frm-show-entry shortcode
* Fix: Remove the non-functional search box from the Formidable -> Entries page for all forms
* Fix: invalid HTML when displaying paragraph field text in a Dynamic List field
* Fix: Prevent a php error message when showing an empty table from the formresults shortcode
* Fix: & was converting to &amp; in fields watching Lookups
* Fix: Remove fields within section from section's logic options to help prevent logic loops
* Fix: Time field conditional statements weren't showing content when they should
* Fix: Time Field validation was having trouble when the start or end settings didn't include the leading zero (7:30 instead of 07:30)
* Fix: Unique time fields were causing errors on submit

= 2.03.03 =
* Fix: Update the minified JS to match the non-minified version. This fixes issues with calculations.
* Fix: Allow the first form action label to be clickable

= 2.03.02 =
* Fix: javascript error in Safari in form builder
* Fix: Prevent null values from leaving a white space on the entries listing page
* Fix: Form shortcode parameters were also affecting the forms in a widget
* Fix: Prevent action trigger options from getting cut off at the bottom of the page
* **Pro Version** *
* New: Add an option on the Global settings page to fade in forms with conditional logic. This fixes issues on sites with javascript errors causing the form to never show.
* Tweak: don't show the section headings in email by default
* Tweak: Force Ajax submit when editing entry inline
* Enhancement: Add time range validation when submitting the form based on settings for each time field
* Fix: prevent duplicate submissions with ajax submit
* Fix: Entries on listing page were showing a php warning for entries submitted by logged out users
* Fix: Prevent form submission while Lookups are loading options
* Fix: Prevent two common calculation errors
* Fix: Hide the child entries in repeating fields on the page that lists all entries
* Fix: After selecting a form in the view settings, it wasn't possible to show the field keys in the sidebar
* Fix: Lookup Checkboxes weren't saving in embedded form
* Fix: Lookup fields weren't getting enabled if change triggered repeatedly
* Fix: Time fields with missing settings were showing php warnings on the page
* Fix: Make rootline look nice in Edge
* Fix: Autosave values with the non-ajax autosave on page turn
* Fix: Prevent multipage forms from submitting on the first page when redirecting after submit
* Fix: "Array" was showing in the default emails for checkbox fields inside a repeating section
* Fix: Prevent autocomplete dropdown from showing twice when editing in place

= 2.03.01 =
* Fix: Some colors were not being used correctly in the styling settings
* **Pro Version** *
* New: Added an option to use the old time field with one dropdown
* Fix: Syntax error on entry submit in older versions of PHP
* Fix: [25 show=value] was returning the displayed value instead of the saved value
* Fix: Conditional fields were showing after ajax save or ajax page turn
* Fix: Save Rich Text value when form is submitted with button and ajax
* Fix: Prevent button styling from applying to buttons inside a rich text field
* Fix: Only include one column on the entries listing page for post status
* Fix: Fields with conditional logic depending on time fields weren't showing correctly
* Fix: PM was always saving as AM in time fields
* Fix: Time fields sometimes had no minute options depending on the start time and minute step settings
* Fix: Lookup Checkboxes were not saving in embedded forms
* Fix: Removed the section descriptions and duplicate section headings in default email message
* Fix: Prevent double submissions with ajax submit enabled with redirect after submit
* Fix: Default Emails were coming through empty if the form only had embedded forms

= 2.03 =
* New: Add a combined list of all entries on the Formidable -> Entries page instead of defaulting to the first form
* New: Replace submit input with button for new forms. This allows us to show the loading indicator on top of the button instead of outside. This applies to new forms only. Existing forms will need the submit button HTML adjusted to see this new styling. But we decided it was best for reverse compatability if we don't change it automatically
* New: Add frm_after_title hook for inserting content between the title and form fields
* Enhancement: Speed up adding and editing field options and conditional logic in the form builder
* Enhancement: Don't save the field options until the whole form is saved
* Tweak: Pass error array in frm_get_paged_fields instead of true/false. If you are using the frm_get_paged_fields hook, it's possible your code may need to be adjusted.
* Fix: styling issue when select field moves when changing between a blank and not blank option
* Fix: Make sure "Activate" button for add-ons is specific to subsite in multisite network
* Removed: pro fields and styling options from the visual styler, extra pro version css, and registering pro scripts. We don't need unused options.
* **Pro Version** *
* New: Add multi page progress bars and rootline to jump to different pages
* New: Add a page number parameter to the url when the form page changes
* New: Auto-save drafts on page turn when drafts are enabled
* New: Add save button to back-end entries. This allows an entry to be saved from any page when editing.
* New: Break out repeating sections in the email and frm-single-entry shortcode. Now they repeat instead of separate with commas.
* New: Add options for email content/single entry shortcode: include_extras="section,page,html", include_fields="10,15", exclude_fields="10,15"
* New: Change the time field to multiple dropdowns that always save in hh:mm format. This allows for secondary sorting by time fields in views. Using h:i A for the time format in a shortcode will be forced to g:i A.
* Enhancement: Switch the unique time functionality from front-end disabling to back-end validation
* Enhancement: Hide form on page load and fade it in to prevent conditional fields flashing
* Enhancement: Order the post type dropdown by post key instead of defaulting to the order of post type creation
* Enhancement: Make the custom field options more helpful by including custom fields only for the selected post type
* Enhancement: Add .frm_loading_form class on the form tag while the form is processing
* Enhancement: Show the dropzone error message all the time instead of only on hover
* Tweak: Use css to make the conditional logic field options shorter instead of truncating in the form builder
* Tweak: Use function to convert field object to array during in_section migration.
* Tweak: Avoid errors after Lookup field is deleted and other fields watched that Lookup field.
* Fix: Do not delete values in frm_item_metas table for all fields selected in Create Post action (such as the conditional logic).
* Fix: Show the correct option label for a blank value. Previously option label for "0" saved value was displaying.
* Fix: Compact file upload field wasn't aligned with other fields in the row
* Fix: Evaluate date strings the same way in view filters and inline conditions for date fields
* Removed: Old auto updating code. If you are running addons you haven't updated since before November 2015, they will no longer auto update.

= 2.02.13 =
* New: Add frm_send_separate_emails filter. If there are multiple emails in the "to" box, this hook will send one email per address.
* Fixed: Prevent field option reset when a style is included with the imported form
* **Pro Version** *
* New: Add dynamic Lookup population options to paragraph fields
* New: Allow no_link=1 for [25 truncate=20 no_link=1]. By default, when a value is truncated in a view, it shows a link to show more content. This shortcode option can remove the link.
* New: Add does_not_contain filter to stats shortcode
* New: Add frm_filter_view hook for modifying View object
* New: Add frm_graph_default_colors hook
* Tweak: Replace all formidablepro.com links with formidableforms.com
* Improved: Remove arrows on read-only HTML5 number field
* Improved: Apply Formidable styling to Dynamic List fields
* Improved: Allow Lookup fields with repeating section values to filter based on parent value
* Improved: Arrows on read-only number field removed from Firefox
* Fixed: Do not enable read-only dependent Lookup fields
* Fixed: Form scrolls correctly on page turn
* Fixed: JavaScript errors caused by file upload field in Woocommerce form
* Fixed: Allow ajax submit if there are no file fields on the page with a value
* Fixed: Correctly import address values into Address field
* Fixed: [created_at] conditionals take timezone into account
* Fixed: Do not urlencode GET variables that are set in form shortcode
* Fixed: Prevent WordPress auto paragraphs from adding unwanted paragraph tags in calendar View headings
* Fixed: Removed repeating fields from non-repeating field logic

= 2.02.12 =
* Fixed: PHP 7.1 illegal string offset warnings addressed.
* **Pro Version** *
* New: Add frm_order_lookup_options hook to adjust the order of options in a Lookup field.
* Fixed: JavaScript errors were occurring when illegal file type was uploaded and Woocommerce was active.
* Fixed: Make sure PDF files display correctly with show_image=1 in WP 4.7.
* Fixed: Scroll error when switching pages with Ajax submit.
* Fixed: Make sure dependent autocomplete Lookup fields aren't disabled permanently.
* Fixed: Make sure GET variables set in View shortcodes work correctly.

= 2.02.11 =
* New: Added frm_create_default_email_action hook to prevent default email action creation.
* New: Added frm_inline_submit CSS Layout Class.
* Improved: Include IP for checking comment blacklist.
* Improved: Load minified themepicker js and placeholder js when possible.
* Improved: Better spam checking with Akismet.
* Improved: Update placeholder JS for old browsers to v2.3.1.
* Fixed: Don’t force fields created by a add-on to a text field when Pro is not installed.
* Fixed: Style success message text color now applies to nested paragraph tags.
* Fixed: Prevent PHP warning messages some sites are seeing during cache key retrieval.
* Fixed: -1 offset in frm_scroll_offset hook now stops auto-scrolling.
* Fixed: Invalid Portuguese translation for field is invalid message.
* Fixed: A few HTML errors on form Settings page are resolved.
* Fixed: Set default margin on checkbox and radio divs. This resolves conflicts with Bootstrap styling and frm_two_col and frm_three_col classes.
* Fixed: If same form is published multiple times on the same page, make sure success message shows with the right occurrence of the form. Auto-scroll to the correct form as well.
* **Pro Version** *
* New: Add time_ago=2 and time_ago=3 to created_at shortcode options. The number used here will determine how many time levels are shown, e.g. time_ago=1 will show “2 years”, time_ago=2 will show “2 years 3 months”, time_ago=3 will show "2 years 3 months 15 days”.
* New: Allow time comparisons with NOW, e.g. [if x less_than="NOW"]Show this content[/if x]
* New: Added author_link parameter to userID shortcode options. [25 show=author_link] will include a link to the WordPress author page.
* New: Added frm_allow_date_mismatch hook to improve validation for certain date formats. This may be needed if you are using a custom format in your date fields that include non-English text.
* New: Added greater than or equal to and less than or equal to filter to stats and graphs.
* Improved: Added comma between values in text calculation when multiple values come from the same field.
* Improved: RTL indented sub-category styling for checkboxes and radio buttons.
* Improved: Include the hidden Dropzone field inside the form instead of in the footer.
* Improved: Remove entry div from the page after fadeout when deleting an entry.
* Improved: Order post type dropdown in Create Post action by post key.
* Improved: Allow repeating fields to be selected in a field's Lookup population options.
* Improved: Repeating entry keys are now randomly generated.
* Improved: Allow dynamic default value shortcodes in calculation box.
* Fixed: Do not filter shortcodes submitted in entry when editing the entry. Process external shortcodes before replacing [input].
* Fixed: The default value is now set correctly in dependent Lookup radio and checkbox fields.
* Fixed: Style font now applies to section headings.
* Fixed: Unnecessary urlencode removed from $_GET variable. Allows searching with + symbol in query string now.
* Fixed: Endless loop prevented when new child form is given same ID as parent from XML.
* Fixed: Allow updating of read-only dropdown in back-end Entries.
* Fixed: Lookup field options wouldn't update while dropdown was open in Chrome on Windows. Dropdown is now disabled until options are completely loaded.
* Fixed: Ensure that autocomplete text shows in dropdown when theme styling is overridden in Style.
* Fixed: UserID field now autopopulates correctly when creating an entry in the WordPress dashboard.
* Fixed: Errors on entries page when file didn't exist.
* Fixed: The include_zero parameter in graphs caused errors with certain WordPress date formats. This now works with any date format.
* Fixed: Using "hours" in a Creation Date View filter would result in erroneous results.
* Fixed: Data was duplicated in the frm_item_metas and post meta or taxonomies table. It is now only present in the post meta or taxonomies table.
* Fixed: Do not force html=1 on file upload field in frm-field-value shortcode.
* Fixed: Prevent errors when a hidden field or text field is used for post status.

= 2.02.10 =
* Add frm_form_error_class hook
* Fix db error when updating title in some forms
* Fix unclickable keys in Customization Panel
* Fix print styling on entries page
* Clear entry cache after delete all entries
* **Pro Features** *
* Add frm_time_ago_levels hook for drilling down time_ago
* Fix adding new file after removal when editing
* In multi-site, only copy forms if copy form setting is checked
* Make sure detaillink works if View is on home page

= 2.02.09 =
* Add frm_before_install hook
* Trigger a database update to flush permalinks
* Fix PHP 5.4 syntax error
* **Pro Features** *
* Fix single post page content

= 2.02.08 =
* Fix recaptcha error (change default to normal)
* Prevent double submit clicks
* Make sure recaptcha English language setting applies
* Add placeholder color CSS
* Add frm_after_import_form action hook
* Add frm_send_email hook for stopping the email
* Add frm_upgrade_page hook
* Include field object in frm_prepare_data_before_db hook
* Fix nav errors when trying to edit form that doesn't exist
* Replace specific cache key deletion with group cache delete for more cache clearing fixes
* **Pro Features** *
* Added pretty URLs to views
* Added month and year labels in credit card dropdowns
* Show user options for admin when editing user-limited dynamic field
* Added frm_load_dropzone hook for disabling dropzone
* Added frm_dynamic_field_user hook for dynamic fields.
* Load pro translations in plugin instead of allowing translations from wp.org to trump
* Fix error when editing a multi-page form with blank repeating section on separate page
* Add space next to collapsible section icons
* Fix required file error when file is present
* Fix clearing dependent autocomplete dropdown in repeating sections
* Update EDD updater

= 2.02.07 =
* Improve cache clearing in order to make Formidable compatible with persistent object caching
* Add vertical-align:baseline to radio and checkboxes to prevent styling conflicts
* Add hook for invalid form entry error message
* Add form id to 'frm_include_meta_keys' hook
* Fix IE11 and Edge form builder issues with editing field options
* Allow localhost to pass URL validation
* Remove frm_field_input_html calls for fields on form builder page
* **Pro Features** *
* Add option to send emails on import
* Allow default style to export with form
* Fix form importing with a style (make sure style is selected in form's settings)
* Fix exporting Views without form
* Fix adding subfield types in Chrome
* Make sure "Remove" link still shows when frm_compact is used in file upload field
* Make sure actions only trigger on import when import is selected
* Check if transient timeout has been deleted to prevent expired update links
* Fix conditional logic in a section after removing and re-adding a row
* Make sure repeating section is cleared after a form is submitted
* Move repeating section form to trash when parent is trashed
* Make sure collapsible icon uses section color
* Fix errors when "Show page content" is selected in form's settings
* If repeating form entries page is accessed directly, go to parent's entries page
* Fix editing entries with file upload in repeating section
* Allow translation of more file upload messages and no results message in autocomplete dropdown
* Fix donut graphs
* Fix PHP 5.2 graph errors
* Adjust lowercase value sorting in Lookup fields
* Fix auto_id errors with WPML
* Prevent multiple View filters from being added during migration
* Fix Phone Number mask on ajax submission form
* Fix exclude_fields option with editlink
* Fix truncating in place so it doesn't cut words in half
* Make sure autocomplete, multi-select, and read-only attributes are included for category dropdowns
* Remove "Unique" option from userID fields
* Fix hidden row_ids field name (in repeating section)
* Make sure character limit, read-only, etc do not apply on form builder page
* Make sure second and third level Lookup fields correctly limit options to the current user
* Fix HTML validation errors for checkboxes in repeating fields

= 2.02.06 =
* Prevent styling conflict with field buttons on form builder
* **Pro Features** *
* Add styling for left and right labels in combo fields
* Fix PHP 5.2 error in graphs controller
* Add taxonomy support to graphs and stats
* A couple other graph fixes
* Fix entry_id and created_at issue with stats
* Fix lowercase text sorting in Lookup fields
* Make sure conditional logic works on embedded form fields when editing
* Fix conditional logic dependent on hidden embedded form field
* Make sure time field displays correctly by default with frm-field-value shortcode
* Add Format option to Text fields

= 2.02.05 =
* Clear caching when updating styling settings
* Add frm_field_div_classes hook
* Remove deprecated safe_mode check
* Warning added for invalid height/padding styling combination
* **Pro Features** *
* Added several new graph types and options
* Prevent repeating field value duplication when saving drafts
* Add migration to remove duplicated repeating section data
* Show child forms in CSV export options
* Allow 2 decimal places for max file size
* Fix frm-stats y=""
* Allow time fields to be used in calculations
* Add a Country label option for Address fields
* Remove a few graph filters
* Make sure address fields display error messages
* Make sure category fields keep value when saving a draft
* Other bug fixes

= 2.02.04 =
* Add field description margin option
* Fixes for submitting forms in <IE10
* **Pro Features** *
* Prevent star styling conflict with some themes
* Fix conditional logic dependent on numeric checkboxes
* Allow arrays in Lookup checkbox field default value
* Make sure Lookup fields load correctly with ajax in repeating section on form builder
* Fix field errors showing on dependent Dynamic fields
* Only validate on change when js validation is on
* Make sure address field errors show up correctly
* Include confirmation field classes for old HTML
* Make repeating fields work with frm_date_field_js hook
* Don't show long decimal for allowed file size
* Fix conditional logic with ampersands
* Fix left label with autocomplete dropdown
* Don't allow switching between sections and HTML field types
* Fix JS error when multi-select logic field has no options selected
* Make sure WP errors are returned correctly in file upload field
* Allow use of show_image, add_link, and show_filename with frm-field-value
* Don't show date pop-up for read-only date field
* Fix ordering by number fields mapped to a custom field


= 2.02.03 =
* Update translations
* **Pro Features: ** *
* Fix file upload field display with an ID ending in 0
* Fix file upload fields in a repeating section when editing entries
* Don't trigger update on all repeating Lookup fields when new row is added
* Don't clear Lookup dropdown values when editing
* Fix showing the error message on confirmation fields
* Fix conditional shortcodes for embedded file upload fields
* Make sure visible repeating dependent Dynamic field values are not cleared

= 2.02.02 =
* Fix clicking the undo link after bulk trash forms
* Add submitFormManual function for custom scripts
* HTML5 error fields now have styling
* **Pro Features: ** *
* Fix conditional logic on radio fields in an embedded form
* Add download link to files in uploader
* Fix undefined message in Address fields
* Make sure new file upload field works in repeating sections
* Show credit card errors
* Make sure date fields show the correct format in repeating sections when editing
* Make sure incorporated upload importer works correctly
* Fix file upload field on multi-page ajax submission form
* Exclude credit cards and passwords from default email
* Fix phone number field JS issues in firefox
* Add loading text to Lookup fields
* Improve reverse compatibility for file upload field ID shortcode
* Allow specific user ID and entry ID searching in form entries tab

= 2.02.01 =
* **Pro Features: ** *
* Add styling for uploader with left label
* Add styling for rich text field with left label
* Make new file upload strings translatable
* Fix default date issue in Firefox
* Search post fields in admin entries tab
* Allow searching by IP address in admin entries tab
* Fixed showing default avatars if there is none selected in the form
* Fixed issue with newly uploaded files getting dropped when there are validation errors while editing an entry
* Fixed issue with showing the url of the icon instead of the uploaded file by default

= 2.02 =
* Improve user role dropdowns in global settings
* Remove some deprecated functions
* More output escaping
* Move file creation for stylesheet to its own file for an easier API
* **Pro Features: ** *
* Add drag and drop file uploading
* Add options to set the file size and count limits
* Merge the upload importer plugin into main plugin
* Filter the media library so uploads in forms can only be viewed by those with permission to edit entries
* Don't allow direct viewing of attachment pages
* Add file protection options per form to prevent direct access to files
* Add a prefix to the attachment slug to prevent the attachments from using top-level slugs that would be better used elsewhere
* Process files during validation if javascript errors on the page, or if browser doesn't support the in-place file uploader
* Include the error message at the top of the page when there are ajax validation errors
* Add frm_image_html_array hook so we can tie in with modals soon
* Add an option to use text calculations for combining values instead of requiring math operations
* Add age shortcode for use in calculations. Use [age id=25] in a calculation, where 25 is the id of the date field.
* If a calculation doesn't have any field triggers, trigger it on load
* Fixed calculations using multiselect fields across page breaks
* Fixed date calculations with an empty date field. The calculation will now wait until any date fields have been filled.
* Only use ajax validation when ajax submit is turned on to prevent double validation checks
* Allow recaptcha to be dragged into section
* Fixed issue with html showing in rich text
* Fixed filtering conditionals from parent form inside of a foreach loop
* Change default sep for images displayed in View
* Don't force links when displaying a non-image file
* Make sure repeating fields watching Lookups keep custom value
* Fix undefined index error with date field calendar style setting
* Make sure page size and limit work together well in Views
* Don't run date calculations until all dates are selected
* Allow address field labels to be edited when field is initially added
* Fix single row Lookup field checkboxes styling

= 2.01.03 =
* **Pro Features: ** *
* Make sure HTML entities don't show in rich text fields

= 2.01.02 =
* Increased minimum required WordPress version to 3.8
* Added frm_skip_form_action hook. This hook can be used to change whether the action is skipped or not
* Added border radius settings to success and error messages
* Fixed issue allowing a trashed form shortcode to still show the form
* Fixed issue causing &amp; to show instead of & when editing paragraph fields
* Removes French and Swedish translations since they are complete online
* Update for better cache deletion in WP 4.0+
* Allow a specific field type to change the value for emails and entry array
* Prevent errors with Redis cache plugin
* Improve styling for submit buttons on mobile devices
* Don't let imported style override default
* Add frm_clean_[field-type-here]_field_options_before_update hook
* Fix &, >, and other character comparison issues
* **Pro Features:** *
* Added frm_combo_dropdown_label filter. This hook can be used to add a label to the blank option in combo field dropdowns (state, and country, Credit card month, year)
* Added frm_use_embedded_form_actions hook. If this hook is used to return the value "true", form actions for embedded forms will be fired
* Make sure the view page number is not 0
* When getting the entry array, address fields will no longer be sent as an array
* Don't load more than 500 options in a lookup field on the form builder page
* Fixed rich text fields with in place edit
* Fixed javascript error when submitting an entry from the back-end
* Use the correct calendar locale when editing an entry from the back-end
* Fixed issue with the unique message being replaced if it was the same as the global unique message
* Fixed issue with dependent dynamic field change events not getting triggered
* Exclude password and credit card fields from the default email message
* Add views parameter to frm-search shortcode
* Fix error that appeared when using a date field in a calculation in multi-page form
* Fix issue with switching from a dropdown to a Lookup field
* Fix issue with loading icon replacing Lookup field options
* Set confirmation field description correctly
* Allow post fields in form action conditional logic
* Set default value correctly when address field is shown
* Don't let spaces break conditional logic
* Fix JS error after in-place edit
* Fix PHP warning message for Lookup fields
* Fix ambiguous form ID SQL error
* Don't require credit card fields when editing
* Make sure ampersand doesn't break Lookup fields
* Add autopopulate option to Image URL, time, and hidden fields
* Add hook to allow LIKE comparison with Lookup fields

= 2.01.01 =
* Use a different email regex to allow more characters, and longer TLDs
* Only load custom styles on the styler. Don't include it on the manage styles, or custom css tabs. Bad custom css can make the page uneditable.
* Fix issue preventing the option to Allow the multiple recaptchas to be turned off
* Fixed issue with white space allowed in field options when bulk editing
* Use javascript instead of jQuery to scroll after submit
* Add missing styling to make inline labels work with checkbox/radio fields
* **Pro Features:** *
* Add checkbox option to Lookup fields
* Hide empty radio/checkbox Lookup fields
* Allow autocomplete Lookup fields to populate text fields
* Add filter for setting field type used for logic 'frm_logic_FIELDTYPE_input_type'
* Prevent duplicate submissions during in-place-edit or ajax-loaded forms
* Make sure post fields show up correctly on edit
* Always show the number range options in a number field
* Fixed issue with dynamic list fields not saving a value when the visibility is set to administrator
* check for __frmDatepicker before using it. This resolves issues when using a date field with custom code.
* Strip slashes shown in an entry right after POSTing
* If a field in calculation is missing, don't break the js
* Prevent conditional loops with dropdown fields
* Fixed issue with Dynamic fields not showing up when expected, and when editing 3+ level Dynamic fields
* Remove the country field when the "other" address option is selected
* Fixed issue with required Address field on conditionally hidden page. They were still required, even when skipped.
* Fixed star fields on multi-paged ajax forms. Going back a page was showing radio buttons.
* Fixed issue with small autocomplete field, when it is hidden on page load.
* Fixed issue that allowed conditional fields to show right after hitting "save draft"
* Fixed default values inside of conditional logic
* Fixed issue preventing "none" from being selected for the date styling in the styles

= 2.01.0 =
* Allow shortcodes for the submit button label
* Increase the timeout for activating a license
* Add a couple static functions to use in add-ons with form actions
* Don't show templates on the addons page
* Add frm_switch_field_types hook for specifying which fields can be switched to/from
* Add Authorize, Stripe, WOO, and copy icons
* Some back-end styling improvements
* Additional bug fixes
* **Pro Features:** *
* Add new "Lookup" field type
* Add "Lookup value" option to several field types
* Add clear on focus and default blank option to Address fields
* Move form submit js to js file
* Add repeating field args to new and edit fields hooks
* Refactor conditional logic
* Improve and limit post redirection
* Cut down on View query size when no field filters are set
* Additional bug fixes

= 2.0.25 =
* Add an option to allow multiple recaptchas. This is off by default to avoid new conflicts.
* Use the recaptcha size setting when displaying multiple recaptchas per page.
* Add frm_after_field_is_imported and frm_prepare_single_field_for_duplication hooks
* Add property="stylesheet" to the stylehsheets when HTML5 is enabled
* **Pro Features:** *
* Redirect to post when View detail page is linked to post
* Make sure entry ID is unique filter does not get used
* Make sure limit applies to a View's pagination
* Add dynamic field to frm_data_sort hook
* Add a message when the file upload field is included before a page break
* Fix the issue with the frm_first class applying to the confirmation field
* Remove the invalid email message used when the email confirmation doesn't match

= 2.0.24 =
* Add option to use the dark reCaptcha
* Show a helpful error message when recaptcha communication fails
* Fix the clear on focus setting to not switch to the default blank
* **Pro Features:** *
* Validate recaptcha during the javascript validation checks
* Make sure required credit card fields are required
* Add option to removes names from credit card fields to prevent the values from being posted to the server (most secure)
* Don't require address fields when conditionally hidden
* Exclude linked dynamic fields from calculation fields dropdown since they aren't functional
* Improve third-party shortcode filtering in Views
* Ignore View filters with no value selected for where field
* Fix the file upload background color setting
* Include a flag on the field to indicate if it is inside a section or not

= 2.0.23 =
* Add support for multiple reCaptchas on a page
* Make sure the screen options for the form listings only shows when needed
* Make sure a value is selected when it includes an &
* Load grid CSS on the back-end entries and form builder pages
* Allow transparent background color on fields and form
* Don't update clear on click options until whole form is saved
* Don't force an array to be a string before going through get_display_value function
* Added frm_trigger_create_action hook to alter action triggering
* Added frm_csv_format hook for changing the exported CSV format
* Added frm_is_field_required hook for allowing a field to be conditionally required
* Added frm_field_options_to_update hook for setting more field options to update
* Added frm_display_FIELDTYPE_value_custom hook
* Added frm_get_FIELDTYPE_display_value
* Added frm_csv_field_columns hook. Once the columns are added, if a field value is an array, it will automatically fill added csv columns
* **Pro Features:** *
* Added straight up client-side validation
* Added Credit card and Address field types. Enable the Credit card field with add_filter( 'frm_include_credit_card', '__return_true' );
* Allow actions to be triggered when a draft is saved
* Allow free text in user id field filtering in views
* Improved the unique filter in Views
* Add Entry ID filter to all existing single entry Views instead of always checking for the entry param in the url
* Allow "any" for the number step
* Updated Chosen script to 1.5.1
* Correctly check for multisite sitewide activation
* Fixed the problem with the file upload attachment option not staying checked
* Fixed filtering by entry ID and fields together in Views
* Make sure the limit setting doesn't affect calendar Views, and show empty calendar for Calendar views with no entries
* Make sure closing shortcodes in After Content are filtered
* Make sure Dynamic List fields show up in default html email
* Make sure we are jumping to the first field in the form after validation instead of the field with the lowest id
* Fix field ID issue in repeating sections across pages

= 2.0.22 =
* Add an upgrade banner when affiliate links are active
* Add permission checks in addition to nonce for several actions for extra security
* Don't allow javascript to be saved in field choices
* Include the admin_url params inside the function to resolve a conflict with WPML
* Prevent XML breaking with US character
* Fix rand() error with float some users are seeing with PHP7
* **Pro Features:** *
* Add the option to automatically delete files when a file is replaced, and and entry is deleted
* Allow a prefix and/or suffix along with the [auto_id] shortcode
* Add is_draft shortcode for views. This allows [is_draft], [if is_draft equals="1"]-draft-[/if is_draft], and [if is_draft equals="0"]-complete-[/if is_draft]
* Add frm_no_entries_message filter to adjust the output when there are no entries found
* Add frm_search_for_dynamic_text hook for searching numeric values in Dynamic fields
* Add the saved value into the array and json response. The entries fetched using FrmEntriesController::show_entry_shortcode were only returning the displayed value. This adds the saved value to the array as well. This covers user id, dynamic fields, radio, dropdown, and checkbox fields anytime the saved and displayed values are different.
* Add filter on add/remove fields to allow translations
* Default new number fields to use "any" step
* Fix conditional logic dependent on a paragraph field
* Fix date fields inside form loaded with in-place-edit

= 2.0.21 =
* Add a timestamp to the css file instead of plugin version number to improve issues with styling caching
* Add pro tips & upgrade calls
* Fix bug with importing old forms with no custom style
* **Pro Features:** *
* Remove autoinsertion options from the view settings. Any views that were set to be inserted automatically will have their shortcodes saved onto that page.
* Allow the delete link to work after an ajax load
* Apply styling settings to HTML fields
* Randomize entry key on CSV import
* Make sure the old transient doesn't delay automatic update
* Allow forced plugin update check if it hasn't been forced before
* Fix CSV import form dropdown with only 1 item
* Fix bug with importing data in embed form fields
* Fix time_ago issue with blank value
* Fix missing pro license message to link to global settings
* Fix a fatal error when a non-Site Admin visits the Global settings page in multisite

= 2.0.20 =
* Added more styling options: box-shadow, font-weight, Form Title, and Form Description
* Fixed a couple issues with activating and deactivating licences
* A few improvements for importing styles
* Add a hook for approved theme authors to add affiliate links. If the free version is packaged with a theme, the theme author can get commissions on upgrades.
* **Pro Features:** *
* Added Parent entry ID to view filters
* Added a button to autofill addon licenses
* Improve accuracy of time_ago for leap years

= 2.0.19 =
* Add CSV export to free version
* Add page with list of add-ons
* Set up base for allowing affiliate links inside the free version
* **Pro Features:**
* Updating improvements
* Add show_image=1 and add_link=1 to file upload field shortcode
* Show draft, pending, and private posts for creator and admin in frm-entry-links shortcode
* Make sure Number fields perform calc when shown
* Deprecate the frm_csv_field_ids filter
* Fix graph bug
* Fix Dynamic Field filtering in Views
* Fix JavaScript error in repeating section
* Fix showing errors in collapsible sections
* Hide the end divider field when needed
* Fix inline label for Dynamic dropdowns
* Make LIKE logic case-insensitive in field ID shortcode
* Make sure multiple file upload fields save on edit when all files are removed
* Fix conditional logic issues with extra white space
* Fix LIKE conditional logic issues with arrays and number values
* Fix calcs with edit in-place
* Include embedded fields in CSV export

= 2.0.18 =
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

= 2.0.17 =
* **Pro Features:**
* Fix post update bug

= 2.0.16 =
* Escape font family correctly for quotation marks
* Only check for updates every 24 hours
* Allow emails to be separated by a space
* Prevent old versions of Akismet from causing errors
* Add unit tests for XML import
* Styling updates for WP 4.4
* Save form action settings even if they aren't in the default options
* More contrast on form builder page
* Use normal font weight for from builder
* **Pro Features:**
* Allow Styles to be duplicated
* Allow the form key in the CSV download url
* Make like/not like conditional logic not case-sensitive
* Fix multiple conditional logics on a Dynamic field
* Fix XML import with repeating fields
* Fix notice for old dfe fields
* Make sure integer is being used for auto_id
* Fix read-only dependent Dynamic field with a default value
* Fix conditional radio button default value issue
* Fixes for conditional logic on sections
* Fix autoupdating for add-ons
* Show a message if no license has been entered for an add-on

= 2.0.15 =
* Send plugin updates through formidableforms.com
* Update Swedish translation
* Use media_buttons hook instead of deprecated media_buttons_context hook
* Unit test updates
* Fix Portuguese translation error
* Allow more characters in field description
* Prevent plugin styling conflict with user roles dropdown
* Fix installing when the plugin is activated
* Get rid of ambiguity in FrmField::getAll function
* Fix the plugin url when used in the mu-plugins folder
* Make selected values show on form builder page before refresh
* Minor styling changes to frm_total class
* Update stylesheet after import
* Make long text wrap in Chrome cb/radio fields
* Add 'compact' option to Recaptcha
* **Pro Features:**
* Fix conditional logic bug
* Fix calculations in repeating sections with visibility admin
* Fix dynamic list fields in repeating sections
* Fix JS error when removing row w/ read only field
* Add JavaScript hook for removeRow function
* Fix error for ordering view by time
* Fix error with no posted values in embedded forms
* Escape CSV cells with ' if they start with = (this is to prevent a vulnerability in Excel)
* Prevent array keys from being appended to user meta vals
* Switch autoupdating to EDD at formidableforms.com
* Prevent frm_display_id from switching when View is updated
* Fix multi-file upload display for embedded forms
* Allow text value to autopopulate dynamic fields
* Make embedded fields show up in View filters
* Don't let userID field affect css class in repeating section
* Don't check for shortcodes if no brackets are present
* A few auto updating fixes for multisite
* Switch [foreach 25] when form is duplicated


= 2.0.14 =
* Stripslashes in Other field
* Prevent collapse icon from being inserted inside of section
* Make sure roles dropdowns show correctly after clicking update (in Global Settings)
* Make form listing and entry listing pages responsive
* Don't truncate form name in excerpt mode
* Fix validating embedded forms
* Fix filtering by repeating fields in Dynamic Views > Detail Page
* Fix dependent Dynamic autocomplete fields with default values
* Fix logic on embedded forms and multiselect dd
* Some readonly field fixes
* Read-only improvements for multiselect dropdown
* Improve JavaScript for multiple forms on a page
* Use the minified css for jQuery UI styling
* Allow view filtering with time fields using NOW
* Allow times to be formatted with [25 format='g:i A']

= 2.0.13 =
* Allow recaptcha to be conditionally hidden on previous pages of form
* Fix error with embedded form on a conditionally hidden page
* Show the editlink after an entry is edited in place
* Make sure collapsible section icons show regardless of characters in the section title
* Don't require conditionally hidden dynamic category fields
* Add fields attribute to [editlink] shortcode
* Fix calculations using repeating checkboxes
* Prevent double click on Add button in repeating section
* Clear conditionally hidden fields even if they aren't visible
* Make sure pro templates get imported in multisite
* Use separate values by default for post status fields
* Make sure the separate values option is allows for post status fields
* Show the frmcal-week-end class consistently
* Fix default values in repeating Dynamic fields
* Make Private Views show up in shortcode builder
* Don't do calculations in conditionally hidden repeating fields
* Do calcs in repeating fields when adding a row
* Improve JS for IE8
* Fix unique time error
* Fix default date in jQuery calendar
* Allow entry key with frm-field-value shortcode
* Fix unique time error
* Improve calculations across page breaks
* Prevent clearing default values when hiding/showing sections
* Run calculations in collapsible sections
* Fix duplicating regular sections
* Add post ID View filter
* Fix empty graphs
* Allow frm-entry-update-field, editlink, and deletelink inside foreach loop
* Fix importing parent_form_id on child forms
* Allow css file updating if FTP creds are present
* A few jshint fixes
* Add 'frm_ajax_url' hook
* Allow layout classes to be used with submit button
* Remove frm_last class
* Prevent get_filesystem_method error in multisite after update
* Conditionally use ssl for the ajax url for form submission


= 2.0.12 =
* Add option to center form on page
* Improve styling classes for more consistency across different field classes, and make all classes responsive
* Added a few more styling classes: frm_three_fourths, frm_two_fifths, frm_three_fifths
* Remove in-place-editing from the field keys on the form builder page
* Add 'frm_after_update_field_name' hook for changing a field name during editing
* Update Bootstrap multiselect to v0.9.13
* Add license page to prepare for add-ons. Big things are coming.
* Fix: Prevent loading icon from being huge in some themes
* Fix: When the jQuery UI css is loaded by another plugin on the form builder page, the required icon looked the same whether required or not. This styling conflict is resolved.
* Fix: Make sure the form description size can be changed in the styling settings.
* **Pro Features:**
* Views can now be filtered by fields in the repeating sections.
* Added [parent_id] shortcode for use in views. This shortcode will only have a value when the displaying entries in repeating sections.
* Allow views to be created using the repeated entries. Since each repeating row is an entry in a hidden form, we can allow views to be created using those repeating rows for more flexability.
* Added order parameter to frm-entry-links
* Allow options in a post status field to come from the form builder. The options should have separate values and the saved values can include 'publish', 'draft', 'private', 'scheduled'.
* Remove the option to lock field and form keys. This is more of a hassle than a feature.
* Allow the entry key to be used with the frm-field-value shortcode instead of forcing the entry
* Replaced inline 50px height for image fields with .frm_image_from_url class for easier control
* Improve file upload field in Chrome to prevent extra space from showing.
* Added 'frm_save_post_name' filter. This can be used for custom form actions that create posts.
* Added 'frm_display_data_opts' filter.
* Prevent frm_display_id custom field from saving when a field is selected in the create post settings instead of customized content
* Fix: When forms were submitted without ajax, the redirect wasn't working consistently.
* Fix: The shortcodes weren't processing in the message shown after an entry is updated. 
* Fix: When we prevented the PayPal action from triggering on import, we stopped all actions. This is now fixed so an action can be set (in the code) to be triggered on import. Posts will now be created on import again.
* Fix: The dynamic list field was showing the entry ID in the entries tab instead of the value.
* Fix: The Add row button wasn't showing in a repeating section when returning to edit an entry if there were more than two rows in the section.
* Fix: Improve dropping a field between two sections.
* Fix: Remove nonce check for frm-entry-update-field shortode. Page caching gives front-end nonce checks issues.
* Fix: We changed the parameters sent to the frm_after_update_field hook without realizing it. The 'field_id' attribute was sometimes an object, but was previously always an integer. This has been updated for reverse compatibility, and 'field' has been added with the full field object.
* Fix: If you put -100 for the start date in a date field, -100 would show in the date field instead of 1915. This is now working correctly for dynamic values like this with three digits.
* Fix: When filtering a view with a Dynamic field, NOT EQUAL TO will work correctly.
* Fix: Double quotes were causing trouble when included inside an error message returned by the frm_validate_field_entry hook
* Fix: Graphs using x_axis and start_date were having trouble
* Fix: The js error after selecting an option in autocomplete field is fixed when there are calculations in the form.

= 2.0.11 =
* Fix issue with clearing array keys when sanitizing an array when displaying entry values
* When the email "reply to" value uses the "from" setting, only use the email address without the name
* Switch the form action events to dropdown instead of checkboxes
* Shrink the reCaptcha on small screens
* Add font-weight 100-900 options to styler
* Add frm_email_message filter
* Fixes for javascript errors while form building in IE8
* Only load font.css for the admin bar when user can edit forms
* Add frm_include_form_tag filter for using div instead of form tag
* Add frm_show_submit_button filter for hiding and showing the submit button
* Fix date calculations using date formats that begin with the year
* Allow classes to be included on a hidden input
* Process the entry a little sooner (wp_loaded instead of wp)
* Add frm_capitalize layout class
* Make frm_customize class work for more field types
* **Pro Features:**
* Set new views to private by default
* Allow clickable=1 in the frm-show-entry shortcode
* Make sure to show the Add row button for repeating sections in IE9
* Correctly require a password before showing password protected views
* Add update_single_field function for easier updating of a single field
* Add frm_datepicker_formats hook for adding format additional options on the Formidable -> global settings page
* Add frm_csv_row hook for adding data to a row in the CSV
* Keep the Pending status selected after save in the post settings
* Add frm_is_dynamic_field_empty filter for overriding whether a dependent dynamic field is available for validation or if the value should be cleared
* Improve dragging and dropping large sections of fields
* Don't trigger form actions while importing
* Make sure $entry_ids is still intact for the frm_after_content hook
* Replace m/d/Y format option
* Fix updating a field with the frm-entry-update-field shortcode
* Work on calculating different combinations of fields inside and outside a repeating section
* Only return unique values when a list field is displayed based on checkboxes
* Fix searching entries with a trailing space
* Fix truncating in the dynamic content

= 2.0.10 =
* Add frm_action_triggers hook for adding custom triggers into the actions
* Add frm_{action name here}_action_options hook so any action can be altered
* Prevent extra form actions when a form is duplicated
* Load correct version of formidable.js based on wp-config debugging constant (Thanks @naomicbush for the contributions!)
* Revert get_sortable_columns changes for < WP 4.0 support
* **Pro Features:**
* Allow calculations inside repeating sections and embedded forms
* Set default values for conditional checkboxes and radio fields and inside conditional sections
* A few changes to the way section fields create divs

= 2.0.09 =
* Add frm_time_to_check duplicate entries filter
* Allow custom JavaScript validation
* Add frm_do_html_shortcodes fiter
* Fix the duplicate entry check
* Include get_columns function in list tables for 4.3
* Use relative URLs in the stylesheet
* Make frm_fifth classes responsive
* Allow 0 to be saved in a checkbox field
* Fix saving forms as drafts
* **Pro Features:**
* Reduce database calls for Views
* Allow format for default [time] and [date] shortcodes ie [time format='H:i A' round=5]
* Include Dynamic List fields in calculation options
* Make file upload fields more responsive
* Improve repeating section styling
* Improve calculation triggering when fields involved are conditionally hidden
* Don't clear readonly fields or default values when hidden conditionally
* Don't trigger dynamic field values if there is other logic telling the field to be hidden
* Include Indonesian option for datepicker
* Allow the post author to be changed by the user ID field on edit
* Trigger calculations at the time a conditional field is shown
* Keep the value submitted in a dynamic list field
* Fix graphs that show multiple fields and define the x-axis
* Allow graphs to be included in the success message after ajax submit
* Conditionally show the max character setting in number fields based on whether HTML5 is enabled
* Allow scale fields to work in calculations across multiple pages
* Turn off ajax submit if repeating section includes a file
* Fix entry creation date format on import
* Fix filtering by a checkbox field in the frm-stats shortcode
* Fix logic for third-level fields with conditional logic inside a repeating section
* Make sure conditional logic only affects the newly added row when triggered after a row is added
* Make sure orphaned data is deleted when switching divider to repeating/non-repeating
* Allow drafts=both with user_id filter in View shortcode
* Fix conditionally required fields when option includes quote
* Fix date field errors on multi-page form that submits with Ajax
* Prevent the JetPack conflict with the shortcodes module
* Fix sorting in dependent Dynamic fields
* Registration add-on login form styling
* Fix inline scale field labels
* Fix spacing issue with repeating section grid
* Fix truncation with special characters
* Fix importing repeating sections and embedded forms
* Fix readonly checkboxes in calculations
* Don't show empty custom field rows in the post settings
* A few fixes to the formresults shortcode including the file display
* Fix error when duplicating a section without any included fields
* Fix timezones for searching by entry creation and updated dates in a view

= 2.0.08 =
* Fix security vulnerability allowing shortcodes to be excuted inside a form https://research.g0blin.co.uk/?p=618&d=i4ziyggqao0oz0L0vpUTd8KZwrO2P9Mw
* Added frm_filter_final_form hook. This will need to be used to cover shortcodes that span multiple blocks of field HTML since we can't do a general shortcode replacement on the rendered form
* Revert change that prevented scripts from firing in the form success message
* Fix timestamp timezone on view/edit entry page
* Added frm_entries_{$col_name}_column hook to allow custom columns on the entries listing page
* Pro: Allow the last page of a form to be conditional
* Pro: When a field is conditionally hidden, clear the value and trigger calculations and child logic 
* Pro: Improved accuracy of calculations using the other option, and across page breaks
* Pro: Added frm_calendar_day_names hook for displaying the full weekday name in calendar view
* Pro: Allow a comma-separated list of ids when filtering by entry id in the view settings
* Pro: Include the remove link on multiple file uploads
* Pro: Display a view row correctly right after a quick-edit
* Pro: Delete views when their form is permanently deleted
* Pro: Only show the ID column in google table when specified
* Pro: Fix boolean values in google entry table
* Pro: Reduce the memory usage when exporting a CSV by preventing entry caching
* Pro: Fix dependent taxonomies
* Pro: Fix the graph tooltips and wrap the text on graphs so it doesn't go beyond the width of the graph
* Pro: Allow the frm_user_can_edit hook to fire when loading a form with the entry id in the form shortcode
* Pro: Fix backslash removal in the phone format option when the form is saved
* Pro: Make sure validation is always performed even if there are only radio fields on the page, before showing a message that the entry failed
* Pro: Fix Dynamic List fields dependent on Dynamic checkboxes
* Pro: Keep the user on the last page when a draft is saved and there is only one field on the last page
* Pro: Export the category name in the CSV instead of the id
* Pro: Save user ID even if it's in a conditional section/page

= 2.0.07 =
* Don't escape urls in ajax
* Correctly save all the options on the form settings page

= 2.0.06 =
* Fix an XSS vulnerability in the lite version. When the pro version is active, the vulnerability was resolved.
* Increased security
* Fix the shortcode display on form listing page
* Add frm_helper_shortcode filter
* Prevent javascript error on form settings page when WooThemes Helper plugin is active
* Prevent conflict from unknown plugin/theme that was modifying the post excerpt in form actions, which prevented them from showing
* Only scroll to the errored field and success message if they are not already in view
* Make sure admins always have permission to view menus
* Pro: Fix datepicker field when the jQuery CSS is set to load on all pages
* Pro: Added frm_footer_scripts hook
* Pro: Don't autoselect 0 in scale fields

= 2.0.05 =
* Remove deprecated jQuery toggle() calls
* Add html ids to hidden fields
* Make sure the entry name doesn't exceed allowed database field size
* Adjust user agent displayed values
* Update Bootstrap javascript to v3.3.4
* Clear more caching for forms, fields, and entries when changes are made
* Lite only: Remove the entry search box on the entries page since the functionality is in pro
* Pro: Fix issue with the CSV export on the Import/Export page
* Pro: Allow for FRMRULES to be on the page multiple times for ajax-loaded forms
* Pro: Add frmThemeOverride_jsErrors function hook
* Pro: Conditionally require fields in a conditional embedded form
* Pro: Fix date calculations and calculations across multiple pages
* Pro: Show the user display name by default with dynamic fields using a user ID field
* Pro: Fix read-only date fields on form submitted with ajax
* Pro: Fix issue with browsing view revisions
* Pro: Fix numeric phone formats without other characters
* Pro: Update masked input js to v1.4
* Pro: Fix issue with NaN showing instead of 0 in values without a number
* Pro: Fix conflict with Easy Digital download auto-updating
* Pro: Include list dynamic fields in the CSV and default email message
* Pro: Match up logic when an option with & is selected

= 2.0.04 =
* Fix XSS vulnerability from add_query_args and remove_query_args
* Remove unneeded options from the form widget and switch old styling setting width from 400px to 100%
* Fix the new form class box in the customizable HTML
* Remove WP support for v3.5 and lower
* Don't require the captcha if the keys haven't been configured
* Styling enhancements for left and right label settings
* Deactivate plugin after uninstall to prevent tables from being added back
* Add frm_text_block class to Layout tab
* Fix migration of email settigns that haven't been updated in over two years
* Fix emailing from only a multiple word name with no email
* Send emails for WordPress default if trying to send from Yahoo
* Pro: Trigger calculation update each time a row is added or removed from repeating section
* Pro: Allow phone format inside of repeating sections
* Pro: Add allow=everyone option to frm-entry-update-field shortcode to prevent permission checking when updating a single field
* Pro: Fix graph limit defaulting to 10 and the min and max options
* Pro: Fix CSV download vulnerability without permission check
* Pro: Fix searching by field on entry listing page
* Pro: Fix exporting multiple entries with the bulk CSV export option
* Pro: Fix Entry ID filter in views when using a comma separated list of ids
* Pro: Fix 3+ level dynamic fields to hide the last field when the first is changed
* Pro: Fix apostraphes in form action logic

= 2.0.03 =
* Use frm_clear instead of clear to minimize conflicts
* Add js fallback for database update on sites without CURL
* Fix issues with emails migrating to actions in php 5.3, and t showing in some emails after updating settings
* Pro: Add frm_date_format filter
* Pro: If a comma separated list of dates is sent for formatting, explode it before formatting
* Pro: Increase the backtrack limit when needed when replacing shortcodes in the view content if server has the limit below default
* Pro: Fix issue causing csv export error
* Pro: Fix the issue cuasing new posts to not be linked to a view if a field is selected for the post content
* Pro: Fix issue some users are having with blank date fields
* Pro: Fix ending collapsible sections at the end of a section instead of waiting for the next section
* Pro: Fix firing calculations on page load when there are multiple calculations
* Pro: Don't allow theme to affect the font size of stars

= 2.0.02 =
* Make sure frm_to_email hook is reverse compatible
* Fix php example in the shortcode examples
* Add styling for frm_half classes combined with left or right labels
* Add a fallback if dbDelta is missing
* Remove inline js from the draft button in the default HTML to prevent 404/403 errors on some servers. This change only applies to new forms
* Move the legend tag into the customizable HTML, but without a migration so it won't be added to existing forms
* Move the "before fields" HTML into the fieldset to it will be parallell with the "After fields" HTML
* Make sure partial form transients aren't saved for long forms. Make sure it's all or nothing.
* Make sure the parent_form_id column was added, and try to add it again if it's not there
* Pro: Allow [25 show=count]. This shortcode will return a count of items instead of the items themselves. Intended for use with a repeating section field, but would work with anything.
* Pro: Fix filtering by text from a dynamic field
* Pro: Make sure conditional logic doesn't apply to fields that follow a section with logic
* Pro: Make sure any post fields used in custom code are included when the post is created
* Pro: Load the datepicker localization file from the new hosted location
* Pro: Rework the CSV export generation with lower memory usage and more hooks for easily removing columns
* Pro: Fix exporting checkbox fields inside a CSV
* Pro: Update the pagination for Genesis the '...'
* Pro: Hopefully fix the missing date format issue some users are running into with the datepicker. Unverified since we couldn't replicate.
* Pro: When creating a new view, make sure the filter and order rows include the fields from the selected form

= 2.0.01 =
* Break transients into chunks for large forms ( > 200 fields )
* Remove the upgrade link and perform the upgrade automatically
* Allow upgrades to be done automatically in WordPress multisite with the 'Upgrade Network' option
* Updated translations
* Only add one line in the email headers for cc and bcc
* Added frm_include_meta_keys hook for including the previously included meta values referenced by field key
* Delete transients with uninstall
* Make sure the legend stays hidden after opening form in a popup
* Pro: Fixed issue with losing conditional logic on fields loaded with ajax on the form builder page
* Pro: Fixed the auto field reordering when adding end sections to old forms
* Pro: Fixed the daily entries graph on the reports page
* Pro: Allow the post author to be overridden with hooks
* Pro: Fixed the [get-param] shortcode for reverse compatibility

= 2.0 =
* Move visual form styler to free version
* Added multiple emails to free version
* Added BCC, CC, and reply to options to emails
* Replaced the reCaptcha with the new no-captcha recaptcha
* Allow multiple roles to be selected for the permissions on the global settings page
* Updated the UI
* Added a trash can for forms as well as draft forms
* Extra security with sanitizing output and prepare database queries extra just to be sure
* Switch to frm_first frm_last frm_half classes for more flexibility
* Added more responsiveness to the styling classes
* Change the field width option from characters to pixels
* Change the user browser info into a more easily readable format, and include it in the lite version
* Add (hidden) legend tag for accessibility
* Fix preview page with 2015 theme
* Reduce duplicate entry check to 1 minute
* Remove a bunch of upgrade messages in the lite version
* Reduce size of indexed db columns for utf8mb4 in WordPress 4.2
* Fixed a SQL vulnerability. Thanks @KacperSzurek for finding it!
* Pro: Added multiple form styling templates, more styling options, and updated the default styling
* Pro: Added repeatable fields and embedded forms
* Pro: Created form actions and consolidated notifications and add-ons
* Pro: All form actions can use conditional logic
* Pro: Added confirmation fields
* Pro: Added read-only radio and check box fields
* Pro: View pagination will automatically take on Genesis theme styling
* Pro: Entire sections can be moved and duplicated
* Pro: Add frm_repeat_start_rows filter to allow the form to start with multiple rows in a repeating section
* Pro: Make the query work for custom code returning a string query on the frm_where_filter hook for reverse compatibility
* Pro: Escape all quotes in CSV
* Pro: Don't require dynamic fields with no options
* Pro: Remove stray div in the calendar view
* Pro: Remove 'no files selected' text if files are selected
* Pro: Add decimal option to calculations
* Pro: Add starts with, ends with, and group by options in View filters
* Pro: Add IP option to view filters
* Pro: Added entry ID to view order options
* Pro: Added hooks: frm_selectable_dates, frm_main_feedback, frm_allowed_times, frm_view_order, frm_csv_headers, frm_map_csv_field
* Pro: Allow min or max in the graph shortcode to be equal to 0
* Pro: Keep users on current page when they click "Save Draft"
* Pro: Add pending for post status options in the post settings
* Pro: Include JS with form when editing in place
* Pro: Fix displaying stats accuracy with partial stars
* Pro: Enqueued scripts right before they are printed for easier integration with more popup plugins
* Pro: Allow slashes in Phone Number Format option
* Pro: Allow default templates to be deleted
* Pro: Reduce the baseline memory load
* Pro: Load the form styling on view pages when set to only load styling on applicable pages
* Pro: Change deletelink so it deletes with ajax
* Pro: Add [user_role] shortcode for current user's role 
* Pro: Add read-only option to Dynamic fields
* Pro: Add single row and multiple row options to Dynamic Radio and Checkbox fields
* Pro: Allow arrays in View filters
* Pro: Allow drafts to be searched with the frm-search form
* Pro: Fix sql error when searching by Hebrew characters
* Pro: Allow the use of field keys in the frm-stats shortcode
* Pro: Force tooltip wrapping in graphs
* Pro: Improve frm_total class for number fields
* Lots of other small features, bug fixes, and code cleanup. Too many little features to list!

= 1.07.12 =
* Add a bunch more caching
* Scroll to field on click in form builder

= 1.07.11 =
* Added hook: frm_check_blacklist for disabling the comment blacklist spam check
* Make nonce id unique per form
* Make sure there is at least one word before truncation
* Pro: Check conditional logic when importing dependent data from entries data
* Pro: Added number field min and max settings to php validation
* Pro: Added dynamic height to the chosen search field
* Pro: Allow HTML in the message for the frm-entry-update-field shortcode
* Pro: Added title parameter to frm-entry-update-field, frm-entry-edit-link, and frm-entry-delete-link
* Pro: Added end_date parameter to frm-stats shortcode
* Pro: Added hook: frm_display_value_atts
* Pro: Added hook: frm_after_duplicate_entry
* Pro: Added hook: frm_show_it
* Pro: Add nonce check for importing entries
* Pro: Fixed display of files with an icon when editing an entry

= 1.07.10 =
* Improve ajax form load speed on form builder page
* Added 4.0 compatibility for deprecated like_escape function
* Remove label html in radio and checkbox fields when label=0 is used on the [input] tag in the customizable HTML
* Fix Akismet integration for current version of Akismet
* Added Italian translation
* Added a few ajax nonce checks but don't require valid nonce for logged-out users for caching reasons
* Allow data-something="value" inside the [input] short code
* Pro: Allow [default-message] to have short code parameters to set rtl (direction=rtl), font size (font_size="14px"), and styling (text_color="000" border_width="1px" border_color="000" bg_color="fff" alt_bg_color="eee").
* Pro: Added frm_html_scoll_box class for scrolling content in an HTML field
* Pro: Allow recurring entries using values like "Third Wednesday of [frmcal-date]"
* Pro: Allow comma separated view filters for entry key and id
* Pro: Allow drafts=both in view short code and form results short code
* Pro: Added created_at and updated_at support to the frm-stats short code
* Pro: Added column separation option to csv export
* Pro: Added hook: frm_csv_column_sep for changing the , separation between columns in csv
* Pro: Added hook: frm_csv_field_ids to specify fields to export in CSV
* Pro: Added hook: frm_filter_where_val and frm_filter_dfe_where_val for filtering only the value instead of the full WHERE statement
* Pro: Added hook: frm_rte_options for adding options to the TinyMce editor
* Pro: Added hook: frm_show_form_after_edit to show or hide the form differently from create
* Pro: Added hook: frm_scroll_offset to change the point of scroll after submit to allow for static headers
* Pro: Added hook: frm_ajax_load_styles for allowing additional styles on ajax loaded form pages
* Pro: Added hook: frm_create_cookies for preventing cookie creation
* Pro: Added hook: frm_filter_auto_content to prevent filtering on auto-inserted views
* Pro: Added hook: frm_file_icon to change what is displayed for a upload field when editing
* Pro: Import CSV values for a taxonomy field correctly when the term name is in the CSV instead of requiring the ID
* Pro: Import CSV dates correctly in entries if format has been changed to a day-first format
* Pro: Added Post ID into the view filtering options
* Pro: Added support for multiselect drop downs in calculations
* Pro: Updated the way templates are fetched for wpmu copying
* Pro: Show any additional info on the entry view page that is stored in the entry description
* Pro: Update to Chosen 1.1.0
* Pro: Allow comma-separated values to populate a checkbox field
* Pro: Make fields and exclude_fields parameter work with multi-page forms (required fields are still a limitation)
* Pro: Allow drafts=1 or drafts=both in stats shortcode
* Pro: Allow field keys in stats filtering
* Miscellaneous bug fixes

= 1.07.09 =
* Added hook: frm_bulk_field_choices for adding custom prepopulated options
* Cleanup styling on bulk option popup
* Fixed submission error affecting some sites
* PRO: Don't reload javascripts after ajax submit
* PRO: Improve no conflict styling mode
* PRO: Fixed cascading calculations
* PRO: Allow HTML for the label and cancel links in the edit link shortcodes

= 1.07.08 =
* Check the words on the WordPress blacklist before submitting an entry
* PRO: Added server dynamic default value for getting values from the PHP SERVER array like the current url
* PRO: Added hook: frm_csv_sep for changing , to a different separator for checkbox fields
* PRO: Exclude child categories in regular dropdown fields
* PRO: Added drafts parameter to formresults shortcode
* PRO: Added x_order parameter to graph shortcode and modified graphs to work correctly with checkboxes
* PRO: Added hook: frm_delete_message to customize the message shown after an entry is deleted
* PRO: Added != option to frm-stats shortcode options
* PRO: Added repeating events
* PRO: Improved database call for Views.
* PRO: Added a no conflict styling mode for overriding theme styling in the styling settings

= 1.07.07 =
* Added clarity to message in license box when pro is not installed
* Added Spanish and Serbian translations. Thank you Ognjen Djuraskovic!
* Fixed XML form export in free version
* PRO: Added import_csv function back in with deprecated message and fallback
* PRO: Improved conditional statement replacing
* PRO: Minimize search form HTML
* PRO: Prevent comments on the view entry page from being deleted when the entry is updated
* PRO: Only run before delete entry hook on delete all button if posts are turned on
* PRO: Switch out ids for dependent fields after importing forms
* PRO: Added x_axis=month and x_axis=quarter option to graph shortcode

= 1.07.06 =
* Return graceful error message if no DOMDocument enabled
* Allow fields to be updated via XML import by field key for non-templates
* Added minimize=1 option to the [formidable] short code to minimize the form HTML to prevent wpautop interference
* Correctly return fallbacks on a couple deprecated functions
* PRO: Allow field keys in the frm-stats shortcode for fieldid=value
* PRO: Fixed attaching file upload to entries when using single files

= 1.07.05 =
* Added XML import/export
* Moved more email settings and bulk form delete to free version
* Added form edit links to admin bar
* Removed .required class from required form inputs to minimize conflicts
* Revert to random entry keys now that data from entries values can be used in filtering views
* Encode email subject with frm_encode_subject hook to prevent encoding
* PRO: Allow entries to be edited via csv import when entry ID is included
* PRO: Expanded conditional logic for email notifications
* PRO: Allow the frm-field-value shortcode to get the entry ID from the URL. [frm-field-value field_id=x entry_id=id]. Replace "id" with the name of the parameter in your URL
* PRO: Added separate set of confirmation options for editing
* PRO: Added option to disable visual tab on each view
* PRO: Added 'action' parameter back to the frm_redirect_url hook
* PRO: Added drafts parameter to view shortcode to show draft entries. [display-frm-data id=40 drafts=1]
* PRO: Switched star ratings to icon font
* PRO: Added multiple="multiple" into multiple file upload fields
* PRO: Allow field keys in the exlude_fields shortcode option
* PRO: Allow updated-at, created-at, updated-by to by used in conditional statements
* PRO: Added update message and button to global default messages
* PRO: Added progress bar to csv import
* PRO: Added hook: frm_csv_line_break filter for changing line breaks in csv export
* PRO: Change the updated_at and updated_by values when a field is changed with the edit field link
* PRO: Fixed adding new conditional logic to newly added notifications
* PRO: Allow "GROUP BY" addition to form in frm_where_filter by rearranging SQL
* PRO: Don't apply custom display filters to single post page
* PRO: Fixed showing only file name in views
* PRO: Removed Pretty Link plugin integration to be placed in an add-on
* PRO: Added delete_link and confirm parameter to formresults shortcode
* PRO: Added entry_id, x_title, y_title, start_date, and tooltip_label to graph shortcode options
* PRO: Allow data from entries fields to be used as x_axis in graphs
* PRO: Allow field keys in graph shortcode
* PRO: Add height and line-height to Global Settings
* PRO: Filter the empty_msg for Views
* PRO: Added draft status to csv export/import
* PRO: Check for valid file type when saving a draft
* PRO: Added sorting on entry listing table for non-post fields
* PRO: Fixed form pagination with errors and no ajax validation
* PRO: Changed image to a link when editing an entry with an image
* PRO: Moved the frm_setup_new_fields_vars hook to fire later when dynamically getting options from a dependent data from entries field
* PRO: Added frm_get_categories hook
* PRO: Added frm_jquery_themes hook for creating custom jQuery calendar themes
* PRO: Added frm_no_data_graph hook for customizing "No Data" message for graphs

= 1.07.04 =
* Minor back-end styling fixes
* PRO: Added frm_show_delete_all hook to hide the "delete all entries" button, and show by default for those with back-end entry editing capabilities
* PRO: Fixed inserting conditional examples from the sidebar box
* PRO: Fixed viewing single post with some view configurations
* PRO: Fixed detailed view for calendar displays when entries are not posts
* PRO: Fixed conditional logic on page load for radio buttons
* PRO: Make sure entries aren't deleted in another form if using the form switcher right after deleting all entries in a form
* PRO: Fixed error when saving a field with conditional logic with no field selected
* PRO: Allow subscribers and below to add custom taxonomies to posts
* PRO: Fixed conditional data from entries fields across multiple pages in an ajax form

= 1.07.03 =
* Removed auto updating from free version
* PRO: Added secondary ordering options in Views
* PRO: Allow newly added custom fields on the "Create posts" tab to be selected from existing options
* PRO: Allow html=1 and show_filename=1 to be used together for showing a filename linking to the file
* PRO: If not using show_filename=1, default to show the file type icon or non-image file types
* PRO: Fixed ordering in a view set to show a single entry
* PRO: Fixed adding new filters to views
* PRO: Allow a low-level user to edit entries submitted by another user when the setting is turned on, even if they have not submitted an entry themselves
* PRO: Fixed data from entries fields across multiple pages
* PRO: Added [updated-by] shortcode for use in views
* PRO: Send the detail page of a view through any set filters
* PRO: In a view, use limit over page size if limit is lower
* PRO: Fixed going backwards in a multi-paged form, when 2 or more pages are skipped at a time

= 1.07.02 =
* Added form switcher to nav and other UI enhancements
* Remove slashes from a single entry retrieved from cache
* Remove slashes added by ajax before saving to db
* Fixed naming so plugin info and change log links are correct on plugins page
* Updated default submit button HTML to include [frmurl] for a dynamic url
* Added nonce fields and checking for increased security
* Switched to placeholder with IE fallback for those using HTML5
* Updated duplicate entry checking for more accuracy
* Improved long form load time and usability
* Added French translation
* Removed unnecessary definitions: FRM_IMAGES_URL, IS_WPMU, FRMPRO_IMAGES_URL
* Dropped support for < jQuery 1.7 (< WP 3.3)
* Added frm_radio_class, frm_checkbox_class, and frm_submit_button_class hooks
* Moved radio and checkbox inputs inside the label tags
* Updated default styling
* Added frm_text_block and frm_clearfix styling classes
* Added force_balance_tags on the in-place-editing fields on the form builder page to prevent issues with adding bad HTML
* PRO: Switch field IDs in email settings in duplicated form
* PRO: Added option to save drafts
* PRO: Added phone format option, including an input mask if format is not a regular expression
* PRO: Added exclude_fields to the form shortcode. Ex [formidable id=2 exclude_fields="25,26"]
* PRO: Added styling reset button on styling page
* PRO: Switch "Custom Display" terminology to "View"
* PRO: Allow any values in the form shortcode to set $_GET values. [formidable id=x get="something"]. Then use [get param="get"] in a field
* PRO: Allow the field value to be used to filter data from entries values in custom displays, statistics, and graphs
* PRO: Increased CSV export efficiency
* PRO: Allow for quotation marks in values used to get stats in the frm-stats shortcode
* PRO: Fixed entry listing widget to get values from stats for more accuracy
* PRO: Updated template export to include all form settings
* PRO: Drop WP_List_Table fallback for < WP 3.1
* PRO: Make custom display pagination unique to allow multiple paginated displays on a single page
* PRO: Remove WPML-related translating options, and move to the add-on
* PRO: Added [entry_count] for use in custom displays
* PRO: Allow a blank option for multiselect data from entries fields when set to autocomplete
* PRO: Adjust imported created and updated times from server setting to UTC
* PRO: Switch time field generation from javascript to php
* PRO: Allow [if created-at less_than="-1 month"]
* PRO: Added frm_default_field_opts hook
* PRO: Added frm_send_to_not_email hook for notifications that are triggered on non-emails
* PRO: Updated file uploading progress bar with frm_uploading_files hook added to text
* PRO: Only show "create entry in form" box if user has permission to create entries
* PRO: Removed icons from error message
* PRO: Fixed collapsable entry list bullets
* PRO: Fixed dependent multi-select data from entries fields on edit
* PRO: Added frm_back_button_class hook
* PRO: Fixed quotation marks in conditional logic
* PRO: Allow filtering by a field value in graphs
* PRO: Make x_axis=created_at work in graphs
* PRO: Added if statements to Default HTML button in email message
* PRO: Added show_filename option to file upload fields
* PRO: Allow dropdown data from entries fields to be set as read only

= 1.07.01 =
* Added for attribute to labels for newly created fields
* Fixed issue with slashes showing in content if retrieved from cache
* Prevent multiple checks for updates when pro is authorized, but free version is installed
* Added frm_form_fields_class hook
* PRO: Fixed days events are shown on the calendar with months starting on Sunday and week start day set to Monday
* PRO: Added option to not load a JQuery UI stylesheet
* PRO: Added "Entry ID" option to the back-end entry search options
* PRO: Added frm_csv_filename hook for changing the csv file name
* PRO: Allow siteurl and sitename in after content box in custom display
* PRO: Allow autocomplete selection to be unselected on front-end
* PRO: Fixed conditional validation for fields in a conditional section heading beyond page 1

= 1.07.0 =
* Submit build form in one input with ajax to prevent max_input_vars limitations
* Load fields on the build page with ajax for long forms and other form builder page optimization
* Added submit button to customizable HTML
* Added clickable styling classes to form builder sidebar
* Create entry key from first required text field
* Set the default name of a field to the field type instead of "Untitled"
* Added minified version of formidable.js
* Added warning message if a non-unique value is added as a field value
* Removed messages for strict standards
* Fixed inline and left labels for checkboxes
* PRO: Added back button on multi-paged forms
* PRO: Added conditional logic on page breaks for skipping pages
* PRO: Added loading indicator by submit button and on dependent data from entries fields
* PRO: Switched out username and passwords for license numbers
* PRO: Updated timestamp in CSV to adjust for WordPress timezone selection
* PRO: Updated value in CSV for file upload fields
* PRO: Include comments in the CSV export
* PRO: Made dynamic default values clickable on form builder page
* PRO: Added column in CSV for value for fields that are set to use separate values
* PRO: Allow for quotation marks in field labels for the CSV export
* PRO: Added frm_import_val hook for CSV importing
* PRO: Removed border styling from the container around radio and checkbox fields
* PRO: Added frm_order_display hook
* PRO: Added utf8 support to sanitize_url=1 option
* PRO: Added "confirm" option to frm-entry-links shortocode that is used before an entry is deleted
* PRO: Copy conditional logic and field calculations into duplicated forms
* PRO: Allow clickable=1 and images to be used with Google formresults shortcode
* PRO: Allow [25 show="user_email"] for data from entries fields to get user info from the user ID from the linked form, and [25 show="30" show_info="user_email"] to get values from a field linked through 2 data from entries fields
* PRO: Allow tags fields to be used with hierarchal taxonomies
* PRO: No longer require fields in a conditionally hidden section heading
* PRO: Added option for frmThemeOverride_frmAfterSubmit function for custom javascript after ajax submit
* PRO: Updated star rating javascript version
* PRO: Check field key when creating a form from a template to see if the trailing "2" can be removed
* PRO: Don't show custom display content for password protected posts until allowed
* PRO: Switch the cancel link to edit link after a form is submitted with in-place-edit and ajax
* PRO: Switched front-end ajax to use hooks (frm_ajax_{controller}_{action})
* PRO: Call ajax later on the init hook to prevent php notices when WooCommerce is active
* PRO: Delete entries on the same page as the frm-entry-links shortcode, and added a confirmation message: confirm="Are you sure?"
* PRO: Correctly check if jQuery on() function exists
* PRO: Fixed calendar display for months starting on Sunday when the week start day is set to Monday
* PRO: Removed "custom display" from the post type options on the "create posts" settings tab
* PRO: Allow multiple values to be imported into an entry via csv in a multi-select dropdown field

= 1.06.11 =
* Added styling classes: two thirds, scroll box, columns (frm_first_two_thirds, frm_last_two_thirds, frm_scroll_box, frm_total, frm_two_col, frm_three_col, frm_four_col, )
* Added container in default html for new check box and radio fields
* PRO: Added a print link on the view entry page in the back-end
* PRO: Added support for category stats in the frm-stats shortcode
* PRO: Allow the edit link to dynamically get the id of the entry when used on a post page. Ex: [frm-entry-edit-link id=current label="Edit" page_id=92]
* PRO: Allow non-admin users to see the user ID drop down in the back-end when they have permission to edit entries from the back-end
* PRO: Added frm_data_sort hook for sorting data from entries options
* PRO: Allow dropdown fields to be selected as the post title
* PRO: Switched data from entries drop downs to use field key in the html id instead of the field id for consistency
* PRO: When importing templates, use the path shown in the box whether it has been saved or not
* PRO: Fixed admin-only fields to still save to created post
* PRO: Fixed issue preventing required multiple file upload fields from being required
* PRO: Updated input mask script to 1.3.1
* PRO: Added hooks for entries in the admin: frm_row_actions, frm_edit_entry_publish_box, frm_show_entry_publish_box, frm_edit_entry_sidebar

= 1.06.10 =
* Allow the usage of any html attributes inside the [input] tag in the customizable HTML
* PRO: Added "Chosen" autocomplete to dropdown fields
* PRO: Added automatic width option to data from entries fields
* PRO: Extended the "admin only" field option to all user roles, or only logged-in or logged-out users
* PRO: Added multiple-select to data from entries dropdowns
* PRO: Added more info to the form settings sidebar
* PRO: Resolved conflict between ajax submit and plugins/themes with whitespace in php files
* PRO: Fixed template export to properly serialize and escape for multiple choice fields

= 1.06.09 =
* DROPPED PHP4 SUPPORT. Do not update if you run PHP4.
* Added the "create template" link into the free version
* Added quotes around the menu position number to minimize menu position conflicts with other plugins
* Moved all stripslashes to the point the data is retrieved from the database
* Switched the field options bulk edit to use the admin ajax url to minimize plugin conflicts
* Changed all occurrences of .live() to .on() for jQuery 1.9 compatibility
* PRO: Added AJAX form submit
* PRO: Dropped Open Flash Chart support due to security vulnerabilities
* PRO: Added multiple option to dropdown fields
* PRO: Added unique error message into global and field settings
* PRO: Added option to limit by ranges in the frm-stats shortcode. Ex: [frm-stats id=50 '-1 month'<45<'-3 days']
* PRO: Automatically strip javascript before displaying entries through a custom display
* PRO: Added striphtml=1 and keepjs=1 options for use in custom displays
* PRO: Added option to get the field description with [125 show="description"]
* PRO: Added separate value column on entries page
* PRO: Added link to delete entry only and leave post
* PRO: Added box for custom css in the styling settings
* PRO: Added buttons to insert default HTML or plain text for those who wish to modify the default message without starting from scratch
* PRO: Added link to uploaded files in the entry edit form
* PRO: Added "like" and "not like" options to the conditional logic for hiding and showing fields
* PRO: Switched section headings to use h3 tags by default instead of h2
* PRO: Migrated "Allow Only One Entry for Each" fields to the unique checkbox on each field
* PRO: Allow for multiple uses of frm-entry-update-field for the same field and entry
* PRO: Allow external short codes in the email recipients box
* PRO: Allow the frm-search shortcode to be used in text widgets
* PRO: Switched conditional fields to show and hide instead of fadeIn and fadeOut
* PRO: Switched rich text fields to default to TinyMCE
* PRO: Correctly send emails to [admin_email], and allow the same email address to receive multiple notifications from the same form
* PRO: Filter shortcodes in success message when the form is limited to one entry per user and editable
* PRO: Correctly show the taxonomy name even if it is not linked to a post
* PRO: Fixed read-only option to work with dropdown fields
* PRO: Fixed post password setting
* PRO: Fixed post content replacement when entry is updated instead of only on creation
* PRO: Fixed frm-stats shortcode to allow field keys when using the value option
* PRO: Fixed custom displays getting used if they are in the trash
* PRO: Fixed custom display pages to not include the unfiltered post content when there are no entries to display
* PRO: Fixed the bulk delete option showing for users without permission to delete in the bulk actions dropdown on the admin entry listing page
* PRO: Fixed the delete link in entry edit links shortcode to prevent it from going to a blank form when using the page_id param
* PRO: Fixed calendar to show the correct number of extra boxes when not starting on Sunday
* PRO: Fixed repeated, inline conditional logic in custom displays
* PRO: Fixed option to copy forms to other sites in multi-site installs, so they will no longer be copied when the box is unchecked 
* PRO: Fixed admin-only fields to not validate for users who can't see the field

= 1.06.08 =
* Changed class names on action links on the form listing table to prevent conflicts with themes and other plugins
* PRO: Filter shortcodes if any in the login message
* PRO: Fixed order of fields shown in default email notification
* PRO: Keep files attached to the post when editing the entry and using multiple file upload option
* PRO: Attach file uploads to WP post even if the upload field is not set as a custom field
* PRO: Fixed bug forcing site name and admin email as the email "from" info when a custom name/email is selected
* PRO: Send a notification even if the notification before it is empty
* PRO: Fixed conditional logic on email notifications to make sure they are stopped when they should be
* PRO: Automatically send emails to the saved value of a field when used in the "Email recipients" box without requiring show=field_value

= 1.06.07 = 
* Added mb_split fallback for servers without mbstring installed
* Changed menu position to prevent override from other plugins and themes
* PRO: Fixed issue with the form shortcode showing if using multiple forms with default values on the same page
* PRO: Fixed javascript error in frm-entry-update-field shortcode
* PRO: Send the "read more" link to the single entry page instead of showing in-place for dynamic displays

= 1.06.06 =
* Removed generic classes from input fields like "text" and "date"
* Correctly jump down to form with error messages
* Added frm_setup_new_entry hook for overriding defaults for all fields in one hook when presenting a blank form
* Added "This field cannot be blank" message to global settings
* Changed substr to mb_substr for language-safe truncation
* WP 3.5 compatibility
* Fixed conflict with W3TC that was adding slashes into options on the form settings page
* Show a message on the form builder page if a reCaptcha is included in the form, but not set up
* Switch from add_object_page to add_menu_page to prevent menu position conflicts
* (Free only) Allow emails to be sent from the admin email instead of forcing an email address from the submitted entry
* PRO: Added multiple-image upload
* PRO: Added unlimited emails per form and conditional routing
* PRO: Use the "customized content" box to save the actual content if no field is selected for the post content
* PRO: Added frm-field-value shortcode to get the value of a field in another form. [frm-field-value field_id=25 user_id=current entry_id=140 ip=1]
* PRO: Added frm-show-entry shortcode to show an entry in the same formats as the default email message. [frm-show-entry id=100 plain_text=1 user_info=1]
* PRO: Added frm_set_get shortcode to artificially set $_GET values for use in custom displays or dynamic defaults values. [frm-set-get any_param="any value" another="value 2"] This can be fetched with [get param="any_param"] [get param="another"]
* PRO: Extended conditional logic for displaying fields to include text, number, email, website, and time fields
* PRO: Added support for the [frm-search] shortcode into the [formresults] table
* PRO: Updated NicEdit
* PRO: If http isn't included in a url or image field, automatically add it during validation
* PRO: Added "wrap" parameter to the frm-graph shortcode to wrap the text in long questions
* PRO: Added localization to custom display calendar to start on day of the week selected in WordPress settings
* PRO: Added entry updated dates to custom display shortcodes
* PRO: Correctly check uniqueness of post fields when there are no other error messages
* PRO: If using a number field with the value "0" that is linked through a data from entries field, show 0 instead of nothing
* PRO: Update for more accurate checking for hierarchal taxonomies when saving posts
* PRO: Evaluate numbers as numeric instead of a string for conditional logic for hiding and showing fields
* PRO: Fix to allow tags fields and other fields in the same form that are mapped to the same taxonomy
* PRO: Fixed conditional logic to work correctly when dependent on the value "0"
* PRO: Fixed display of shortcodes inside the before or after content areas of the custom display if nesting [get param=something]
* PRO: Fixed calculations for multiple-paged calculations with checkbox fields that may not be checked
* PRO: Fixed checkbox fields linked through another field to display properly in a custom display
* PRO: Fixed separate values to work with sending to email addresses
* PRO: Show a max of 500 options in a data from entries field in the admin to prevent server limits from making the form inaccessible
* PRO: Make sure the graphs printed from the reports page are not split when printing
* PRO: Fixed the link to show more text in the custom display to show the text in place or link to the single page correctly depending on the custom display type
* PRO: Removed "just show it" data from entries fields in the email checkbox settings
* PRO: Remove post custom fields from database if blank
* PRO: Fixed frm-stats shortcode to work with post custom fields combined with the value parameter
* PRO: Fixed div nesting issue when using collapsible section headings followed by non-collapsible sections headings
* PRO: Removed separate values checkbox for post status and taxonomy fields
* PRO: Fixed double filtering forms if inserted in the dynamic box of a custom display used for posts
* PRO: Fixed page size and limit overriding single entry displays

= 1.06.05 =
* Fixed WP 3.4 layout issues with missing sidebars
* Added responsive css for WP 3.4 to keep the form builder sidebar box showing on small screens
* Updated the delete option trash can to appear more easily
* Use absolute path for php includes() and requires() to prevent them from using files from other plugins or themes
* Updated translations
* PRO: Prevent wp_redirect from stripping square brackets from urls
* PRO: Fixed calculations for fields hidden in a collapsible section
* PRO: Fixed delete link to work on pages without forms
* PRO: Added support to import checkbox field values in multiple columns

= 1.06.04 =
* Moved form processing to avoid multiple submissions when some plugins are activated and remove the page before redirection
* Removed BuddyPress filters from the email notifications to avoid forcing them to send from noreply@domain.com
* Allow blank required indicator and to email in forms
* Fix to allow access to form, entry, and display pages for WordPress versions < 3.1
* Fixed default checkbox or radio field values for fields with separate option values
* Corrected Arkansas abbreviation in templates and bulk edit options
* Fixed display of radio field values from fields with separate values
* PRO: Added custom display content box into "create posts" settings tab
* PRO: Added options to auto-create fields for post status and post categories/taxonomies
* PRO: Added link to de-authorize a site to use your Pro credentials
* PRO: Added meta box on posts with link to automatically create a form entry linked to the post
* PRO: Hide pro credentials settings form when pro is active
* PRO: Fixed redirect URL to correctly replace shortcodes for forms set to not save any entries
* PRO: Fixed regular dropdown field taxonomies to trigger conditional logic and use the auto width option
* PRO: Allow searching by user login when selecting a user ID field to search by on the admin entries page
* PRO: Updated the auto_id default value to continue functioning correctly even if there are non-numeric values in entries
* PRO: Added an index.php file into the uploads/formidable folder to prevent file browsing for those without an htaccess file
* PRO: Allow field IDs as dynamic default values ie [25]. This will ONLY work when the value has just been posted.
* PRO: Added the display object into the args array to pass to the frm_where_filter hook
* PRO: Allow for negative numbers in calculations
* PRO: Allow for unlimited GET parameter setting in the custom display shortcode. [display-frm-data id=2 whatever=value whatever2=value2]
* PRO: Switched phone field to HTML5 "tel" input type
* PRO: Added a frm_cookie_expiration hook to change the cookie expiration time
* PRO: Added cookie expiration option
* PRO: Added frm_used_dates hook for blocked out dates in unique datepickers
* PRO: Added frm_redirect_url hook
* PRO: Fixed forms submit button labels for forms in add entry mode that follow a form in edit mode on the same page
* PRO: Fixed CSV import for delimiters other than a comma
* PRO: Added three icons to the error icon setting
* PRO: Fixed duplicate deletion messages when using [deletelink] in the form customizable HTML
* PRO: Updated calculations and conditional logic to work across multi-paged forms
* PRO: Added basic support for data from entries csv import 
* PRO: Show image for data from entries fields using upload fields

= 1.06.03 =
* Added option to not store entries in the database from a specific form
* Added option to skip Akismet spam check for logged in users
* The forms, entries, and custom display page columns that are shown and entries per page are now customizable for those running at least v3.1 of WordPress
* Added a css class option to the field options with predefined CSS classes for multi-column forms: frm_first_half, frm_last_half, frm_first_third, frm_third, frm_last_third, frm_first_fourth, frm_fourth, frm_last_fourth, frm_first_inline, frm_inline, frm_last_inline, frm_full, frm_grid_first, frm_grid, frm_grid_odd
* Added the option to add a class to an input. In the customizable HTML, change [input] to [input class="your_class_here"]
* Added "inline" option to label position options to have a label to the left without the width restriction
* Switched the "action" parameter to "frm_action" to prevent conflicts. If no "frm_action" value is present, "action" will still be used
* Updated templates with new styling classes
* Show quotation marks instead of entities in the site name in email notifications
* Added Polish translation
* PRO: Removed a vulnerable Open Flash Charts file. If you do not update, be sure to REMOVE THIS FILE! (pro/js/ofc-library/ofc_upload_image.php)
* PRO: Added option to use a separate value for the radio, checkbox, and select choices
* PRO: Added option to use dynamic default values for radio, checkbox, dropdown, and user ID fields
* PRO: Added option to use Google charts and automatically fall back to them on mobile devices [frm-graph id=x type=bar google=1]
* PRO: Added data from entry field support to graphs
* PRO: Added option to use Google tables for easy pagination and sorting [formresults id=x google=1]
* PRO: Added edit link option to formresults shortcode. [formresults id=x edit_link="Edit" page_id=5]
* PRO: Added date support to built-in calculations for date1-date2 types of calculations
* PRO: Added checking for disabled used dates for fields set as post fields in date picker for dates marked as unique
* PRO: Added not_like, less_than, and greater_than options to conditional custom display statements. Ex [if 25 not_like="hello"]...[/if 25]
* PRO: Allow [if created-at less_than='-1 month'] type of statements in the custom display for date fields, created-at, and updated-at
* PRO: Added option to display the field label in custom displays. Ex [25 show="field_label"]
* PRO: Added option to turn off auto paragraphs for paragraph fields. Ex [25 wpautop=0]
* PRO: Added options to custom display shortcode: [display-frm-data id=5 get="whatever" get_value="value"]. This allows the use of [get param="whatever"] in the custom display. 
* PRO: Updated the frm-entry-links shortcode to use show_delete with type=list
* PRO: Updated custom display where options to fetch entries more accurately when "not like" and "not equal to" are used
* PRO: Fixed image upload naming for uploads with numeric names like 1.png
* PRO: Fixed issue with multiple editable forms on the same page when one is set to only allow one entry per user
* PRO: Added a check for automatically inserted custom displays to make sure we are in the loop to avoid the need for increasing the insert position setting
* PRO: Show the post type label in the post type dropdown instead of the singular label to avoid blank options for custom post types without a singular name defined
* PRO: Switched out the case-sensitive sorting in data from entries fields
* PRO: If a custom display has detail link parameters defined, ONLY allow those parameters
* PRO: Added an input mask option available via the $frm_input_masks global and 'frm_input_masks' hook
* PRO: Added type=maximum and type=minimum to the frm-stats shortcode
* PRO: Month and year dropdowns added to custom display calendar, along with a few styling changes
* PRO: Get the custom display calendar month and day names from WordPress
* PRO: Allow dynamic default values in HTML field type
* PRO: Get post status options from WordPress function instead of a copy
* PRO: Check the default [auto_id] value after submit to make sure it's still unique
* PRO: If the "round" parameter is used in the frm-stats shortcode, floating zeros will be kept
* PRO: If greater than or less than options are used with a number field in a custom display, treat them as numbers instead of regular text
* PRO: Allow user logins for the user_id parameter in the frm-graph, frm-stats, and display-frm-data shortcodes
* PRO: Fixed the date format d-mm-yyyy to work correctly in the date field
* PRO: Added timeout to redirect so users will see the redirect message for a few seconds before being redirected
* PRO: Allow decimal values in graphs instead of forcing integers
* PRO: Updated the time field to use a true select box instead of a text field
* PRO: Removed included swfobject and json2 javascripts to use the included WordPress versions
* PRO: Added 'frm_graph_value' filters to change the value used in the graphs
* PRO: Populate strings to be translated without requiring a visit to the WPML plugin
* PRO: If the where options in a custom display include a GET or POST value that is an array, translate the search to check each value instead of searching for a comma-separated value in one record.
* PRO: Added entry key and entry ID to the where options in custom displays
* PRO: Added HTML classes on the search form, so if themes include styling for the WP search form, it will be applied to the [frm-search] as well
* PRO: Allow multiple data from entries fields to be searched using the frm-search shortcode instead of only one
* PRO: Fixed update checking to not cause a slow down if the formidableforms.com server is down
* PRO: Updated the user_id parameter for the display-frm-data shortcode to be used even if there's no user ID field selected in the where options for that custom display
* PRO: Added DOING_AJAX flags for WPML compatibility
* PRO: Added time_ago=1 option for displaying dates. Ex: [created-at time_ago=1] or [25 time_ago=1]
* PRO: Updated file upload process to change the file path before uploading instead of moving the files afterwards

= 1.06.02 =
* Fixed selection of dropdowns on the form builder page in Chrome
* Added WPML integration. Requires the add-on available from WPML. Pro version includes a quick translation page.
* Added option to use the custom menu name site wide in multi-site installs
* Added 'frm_use_wpautop' filter for disabling all built-in occurrences of auto paragraphs (form description, HTML fields, and displaying paragraph fields)
* Only show the form icon button on the edit post page for users with permission to view forms
* Changed .form-required class to .frm_required_field
* Start with label in edit mode after adding a new field
* Added required indicator to styling
* Don't allow whitespace to pass required field validation
* PRO: Added option to restrict the file types uploaded in file upload fields
* PRO: Added export to XML and export to CSV to bulk action dropdowns
* PRO: Added [user_id] dynamic default value
* PRO: Allow dynamic dates in the frm-graph shortcode. Ex [frm-graph id=x x_axis="created_at" x_start="-1 month"]
* PRO: Added bar_flat to the graphs. Ex [frm-graph id=x type="bar_flat"]
* PRO: Dynamically hide some x-axis labels if there are too many for the width of the graph. Note: Does not work with percentage widths
* PRO: Added the option to select an end date in calendar custom displays for displaying multiple day events
* PRO: Added 'frm_show_entry_dates' filter for customizing which dates an entry should show on
* PRO: Disabled used dates in date picker for dates marked as unique
* PRO: Added option to search by entry creation date on admin entries listing page
* PRO: Added windows-1251 option for CSV export format
* PRO: Added the class parameter to the edit-in-place cancel link
* PRO: Improved CSV import to work better with large files
* PRO: Make a guess at which fields should match up on CSV import
* PRO: Added option to resend the email notifications when entry is updated. (This will be expanded when conditional email routing is added.)
* PRO: Don't send autoresponder message when importing
* PRO: Allow an entry id in the frm-stats shortcode. Ex [frm-stats id=25 entry_id=100]. Display a star vote as stars for a single entry in a custom display with [frm-stats id=25 type=star entry_id=[id]]
* PRO: Allow multiple star ratings for the same field on the same page
* PRO: Fixed post options that would not deselect
* PRO: Fixed issue causing the wrong conditional logic row to sometimes be removed
* PRO: Fixed bug preventing hidden fields from saving as a post field
* PRO: Fixed required tags fields to not return errors when not blank
* PRO: Fixed bug preventing some javascripts and stylesheets from getting loaded on admin pages if the menu title was changed
* PRO: Fixed graphs to show x_axis dates in the correct order if 2011 and 2012 dates are in the same graph
* PRO: Corrected WP multisite table name for the table to copy forms and custom displays
* PRO: Fixed issue with graphs showing in front of dropdown menus in Chrome
* PRO: Fixed bug in custom displays causing the wrong entries to be returned when a post category field is set to NOT show a certain category
* PRO: Fixed bug with multiple paged forms that was sometimes causing the next page to show even if errors were present on previous page
* PRO: Allow entries to be correctly editing from the backend by a user other than the one who created it, when data from entries field options are set to be limited to only the user currently filling out the form
* PRO: Updated conditional logic for those who set up the logic before v1.6 and haven't clicked the update button in their forms
* PRO: Corrected file upload naming for the various sizes of an upload with the same name as an existing upload

= 1.06.01 =
* Added option to customize the admin menu name
* Added instructions to publish forms if no entries exist
* Free only: Fixed form settings page to allow tabs to work
* Free only: Updated styling to align multiple checkboxes/radio buttons when the label is aligned left
* PRO: Fixed issue with the default value getting lost from a hidden field when updating from the form settings page
* PRO: Fixed conditionally hidden fields that are already considered hidden if inside a collapsible section
* PRO: Fixed graphs using x_axis=created_at and user_id=x
* PRO: Fixed multiple paged forms with more than two pages
* PRO: Validate HTML for checkbox taxonomies

= 1.06 =
* User Interface improvements
* Increased security and optimization
* Moved the "automatic width" check box for drop-down select fields to free version
* Moved email "From/Reply to" options to free version
* Fixed form preview page for form templates
* Added German translation  (Andre Lisbert)
* Added ajax to uninstall button
* Correctly filter external shortcodes in the form success message
* Show error messages at the top if they are not for fields in the form (ie Akismet errors)
* Updated bulk edit options to change the dropdown in the form builder at the time the options are submitted
* Fixed default values set to clear on click to work with values that include hard returns
* Free only: Fixed hidden label CSS
* PRO: Extended the conditional field logic
* PRO: Added graphs for fields over time, and other customizing options: x_axis, x_start, x_end, min, max, grid_color, show_key, and include_zero
* PRO: Moved post creation settings from individual fields to the forms settings page
* PRO: Added option in WP 3.3 to use Tiny MCE as the rich text editor
* PRO: Added "format" option to date fields. Example [25 format='Y-m-d']
* PRO: Added star rating option to scale fields
* PRO: Added star type to [frm-stats] shortcode to display the average in star format. Example [frm-stats id=5 type=star]
* PRO: Added option to format individual radio and checkbox fields in one or multiple rows
* PRO: Added server-side validation for dates inserted into date fields
* PRO: Allow multiple fields for the same taxonomy/category
* PRO: Allow a taxonomy/category to be selected for data from entries fields. This makes cascading category fields possible.
* PRO: Added [post_author_email] dynamic default value
* PRO: Added a frm_notification_attachment hook
* PRO: Added clickable and user_id options to the formresults shortcode. ex [formresults id=x clickable=1 user_id=current]
* PRO: Improved field calculations to extract a price from the end of an option
* PRO: Added the option to specify how many decimal places to show, and what characters to use for the decimal and thousands separator. For example, to format USD:
$[25 decimal=2 dec_point='.' thousands_sep=',']
* PRO: Added a message before the user is redirected, along with a filter to change it (frm_redirect_msg).
* PRO: Added a button to delete ALL entries in a form at the bottom of the entries page
* PRO: Added a password field type
* PRO: Conditionally remove HTML5 validation of form if default values are present
* PRO: Added like parameter for inline conditions in custom displays. Example: [if 25 like="hello"]That field said hello[/if 25]
* PRO: Allow fields set as custom post fields to be used for sorting custom displays
* PRO: Updated import to create the posts at the time of import
* PRO: Unattach images from a post if they are replaced
* PRO: Leave the date format in yyyy-dd-mm format in the CSV export
* PRO: Allow importing into checkbox fields
* PRO: Added option to use previously uploaded CSV for import so new upload isn't required when reimporting
* PRO: Added option to change the text on the submit button in the frm-search shortcode. Example [frm-search label="Search"]
* PRO: Fixed bug preventing a field dependent on another data from entries field from updating
* PRO: Fixed bug affecting pages with multiple editable forms on the same page that caused the first form to always be submitted
* PRO: Updated the truncate option to not require full words if truncating 10 or less characters
* PRO: Fixed bug preventing front-end entry deletion when the form was editable and limited to one per user
* PRO: Fixed bug preventing checkbox selections from showing in custom email notifications if a checkbox contained a quotation mark
* PRO: Prevent the uploading files message from showing if no files were selected
* PRO: Check a default value when using dynamic default values in the check box options
* PRO: Fixed bug preventing a newly created post from getting assigned to the user selected in the user ID dropdown if the selected user was not the user submitting the entry or was created with the registration add-on in the same form
* PRO: Fixed bug preventing Data from entries "just show it" fields from showing a value in admin listing and view entry pages
* PRO: Fixed bug causing the options to be empty if the data from entries options are limited to the current user and the form they are pulled from are creating posts
* PRO: Fixed empty results in the [formresults] table for forms that create posts
* PRO: When a blog is deleted in WP multi-site, delete database table rows related to copying forms from that blog
* PRO: Don't strip out desired backslashes 
* PRO: Updated to latest version of datepicker javascript

= 1.05.05 =
* Added Dutch translation (Eric Horstman)
* Fixed "Customize Form HTML" link issues some users were having
* PRO: Load jQuery UI javascript for datepicker
* PRO: Fixed custom display "where" options to work with multiple where rows

= 1.05.04 =
* Bulk edit and add radio, select, and check box choices
* Added option to turn off HTML5 use in front-end forms
* Added option to turn off user tracking
* Scroll field choices in the form edit page if radio, check box, or select fields have more than 10 choices
* Free only: Removed export template link since the functionality behind it is only in Pro version
* PRO: Added CSV entry import
* PRO: Added file icons when editing an entry with a non-image file type attached
* PRO: Added functionality for time fields set as unique so time options will be removed after a date is selected
* PRO: Check wp_query if no matching GET or POST variable in the get shortcode
* PRO: Switch taxonomy lists to links in custom displays
* PRO: Added functionality for a where option to be set to a taxonomy name ie [get param=tag]
* PRO: Added functionality for a taxonomy to work with equals and not_equal in custom displays
* PRO: Removed ajax error checking on the captcha field to fix the incorrect response messages
* PRO: Fixed dependent data from entries fields to show the selected values on validation error and on edit
* PRO: Added `[frm-entry-update-field]` shortcode to update a single field in an entry with an ajax link
* PRO: Added global styling option to set newly-added select fields to an automatic width
* PRO: Fixed calendar to allow fields mapped to a post to be used as the date field
* PRO: Fixed conditionally hidden field options to work with post category and post status fields
* PRO: Fixed custom displays to work automatically with pages instead of just post and custom post types
* PRO: Added functionality to frm-stats shortcode to work with posts and adds where options in key/id=value pairs. ex: [frm-stats id=x 25=hello] where 25 is the field ID and "Hello" is the value the other field in the form should have in order to display
* PRO: Updated datepicker and timepicker to latest versions
* PRO: Fixed bug preventing images for saving correctly if the form is set to create a post and the upload field is not set as a post field
* PRO: Added an "Insert Position" option to the custom display. This will prevent the custom display from being loaded multiple times per page, but will allow users to set when it shows up for themes like Thesis
* PRO: Fixed number field to work with decimals and when ordering descending
* PRO: Added a limit to the number of entries that show in the entry drop-down in places like the custom display page to prevent memory errors
* PRO: Fixed field options to work better with symbols like &reg; in graphs
* PRO: Automatically open collapsible heading if there is an error message inside it
* PRO: Added type=deviation to the frm-stats shortcode. Example: [frm-stats id=x type=deviation]
* PRO: Updated calculations to work with radio, scale, and drop-down fields
* PRO: Fixed default values for check boxes
* PRO: Added CSV export format option
* PRO: Fixed scale field reports to show all options

= 1.05.03 =
* Updated user role options to work more reliably with WP 3.1
* Added functionality for "Fit Select Boxes into SideBar" checkbox and field size in widget in free version
* Moved reCaptcha error message to individual field options
* Updated referring URL and added tracking throughout the visit
* PRO: Added "clickable" option for use in custom displays to make email addresses and URLs into links. ex `[25 clickable=1]`
* PRO: Added option to select the taxonomy type
* PRO: Updated form styling to work better in IE
* PRO: Updated emails to work with Data from entries checkbox fields
* PRO: Updated dependent Data from entries fields to work with checkboxes
* PRO: Adjusted [date] and [time] values to adjust for WordPress timezone settings
* PRO: Updated the way conditionally hidden fields save in the admin to prevent lingering dependencies
* PRO: Fixed link to duplicate entries
* PRO: Updated file upload indicator to show up sooner
* PRO: Added ajax delete to [deletelink] shortcode
* PRO: Updated admin only fields to show for administrators on the front-end
* PRO: Added more attributes to the [display-frm-data] shortcode: limit="5", page_size="5", order_by="rand" or field ID, order="DESC" or "ASC"
* PRO: Fixed custom display bulk delete
* PRO: Updated WPMU copy features to work with WP 3.0+
* PRO: Switched the email "add/or" drop-down to check boxes
* PRO: Added box for message to be displayed if there are no entries for a custom display
* PRO: Added ajax edit options with [frm-entry-edit-link id=x label=Edit cancel=Cancel class='add_classes' page_id= prefix='frm_edit_' form_id=>y]. Also works with [editlink location=front] in custom displays.
* PRO: Moved styling options into a tab on the settings page
* PRO: Added limited "data from entries" options to the custom display "where" row. Entry keys or IDs can be used
* PRO: Added unique validation for fields set as post fields
* PRO: Removed error messages for required fields hidden via the shortcode options
* PRO: Only return [deletelink] if user can delete the entry
* PRO: Added order options to calendar displays
* PRO: Updated custom display ordering to order correctly when using a 12 hour time field
* PRO: Added taxonomy options to the "Tags" field
* PRO: Added HTML escaping to text fields to allow HTML entities to remain as entities when editing
* PRO: Added functionality to use taxonomy fields in where options in custom displays
* PRO: Added option to use [get param=CUSTOM] in custom displays

= 1.05.02 =
* Fixed issue with PHP4 that was causing the field options to get cleared out and only show a "0" or "<" instead of the field
* Prevent javascript from getting loaded twice
* Updated stylesheets for better looking left aligned field labels. In the Pro version, setting the global labels to one location and setting a single field to another will keep the field description and error messages aligned.
* PRO: Fixed issue causing form to be hidden on front-end edit if it was set not to show with the success message
* PRO: Show the linked image instead of the url when a file is linked in a "just show it" data from entries field
* PRO: Added functionality for ordering by post fields in a custom display

= 1.05.01 = 
* PRO: Fix custom display settings for posts

= 1.05.0 =
* Moved a form widget from Pro into the free version
* Updated some templates with fields aligned in a row
* Moved error messages underneath input fields
* Added option to display labels "hidden" instead of just none. This makes aligning fields in a row with only one label easier
* Additional XHTML compliance for multiple forms on one 
* Removed the HTML5 required attribute (temporarily)
* Corrected the label position styling in the regular version
* A little UI clean up
* Added hook for recaptcha customizations
* PRO: Added custom post type support
* PRO: Added hierarchy to post categories
* PRO: Added a loading indicator while files are uploading
* PRO: Added a `[default-message]` shortcode for use in the email message. Now you can add to the default message without completely replacing it 
* PRO: Added default styling to the formresults shortcode, as well as additional shortcode options: `[formresults id=x style=1 no_entries="No Entries Found" fields="25,26,27"]`
* PRO: Added localizations options to calendar
* PRO: Fixed collapsible Section headings to work with updated HTML
* PRO: Added functionality to admin search to check data from entries fields
* PRO: Added start and end time options for time fields
* PRO: Added 'type' to `[frm-graph]` shortcode to force 'pie' or 'bar': `[frm-graph id=x type=pie]`
* PRO: Added post_id option to the `[frm-search]` shortcode. This will set the action link for the search form. Ex: `[frm-search post_id=3]`
* PRO: Fixed `[frm-search]` shortcode for use on dynamic custom displays. If searching on a detailed entry page, the search will return to the listing page.
* PRO: Updated post fields to work in "data from entries" fields

= 1.04.07 =
* Minor bug fixes
* PRO: Fixed bug preventing some hidden field values from being saved
* PRO: Removed PHP warnings some users were seeing on the form entries page

= 1.04.06 =
* Additional back-end XHTML compliance
* PRO: Fixed conditionally hidden fields bug some users were experiencing

= 1.04.05 =
* Added duplicate entry checks
* Added a checkbox to mark fields required
* Moved the duplicate field option into free version
* Show the success message even if the form isn't displayed with it
* Added option to not use dynamic stylesheet loading
* PRO: Added option to resend email notification and autoresponse
* PRO: Fixes for editing forms with unique fields
* PRO: Fixes for editing multi-paged forms with validation errors
* PRO: Fixes for multiple multi-paged form on the same page
* PRO: Added linked fields into the field drop-downs for inserting shortcodes and sending emails
* PRO: Added field calculations
* PRO: Allow hidden fields to be edited from the WordPress admin
* PRO: Allow sections of fields to be hidden conditionally with the Section Header fields
* PRO: Added user_id option to the `[frm-graph]` shortcode
* PRO: Updated the custom display settings interface
