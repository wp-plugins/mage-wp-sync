=== Magento Wordpress Sync ===
Contributors: smackcoders
Donate link: http://www.smackcoders.com/donate.html
Tags: magento, magentointegration, woocommerce, ecommerce, sync, data, admin, store, customers, products, sales, orders, automate, migration, fields, customfields, commerce 
Requires at least: 4
Tested up to: 4.1.1
Stable tag: 1.0	
Version: 1.0
Author: smackcoders
Author URI: http://profiles.wordpress.org/smackcoders/

License: GPLv2 or later

Magento Wordpress Synchronization plugin to sync all essential magento store data.

== Description ==
Magento Wordpress Synchronization plugin to sync all essential magento store data.

What to sync from magento using Magento Wordpress plugin?

1. Categories - Can import all categories with parental hierarchy, name, slug, description, parent details.
2. Customers - import all customers and their shipping, billing details
3. Sales Orders - import all Sales orders along with following order status Processing, Pending Payment, On Hold, Cancelled, Failed
4. Products - import all Magento Products into WooCommerce products along with all product meta fields.

= Supported product Attributes and Meta Fields =
visibility
sku
manage_stock
stock_status
regular_price
sale_price
sale_price_dates_from
sale_price_dates_to
tax_class_id
tax_status
tax_class
purchase_note
featured
weight
length
width
height
backorders
file_paths
download_limit
download_expiry
product_url
button_text

Let us know your feedback, feature suggestion etc., here - 

== Installation ==

I. For simple general way to install

* Download the plugin (.zip file) on the right side above menu
* Click the Red Download Button ( Download Version X.X.X)
* Login to your Wordpress Admin (e.g. yourdomain.com/wp-admin/)
* Go to Plugins >> Add New
* Select the tab "Upload"
* Browse and Upload the downloaded zip file
* Activate the plugin after install
* You can see a new menu Magento Wordpress Sync in your Admin now

II. For familiar FTP users

* Download the plugin (.zip file) on the right side above menu
* Click the Red Download Button ( Download Version X.X.X)
* Extract the plugin zip
* Upload plugin zip to /wp-content/plugins/ 
* Go to Plugins >> Installed Plugins >> Inactive 	
* Click Activate to activate the plugin
* You can see a new menu Magento Wordpress Sync in your Admin now

III. Straight from Wordpress Admin

* Login to your Wordpress Admin (e.g. yourdomain.com/wp-admin/)
* Go to Plugins >> Add New
* Search for Magento Wordpress Sync
* Click Install Now to install
* Activate the plugin after install
* You can see a new menu Magento Wordpress Sync in your Admin now	


== Screenshots ==
1. Magento Wordpress Sync Settings and configuration
2. Magento Wordpress Sync Options
3. Magento Wordpress Sync logs

== Frequently Asked Questions ==
How to configure the sync settings?
In setting tab provide the Magento API URL, Api User, Api Key to configure with your magento store for transfer/sync records to your WordPress store. This can be used for complete magento to wordpress migration or periodical sync between Magento Wordpress stores.

== Changelog ==
= 1.0.0 =	
* Initial release version. Tested and found works well without any issues.

== Upgrade Notice ==
= 1.0.0 =	
* Initial stable release version
