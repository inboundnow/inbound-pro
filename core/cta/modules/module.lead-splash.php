<?php
//define('WP_DEBUG',true);
require_once('../../../../wp-admin/admin.php');
$matches = array();
preg_match('/wp-admin/', $_SERVER['HTTP_REFERER'], $matches, null, 0);

$lead_id = $_GET['lead_id'];
$page_id = $_GET['post_id'];
$wplead_data = get_post_custom($lead_id);

$data['lead_id'] = $lead_id;
$data['page_id'] = $page_id;
$data['lead_custom_fields'] = $wplead_data;

?>

<?php 	$city = (isset($wplead_data['wpleads_city'][0])) ? $wplead_data['wpleads_city'][0] : 'NA';
		$region = (isset($wplead_data['wpleads_region_name'][0])) ? $wplead_data['wpleads_region_name'][0] : 'NA'; ?>
<link rel='stylesheet'  href='/wp-content/plugins/wp-call-to-actions/css/admin-style.css' type='text/css' media='all' />

<div id='lead-details-container'>
<div id="wp-leads-splash-header">
<h3 class='wp-cta-lead-splash-h3'><?php _e( 'Lead Details:' , 'cta' ); ?></h3>
<?php // Conditional check for wp leads add on ?>
<a href="/wp-admin/post.php?post=<?php echo $lead_id . "&action=edit";?>" class="wpwp-cta-green-button wpwp-cta-right" target="_blank">View/Edit Lead</a>
</div>
<div id="wp-leads-splash-name">
<div class="wp-lead-label"><?php _e( 'Name:' , 'cta' ); ?></div> <?php echo $wplead_data['wpleads_first_name'][0]; ?> <?php echo $wplead_data['wpleads_last_name'][0]; ?> 
</div>
			
<div id="wp-leads-splash-email">
<div class="wp-lead-label"><?php _e( 'Email Address:' , 'cta' ); ?></div> <?php echo $wplead_data['wpleads_email_address'][0]; ?>
</div>
<div id="wp-leads-splash-ip">
<div class="wp-lead-label"><?php _e( 'IP Address:' , 'cta' ); ?> </div> <?php echo $wplead_data['wpleads_ip_address'][0]; ?>
</div>
<div id="wp-leads-splash-city">
<div class="wp-lead-label"><?php _e( 'City:' , 'cta' ); ?> </div> <?php echo $city; ?>
</div>
<div id="wp-leads-splash-state">
<div class="wp-lead-label"><?php _e( 'State:' , 'cta' ); ?> </div> <?php echo $region; ?>
</div>

<div id="wp-leads-extra-data">
<?php // Conditional check for wp leads add on. If not on, have button to have people download_url( $url, $timeout = 300 ) ?>
<?php
do_action('wp_cta_module_lead_splash_post',$data);
?>
</div>