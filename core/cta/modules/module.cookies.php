<?php

add_action('wp_cta_record_conversion','wp_cta_cookie_user');
function wp_cta_cookie_user($wp_cta_id)
{	
	
	//keeps track of how many times a user has performed an action
	if (isset($_COOKIE['wp-call-to-action-user-action-count']))
	{
		$count = $_COOKIE['wp-call-to-action-user-action-count'];
		$count++;
		setcookie('wp-call-to-action-user-action-count' , $count, time() + (20 * 365 * 24 * 60 * 60),'/');
	}
	else
	{
		setcookie('wp-call-to-action-user-action-count' , 1, time() + (20 * 365 * 24 * 60 * 60),'/');
	}
	
	//tracks information about which landing pages the user has performed an action on
	setcookie('wp-call-to-action-action-'.$wp_cta_id , '1', time() + (20 * 365 * 24 * 60 * 60),'/');
}