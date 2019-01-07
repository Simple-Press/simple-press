<?php
/*
Simple:Press
Desc: Privacy - Personal Data Export
$LastChangedDate: 2014-05-24 09:12:47 +0100 (Sat, 24 May 2014) $
$Rev: 11461 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Specific forum profile data filter hook
add_filter('sp_privacy_profile_data', 'sp_privacy_profile_export', 20, 4);

# Register the export
function sp_register_privacy_exporter($exporters) {
	$exporters['simple_press'] = array(
		'exporter_friendly_name' => SP()->primitives->admin_text('Simple Press Forum'),
		'callback' => 'sp_privacy_exporter',
	);
	return $exporters;
}

# Main Exporter function
function sp_privacy_exporter($email_address, $page = 1) {
	$number = 250; // Limit us to avoid timing out
	$page = (int) $page;

	# get users ID
	$userID = SP()->DB->table(SPUSERS, "user_email='$email_address'", 'ID');
	if (!empty($userID)) {
		$exportItems = array();
		$spUserData = SP()->user->get($userID);
		$groupID = SP()->primitives->admin_text('Forum Profile Group');
		$groupLabel = SP()->primitives->admin_text('Forum Profile');

		# call generic forum profile filter hook		
		$exportItems = apply_filters('sp_privacy_profile_data', $exportItems, $spUserData, $groupID, $groupLabel);
		# call new section filter hook
		$exportItems = apply_filters('sp_privacy_section_data', $exportItems, $spUserData);

		return array(
			'data' => $exportItems,
			'done' => true,
		);
	}
}

# Specific profile data exporter using general profile filter hook
function sp_privacy_profile_export($exportItems, $spUserData, $groupID, $groupLabel) {
	$items = array(
		'display_name' 	=> SP()->primitives->admin_text('Forum Display Name'),
		'location'		=> SP()->primitives->admin_text('Location'),
		'msn'			=> SP()->primitives->admin_text('MSN Identity'),
		'icq'			=> SP()->primitives->admin_text('ICQ Identity'),
		'skype'			=> SP()->primitives->admin_text('Skype Identity'),
		'myspace'		=> SP()->primitives->admin_text('MySpace Identity'),
		'facebook'		=> SP()->primitives->admin_text('FaceBook Identity'),
		'twitter'		=> SP()->primitives->admin_text('Twitter Identity'),
		'linkedin'		=> SP()->primitives->admin_text('LinedIn Identity'),
		'youtube'		=> SP()->primitives->admin_text('YouTube Identity'),
		'googleplus'	=> SP()->primitives->admin_text('Google Plus Identity'),
		'photos'		=> SP()->primitives->admin_text('Photo URL')
	);

	$data = array();
	foreach($items as $item => $label) {
		if (!empty($spUserData->$item)) {
			# checkfor photos as this is an array
			if ($item == 'photos') {
				foreach($spUserData->$item as $photo) {
					$data[] = array(
						'name'	=> $label,
						'value'	=> $photo
					);
				}				
			} else {
				$data[] = array(
					'name'	=>	$label,
					'value'	=>	$spUserData->$item
				);
			}
		}
	}
	
	$exportItems[] = array(
		'group_id'		=> $groupID,
		'group_label' 	=> $groupLabel,
		'item_id' => 'Profile',
		'data' => $data,
	);

	return $exportItems;
}

