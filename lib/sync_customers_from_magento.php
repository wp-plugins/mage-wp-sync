<?php
class TransferCustomers {

	public static function transferring_customers () {
                global $process_id;
                global $wpdb;
                $new_process_id = $process_id['new_process_id'];
                $wpdb->insert("wp_smack_sync_process_id", array('process_id' => $new_process_id));
                $process_ref_id = $wpdb->insert_id;

		$WPMageWooComHelperObj = new WPMageWooComHelper();
		$mage_api_credentials = $WPMageWooComHelperObj->mage_api_credentials();
		$mageURL = $mage_api_credentials['magentoURL'];
		$apiUser = $mage_api_credentials['apiusername'];
		$apiKey = $mage_api_credentials['apikey'];
		$client = new SoapClient($mageURL);

		// If somestuff requires api authentification,
		// then get a session token
		$session = $client->login($apiUser, $apiKey);

		$result = $client->call($session, 'customer.list');

		// Parse the reterived customer details
		foreach($result as $customerKey => $customerData) {
			$customer_id = $customerData['customer_id'];
			$customer_email = $customerData['email'];

			// Insert customer informations into WordPress
			$wp_customer_id = self::set_customer_with_info($customerData, $client, $session, $process_ref_id, $customer_id);
			// Set Role to the respective users
			$user_role = array('customer' => 1);
			update_user_meta($wp_customer_id, 'wp_capabilities', $user_role);
			// Reterive customer address info
			try{
				$customer_address_details = array();
				$customer_address_details = self::get_customer_address_info($customer_id, $client, $session);
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
							$billing_details['billing_email'] = $customer_email;
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
				if($wp_customer_id)
					echo "<b> Success: </b> User created with email id - $customer_email and user id - $wp_customer_id . <br>";
				else
					echo "User with email id - $customer_email already exists! <br>";
			} catch (Exception $e) {
				echo "Address information not found for the user with Email:" . $customerData['email'] . "<br>";
			}
		}

		// If you don't need the session anymore
		//$client->endSession($session); */
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

		// Assign group to the respective users
                $table_user_group = $wpdb->prefix . 'groups_user_group';
		if( !is_wp_error( $group_id ) ) {
			$group_id = 1;
		}
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

TransferCustomers::transferring_customers();
