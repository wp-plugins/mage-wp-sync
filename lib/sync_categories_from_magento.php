<?php
class TransferCategories {

	public static function transferring_categories () {
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

		$result = $client->call($session, 'catalog_category.tree');

		// Parse the reterived category details
		foreach($result['children'] as $categKey => $categData) {
			$categ_id = $categData['category_id'];
			// Reterive Parent Category Info
			$categoryDetails = self::get_category_info($categ_id, $client, $session);
			$parent_id = $categoryDetails['parent_id'];
			$all_childrens = explode(',', $categoryDetails['all_children']);
			// Parent Category Info 
			$categ_name = $categoryDetails['name'];
			$categ_slug = $categoryDetails['url_key'];
			$categ_desc = $categoryDetails['description'];
			// Insert the Parent Category into WordPress
			self::set_category_with_info($parent_id, $categ_name, $categ_slug, $categ_desc, $client, $session, $process_ref_id, $categ_id);
			foreach($all_childrens as $childcatID) {
				if($childcatID != $categ_id) {
					// Reterive Child Category Info
					$child_categoryDetails = self::get_category_info($childcatID, $client, $session);
					$child_parent_id = $child_categoryDetails['parent_id'];
					$child_categ_name = $child_categoryDetails['name'];
					$child_categ_slug = $child_categoryDetails['url_key'];
					$child_categ_desc = $child_categoryDetails['description'];
					// Insert the Child Category into WordPress
					self::set_category_with_info($child_parent_id, $child_categ_name, $child_categ_slug, $child_categ_desc, $client, $session, $process_ref_id, $childcatID);
				}
			}
		}

		// If you don't need the session anymore
		//$client->endSession($session); */
	}

	public static function get_category_info($categ_id, $client, $session) {
		$sub_categ_info = $client->call($session, 'catalog_category.info', $categ_id);
		return $sub_categ_info;
	}

	public static function set_category_with_info($parent_id, $categ_name, $categ_slug, $categ_desc, $client, $session, $process_ref_id, $mage_ref_id) {
		global $wpdb;
		$taxonomy = 'product_cat';
		if($parent_id == 1) {
			$parent_id = 0;
			$inserted_term_id = wp_insert_term("$categ_name", "$taxonomy", array('description' => $categ_desc, 'slug' => $categ_slug, 'parent' => $parent_id));
			if( !is_wp_error ($inserted_term_id) )
				$termID = $inserted_term_id['term_id'];
			else
				$termID = false;
		} else {
			$reterive_parent_categ_info = $client->call($session, 'catalog_category.info', "$parent_id");
			$WP_Parent_Categ_Name = $reterive_parent_categ_info['name'];
			$WP_Parent_Category_Details = get_term_by('name', $WP_Parent_Categ_Name, $taxonomy);
			if($WP_Parent_Category_Details)
				$parent_id = $WP_Parent_Category_Details->term_id;
			else
				$parent_id = 0;
			$inserted_term_id = wp_insert_term("$categ_name", "$taxonomy", array('description' => $categ_desc, 'slug' => $categ_slug, 'parent' => $parent_id));
#print_r($inserted_term_id); die;
			if( !is_wp_error ($inserted_term_id) )
				$termID = $inserted_term_id['term_id'];
			else
				$termID = false;
		}
		if($termID) {
			add_woocommerce_term_meta($termID, 'display_type', 'products');
			$process_time = date('Y-m-d H:i:s');
			$description = 'Product Category "' . $categ_name . '" created with ID: ' . $termID;
			$wpdb->insert("wp_smack_letme_sync", array('process_ref_id' => $process_ref_id, 'sync_module' => 'Category', 'sync_process_time' => $process_time, 'mage_ref_id' => $mage_ref_id, 'wp_ref_id' => $termID, 'sync_flow' => 'Magento to WordPress', 'status' => 'Success', 'description' => $description));
			echo 'Success: Product Category "' . $categ_name . '" created with ID: ' . $termID . '. <br>';
		} else {
			$process_time = date('Y-m-d H:i:s');
                        $description = "Product Category doesn't sync successfully. It may have duplicate details.";
                        $wpdb->insert("wp_smack_letme_sync", array('process_ref_id' => $process_ref_id, 'sync_module' => 'Category', 'sync_process_time' => $process_time, 'mage_ref_id' => $mage_ref_id, 'wp_ref_id' => $termID, 'sync_flow' => 'Magento to WordPress', 'status' => 'Skipped', 'description' => $description));
			echo "Failure: Product Category doesn't sync successfully. It may have duplicate details. <br>";
		}
	}
}

TransferCategories::transferring_categories();
