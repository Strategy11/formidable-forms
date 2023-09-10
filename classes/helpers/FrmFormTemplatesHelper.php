<?php
/**
 * Form Templates Helper class.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

/**
 * Copyright (C) 2023 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmFormTemplatesHelper.
 * Handles the Form Templates page in the admin area.
 *
 * @since x.x
 */
class FrmFormTemplatesHelper {

	/**
	 * Retrieves the list of template categories to ignore.
	 *
	 * @since x.x
	 *
	 * @return string[] Array of categories to ignore.
	 */
	public static function ignore_template_categories() {
		return array( 'Business', 'Elite', 'Personal', 'Creator', 'Basic', 'free' );
	}

	/**
	 * Renders a template icon based on the given categories.
	 *
	 * @since x.x
	 *
	 * @param array $categories The categories to render the icon for.
	 * @param array $atts {
	 *     Optional. An array of attributes for rendering.
	 *     @type string  $html 'span' or 'div'. Default 'span'.
	 *     @type boolean $bg   Whether to add a background color or not. Default false.
	 * }
	 *
	 * @return void
	 */
	public static function template_icon( $categories, $atts = array() ) {
		// Define defaults.
		$defaults = array(
			'bg'   => true,
		);
		$atts = array_merge( $defaults, $atts );

		// Filter out ignored categories.
		$ignore     = self::ignore_template_categories();
		$categories = array_diff( $categories, $ignore );

		// Define icons mapping.
		$icons = array(
			'WooCommerce'         => array( 'woocommerce', 'var(--purple)' ),
			'Post'                => array( 'wordpress', 'rgb(0,160,210)' ),
			'User Registration'   => array( 'register', 'var(--pink)' ),
			'Registration and Signup' => array( 'register', 'var(--pink)' ),
			'PayPal'              => array( 'paypal' ),
			'Stripe'              => array( 'credit_card', 'var(--green)' ),
			'Twilio'              => array( 'sms' ),
			'Payment'             => array( 'credit_card' ),
			'Order Form'          => array( 'product' ),
			'Finance'             => array( 'total' ),
			'Health and Wellness' => array( 'heart', 'var(--pink)' ),
			'Event Planning'      => array( 'calendar', 'var(--orange)' ),
			'Real Estate'         => array( 'house' ),
			'Nonprofit'           => array( 'heart_solid' ),
			'Calculator'          => array( 'calculator', 'var(--purple)' ),
			'Quiz'                => array( 'percent' ),
			'Registrations'       => array( 'address_card' ),
			'Customer Service'    => array( 'users_solid' ),
			'Education'           => array( 'pencil' ),
			'Marketing'           => array( 'eye' ),
			'Feedback'            => array( 'smile' ),
			'Business Operations' => array( 'case' ),
			'Contact Form'        => array( 'email' ),
			'Conversational Forms' => array( 'chat_forms' ),
			'Survey'              => array( 'chat_forms', 'var(--orange)' ),
			'Application'         => array( 'align_right' ),
			'Signature'           => array( 'signature' ),
			''                    => array( 'align_right' ),
		);

		// Determine the icon to be used.
		$icon = $icons[''];
		if ( count( $categories ) === 1 ) {
			$category = reset( $categories );
			$icon = isset( $icons[ $category ] ) ? $icons[ $category ] : $icon;
		} elseif ( ! empty( $categories ) ) {
			$icons = array_intersect_key( $icons, array_flip( $categories ) );
			$icon = reset( $icons );
		}

		// Prepare variables for output.
		$icon_name = $icon[0];
		$bg_color  = isset( $icon[1] ) ? $icon[1] : '';

		// Render the icon.
		echo '<span class="frm-category-icon frm-icon-wrapper"';
		echo $bg_color && $atts['bg'] ? ' style="background-color:' . esc_attr( $bg_color ) . '"' : '';
		echo '>';
			FrmAppHelper::icon_by_class( 'frmfont frm_' . $icon_name . '_icon' );
		echo '</span>';
	}

	/**
	 * @since 4.02
	 */
	public static function get_template_install_link( $template, $args ) {
		$defaults = array(
			'class' => 'install-now',
			'href'  => 'href',
			'atts'  => true,
		);

		if ( ! empty( $template['url'] ) ) {
			$link = array(
				'url'   => $template['url'],
				'label' => __( 'Create Form', 'formidable' ),
				'class' => 'frm-install-template',
				'href'  => 'rel',
				'atts'  => '',
			);
		} elseif ( self::plan_is_allowed( $args ) ) {
			$link = array(
				'url'   => FrmAppHelper::admin_upgrade_link( 'addons', 'account/downloads/' ) . '&utm_content=' . $template['slug'],
				'label' => __( 'Renew', 'formidable' ),
			);
		} else {
			$link = array(
				'url'   => $args['pricing'],
				'label' => __( 'Upgrade', 'formidable' ),
			);
		}

		return array_merge( $defaults, $link );
	}

	/**
	 * Is the template included with the license type?
	 *
	 * @since 4.02.02
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public static function plan_is_allowed( $args ) {
		if ( empty( $args['license_type'] ) ) {
			return false;
		}

		$included = $args['license_type'] === strtolower( $args['plan_required'] );

		$plans = array( 'free', 'personal', 'business', 'elite' );
		if ( $included || ! in_array( strtolower( $args['plan_required'] ), $plans, true ) ) {
			return $included;
		}

		foreach ( $plans as $plan ) {
			if ( $included || $plan === $args['license_type'] ) {
				break;
			}
			$included = $plan === strtolower( $args['plan_required'] );
		}

		return $included;
	}
}
