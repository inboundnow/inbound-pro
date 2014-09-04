<?php

// Not currently firing. Need to roll into core tracking
add_action('lp_record_conversion','lp_cookie_user');
function lp_cookie_user($lp_id)
{	
	
	//keeps track of how many times a user has performed an action
	if (isset($_COOKIE['landing-page-user-action-count']))
	{
		$count = $_COOKIE['landing-page-user-action-count'];
		$count++;
		setcookie('landing-page-user-action-count' , $count, time() + (20 * 365 * 24 * 60 * 60),'/');
	}
	else
	{
		setcookie('landing-page-user-action-count' , 1, time() + (20 * 365 * 24 * 60 * 60),'/');
	}
	
	//tracks information about which landing pages the user has performed an action on
	setcookie('landing-page-action-'.$lp_id , '1', time() + (20 * 365 * 24 * 60 * 60),'/');
}