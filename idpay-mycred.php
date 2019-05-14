<?php

/*
Plugin Name: IDPay myCRED
Version: 1.0
Description: IDPay payment gateway for myCRED
Author: IDPay
Author URI: https://idpay.ir
Text Domain: idpay-mycred
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-mycred-gateway-idpay.php' );
