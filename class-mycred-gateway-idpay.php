<?php

add_action( 'plugins_loaded', 'mycred_idpay_plugins_loaded' );

function mycred_idpay_plugins_loaded() {
	add_filter( 'mycred_setup_gateways', 'Add_IDPay_to_Gateways' );

	function Add_IDPay_to_Gateways( $installed ) {

		$installed['idpay'] = [
			'title'    => get_option( 'idpay_display_name' ) ? get_option( 'idpay_display_name' ) : __( 'IDPay payment gateway', 'idpay-mycred' ),
			'callback' => [ 'myCred_IDPay' ],
		];

		return $installed;
	}

	add_filter( 'mycred_buycred_refs', 'Add_IDPay_to_Buycred_Refs' );

	function Add_IDPay_to_Buycred_Refs( $addons ) {
		$addons['buy_creds_with_idpay'] = __( 'IDPay Gateway', 'idpay-mycred' );

		return $addons;
	}

	add_filter( 'mycred_buycred_log_refs', 'Add_IDPay_to_Buycred_Log_Refs' );

	function Add_IDPay_to_Buycred_Log_Refs( $refs ) {
		$idpay = [ 'buy_creds_with_idpay' ];

		return $refs = array_merge( $refs, $idpay );
	}
}

spl_autoload_register( 'mycred_idpay_plugin' );

