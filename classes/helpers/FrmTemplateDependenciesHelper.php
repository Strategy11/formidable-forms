<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Resolve template requirements before importing any forms.
 *
 * @since x.x
 */
class FrmTemplateDependenciesHelper {

	/**
	 * Get missing dependencies for a validated template download URL.
	 *
	 * @since x.x
	 *
	 * @param string $url The template XML download URL.
	 *
	 * @return array[] Missing dependencies and their available actions.
	 */
	public static function get_missing_addons( $url ) {
		$api       = new FrmFormTemplateApi();
		$templates = $api->get_api_info();
		$path      = wp_parse_url( $url, PHP_URL_PATH );
		$required  = array();

		foreach ( $templates as $template ) {
			if ( ! is_array( $template ) || empty( $template['url'] ) || $path !== wp_parse_url( $template['url'], PHP_URL_PATH ) ) {
				continue;
			}

			$required = self::get_required_addons( $template );
			break;
		}

		if ( ! $required ) {
			return array();
		}

		$api     = new FrmFormApi();
		$addons  = $api->get_api_info();
		$missing = array();

		foreach ( $required as $requirement ) {
			$addon = self::find_addon( $requirement, $addons );

			if ( $addon && is_plugin_active( $addon['plugin'] ) ) {
				continue;
			}

			$key             = $addon ? $addon['plugin'] : $requirement;
			$missing[ $key ] = self::get_dependency_action( $requirement, $addon );
		}

		return array_values( $missing );
	}

	/**
	 * Combine the API add-ons with known template corrections.
	 *
	 * @since x.x
	 *
	 * @param array $template The API template record.
	 *
	 * @return string[] Add-on identifiers or legacy display names.
	 */
	public static function get_required_addons( $template ) {
		$required = array();

		foreach ( (array) ( $template['used_addons'] ?? array() ) as $name ) {
			// Basic payment gateways ship in Lite and do not need separate add-ons.
			if ( ! is_string( $name ) || in_array( $name, array( 'Stripe', 'Square', 'PayPal Commerce' ), true ) ) {
				continue;
			}

			$required[] = $name;
		}

		$overrides = self::get_legacy_requirements();
		$required  = array_merge( $required, $overrides[ (int) ( $template['id'] ?? 0 ) ] ?? array() );

		return array_values( array_unique( $required ) );
	}

	/**
	 * Fill known omissions in the template API add-on metadata.
	 *
	 * Date overrides use dynamic date boundaries or restricted weekdays, not ordinary date fields.
	 *
	 * @since x.x
	 *
	 * @return array<int,string[]> Required add-on slugs indexed by template ID.
	 */
	private static function get_legacy_requirements() {
		return array(
			// Job Application.
			20874045 => array( 'geo' ),
			// Emergency Contact.
			20874049 => array( 'dates' ),
			// Direct Deposit ACH Authorization.
			28028161 => array( 'signature', 'dates' ),
			// Signup Sheet.
			28173825 => array( 'chat' ),
			// Electronic Fund Transfer.
			28314884 => array( 'dates' ),
			// Counseling Informed Consent.
			28028170 => array( 'signature' ),
			// Employee Write Up.
			28150044 => array( 'signature', 'dates' ),
			// New Hire.
			28221984 => array( 'signature' ),
			// Leave of Absence.
			28195109 => array( 'dates' ),
			// Harry Potter Character Quiz.
			28200493 => array( 'chat' ),
			// Return Merchandise Authorization Form.
			28225326 => array( 'dates' ),
			// Personality Quiz.
			28215091 => array( 'chat' ),
			// Patient Satisfaction Survey.
			28215099 => array( 'dates' ),
			// Employee Termination.
			28057162 => array( 'dates' ),
			// Bakery Order.
			28031306 => array( 'dates' ),
			// Google Autofill Address.
			28121163 => array( 'geo' ),
			// Esthetician Consent.
			28031311 => array( 'signature' ),
			// Audit.
			28222545 => array( 'signature' ),
			// Suggestion Box.
			28108886 => array( 'chat' ),
			// Receipt.
			28222552 => array( 'dates' ),
			// Personal Financial Statement.
			28235865 => array( 'signature' ),
			// Evaluation.
			28145242 => array( 'signature' ),
			// Notary.
			28223335 => array( 'signature' ),
			// Jail Release.
			28222569 => array( 'dates' ),
			// Bid.
			28142704 => array( 'signature' ),
			// FAQ.
			28172146 => array( 'chat' ),
			// Donation Pledge.
			28029811 => array( 'signature' ),
			// Uninsured Contractor Waiver.
			28313460 => array( 'dates' ),
			// Credit Card Application.
			28223861 => array( 'signature' ),
			// AI Pre-Sale Assistant.
			28194423 => array( 'ai' ),
			// Personal Information.
			28172151 => array( 'chat' ),
			// Release of Information.
			28221049 => array( 'dates' ),
			// Attachment Style Quiz.
			28219004 => array( 'chat' ),
			// Straw Poll.
			28200063 => array( 'chat' ),
			// Volunteer Hours.
			28220800 => array( 'dates' ),
			// Student Enrollment.
			28221314 => array( 'dates' ),
			// Lab Requisition.
			28222595 => array( 'signature' ),
			// Positive Covid Test Results.
			28247428 => array( 'dates' ),
			// Community Service.
			28221318 => array( 'dates' ),
			// Appeal.
			28223625 => array( 'signature' ),
			// Pizza Survey.
			28269198 => array( 'chat' ),
			// BMI Calculator AI.
			28195727 => array( 'ai' ),
			// Minor Travel Consent.
			28227216 => array( 'signature', 'dates' ),
			// Online Pledge.
			28217745 => array( 'dates' ),
			// Social Media Photo Release.
			28228498 => array( 'dates' ),
			// Online Contact Us.
			28104595 => array( 'chat' ),
			// AI Auto-Mate.
			28194205 => array( 'ai' ),
			// Florida Living Will.
			28227230 => array( 'signature' ),
			// Employee Evaluation.
			28029869 => array( 'dates' ),
			// PTO Request.
			28218801 => array( 'dates' ),
			// Home Inspection Request Form.
			28307636 => array( 'signature' ),
			// Expense Reimbursement.
			28015032 => array( 'signature' ),
			// Maintenance Request.
			20908990 => array( 'dates' ),
			// Direction of Pay.
			28232128 => array( 'signature' ),
			// Membership.
			28150471 => array( 'signature', 'geo' ),
			// Credit Card Authorization.
			28150475 => array( 'signature', 'geo' ),
			// Bio Writing Assistant.
			28194252 => array( 'ai' ),
			// Product Name Generator.
			28194254 => array( 'ai' ),
			// Book Buddy.
			28194256 => array( 'ai' ),
			// Authorization.
			28150482 => array( 'signature', 'geo' ),
			// Auction Item Registration.
			28303585 => array( 'signature' ),
			// Deadline Calculator.
			28030947 => array( 'dates' ),
			// Employee Discipline.
			28149480 => array( 'signature' ),
			// AI Customer Support.
			28198901 => array( 'ai' ),
			// Free Estimate.
			28173813 => array( 'chat' ),
			// Counseling Intake.
			28028153 => array( 'dates' ),
			// Complaint.
			20908994 => array( 'dates' ),
		);
	}

