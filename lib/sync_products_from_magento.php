<?php
class TransferProducts {

	public static function transferring_products () {
                global $process_id;
                global $wpdb;
                $new_process_id = $process_id['new_process_id'];
                $wpdb->insert("wp_smack_sync_process_id", array('process_id' => $new_process_id));
                $process_ref_id = $wpdb->insert_id;

		$WPMageWooComHelperObj = new WPMageWooComHelper();
		$mage_api_credentials = $WPMageWooComHelperObj->mage_api_credentials();
#		print_r($mage_api_credentials);
		$mageURL = $mage_api_credentials['magentoURL'];
		$apiUser = $mage_api_credentials['apiusername'];
		$apiKey = $mage_api_credentials['apikey'];
		$client = new SoapClient($mageURL);

		// If somestuff requires api authentification,
		// then get a session token
		$session = $client->login($apiUser, $apiKey);

		$result = $client->call($session, 'catalog_product.list');

		// Parse the reterived product details
		foreach($result as $productIndex => $productData) {
			$product_id = $productData['product_id'];
			// Reterive Product Info
			$productDetails = self::get_product_info($product_id, $client, $session);

			// print('<pre>'); #print_r($productDetails); #print('</pre>');
			// print(date('Y-m-d H:i:s', $productDetails['created_at']));
			// Single Product Base Information
			$single_product['post_title'] = $productDetails['name'];
			$single_product['post_content'] = $productDetails['description'];
			$single_product['post_excerpt'] = $productDetails['short_description'];
			$single_product['post_status'] = 'publish';
			$single_product['post_date'] = date("Y-m-d H:i:s", time());
			$single_product['post_modified'] = date("Y-m-d H:i:s", time());
			$single_product['post_author'] = 1;
			$single_product['post_name'] = $productDetails['url_key'];
			$single_product['comment_status'] = 'open';
			$single_product['ping_status'] = 'open';
			$single_product['post_type'] = 'product';

			$wp_product_id = wp_insert_post($single_product);
			// Capturing product sync log
			$process_time = date('Y-m-d H:i:s');
			$description = 'Product "' . $productDetails['name'] . '" created with ID: ' . $wp_product_id;
			$wpdb->insert("wp_smack_letme_sync", array('process_ref_id' => $process_ref_id, 'sync_module' => 'Product', 'sync_process_time' => $process_time, 'mage_ref_id' => $product_id, 'wp_ref_id' => $wp_product_id, 'sync_flow' => 'Magento to WordPress', 'status' => 'Success', 'description' => $description));

			// Single Product Meta Information
			if ($productDetails['visibility'] == 1) {
				$visibility = 'hidden';
			} else if ($productDetails['visibility'] == 2) {
				$visibility = 'catalog';
			} else if ($productDetails['visibility'] == 3) {
				$visibility = 'search';
			} else if ($productDetails['visibility'] == 4) {
				$visibility = 'visibile';
			} else {
				$visibility = 'visibile';
			}
			$single_product_meta['_sku'] = $productDetails['sku'];
			$single_product_meta['_visibility'] = $visibility;
			$single_product_meta['_manage_stock'] = 'no';
#			$single_product_meta['_stock'] = ;
			$single_product_meta['_stock_status'] = 'instock';
#			$single_product_meta['_downloadable'] = ;
#			$single_product_meta['_virtual'] = '';
			$single_product_meta['_regular_price'] = $productDetails['price'];
			$single_product_meta['_sale_price'] = $productDetails['special_price'];
			$single_product_meta['_price'] = $productDetails['special_price'];
			$single_product_meta['_sale_price_dates_from'] = $productDetails['special_from_date'];
			$single_product_meta['_sale_price_dates_to'] = $productDetails['special_to_date'];
			if ($productDetails['tax_class_id'] == 1) {
				$tax_status = 'none';
			} else if ($productDetails['tax_class_id'] == 2) {
				$tax_status = 'taxable';
			} else if ($productDetails['tax_class_id'] == 3) {
				$tax_status = 'shipping';
			} else {
				$tax_status = 'none';
			}
			$single_product_meta['_tax_status'] = $tax_status;
			$single_product_meta['_tax_class'] = '';
			$single_product_meta['_purchase_note'] = '';
			$single_product_meta['_featured'] = '';
			$single_product_meta['_weight'] = $productDetails['weight'];
			$single_product_meta['_length'] = 0;
			$single_product_meta['_width'] = 0;
			$single_product_meta['_height'] = 0;
			$single_product_meta['_backorders'] = '';
			$single_product_meta['_file_paths'] = '';
			$single_product_meta['_download_limit'] = '';
			$single_product_meta['_download_expiry'] = '';
			$single_product_meta['_product_url'] = $productDetails['url_path'];
			$single_product_meta['_button_text'] = '';
			$product_type = 'simple';
			wp_set_object_terms($wp_product_id, $product_type, 'product_type');
			if (!empty ($single_product_meta)) {
				foreach ($single_product_meta as $meta_key => $meta_value) {
					update_post_meta($wp_product_id, $meta_key, $meta_value);
				}
			}
			echo 'Success: Product "' . $single_product['post_title'] . '" created with ID: ' . $wp_product_id . '. <br>';
			/*                                        $product_type = 'simple';
								  if ($new_post[$ckey] == 1) {
								  $product_type = 'simple';
								  }
								  if ($new_post[$ckey] == 2) {
								  $product_type = 'grouped';
								  }
								  if ($new_post[$ckey] == 3) {
								  $product_type = 'external';
								  }
								  if ($new_post[$ckey] == 4) {
								  $product_type = 'variable';
								  }
								  wp_set_object_terms($post_id, $product_type, 'product_type'); 
								  $single_product_meta['_product_shipping_class'] = ;
								  $single_product_meta['_sold_individually'] = ;
								  $single_product_meta['_crosssell_ids'] = ;
								  $single_product_meta['_upsell_ids'] = ;

								  print_r($single_product);
								  print_r($single_product_meta);
								  print('</pre>');
								  die; */
			// Insert the Parent Category into WordPress
			//self::set_product_with_info($single_product, $client, $session);
		}

		// If you don't need the session anymore
		//$client->endSession($session); */
	}

	public static function get_product_info($product_id, $client, $session) {
		$product_info = $client->call($session, 'catalog_product.info', $product_id);
		return $product_info;
	}

}

TransferProducts::transferring_products();
