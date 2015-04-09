<?php
/******************************************************************************************
 * Copyright (C) Smackcoders 2014 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

class WPMageWooComHelper {

	public $mage_api_credentials = array();

        public function __construct() {

        }

	/**
	 * Function to reterive Magento settings
	 */
	public static function mage_api_credentials() {
                $get_mage_woocom_settings = get_option('smack_magewoocom_config');
                if(is_array($get_mage_woocom_settings)) {
                        $mage_api_credentials['magentoURL'] = $get_mage_woocom_settings['magentoURL'];
                        $mage_api_credentials['apiusername'] = $get_mage_woocom_settings['apiusername'];
                        $mage_api_credentials['apikey'] = $get_mage_woocom_settings['apikey'];
                }
		return $mage_api_credentials;
	}

        /**
         * Function to show template view based on the selected menu
         *
         */
        public static function output_fd_page() {
		WPMageWooComHelper::renderMenu();
		WPMageWooComHelper::renderModule();
        }

	public static function renderMenu() {
		$synclogs = $module = $settings = '';
		if(isset($_REQUEST['__module']) && $_REQUEST['__module'] == 'synclogs') {
			$synclogs = 'active';
			$module = 'inactive';
			$settings = '';
		} else if(isset($_REQUEST['__module']) && $_REQUEST['__module'] == 'syncdata') {
                        $synclogs = 'inactive';
                        $module = 'active';
                        $settings = 'inactive';
                } else if(isset($_REQUEST['__module']) && $_REQUEST['__module'] == 'settings') {
                        $synclogs = 'inactive';
                        $module = 'inactive';
                        $settings = 'active';
                } else if(!isset($_REQUEST['__module']))
                {
			$synclogs = 'inactive';
	                $module = 'inactive';
        	        $settings = 'active';
                }
		$menuHTML = "<nav class='' role='navigation' style='width: 98.5%;margin-top: 20px;'>
			<div>
			<ul class='nav nav-tabs'>";
/*		$menuHTML .=" <li role='presentation' class='{$dashboard}' ><a href='admin.php?page=" . WP_CONST_MAGE_WOOCOM_SLUG . "/index.php&__module=dashboard'> Dashboard </a></li>";
		$menuHTML .=" <li role='presentation' class='{$module} '><a href='admin.php?page=" . WP_CONST_MAGE_WOOCOM_SLUG . "/index.php&__module=syncdata'> Sync Data </a></li>";
		$menuHTML .= "<li role='presentation' class='{$settings}'><a href='admin.php?page=" . WP_CONST_MAGE_WOOCOM_SLUG . "/index.php&__module=settings'/> Settings </a></li> */
                $menuHTML .= "<div class='btn-group'><a href='admin.php?page=" . WP_CONST_MAGE_WOOCOM_SLUG . "/index.php&__module=settings'/><li role='presentation' class='{$settings} btn btn-nav'><i class='fa fa-cog'></i><p> Settings</p></li> </a></div>";
                $menuHTML .="<div class='btn-group'><a href='admin.php?page=" . WP_CONST_MAGE_WOOCOM_SLUG . "/index.php&__module=syncdata'><li role='presentation' class='{$module} btn btn-nav '><i class='fa fa-refresh '></i><p>Sync Data</p></li></a></div>";
		$menuHTML .="<div class='btn-group'><a href='admin.php?page=" . WP_CONST_MAGE_WOOCOM_SLUG . "/index.php&__module=synclogs'><li role='presentation' class='{$synclogs} btn btn-nav' ><i class='fa fa-history'></i><p>Sync Logs</p></li></a></div>";
		$menuHTML .= "</ul>";
		$menuHTML .= "</div>";
		$menuHTML .= "<div class='msg' id = 'showMsg' style = 'display:none;'></div>";
		$menuHTML .= "</nav>";

		echo $menuHTML;
	}

	public static function smack_magewoocom_activate() {
		global $wpdb;
		$query1 = "ALTER TABLE $wpdb->users CHANGE user_pass user_pass VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$wpdb->query($query1);
		$query2 = "CREATE TABLE IF NOT EXISTS wp_smack_sync_process_id (id int(11) NOT NULL AUTO_INCREMENT, process_id int(6) zerofill, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		$wpdb->query($query2);
		$query3 = "CREATE TABLE IF NOT EXISTS wp_smack_letme_sync (log_id int(11) NOT NULL AUTO_INCREMENT, process_ref_id int(11), sync_module varchar(60), sync_process_time datetime default '0000-00-00 00:00:00', mage_ref_id int(11), wp_ref_id int(11), sync_flow varchar(120), status varchar(60), description varchar(300), PRIMARY KEY (log_id)) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		$wpdb->query($query3);
	}

        public static function renderModule() {
		if(isset($_REQUEST['__module']) && $_REQUEST['__module'] == 'synclogs') {
			require_once(dirname(__FILE__) . '/../modules/synclogs.php');
		} else if(isset($_REQUEST['__module']) && $_REQUEST['__module'] == 'syncdata') {
                        require_once(dirname(__FILE__) . '/../modules/syncdata.php');
		} else if(isset($_REQUEST['__module']) && $_REQUEST['__module'] == 'settings') {
                        require_once(dirname(__FILE__) . '/../modules/settings.php');
		} else if(!isset($_REQUEST['__module']))
                {
			require_once(dirname(__FILE__) . '/../modules/settings.php');

                }
	}
}
