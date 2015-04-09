function proceedsync (id, value) {
	document.getElementById('syncprocessing').style.display = '';
        jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                        'action': 'transferdata_from_magento',
                        'option': id,
                        'value': value,
                },
                success: function (response) {
			document.getElementById('syncprocessing').style.display = 'none';
			document.getElementById('sync_log').innerHTML = response;
                }
        });
}

jQuery(document).ready(function() {
    jQuery('#mage-sync-logtab').dataTable();
});


