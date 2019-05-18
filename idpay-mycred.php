<?php

/*
Plugin Name: IDPay myCRED
Version: 1.0.3
Description: IDPay payment gateway for myCRED
Author: IDPay
Author URI: https://idpay.ir
Text Domain: idpay-mycred
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function idpay_mycred_load_textdomain() {
	load_plugin_textdomain( 'idpay-mycred', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'idpay_mycred_load_textdomain' );

require_once( plugin_dir_path( __FILE__ ) . 'class-mycred-gateway-idpay.php' );
