<?php
$getsmack_magewoocom_config = get_option('smack_magewoocom_config');
if(is_array($getsmack_magewoocom_config)) {
        $magentoURL = $getsmack_magewoocom_config['magentoURL'];
        $apiusername = $getsmack_magewoocom_config['apiusername'];
        $apikey = $getsmack_magewoocom_config['apikey'];
}
#$client = new SoapClient($magentoURL);

// If somestuff requires api authentification,
// then get a session token
#$session = $client->login($apiusername, $apikey);

#$result = $client->call($session, 'customer.list');
#print('<pre>'); var_dump ($result);
#echo MD5('rajkumarm');
#print('<pre>'); 
#print_r($session); 
#print('</pre>');
?>

<div class="mage-sync-settings" style="max-width:97%; padding-left:15px; margin-top: 25px;">
<div style="height:40px;background-color:#428bca;"><div style="padding-top:6px;color:#FFF;"><span class="" style="margin: -5px 5px 5px 10px;"><i class="fa fa-refresh fa-2x"></i>
<!--<img src="<?php echo WP_CONTENT_URL;?>/plugins/<?php echo WP_CONST_MAGE_WOOCOM_SLUG; ?>/images/settings.png">--></span><span>SYNC : Magento - WooCommerce</span></div></div>
<div class="sync-content" style="border:1px solid #428bca;">
<div class="container" style="width:78%;margin:27px 114px;padding:0px">
    <div class="">
        <div class="col-md-6" style="width:100%;padding:0px;">
            <div class="panel panel-primary">
<!--                <div class="panel-heading">
                    <div class="panel-title">
                        <span class="glyphicon glyphicon-bookmark"></span> Quick Shortcuts</div>
                </div>-->
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6 col-md-6" style="padding-left:38px;">
                          <div  class="btn btn-danger btn-lg" role="button" name='sync_categories_from_magento' id='sync_categories_from_magento' onclick="proceedsync(this.id);" value="SYNC" ><i class="fa fa-tags"></i> <br/>Categories</div>
                          <div class="btn btn-warning btn-lg" role="button" name='sync_customers_from_magento' id='sync_customers_from_magento' onclick="proceedsync(this.id);" value="SYNC" style="float:right;"><i class="fa fa-user"></i> <br/>Customers</div>
                        </div>
                        <div class="col-xs-6 col-md-6" style="padding-left:38px;">
                          <div class="btn btn-success btn-lg" role="button" name='sync_products_from_magento' id='sync_products_from_magento' onclick="proceedsync(this.id);" value="SYNC" ><i class="fa fa-shopping-cart"></i> <br/>Products</div>
                          <div class="btn btn-danger btn-lg" role="button" name='sync_salesorders_from_magento' id='sync_salesorders_from_magento' onclick="proceedsync(this.id);" value="SYNC"><i class="fa fa-money"></i> <br/>Salesorders</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<div id='syncprocessing' style='display:none;' align='center'> <img src="<?php echo WP_CONTENT_URL;?>/plugins/<?php echo WP_CONST_MAGE_WOOCOM_SLUG; ?>/images/loading.GIF" height='30' width='30'/> Sync in Process... </div>
    </div>
</div>
<!-- Log Section -->
<div style="margin-top:40px;width:78.1%;margin-left:112px">
<div style="height:300px;margin-bottom:10px; border:1px solid #428bca;background-color:#FFF;padding:10px;" >
<div class="panel-heading" style="background-color:#428bca;color:#fff;border-radius:8px;border:none;">
                <div class="panel-title">
                        <i class="fa fa-history"></i><span style="margin-left:5px;">Logs</span></div>
</div>
<div name="sync_log" id="sync_log" rows=30  readonly="readonly" style="margin-top:20px;margin-bottom:10px;resize: none;overflow-y: auto;padding:10px;height:215px;">
</div>

</div>
</div>

</div>
</div>
