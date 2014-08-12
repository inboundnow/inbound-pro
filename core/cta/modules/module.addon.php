<?php

function wp_cta_addon_display()
{
	?>
	<script type='text/javascript'>
		jQuery(document).ready(function($) {

			new easyXDM.Socket({
				remote: "http://plugin.inboundnow.com/downloads/category/extensions/",  
				container: document.getElementById("wp-cta-store-iframe-container"),
				onMessage: function(message, origin){
					var height = Number(message) + 1000;
					this.container.getElementsByTagName("iframe")[0].scrolling="no";
					this.container.getElementsByTagName("iframe")[0].style.height = height + "px";
				}
			});
		
		});
	</script>
	<div id="wp-cta-store-iframe-container">
    </div>

<?php }