<?php
global $wpdb;
$synclog = $wpdb->get_results("select * from wp_smack_letme_sync");
$syncid = $wpdb->get_col("select log_id from wp_smack_letme_sync");
$syncmodule = $wpdb->get_col("select sync_module from wp_smack_letme_sync");
$log_table ="<div class='mage-sync-logview' style='margin-top:32px;width:98%;'>
<table id='mage-sync-logtab' class='display' cellspacing='0' width='100%'>
    <thead>
        <tr>
            <th>ID</th>
	    <th>Module</th>
	    <th>Process time</th>
	    <th style='width:74px;'>Magento ID</th>
            <th style='width:85px;'>WordPress ID</th>
            <th>Flow</th>
	    <th>Status</th>
            <th>Description</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th>ID</th>
	    <th>Module</th>
	    <th style='width: 81px;'>Process time</th>
	    <th style='width:74px;'>Magento ID</th>
	    <th style='width:85px;'>WordPress ID</th>
            <th>Flow</th>
	    <th>Status</th>
            <th>Description</th>
        </tr>
    </tfoot>
    <tbody>
        <tr>";
$i = 0;
foreach($synclog as $idkey) {
foreach($idkey as $idcontent => $iddata) {
switch ($idcontent) {
    case "log_id":
         $log_table .= "<td>" .$iddata."</td>";
        break;
    case "sync_module":
         $log_table .= "<td>" .$iddata."</td>";
        break;
    case "sync_process_time":
         $log_table .= "<td>" .$iddata."</td>";
        break;
    case "mage_ref_id":
	$log_table .= "<td>" .$iddata."</td>";
        break;
    case "wp_ref_id":
        $log_table .= "<td>" .$iddata."</td>";
        break;
    case "sync_flow":
         $log_table .= "<td>" .$iddata."</td>";
        break;
    case "status":
         $log_table .= "<td>" .$iddata."</td>";
        break;
    case "description":
         $log_table .= "<td>" .$iddata."</td>";
        break;
}}
$i++;
$log_table .="</tr>";
}
$log_table .="</tbody></table>";
echo "$log_table";
?>
