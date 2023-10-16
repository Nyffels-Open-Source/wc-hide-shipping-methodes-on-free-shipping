<?php
/**
 * Plugin Name: Hide shipping methods on free shipping for WooCommerce
 * Description: Hide shipping methods (excl. local pickup) if free shipping is present for WooCommerce.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Nyffels IT
 * Author URI: https://nyffels-it.be 
 * License: MIT
 **/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

	/**
	 * Add settings
	 */

	add_filter('woocommerce_get_settings_shipping', 'nyffels_account_settings', 10, 2);
	function nyffels_account_settings($settings)
	{

		/**
		 * Check the current section is what we want
		 **/

		$settings[] = array('title' => __('Hide shipping methods', 'woocommerce'), 'type' => 'title', 'id' => 'wc_hide_shipping');


		$settings[] = array(
			'title' => __('When "Free Shipping" is available: ', 'woocommerce'),
			'desc' => __('', 'woocommerce'),
			'id' => 'wc_hide_shipping_options',
			'type' => 'radio',
			'desc_tip' => true,
			'options' => array('hide_all' => 'Hide all other methods and only show the "Free Shipping" methodes', 'hide_except_local' => 'Hide all other methods and only show the "Free Shipping" and "Local Pickup" methodes'),
		);

		$settings[] = array('type' => 'sectionend', 'id' => 'wc_hide_shipping');
		return $settings;

	}

	if (get_option('wc_hide_shipping_options') == 'hide_all') {

		add_filter('woocommerce_package_rates', 'wc_hide_shipping_when_free_is_available', 10, 2);

		function wc_hide_shipping_when_free_is_available($rates)
		{
			$free = array();
			foreach ($rates as $rate_id => $rate) {
				if ('free_shipping' === $rate->method_id) {
					$free[$rate_id] = $rate;
				}
			}
			return !empty($free) ? $free : $rates;
		}
	}

	if (get_option('wc_hide_shipping_options') == 'hide_except_local') {

		add_filter('woocommerce_package_rates', 'wc_hide_shipping_when_free_is_available_keep_local', 10, 2);

		function wc_hide_shipping_when_free_is_available_keep_local($rates, $package)
		{
			$new_rates = array();
			foreach ($rates as $rate_id => $rate) {
				if ('free_shipping' === $rate->method_id) {
					$new_rates[$rate_id] = $rate;
				}
			}

			if (!empty($new_rates)) {
				foreach ($rates as $rate_id => $rate) {
					if ('local_pickup' === $rate->method_id) {
						$new_rates[$rate_id] = $rate;
					}
				}
				return $new_rates;
			}

			return $rates;
		}
	}
}

function nyffels_update_default_option()
{
	update_option('wc_hide_shipping_options', 'hide_all');
}

register_activation_hook(__FILE__, 'nyffels_update_default_option');