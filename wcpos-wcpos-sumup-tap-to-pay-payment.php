<?php
/**
 * Plugin Name: WooCommerce POS WCPOS Sumup Tap To Pay payment Gateway
 * Plugin URI: https://github.com/TheStingPilot/sumup-tap-to-pay-gateway
 * Description: Sumup Tap To Pay payment
 * Version: 0.0.1
 * Author: killbot
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway
 */

add_action( 'plugins_loaded', 'woocommerce_pos_wcpos_sumup_tap_to_pay_payment_gateway_init', 0 );

function woocommerce_pos_wcpos_sumup_tap_to_pay_payment_gateway_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
	 * Gateway class
	 */
	class WCPOS_WCPOS_Sumup_Tap_To_PayPayment extends WC_Payment_Gateway {
		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = 'wcpos_wcpos_sumup_tap_to_pay_payment';
			$this->icon               = '';
			$this->has_fields         = false;
			$this->method_title       = 'WCPOS Sumup Tap To Pay payment Gateway';
			$this->method_description = 'Sumup Tap To Pay payment';

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			// only allow in the POS
			$this->enabled = false;

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'title'       => array(
					'title'       => __( 'Title', 'woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway' ),
					'default'     => __( 'WCPOS Sumup Tap To Pay payment', 'woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway' ),
					'default'     => __( 'SumUp Tap To Pay Payment', 'woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway' ),
				),
			);
		}

		public function payment_fields() {
			echo wpautop( wptexturize( $this->description ) );
			// Add any custom payment fields here
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Awaiting payment', 'woocommerce-pos-wcpos-sumup-tap-to-pay-payment-gateway' ) );

			// Reduce stock levels
			wc_reduce_stock_levels( $order_id );

			// Remove cart
			WC()->cart->empty_cart();

			// Return thankyou redirect
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}

	/**
	 * Add the Gateway to WooCommerce
	 */
	add_filter(
		'woocommerce_payment_gateways',
		function ( $methods ) {
			$methods[] = 'WCPOS_WCPOS_Sumup_Tap_To_PayPayment';
			return $methods;
		}
	);
}

