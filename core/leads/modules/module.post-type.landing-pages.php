<?php

add_action('lp_lead_table_data_is_details_column','wpleads_add_user_edit_button');
function wpleads_add_user_edit_button($item) {
	$image = WPL_URLPATH.'/images/icons/edit_user.png';
	echo '&nbsp;&nbsp;<a href="'.get_admin_url().'post.php?post='.$item['ID'].'&action=edit" target="_blank"><img src="'.$image.'" title="Edit Lead"></a>';
}

add_action('lp_module_lead_splash_post','wpleads_add_user_conversion_data_to_splash');
function wpleads_add_user_conversion_data_to_splash($data) {
	$conversion_data = $data['lead_custom_fields']['wpleads_conversion_data'];

	echo "<h3  class='lp-lead-splash-h3'>".__( 'Recent Conversions:' , 'leads' ) ."</h3>";
	echo "<table>";
	echo "<tr>";
		echo "<td class='lp-lead-splash-td' 'id='lp-lead-splash-0'>#</td>";
		echo "<td class='lp-lead-splash-td' 'id='lp-lead-splash-1'>". __( 'Location' , 'leads' ) ."</td>";
		echo "<td class='lp-lead-splash-td' 'id='lp-lead-splash-2'>". __( 'Datetime' , 'leads' ) ."</td>";
		echo "<td class='lp-lead-splash-td' 'id='lp-lead-splash-3'>". __( 'First-time?' , 'leads' ) ."</td>";
	echo "<tr>";

	foreach ($conversion_data as $key=>$value) {
		$i = $key+1;
		$value = json_decode($value, true);

		foreach ($value as $k=>$row) {

			echo "<tr>";
				echo "<td>";
					echo "[$i]";
					//echo $row['id'];
					//print_r($row);exit;
				echo "</td>";
				echo "<td>";
					echo "<a href='".get_permalink($row['id'])."' target='_blank'>".get_the_title(intval($row['id']))."</a>";
				echo "</td>";
				echo "<td>";
					echo $row['datetime'];
				echo "</td>";
				echo "<td>";
					if ($row['first_time']==1)
					{
						_e( 'yes' , 'leads' );
					}
				echo "</td>";
			echo "<tr>";
			$i++;
		}
	}

	echo "</table>";
}
