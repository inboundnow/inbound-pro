<?php
//define('WP_DEBUG',true);
// Going to be replace with another admin page
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
<link rel='stylesheet'  href='/wp-content/plugins/landing-pages/css/admin-style.css' type='text/css' media='all' />

<div id='lead-details-container'>
<div id="wp-leads-splash-header">
<h3 class='lp-lead-splash-h3'><?php _e('Lead Details ' , 'landing-pages') ?>: </h3>
<?php // Conditional check for wp leads add on ?>
<a href="/wp-admin/post.php?post=<?php echo $lead_id . "&action=edit";?>" class="wplp-green-button wplp-right" target="_blank"> <?php _e( 'View/Edit Lead' , 'landing-pages'); ?> </a>
</div>
<div id="wp-leads-splash-name">
<div class="wp-lead-label"><?php _e('Name' , 'landing-pages'); ?>:</div> <?php echo $wplead_data['wpleads_first_name'][0]; ?> <?php echo $wplead_data['wpleads_last_name'][0]; ?> 
</div>
			
<div id="wp-leads-splash-email">
<div class="wp-lead-label"><?php _e('Email Address' , 'landing-pages'); ?>:</div> <?php echo $wplead_data['wpleads_email_address'][0]; ?>
</div>
<div id="wp-leads-splash-ip">
<div class="wp-lead-label"><?php _e('IP Address' , 'landing-pages'); ?>: </div> <?php echo $wplead_data['wpleads_ip_address'][0]; ?>
</div>
<div id="wp-leads-splash-city">
<div class="wp-lead-label"><?php _e('City' , 'landing-pages'); ?>: </div> <?php echo $city; ?>
</div>
<div id="wp-leads-splash-state">
<div class="wp-lead-label"><?php _e('State' , 'landing-pages'); ?>: </div> <?php echo $region; ?>
</div>

<div id="wp-leads-extra-data">
<?php // Conditional check for wp leads add on. If not on, have button to have people download_url( $url, $timeout = 300 ) ?>
<?php
do_action('lp_module_lead_splash_post',$data);
?>
</div>