jQuery(document).ready(function($) {
   // http://glocal.dev/wp-admin/edit.php?page=lead_management&post_type=wp-lead&wplead_list_category%5B%5D=51&relation=AND&orderby=date&order=asc&s=&t=&submit=Search+Leads
   function getParameterByName(url, name) {
       name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
       var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
           results = regex.exec(url);
       return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
   }
   var current_url = window.location.href.split("?");
   console.log(current_url[0]);
   var clean_admin = current_url[0].replace("edit-tags.php", "");
   jQuery('.edit a').each(function(){
   		var url = jQuery(this).attr('href');
   		var id_check = /[?&]tag_ID=([^&]+)/i;
   		var match = id_check.exec(url);
   		if (match != null) {
   		    final_id = match[1];
   		} else {
   		    final_id = "";
   		}
   		console.log(final_id);
   		var replacement_base = clean_admin + "edit.php?page=lead_management&post_type=wp-lead&wplead_list_category%5B%5D=" + final_id + "&relation=AND&orderby=date&order=asc&s=&t=&submit=Search+Leads";
   		var main_link = jQuery(this).parent().parent().parent().find(".row-title");
   		var main_text = main_link.text();
   		main_link.attr('href', replacement_base);
   		main_link.attr('title', "View Leads in " + main_text);
   		jQuery(this).parent().parent().find(".view a").attr('href', replacement_base).attr('title', "View Leads in " + main_text);

   		jQuery(".tag-link-" + final_id).attr('href', replacement_base).attr('title', "View Leads in " + main_text);
   });

 });
