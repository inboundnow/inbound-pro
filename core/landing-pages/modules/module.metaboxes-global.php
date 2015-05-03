<?php

/* add meta boxes to posts, pages, and non excluded cpts */
add_action('add_meta_boxes', 'lp_add_global_meta_box' , 10 );
function lp_add_global_meta_box( $post_type )
{
	global $pagenow;

	$exclude[] = 'attachment';
	$exclude[] = 'revisions';
	$exclude[] = 'nav_menu_item';
	$exclude[] = 'wp-lead';
	$exclude[] = 'automation';
	$exclude[] = 'rule';
	$exclude[] = 'list';
	$exclude[] = 'wp-call-to-action';
	$exclude[] = 'tracking-event';
	$exclude[] = 'inbound-forms';
	$exclude[] = 'email-template';
	$exclude[] = 'inbound-email';
	$exclude[] = 'inbound-log';
	$exclude[] = 'landing-page';
	$exclude[] = 'acf-field-group';
	// add filter

	if ( $pagenow === 'post.php' && !in_array($post_type,$exclude) ) {
		add_meta_box( 'lp-post-statistics', __( 'Inbound Statistics' , 'landing-pages' ) , 'lp_global_statistics_meta_box' , $post_type, 'side', 'high');
	}

}

function lp_global_statistics_meta_box() {

	global $post;
	
	?>
	<div>
		<script >
		jQuery(document).ready(function($) { 	
			jQuery( 'body' ).on( 'click', '.lp-delete-var-stats', function() {
				var post_id = jQuery(this).attr("rel");
			
				//console.log(selector);
				if (confirm('Are you sure you want to delete stats for this post?')) {	  
					jQuery.ajax({
						  type: 'POST',
						  url: ajaxurl,
						  context: this,
						  data: {
							action: 'lp_clear_stats_post',
							post_id: post_id
						  },				  
						success: function(data){
							jQuery(".bab-stat-span-impressions").text("0");
							jQuery(".bab-stat-span-conversions").text("0");
							jQuery(".bab-stat-span-conversion_rate").text("0");
							
						},

						  error: function(MLHttpRequest, textStatus, errorThrown){
							alert("Ajax not enabled");
							}
						});
						
						return false;
				} 
			});
		});
		</script>
		<div class="inside" style='margin-left:-8px;'>
			<div id="bab-stat-box">
			
			<?php
			$impressions = apply_filters('inbound_impressions' , get_post_meta($post->ID,'_inbound_impressions_count', true) );
			$conversions = apply_filters('inbound_conversions' , get_post_meta($post->ID,'_inbound_conversions_count', true) );


			(is_numeric($impressions)) ? $impressions = $impressions : $impressions = 0;
			(is_numeric($conversions)) ? $conversions = $conversions : $conversions = 0;

			if ($impressions>0) {
				$conversion_rate = $conversions / $impressions;
				(($conversions===0)) ? $sign = "" : $sign = "%";
				$conversion_rate = round($conversion_rate,2) * 100 . $sign;
			} else {
				$conversion_rate = 0;
			}
			?>
				<div id="" class="bab-variation-row" >
					<div class="bab-stat-row">
						<div class='bab-stat-stats' colspan='2'>
							<div class='bab-stat-container-impressions bab-number-box'>
								<span class='bab-stat-span-impressions'><?php echo $impressions; ?></span>
								<span class="bab-stat-id">Views</span>
							</div>
							<div class='bab-stat-container-conversions bab-number-box'>
								<span class='bab-stat-span-conversions'><?php echo $conversions; ?></span>
								<span class="bab-stat-id">Conversions</span></span>
							</div>
							<div class='bab-stat-container-conversion_rate bab-number-box'>
								<span class='bab-stat-span-conversion_rate'><?php echo $conversion_rate; ?></span>
								<span class="bab-stat-id bab-rate">Conversion Rate</span>
							</div>						
						</div>
					</div>
					<div class='bab-stat-control-container'>
						<span class="lp-delete-var-stats" rel='<?php echo $post->ID;?>' title="Delete this variations stats">Clear Stats</span>
					</div>
				</div>
			</div>

		</div>
	</div>

	<?php
}
