<?php
/* Add Stats metabox on right */
add_action('add_meta_boxes', 'lp_ab_display_stats_metabox');
function lp_ab_display_stats_metabox() {

		add_meta_box(
		'lp_ab_display_stats_metabox',
		__( 'A/B Testing', 'landing-pages'),
		'lp_ab_stats_metabox',
		'landing-page' ,
		'side',
		'high' );
}

function lp_ab_stats_metabox() {
	global $post;
	$variations = get_post_meta($post->ID,'lp-ab-variations', true);
	$variations = explode(',',$variations);
	$variations = array_filter($variations,'is_numeric');
	?>
	<div>
		<style type="text/css">

		</style>
		<div class="inside" id="a-b-testing">
			<div id="bab-stat-box">
			<?php if (isset($_GET['new_meta_key'])) { ?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
			   // This fixes meta data saves for cloned pages
			   function isNumber (o) {
				  return ! isNaN (o-0) && o !== null && o !== "" && o !== false;
				}
			   var new_meta_key = "<?php echo $_GET['new_meta_key'];?>";
			     jQuery('#template-display-options input[type=text], #template-display-options select, #template-display-options input[type=radio], #template-display-options textarea').each(function(){
			        var this_id = jQuery(this).attr("id");
			        var final_number = this_id.match(/[^-]+$/g);
			        var new_id = this_id.replace(/[^-]+$/g, new_meta_key);
			        var is_number = isNumber(final_number);
			        console.log(final_number);
			        console.log(is_number);
			        if (is_number === false) {
			        	jQuery(this).attr("id", this_id + "-" + new_meta_key);
			        	jQuery(this).attr("name", this_id + "-" + new_meta_key);
			        } else {
				        jQuery(this).attr("id", new_id);
				        jQuery(this).attr("name", new_id);
			    	}
			    });
			 });
			</script>
			<?php }	?>
				<?php
				$howmany = count($variations);
				foreach ($variations as $key=>$vid)
				{
					if (!is_numeric($vid)&&$key==0) {
						$vid = 0;
					}
					
					$variation_status = lp_ab_get_lp_active_status($post,$vid);
					$variation_status_class = ($variation_status ==1) ? "variation-on" : 'variation-off';

					$permalink = get_permalink($post->ID);
					if (strstr($permalink,'?lp-variation-id'))
					{
						$permalink = explode('?',$permalink);
						$permalink = $permalink[0];
					}
					$permalink = $permalink."?lp-variation-id=".$vid;

					$impressions = get_post_meta($post->ID,'lp-ab-variation-impressions-'.$vid, true);
					$conversions = get_post_meta($post->ID,'lp-ab-variation-conversions-'.$vid, true);


					(is_numeric($impressions)) ? $impressions = $impressions : $impressions = 0;
					(is_numeric($conversions)) ? $conversions = $conversions : $conversions = 0;

					if ($impressions>0)
					{
						$conversion_rate = $conversions / $impressions;
						(($conversions===0)) ? $sign = "" : $sign = "%";
						$conversion_rate = round($conversion_rate,2) * 100 . $sign;
					}
					else
					{
						$conversion_rate = 0;
					}

					if ($key==0)
					{
						$title = get_post_meta($post->ID,'lp-main-headline', true);
					}
					else
					{
						$title = get_post_meta($post->ID,'lp-main-headline-'.$vid, true);
					}

					//determine letter from key
					?>

					<div id="lp-variation-<?php echo lp_ab_key_to_letter($key); ?>" class="bab-variation-row <?php echo $variation_status_class;?>" >
						<div class='bab-varation-header'>
								<span class='bab-variation-name'><?php _e('Variation', 'landing-pages'); ?> <span class='bab-stat-letter'><?php _e(lp_ab_key_to_letter($key), 'landing-pages'); ?></span>
								<?php
								if($variation_status!=1)
								{
								?>
									<span class='is-paused'>(<?php _e('Paused', 'landing-pages') ?>)</span>
								<?php
								}
								?>
								</span>


								<span class="lp-delete-var-stats" data-letter='<?php echo lp_ab_key_to_letter($key); ?>' data-vid='<?php echo $vid; ?>' rel='<?php echo $post->ID;?>' title="<?php _e('Delete this variations stats' , 'landing-pages'); ?>"><?php _e('Clear Stats' , 'landing-pages'); ?></span>
							</div>
						<div class="bab-stat-row">
							<div class='bab-stat-stats' colspan='2'>
								<div class='bab-stat-container-impressions bab-number-box'>
									<span class='bab-stat-span-impressions'><?php echo $impressions; ?></span>
									<span class="bab-stat-id"><?php _e( 'Views' , 'landing-pages'); ?> </span>
								</div>
								<div class='bab-stat-container-conversions bab-number-box'>
									<span class='bab-stat-span-conversions'><?php echo $conversions; ?></span>
									<span class="bab-stat-id"><?php _e('Conversions' , 'landing-pages'); ?></span></span>
								</div>
								<div class='bab-stat-container-conversion_rate bab-number-box'>
									<span class='bab-stat-span-conversion_rate'><?php echo $conversion_rate; ?></span>
									<span class="bab-stat-id bab-rate"><?php _e('Conversion Rate' , 'landing-pages'); ?></span>
								</div>
								<div class='bab-stat-control-container'>
									<span class='bab-stat-control-pause'><a title="<?php _e('Pause this variation' , 'landing-pages'); ?>" href='?post=<?php echo $post->ID; ?>&action=edit&lp-variation-id=<?php echo $vid; ?>&ab-action=pause-variation'><?php _e('Pause' , 'landing-pages'); ?></a></span> <span class='bab-stat-seperator pause-sep'>|</span>
									<span class='bab-stat-control-play'><a title="<?php _e('Turn this variation on' , 'landing-pages'); ?>" href='?post=<?php echo $post->ID; ?>&action=edit&lp-variation-id=<?php echo $vid; ?>&ab-action=play-variation'><?php _e('Play' , 'landing-pages'); ?></a></span> <span class='bab-stat-seperator play-sep'>|</span>
									<span class='bab-stat-menu-edit'><a title="<?php _e('Edit this variation' , 'landing-pages'); ?>" href='?post=<?php echo $post->ID; ?>&action=edit&lp-variation-id=<?php echo $vid; ?>'><?php _e('Edit' , 'landing-pages'); ?></a></span> <span class='bab-stat-seperator'>|</span>
									<span class='bab-stat-menu-preview'><a title="<?php _e('Preview this variation' , 'landing-pages'); ?>" class='thickbox' href='<?php echo $permalink; ?>&iframe_window=on&post_id=<?php echo $post->ID;?>&TB_iframe=true&width=1503&height=467' target='_blank'><?php _e('Preview' , 'landing-pages'); ?></a></span> <span class='bab-stat-seperator'>|</span>
									<span class='bab-stat-menu-clone'><a title="<?php _e('Clone this variation' , 'landing-pages'); ?>" href='?post=<?php echo $post->ID; ?>&action=edit&new-variation=1&clone=<?php echo $vid; ?>&new_meta_key=<?php echo $howmany; ?>'><?php _e('Clone' , 'landing-pages'); ?></a></span> <span class='bab-stat-seperator'>|</span>
									<span class='bab-stat-control-delete'><a title="<?php _e('Delete this variation' , 'landing-pages'); ?>" href='?post=<?php echo $post->ID; ?>&action=edit&lp-variation-id=<?php echo $vid; ?>&ab-action=delete-variation'><?php _e('Delete' , 'landing-pages'); ?></a></span>
								</div>
							</div>
						</div>
						<div class="bab-stat-row">

								<div class='bab-stat-menu-container'>

									<?php do_action('lp_ab_testing_stats_menu_post'); ?>

							</div>
						</div>
					</div>
						<?php

				}
				?>
			</div>

		</div>
	</div>
	<?php
}

