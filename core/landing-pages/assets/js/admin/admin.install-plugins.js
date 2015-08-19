jQuery(document).ready(function($) {
	/* Loads on /themes.php?page=install-inbound-plugins */
	var install_status = jQuery("#the-list td.status.column-status").text();
	var click_apply = "<h2 class='click-to-activate'><span>←</span>Click Apply to Install</h2>";
	var activate_apply = "<h2 class='click-to-activate-bulk'><span>←</span>Click Apply to Bulk Activate Plugins</h2>";
	jQuery(".alignleft.actions.bulkactions").after(click_apply);
	jQuery(".alignleft.actions.bulkactions").after(activate_apply);
	console.log(install_status);
	jQuery('#the-list td.status.column-status').each(function(){
		var installed_on = $(this).text();
		if (installed_on === "Not Installed") {
			$(this).parent().find("input[type=checkbox]").attr("checked", "on");
			if ( $(".click-to-activate-bulk").is(":hidden") ) {
			jQuery('.click-to-activate').show();
			jQuery(".alignleft.actions.bulkactions select").val('tgmpa-bulk-install');
			}

		}
		if (installed_on === "Installed But Not Activated") {
			$(this).parent().find("input[type=checkbox]").attr("checked", "on");
			if ( $(".click-to-activate").is(":hidden") ) {
			jQuery('.click-to-activate-bulk').show();
			jQuery(".alignleft.actions.bulkactions select").val('tgmpa-bulk-activate');
			}

		}
	});
	jQuery("#cb-select-all-1, #cb-select-all-2").attr("checked", "on");

 });
