<?php
if(isset($_POST['save_config'])) {
	$set_magewoocom_config['magentoURL'] = sanitize_text_field( $_POST['magentoURL'] );
	$set_magewoocom_config['apiusername'] = sanitize_text_field( $_POST['apiusername'] );
	$set_magewoocom_config['apikey'] = sanitize_text_field( $_POST['apikey'] );
	update_option('smack_magewoocom_config', $set_magewoocom_config);
}
$magentoURL = $apiusername = $apikey = '';
$getsmack_magewoocom_config = get_option('smack_magewoocom_config');
if(is_array($getsmack_magewoocom_config)) {
	$magentoURL = $getsmack_magewoocom_config['magentoURL'];
	$apiusername = $getsmack_magewoocom_config['apiusername'];
	$apikey = $getsmack_magewoocom_config['apikey'];
}
?>

<div class="settings" style="max-width:97%; padding-left:15px; margin-top: 25px;">
<div style="height:40px;background-color:#428bca;"><div style="padding:6px 0px;color:#FFF;"><span class="" style="margin: -5px 5px 5px 10px;"><i class="fa fa-refresh fa-2x"></i></span><span>Settings : Magento - WooCommerce</span></div>
<div class="container-fluid">
  <div class="row" style="border: 1px solid #428bca;padding:20px;margin:0px;background-color:#FFF;color:#428bca;">
<form method="post" action="" name="magento_configuration">
  <div class="form-group col-xs-12">
    <label for="magentoURL">Magento URL</label>
    <input type="url" class="form-control" id="magentoURL" name="magentoURL" value="<?php echo esc_url( $magentoURL ); ?>" placeholder="Enter your Magento URL" required style="border: 1px solid #999;">
  </div>
  <div class="form-group col-xs-6">
    <label for="apiusername">API User Name</label>
    <input type="text" class="form-control" id="apiusername" name="apiusername" value="<?php echo $apiusername; ?>" placeholder="Enter API User Name" required style="border: 1px solid #999;">
  </div>
  <div class="form-group col-xs-6">
    <label for="apikey">API Key</label>
    <input type="text" class="form-control" id="apikey" name="apikey" value="<?php echo $apikey; ?>" placeholder="Enter API Key" required style="border: 1px solid #999;">
  </div>
  <button type="submit" class="btn btn-sync" name="save_config" style="float:right;margin-right:15px;margin-top:10px;">Submit</button>
</form>
  </div>
</div>
</div>

<div style='width:98%; margin-top: 25%;' align='center'> <div class='alert'>
<p><strong>Warning!</strong> You should proceed the following order while sync the data to avoid such dependency conflicts. </p>
<p>1. Customers, 2. Categories, 3. Products, 4. SalesOrders </p>
</div></div>