//print out tabs
add_action('edit_form_after_title','lp_ab_testing_add_tabs', 5);
function lp_ab_testing_add_tabs()
{
	global $post;
	$post_type_is = get_post_type($post->ID);
	$permalink = get_permalink($post->ID);

	// Only show lp tabs on landing pages post types (for now)
	if ($post_type_is === "landing-page")
	{
		$current_variation_id = lp_ab_testing_get_current_variation_id();
		
		if (isset($_GET['new_meta_key'])) {
			$current_variation_id = $_GET['new_meta_key'];
		}

		echo "<input type='hidden' id='open_variation' value='{$current_variation_id}'>";

		$variations = get_post_meta($post->ID,'lp-ab-variations', true);
		$array_variations = explode(',',$variations);
		$variations = array_filter($array_variations,'is_numeric');
		sort($array_variations,SORT_NUMERIC);

		$lid = end($array_variations);
		$new_variation_id = $lid+1;

		if ($current_variation_id>0||isset($_GET['new-variation']))
		{
			$first_class = 'inactive';
		}
		else
		{
			$first_class = 'active';
		}

		
		$var_id_marker = 1;
		
		
		echo '<h2 class="nav-tab-wrapper a_b_tabs">';

		foreach ($array_variations as $i => $vid)
		{
			$letter = lp_ab_key_to_letter($i);
			($i<1) ?  $pre = __( 'Version ' , 'landing-pages' ) : $pre = '';

			if ($current_variation_id==$vid&&!isset($_GET['new-variation']))
			{
				$cur_class = 'active';
			}
			else
			{
				$cur_class = 'inactive';
			}
			echo '<a href="?post='.$post->ID.'&lp-variation-id='.$vid.'&action=edit" class="lp-nav-tab nav-tab nav-tab-special-'.$cur_class.'" id="tabs-add-variation">'. $pre.$letter .'</a>';
		}

		if (!isset($_GET['new-variation']))
		{
			echo '<a href="?post='.$post->ID.'&lp-variation-id='.$new_variation_id.'&action=edit&new-variation=1" class="lp-nav-tab nav-tab nav-tab-special-inactive nav-tab-add-new-variation" id="tabs-add-variation">'.__('Add New Variation' , 'landing-pages').'</a>';
		}
		else
		{
			$variation_count = $i + 1;
			$letter = lp_ab_key_to_letter($variation_count);
			echo '<a href="?post='.$post->ID.'&lp-variation-id='.$new_variation_id.'&action=edit" class="lp-nav-tab nav-tab nav-tab-special-active" id="tabs-add-variation">'.$letter.'</a>';
		}
		$edit_link = (isset($_GET['lp-variation-id'])) ? '?lp-variation-id='.$_GET['lp-variation-id'].'' : '?lp-variation-id=0';
		$post_link = get_permalink($post->ID);
		$post_link = preg_replace('/\?.*/', '', $post_link);
		echo "<a rel='".$post_link."' id='launch-visual-editer' class='button-primary new-save-lp-frontend' href='$post_link$edit_link&template-customize=on'>".__('Launch Visual Editor' , 'landing-pages')."</a>";
		echo '</h2>';
	}

}