function mycred_idpay_plugin() {
	if ( ! class_exists( 'myCRED_Payment_Gateway' ) ) {

		return;
	}

	if ( ! class_exists( 'myCred_IDPay' ) ) {

		class myCred_IDPay extends myCRED_Payment_Gateway {

			function __construct( $gateway_prefs ) {

				$types            = mycred_get_types();
				$default_exchange = [];

				foreach ( $types as $type => $label ) {

					$default_exchange[ $type ] = 1000;
				}

				parent::__construct( [

					'id'       => 'idpay',
					'label'    => get_option( 'idpay_display_name' ) ? get_option( 'idpay_display_name' ) : __( 'IDPay payment gateway', 'idpay-mycred' ),
					'defaults' => [
						'api_key'            => NULL,
						'sandbox'            => FALSE,
						'idpay_display_name' => __( 'IDPay payment gateway', 'idpay-mycred' ),
						'currency'           => 'rial',
						'exchange'           => $default_exchange,
						'item_name'          => __( 'Purchase of myCRED %plural%', 'mycred' ),
					],
				], $gateway_prefs );
			}

			public function IDPay_Iranian_currencies( $currencies ) {
				unset( $currencies );

				$currencies['rial']  = __( 'Rial', 'idpay-mycred' );
				$currencies['toman'] = __( 'Toman', 'idpay-mycred' );

				return $currencies;
			}

			function preferences() {
				add_filter( 'mycred_dropdown_currencies', [
					$this,
					'IDPay_Iranian_currencies',
				] );

				$prefs = $this->prefs;
				?>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'api_key' ); ?>"><?php _e( 'API Key', 'idpay-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'api_key' ); ?>"
                                   name="<?php echo $this->field_name( 'api_key' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['api_key']; ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'sandbox' ); ?>"><?php _e( 'Sandbox', 'idpay-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'sandbox' ); ?>"
                                   name="<?php echo $this->field_name( 'sandbox' ); ?>"
                                   type="checkbox"
								<?php if ( $prefs['sandbox'] == "on" )
									echo 'checked="checked"' ?>

                            />
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'idpay_display_name' ); ?>"><?php _e( 'Title', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'idpay_display_name' ); ?>"
                                   name="<?php echo $this->field_name( 'idpay_display_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['idpay_display_name'] ? $prefs['idpay_display_name'] : __( 'IDPay payment gateway', 'idpay-mycred' ); ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'currency' ); ?>"><?php _e( 'Currency', 'mycred' ); ?></label>
                <ol>
                    <li>
						<?php $this->currencies_dropdown( 'currency', 'mycred-gateway-idpay-currency' ); ?>
                    </li>
                    0
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'item_name' ); ?>"><?php _e( 'Item Name', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'item_name' ); ?>"
                                   name="<?php echo $this->field_name( 'item_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['item_name']; ?>"
                                   class="long"/>
                        </div>
                        <span class="description"><?php _e( 'Description of the item being purchased by the user.', 'mycred' ); ?></span>
                    </li>
                </ol>

                <label class="subheader"><?php _e( 'Exchange Rates', 'mycred' ); ?></label>
                <ol>
                    <li>
						<?php $this->exchange_rate_setup(); ?>
                    </li>
                </ol>
				<?php
			}

			public function sanitise_preferences( $data ) {
				$new_data['api_key']            = sanitize_text_field( $data['api_key'] );
				$new_data['idpay_display_name'] = sanitize_text_field( $data['idpay_display_name'] );
				$new_data['currency']           = sanitize_text_field( $data['currency'] );
				$new_data['item_name']          = sanitize_text_field( $data['item_name'] );
				$new_data['sandbox']            = sanitize_text_field( $data['sandbox'] );

				if ( isset( $data['exchange'] ) ) {

					foreach ( (array) $data['exchange'] as $type => $rate ) {

						if ( $rate != 1 && in_array( substr( $rate, 0, 1 ), [
								'.',
								',',
							] ) ) {

							$data['exchange'][ $type ] = (float) '0' . $rate;
						}
					}
				}

				$new_data['exchange'] = $data['exchange'];

				update_option( 'idpay_display_name', $new_data['idpay_display_name'] );

				return $data;
			}

			public function process() {

				$pending_post_id = sanitize_text_field( $_REQUEST['payment_id'] );

				$org_pending_payment = $pending_payment = $this->get_pending_payment( $pending_post_id );

				$status   = sanitize_text_field( $_POST['status'] );
				$track_id = sanitize_text_field( $_POST['track_id'] );
				$id       = sanitize_text_field( $_POST['id'] );
				$order_id = sanitize_text_field( $_POST['order_id'] );
				$amount   = sanitize_text_field( $_POST['amount'] );
				$card_no  = sanitize_text_field( $_POST['card_no'] );
				$date     = sanitize_text_field( $_POST['date'] );

				if ( $status == 10 ) {
					$api_key = $api_key = $this->prefs['api_key'];
					$sandbox = $this->prefs['sandbox'];

					$data = [
						'id'       => $id,
						'order_id' => $order_id,
					];

					$headers = [
						'Content-Type' => 'application/json',
						'X-API-KEY'    => $api_key,
						'X-SANDBOX'    => $sandbox,
					];

					$args = [
						'body'    => json_encode( $data ),
						'headers' => $headers,
						'timeout' => 30,
					];

					$response = $this->call_gateway_endpoint( 'https://api.idpayy.ir/v1.1/payment/verify', $args );
					if ( is_wp_error( $response ) ) {
						$log = $response->get_error_message();
						$this->log_call( $pending_post_id, $log );
						wp_die( $log );
						exit;
					}
					$http_status = wp_remote_retrieve_response_code( $response );
					$result      = wp_remote_retrieve_body( $response );
					$result      = json_decode( $result );


					if ( $http_status != 200 ) {
						$log = sprintf( __( 'An error occurred while verifying the transaction. status: %s, code: %s, message: %s', 'idpay-mycred' ), $http_status, $result->error_code, $result->error_message );
						$this->log_call( $pending_post_id, $log );

					} else {

						if ( $result->status >= 100 ) {

							if ( $this->complete_payment( $org_pending_payment, $id ) ) {
								$log = sprintf( __( 'Payment succeeded. Status: %s, Track id: %, Card no: %s', 'idpay-mycred' ), $result->status, $result->track_id, $result->payment->card_no );
								$this->log_call( $pending_post_id, $log );
								$this->trash_pending_payment( $pending_post_id );
								wp_redirect( $this->get_thankyou() );
								exit;
							} else {
								$log = sprintf( __( 'An unexpected error occurred when completing the payment but it is done at the gateway. Track id is: %s', 'idpay-mycred', $result->track_id ) );
								$this->log_call( $pending_post_id, $log );
								wp_redirect( $this->get_cancelled() );
								exit;
							}
						} else {
							$log = sprintf( __( 'Payment failed. Status: %s, Track id: %, Card no: %s', 'idpay-mycred' ), $result->status, $result->track_id, $result->payment->card_no );
							$this->log_call( $pending_post_id, $log );
							wp_redirect( $this->get_cancelled() );
							exit;
						}

					}
				} else {
					$log = sprintf( __( 'Payment failed. Status: %s, Track id: %, Card no: %s', 'idpay-mycred' ), $status, $track_id, $card_no );
					$this->log_call( $pending_post_id, $log );
					wp_redirect( $this->get_cancelled() );
					exit;
				}
			}

			public function returning() {


			}


			/**
			 * Prep Sale
			 *
			 * @since   1.8
			 * @version 1.0
			 */
			public function prep_sale( $new_transaction = FALSE ) {

				// Point type
				$type   = $this->get_point_type();
				$mycred = mycred( $type );

				// Amount of points
				$amount = $mycred->number( $_REQUEST['amount'] );

				// Get cost of that points
				$cost = $this->get_cost( $amount, $type );
				$cost = abs( $cost );

				$to   = $this->get_to();
				$from = $this->current_user_id;

				// Revisiting pending payment
				if ( isset( $_REQUEST['revisit'] ) ) {
					$this->transaction_id = strtoupper( $_REQUEST['revisit'] );
				} else {
					$post_id              = $this->add_pending_payment( [
						$to,
						$from,
						$amount,
						$cost,
						$this->prefs['currency'],
						$type,
					] );
					$this->transaction_id = get_the_title( $post_id );
				}

				$callback = add_query_arg( 'payment_id', $this->transaction_id, $this->callback_url() );
				$api_key  = $this->prefs['api_key'];
				$sandbox  = $this->prefs['sandbox'];


				$data = [
					'order_id' => $this->transaction_id,
					'amount'   => $cost,
					'name'     => '',
					'phone'    => '',
					'mail'     => '',
					'desc'     => '',
					'callback' => $callback,
				];

				$headers = [
					'Content-Type' => 'application/json',
					'X-API-KEY'    => $api_key,
					'X-SANDBOX'    => $sandbox,
				];

				$args = [
					'body'    => json_encode( $data ),
					'headers' => $headers,
					'timeout' => 30,
				];

				$response = $this->call_gateway_endpoint( 'https://api.idpay.ir/v1.1/payment', $args );
				if ( is_wp_error( $response ) ) {
					wp_die( $response->get_error_message() );
					exit;
				}
				$http_status = wp_remote_retrieve_response_code( $response );
				$result      = wp_remote_retrieve_body( $response );
				$result      = json_decode( $result );


				if ( $http_status != 201 || empty( $result ) || empty( $result->id ) || empty( $result->link ) ) {
					if ( ! empty( $result->error_code ) && ! empty( $result->error_message ) ) {
						wp_die( $result->error_message );
						exit;
					}

				}

				$item_name = str_replace( '%number%', $this->amount, $this->prefs['item_name'] );
				$item_name = $this->core->template_tags_general( $item_name );


				$redirect_fields = [
					//'pay_to_email'        => $this->prefs['account'],
					'transaction_id'      => $this->transaction_id,
					'return_url'          => $this->get_thankyou(),
					'cancel_url'          => $this->get_cancelled( $this->transaction_id ),
					'status_url'          => $this->callback_url(),
					'return_url_text'     => get_bloginfo( 'name' ),
					'hide_login'          => 1,
					'merchant_fields'     => 'sales_data',
					'sales_data'          => $this->post_id,
					'amount'              => $this->cost,
					'currency'            => $this->prefs['currency'],
					'detail1_description' => __( 'Item Name', 'mycred' ),
					'detail1_text'        => $item_name,
				];

				// Customize Checkout Page
				if ( isset( $this->prefs['account_title'] ) && ! empty( $this->prefs['account_title'] ) ) {
					$redirect_fields['recipient_description'] = $this->core->template_tags_general( $this->prefs['account_title'] );
				}

				if ( isset( $this->prefs['account_logo'] ) && ! empty( $this->prefs['account_logo'] ) ) {
					$redirect_fields['logo_url'] = $this->prefs['account_logo'];
				}

				if ( isset( $this->prefs['confirmation_note'] ) && ! empty( $this->prefs['confirmation_note'] ) ) {
					$redirect_fields['confirmation_note'] = $this->core->template_tags_general( $this->prefs['confirmation_note'] );
				}

				// If we want an email receipt for purchases
				if ( isset( $this->prefs['email_receipt'] ) && ! empty( $this->prefs['email_receipt'] ) ) {
					$redirect_fields['status_url2'] = $this->prefs['account'];
				}

				// Gifting
				if ( $this->gifting ) {

					$user                                   = get_userdata( $this->recipient_id );
					$redirect_fields['detail2_description'] = __( 'Recipient', 'mycred' );
					$redirect_fields['detail2_text']        = $user->idpay_display_name;

				}

				$this->redirect_fields = $redirect_fields;


				$this->redirect_to = $result->link;

			}

			/**
			 * AJAX Buy Handler
			 *
			 * @since   1.8
			 * @version 1.0
			 */
			public function ajax_buy() {

				// Construct the checkout box content
				$content = $this->checkout_header();
				$content .= $this->checkout_logo();
				$content .= $this->checkout_order();
				$content .= $this->checkout_cancel();
				$content .= $this->checkout_footer();

				// Return a JSON response
				$this->send_json( $content );

			}

			/**
			 * Checkout Page Body
			 * This gateway only uses the checkout body.
			 *
			 * @since   1.8
			 * @version 1.0
			 */
			public function checkout_page_body() {

				echo $this->checkout_header();
				echo $this->checkout_logo( FALSE );

				echo $this->checkout_order();
				echo $this->checkout_cancel();

				echo $this->checkout_footer();

			}

			/**
			 * Calls the gateway endpoints.
			 *
			 * Tries to get response from the gateway for 4 times.
			 *
			 * @param $url
			 * @param $args
			 *
			 * @return array|\WP_Error
			 */
			private function call_gateway_endpoint( $url, $args ) {
				$number_of_connection_tries = 4;
				while ( $number_of_connection_tries ) {
					$response = wp_safe_remote_post( $url, $args );
					if ( is_wp_error( $response ) ) {
						$number_of_connection_tries --;
						continue;
					} else {
						break;
					}
				}

				return $response;
			}

		}
	}
}
