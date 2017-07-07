<?php
class TransferCategories {
	public static function transferring_categories () {
		global $wpdb;

		$WPMageWooComHelperObj = new WPMageWooComHelper();
		$mage_api_credentials = $WPMageWooComHelperObj->mage_api_credentials();
		$mageURL = $mage_api_credentials['magentoURL'];
		$apiUser = $mage_api_credentials['apiusername'];
		$apiKey = $mage_api_credentials['apikey'];
		$client = new SoapClient($mageURL);

		// If somestuff requires api authentification,
		// then get a session token
		$session = $client->login($apiUser, $apiKey);

		$result = $client->call($session, 'catalog_category.tree', 979, 1); 

		self::loop_categories($client,$session, $result['children']);
		
	}
	
	public static function loop_categories($client, $session, $array, $parent = 0){
		global $wpdb;
		foreach($array as $cat) {
			$categ_id = $cat['category_id'];
			$categ_name = $cat['name'];
			$detail_info = $client->call($session, 'catalog_category.info', $categ_id);
			$categ_slug = $detail_info['url_key'];
			$categ_desc = $detail_info['description'];
			
			$woo_cat_id = wp_insert_term($categ_name, "product_cat", array('description' => $categ_desc, 'slug' => $categ_slug, 'parent' => $parent));		
			add_woocommerce_term_meta($woo_cat_id['term_id'], 'display_type', 'products');
			$date = date('Y-m-d H:i:s');
			$ref = time().rand(0,100);
			$wpdb->insert("wp_smack_letme_sync", array(
				'process_ref_id' => $ref,
				'sync_module' => 'Kategori',
				'sync_process_time' => $date, 
				'mage_ref_id' => $categ_id, 
				'wp_ref_id' => $woo_cat_id['term_id'], 
				'sync_flow' => 'Magento to WordPress', 
				'status' => 'Success', 
				'description' => "by www.trkodlama.com | Oral UNAL | oralunal")
			);
			
			if(count($cat['children'])>0) self::loop_categories($client, $session, $cat['children'], $woo_cat_id['term_id']);
		}
	}
}

TransferCategories::transferring_categories();
