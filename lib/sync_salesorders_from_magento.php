<?php
class TransferSalesorders {

	public static function transferring_salesorders () {
                global $process_id;
                global $wpdb;
                $new_process_id = $process_id['new_process_id'];
                $wpdb->insert("wp_smack_sync_process_id", array('process_id' => $new_process_id));
                $process_ref_id = $wpdb->insert_id;

		$result = array();
		$WPMageWooComHelperObj = new WPMageWooComHelper();
		$mage_api_credentials = $WPMageWooComHelperObj->mage_api_credentials();
		$mageURL = $mage_api_credentials['magentoURL'];
		$apiUser = $mage_api_credentials['apiusername'];
		$apiKey = $mage_api_credentials['apikey'];
		$client = new SoapClient($mageURL);

		// If somestuff requires api authentification,
		// then get a session token
		$session = $client->login($apiUser, $apiKey);

		$result = $client->call($session, 'order.list');

		// Parse the reterived salesorders details
		foreach($result as $salesordersIndex => $salesordersData) {
			#$order_id = $salesordersData['order_id'];
			$increment_id = $salesordersData['increment_id'];
			// Reterive Product Info
			$orderDetails = array();
			if($increment_id != '')
				$orderDetails = self::get_salesorder_info($increment_id, $client, $session);
				$mage_customer_email = $orderDetails['customer_email'];
				$mage_customer_id = $orderDetails['customer_id'];

				$wp_customer_id = email_exists($mage_customer_email);

			if(!$wp_customer_id) {
				if($mage_customer_id) {
					$userlist = $client->call($session, 'customer.list');
					foreach($userlist as $customerKey => $customerData) {
						if($customerData['email'] == $mage_customer_email) {
							// Insert customer informations into WordPress
							$wp_customer_id = self::set_customer_with_info($customerData, $client, $session, $process_ref_id, $mage_customer_id);
							// Set Role to the respective users
							$user_role = array('customer' => 1);
							update_user_meta($wp_customer_id, 'wp_capabilities', $user_role);
							// Reterive customer address info
							try{
								$customer_address_details = array();
								$customer_address_details = self::get_customer_address_info($mage_customer_id, $client, $session);
								if($customer_address_details) {
									foreach($customer_address_details as $addr_index => $addr_data) {
										if($addr_data['is_default_billing'] == 1) {
											$billing_details['billing_first_name'] = $addr_data['firstname'];
											$billing_details['billing_last_name'] = $addr_data['lastname'];
											$billing_details['billing_company'] = $addr_data['company'];
											$billing_details['billing_address_1'] = $addr_data['street'];
											$billing_details['billing_city'] = $addr_data['city'];
											$billing_details['billing_postcode'] = $addr_data['postcode'];
											$billing_details['billing_country'] = $addr_data['country_id'];
											$billing_details['billing_state'] = $addr_data['region'];
											$billing_details['billing_phone'] = $addr_data['telephone'];
											$billing_details['billing_email'] = $mage_customer_email;
										}
										if($addr_data['is_default_shipping'] == 1) {
											$shipping_details['shipping_first_name'] = $addr_data['firstname'];
											$shipping_details['shipping_last_name'] = $addr_data['lastname'];
											$shipping_details['shipping_company'] = $addr_data['company'];
											$shipping_details['shipping_address_1'] = $addr_data['street'];
											$shipping_details['shipping_city'] = $addr_data['city'];
											$shipping_details['shipping_postcode'] = $addr_data['postcode'];
											$shipping_details['shipping_country'] = $addr_data['country_id'];
											$shipping_details['shipping_state'] = $addr_data['region'];
										}
										//Import WooCommerce shipping info
										if (!empty ($shipping_details)) {
											self::set_customer_address_info($wp_customer_id, $shipping_details);
										}
										//Import WooCommerce billing info
										if (!empty ($billing_details)) {
											self::set_customer_address_info($wp_customer_id, $billing_details);
										}
									}
								}
								echo "<b> Success: </b> User created with email id - $customer_email and user id - $wp_customer_id . <br>";
							} catch (Exception $e) {
								echo "Address information not found for the user with Email:" . $customerData['email'] . "<br>";
							}
						}
					}
				}
			}
			$order_id = false;
			if(!empty($orderDetails) && isset($orderDetails['increment_id'])) {
				$is_order = 1;
				$orderData['post_date'] = $orderDetails['created_at'];
				$orderData['post_author'] = 1;
				$orderData['post_type'] = 'shop_order';
				$orderData['post_excerpt'] = '';
				$order_status = $orderDetails['status'];
				$get_wp_order_status = self::get_wp_order_status($order_status);
				$orderData['post_status'] = $get_wp_order_status;
				$wp_order_id = wp_insert_post($orderData); 
				$order_id = absint( $wp_order_id );
			}

			// Order Meta Datas
			$order_metadata['_payment_method_title'] = $orderDetails['payment']['cc_type'];
			$order_metadata['_payment_method'] = $orderDetails['payment']['method'];
			$order_metadata['_transaction_id'] = $orderDetails['payment']['payment_id'];
			$order_metadata['_billing_first_name'] = $orderDetails['billing_address']['firstname'];
			$order_metadata['_billing_last_name'] = $orderDetails['billing_address']['lastname'];
			$order_metadata['_billing_company'] = $orderDetails['billing_address']['company'];
			$order_metadata['_billing_address_1'] = $orderDetails['billing_address']['street'];
#			$order_metadata['_billing_address_2'] = $orderDetails['payment'][''];
			$order_metadata['_billing_city'] = $orderDetails['billing_address']['city'];
			$order_metadata['_billing_postcode'] = $orderDetails['billing_address']['postcode'];
			$order_metadata['_billing_state'] = $orderDetails['billing_address']['region'];
			$order_metadata['_billing_country'] = $orderDetails['billing_address']['country_id'];
			$order_metadata['_billing_phone'] = $orderDetails['billing_address']['telephone'];
			$order_metadata['_billing_email'] = $orderDetails['billing_address']['email'];
			$order_metadata['_shipping_first_name'] = $orderDetails['shipping_address']['firstname'];
			$order_metadata['_shipping_last_name'] = $orderDetails['shipping_address']['lastname'];
			$order_metadata['_shipping_company'] = $orderDetails['shipping_address']['company'];
			$order_metadata['_shipping_address_1'] = $orderDetails['shipping_address']['street'];
#			$order_metadata['_shipping_address_2'] = $orderDetails['payment'][''];
			$order_metadata['_shipping_city'] = $orderDetails['shipping_address']['city'];
			$order_metadata['_shipping_postcode'] = $orderDetails['shipping_address']['postcode'];
			$order_metadata['_shipping_state'] = $orderDetails['shipping_address']['region'];
			$order_metadata['_shipping_country'] = $orderDetails['shipping_address']['country_id'];
			$order_metadata['_customer_user'] = $wp_customer_id;
			$order_metadata['_order_currency'] = $orderDetails['order_currency_code'];
			$order_metadata['_order_shipping_tax'] = $orderDetails['base_shipping_tax_amount'];
			$order_metadata['_order_tax'] = $orderDetails['tax_amount'];
			$order_metadata['_order_total'] = $orderDetails['payment']['amount_ordered'];
			$order_metadata['_cart_discount_tax'] = 0;
			$order_metadata['_cart_discount'] = 0;
			$order_metadata['_order_shipping'] = $orderDetails['payment']['shipping_amount'];
			$order_metadata['_refund_amount'] = $orderDetails['payment']['amount_refunded']; 

			if($order_id) {

				// Add all order meta details
				foreach($order_metadata as $meta_key => $meta_value) {
					update_post_meta($order_id, $meta_key, $meta_value);
				}

				// Order - Items Details
				$orderedItems = $orderDetails['items'];

				$billing_email = $orderDetails['customer_email'];
  				$exists = email_exists($billing_email);

				foreach($orderedItems as $item_index => $item_data) {
					$item_metadata['order_item_name'] = $item_data['name'];
					$item_metadata['order_item_type'] = 'line_item';
					$item_metadata['_variation_id'] = '';
					$item_metadata['_line_subtotal'] = $orderDetails['base_subtotal'];
					$item_metadata['_line_subtotal_tax'] = 0;
					$item_metadata['_line_total'] = $orderDetails['base_grand_total'];
					$item_metadata['_line_tax'] = $item_data['tax_amount'];
					$item_metadata['_line_tax_data'] = 'a:2:{s:5:"total";a:0:{}s:8:"subtotal";a:0:{}} | a:2:{s:5:"total";a:0:{}s:8:"subtotal";a:0:{}}';
					$item_metadata['_tax_class'] = 'reduced-rate';
					$get_qty = explode('.', $item_data['qty_ordered']);
					$qty = $get_qty[0];
					$item_metadata['_qty'] = $qty;

					$item = array(
							'order_item_name'     => $item_data['name'],
							'order_item_type'     => 'line_item',
						     );
					$wpdb->insert( $wpdb->prefix . "woocommerce_order_items",
							array(
								'order_item_name'     => $item['order_item_name'],
								'order_item_type'     => $item['order_item_type'],
								'order_id'        => $order_id
							     ),
							array(
								'%s', '%s', '%d'
							     )
						     );
					$item_id = absint( $wpdb->insert_id );
					if($item_metadata['_variation_id'] != '')
						woocommerce_add_order_item_meta( $item_id, '_variation_id', $item_metadata['_variation_id']);
					woocommerce_add_order_item_meta( $item_id, '_line_subtotal', $item_metadata['_line_subtotal']);
					woocommerce_add_order_item_meta( $item_id, '_line_subtotal_tax', $item_metadata['_line_subtotal_tax']);
					woocommerce_add_order_item_meta( $item_id, '_line_total', $item_metadata['_line_total']);
					woocommerce_add_order_item_meta( $item_id, '_line_tax', $item_metadata['_line_tax']);
					if($item_metadata['_line_tax_data']) {
						$unserialized_tax_data = unserialize($item_metadata['_line_tax_data']);
						woocommerce_add_order_item_meta( $item_id, '_line_tax_data', $unserialized_tax_data);
					}
					woocommerce_add_order_item_meta( $item_id, '_tax_class', $item_metadata['_tax_class']);
					woocommerce_add_order_item_meta( $item_id, '_qty', $item_metadata['_qty']);
				}

				/*			$fee_metadata['order_item_name'] = $orderDetails['payment'][''];
							$fee_metadata['order_item_type'] = $orderDetails['payment'][''];
							$fee_metadata['_tax_class'] = $orderDetails['payment'][''];
							$fee_metadata['_line_total'] = $orderDetails['payment'][''];
							$fee_metadata['_line_tax'] = $orderDetails['payment'][''];
							$fee_metadata['_line_tax_data'] = $orderDetails['payment'][''];
							$fee_metadata['_line_subtotal'] = $orderDetails['payment'][''];
							$fee_metadata['_line_subtotal_tax'] = $orderDetails['payment']['']; */

				// Order - Shipment Details
				$shipment_metadata['order_item_name'] = $orderDetails['shipping_description'];
				$shipment_metadata['method_id'] = 'flat_rate';
				$shipment_metadata['cost'] = $orderDetails['shipping_amount'];
				$shipment_metadata['taxes'] = '';
				$shipmentitem = trim($shipment_metadata['order_item_name']);
				$item = array(
						'order_item_name'     => $shipment_metadata['order_item_name'],
						'order_item_type'     => 'shipping',
					     );

				$wpdb->insert( $wpdb->prefix . "woocommerce_order_items",
						array(
							'order_item_name'     => $item['order_item_name'],
							'order_item_type'     => $item['order_item_type'],
							'order_id'        => $order_id
						     ),
						array(
							'%s', '%s', '%d'
						     )
					     );
				$item_id = absint( $wpdb->insert_id );
				woocommerce_add_order_item_meta( $item_id, 'method_id', $shipment_metadata['method_id']);
				woocommerce_add_order_item_meta( $item_id, 'cost', $shipment_metadata['cost']);
				if($shipment_metadata['taxes'] != '') {
					$unserialized_tax_data = unserialize($shipment_metadata['taxes']);
					woocommerce_add_order_item_meta( $item_id, 'taxes', $unserialized_tax_data);
				}
				// Capturing order sync log
				$process_time = date('Y-m-d H:i:s');
				$description = 'New Order has been placed in your store with order id - ' . $order_id;
				$wpdb->insert("wp_smack_letme_sync", array('process_ref_id' => $process_ref_id, 'sync_module' => 'Salesorder', 'sync_process_time' => $process_time, 'mage_ref_id' => $increment_id, 'wp_ref_id' => $order_id, 'sync_flow' => 'Magento to WordPress', 'status' => 'Success', 'description' => $description));
				echo "New Order has been placed in your store with order id - " . $order_id . "<br>";
			}
			// Insert the Parent Category into WordPress
			//self::set_product_with_info($single_product, $client, $session);
			//die('One record fetched successfully');
		}

		// If you don't need the session anymore
		//$client->endSession($session); */
	}