	/**
	 * Resolve API IDs, slugs, plugin basenames, and legacy display names.
	 *
	 * @since x.x
	 *
	 * @param string $requirement The required add-on identifier.
	 * @param array  $addons The add-on API catalog.
	 *
	 * @return array|false The matching add-on record.
	 */
	private static function find_addon( $requirement, $addons ) {
		$aliases = array(
			'digital-signatures' => 'signature',
			'ai-forms'           => 'ai',
			'datepicker-options' => 'dates',
		);
		$slug    = sanitize_title( $requirement );
		$slug    = $aliases[ $slug ] ?? $slug;

		foreach ( $addons as $id => $addon ) {
			if ( ! is_array( $addon ) || empty( $addon['plugin'] ) ) {
				continue;
			}

			$directory   = dirname( $addon['plugin'] );
			$identifiers = array( (string) $id, $addon['plugin'], $directory, preg_replace( '/^formidable-/', '', $directory ), $addon['slug'] ?? '', $addon['title'] ?? '' );

			foreach ( $identifiers as $identifier ) {
				$identifier_slug = sanitize_title( $identifier );
				$identifier_slug = $aliases[ $identifier_slug ] ?? $identifier_slug;

				if ( $slug === $identifier_slug ) {
					return $addon;
				}
			}
		}

		return false;
	}

	/**
	 * Describe an available activation, installation, or manual resolution.
	 *
	 * @since x.x
	 *
	 * @param string      $requirement The required add-on identifier.
	 * @param array|false $addon The matching catalog record.
	 *
	 * @return array The dependency label and action.
	 */
	private static function get_dependency_action( $requirement, $addon ) {
		$result = array(
			'name'    => $addon ? $addon['title'] : $requirement,
			'action'  => '',
			'plugin'  => '',
			'url'     => admin_url( 'admin.php?page=formidable-addons' ),
			'message' => __( 'Install and activate this add-on before importing the template.', 'formidable' ),
		);

		if ( ! current_user_can( 'activate_plugins' ) ) {
			$result['message'] = __( 'Ask an administrator to activate this add-on before importing the template.', 'formidable' );
			return $result;
		}

		if ( ! $addon ) {
			return $result;
		}

		if ( file_exists( WP_PLUGIN_DIR . '/' . $addon['plugin'] ) ) {
			$result['action'] = 'frm_activate_addon';
			$result['plugin'] = $addon['plugin'];
		} elseif ( ! empty( $addon['url'] ) && current_user_can( 'install_plugins' ) ) {
			$result['action'] = 'frm_install_addon';
			$result['plugin'] = $addon['url'];
		} else {
			$result['message'] = __( 'This add-on is not available to install. Check your license or install it manually, then try again.', 'formidable' );
		}

		return $result;
	}
}
