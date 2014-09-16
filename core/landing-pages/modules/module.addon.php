<?php

function lp_addon_display()
{
	?>
	<script type='text/javascript'>
		jQuery(document).ready(function($) {

			new easyXDM.Socket({
				remote: "http://plugin.inboundnow.com/downloads/category/extensions/",  
				container: document.getElementById("lp-store-iframe-container"),
				onMessage: function(message, origin){
					var height = Number(message) + 1000;
					this.container.getElementsByTagName("iframe")[0].scrolling="no";
					this.container.getElementsByTagName("iframe")[0].style.height = height + "px";
				}
			});
		
		});
	</script>
	<div id="lp-store-iframe-container">
    </div>

<?php

}

?>