	public static function get_salesorder_info($order_id, $client, $session) {
		$order_info = $client->call($session, 'sales_order.info', $order_id);
		return $order_info;
	}

	public static function get_wp_order_status($mage_order_status) {
		$wp_order_status = array('canceled' => 'wc-cancelled', 'closed' => 'wc-failed', 'complete' => 'wc-completed', 'fraud' => 'wc-failed', 'holded' => 'wc-on-hold', 'payment_review' => 'wc-processing', 'pending' => 'wc-pending', 'pending_payment' => 'wc-pending', 'pending_paypal' => 'wc-pending', 'processing' => 'wc-processing');
		return $wp_order_status[$mage_order_status];
	}

	public static function get_itemname_by_id($mage_item_id, $client, $session) {
		$get_product_list = $client->call($session, 'product.list');
#print_r($get_item_name); die;
		$item_name = false;
		foreach($get_product_list as $prod_index => $product_data) {
			if($product_data['product_id'] == $mage_item_id)
				$item_name = $product_data['name'];
		}
		if($item_name)
			return $item_name;

		return false;
	}

	public static function get_wp_shipping_method($mage_shpping_method) {
		$wp_shipping_methods = array('' => '',);
		return $wp_shipping_methods[$mage_shpping_method];
	}

        public static function get_customer_address_info($customer_id, $client, $session) {
                $sub_categ_info = $client->call($session, 'customer_address.list', $customer_id);
                if($sub_categ_info)
                        return $sub_categ_info;
                else
                        return false;
        }

