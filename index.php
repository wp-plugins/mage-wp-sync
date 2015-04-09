<?php
/******************************************************************************************
 *
 * Plugin Name: Mage WP Sync
 * Description: A plugin that helps to sync the data's from your magento.
 * Version: 1.0
 * Author: smackcoders.com
 * Plugin URI: https://www.smackcoders.com
 * Author URI: https://www.smackcoders.com
 *
 * Copyright (C) Smackcoders 2014 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

define('WP_CONST_MAGE_WOOCOM_URL', 'http://www.smackcoders.com');
define('WP_CONST_MAGE_WOOCOM_NAME', 'Mage WP Sync');
define('WP_CONST_MAGE_WOOCOM_SLUG', 'mage-wp-sync');
define('WP_CONST_MAGE_WOOCOM_SETTINGS', 'Magento Wordpress Sync');

define('WP_CONST_MAGE_WOOCOM_DIR', WP_PLUGIN_URL . '/' . WP_CONST_MAGE_WOOCOM_SLUG . '/');
define('WP_CONST_MAGE_WOOCOM_DIRECTORY', plugin_dir_path(__FILE__));
define('WP_MAGE_WOOCOM_PLUGIN_BASE', WP_CONST_MAGE_WOOCOM_DIRECTORY);

require_once('includes/WPMageWooComHelper.php');
require_once('includes/TransferData.php');

register_activation_hook(__FILE__, array('WPMageWooComHelper', 'smack_magewoocom_activate'));

function action_magewoocom_admin_menu() {
	add_menu_page(WP_CONST_MAGE_WOOCOM_SETTINGS, WP_CONST_MAGE_WOOCOM_NAME, 'manage_options', __FILE__, array('WPMageWooComHelper', 'output_fd_page'), WP_CONST_MAGE_WOOCOM_DIR . "images/sync.png");
}
add_action("admin_menu" , "action_magewoocom_admin_menu") ;

function action_magewoocomsync_admin_init() {
	if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'mage-wp-sync/index.php' || $_REQUEST['page'] == 'page')) {
		wp_enqueue_style('magewoocom-bootstrap-css', plugins_url('css/bootstrap.css', __FILE__));
		wp_enqueue_style('magewoocom-style-css', plugins_url('css/style.css', __FILE__));
		wp_enqueue_style('magewoocom-font-awesome-min-css', plugins_url('css/font-awesome.min.css', __FILE__));
		wp_enqueue_style('magewoocom-jquery-dataTables-css', plugins_url('css/jquery.dataTables.css', __FILE__));
		wp_enqueue_style('magewoocom-jquery-dataTables-min-css', plugins_url('css/jquery.dataTables.min.css', __FILE__));
		wp_enqueue_script('letme_sync_js', plugins_url('js/letmesync.js', __FILE__)); 
		wp_enqueue_script('jquery_js', plugins_url('js/jquery.js', __FILE__)); 
		wp_enqueue_script('jquery_dataTables_js', plugins_url('js/jquery.dataTables.js', __FILE__)); 
	}
}
add_action('admin_init', 'action_magewoocomsync_admin_init');
?>