        public static function set_customer_with_info($customer_info, $client, $session, $process_ref_id, $mage_ref_id) {
                global $wpdb;
                $customer_created_at = $customer_info['created_at'];
                $customer_updated_at = $customer_info['updated_at'];
                $customer_email = $customer_info['email'];
                $customer_firstname = $customer_info['firstname'];
                $customer_lastname = $customer_info['lastname'];
                $customer_password = $customer_info['password_hash'];
                $get_user_login = explode('@', $customer_email);
                $user_login = $get_user_login[0];
                $display_name = $customer_firstname . ' ' . $customer_lastname;

                // User Creation
                $wp_user_details['user_login'] = $user_login;
                $wp_user_details['user_pass'] = $customer_password;
                $wp_user_details['user_nicename'] = $user_login;
                $wp_user_details['user_email'] = $customer_email;
                $wp_user_details['user_registered'] = $customer_created_at;
                $wp_user_details['display_name'] = $display_name;

                $user_id = wp_insert_user($wp_user_details); // Insert User with base deatils

                $group_id = $customer_info['group_id'];
                // Reterive Groups and their Details
                $get_group_list = $client->call($session, 'customer_group.list');
                foreach($get_group_list as $group_index => $group_data) {
                        if($group_data['customer_group_id'] == $group_id) {
                                $group_name = $group_data['customer_group_code']; // Get Group name based on their group id
                                break;
                        }
                }

                // Get group id based on the group name
                $table = $wpdb->prefix . 'groups_group';
                $sql = "select group_id from $table where name = '$group_name'";
                $get_group_id = $wpdb->get_results($sql);
                $group_id = $get_group_id[0]->group_id; // Group id
                if( !is_wp_error( $group_id ) ) {
                        $group_id = 1;
                }

                // Assign group to the respective users
                $table_user_group = $wpdb->prefix . 'groups_user_group';
		if( !is_wp_error( $user_id ) ) {
			$sql = "insert into $table_user_group (user_id, group_id) values ($user_id, $group_id);";
			$wpdb->query($sql);
			$process_time = date('Y-m-d H:i:s');
			$description = 'Customer with email-id "' . $customer_email . '" created with ID: ' . $user_id;
			$wpdb->insert("wp_smack_letme_sync", array('process_ref_id' => $process_ref_id, 'sync_module' => 'Customer', 'sync_process_time' => $process_time, 'mage_ref_id' => $mage_ref_id, 'wp_ref_id' => $user_id, 'sync_flow' => 'Magento to WordPress', 'status' => 'Success', 'description' => $description));
		} else {
			$process_time = date('Y-m-d H:i:s');
			$user_id = false;
			$description = 'Customer with email-id "' . $customer_email . '" already exists!';
			$wpdb->insert("wp_smack_letme_sync", array('process_ref_id' => $process_ref_id, 'sync_module' => 'Customer', 'sync_process_time' => $process_time, 'mage_ref_id' => $mage_ref_id, 'wp_ref_id' => $user_id, 'sync_flow' => 'Magento to WordPress', 'status' => 'Skipped', 'description' => $description));
		}

                return $user_id;
        }

        public static function set_customer_address_info($customer_id, $customer_address_info) {
                foreach ($customer_address_info as $addr_key => $addr_value) {
                        update_user_meta($customer_id, $addr_key, $addr_value);
                }
        }

}

TransferSalesorders::transferring_salesorders();
