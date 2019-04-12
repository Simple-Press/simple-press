<?php
/*
Simple:Press
Desc: Privacy - Personal Data Export and Erase
$LastChangedDate: 2014-05-24 09:12:47 +0100 (Sat, 24 May 2014) $
$Rev: 11461 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');


# --------------------------------------------
# Export of SP related personal profile data
# --------------------------------------------

# Specific forum profile data filter hook
add_filter('sp_privacy_profile_data', 'sp_privacy_profile_export', 20, 4);

# Register the profile export
function sp_register_profile_exporter($exporters) {
	$exporters['simple_press_profile'] = array(
		'exporter_friendly_name' => SP()->primitives->admin_text('Simple Press Profile'),
		'callback' => 'sp_profile_exporter'
	);
	return $exporters;
}

# Main SP Profile Data Exporter function
function sp_profile_exporter($email_address, $page = 1) {
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
		$exportItems = apply_filters('sp_privacy_profile_section_data', $exportItems, $spUserData);

		return array(
			'data' => $exportItems,
			'done' => true
		);
	}
}

# Specific profile data exporter using general profile filter hook
function sp_privacy_profile_export($exportItems, $spUserData, $groupID, $groupLabel) {
	$data = array();

	# Additional Profile Data from UserMeta
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
		'instagram'		=> SP()->primitives->admin_text('Instagram Identity'),
		'photos'		=> SP()->primitives->admin_text('Photo URL')
	);

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

	# Add any IP addresses used in forum posts
	$query				= new stdClass();
	$query->type		= 'col';
	$query->table		= SPPOSTS;
	$query->fields		= 'poster_ip';
	$query->distinct	=	true;
	$query->where		= "user_id = ".$spUserData->ID." AND poster_ip != ''";
	$query->orderby		= 'poster_ip';
	$ips = SP()->DB->select($query);
	
	if (!empty($ips)) {
		$ipList = '';
		foreach($ips as $ip) {
			$ipList.= $ip.'</br>';		
		}
		$data[] = array(
			'name'	=>	SP()->primitives->admin_text('Used IP Addresses'),
			'value'	=>	$ipList
		);
	}
	
	# Now to export the base forum profile data
	$exportItems[] = array(
		'group_id'		=> $groupID,
		'group_label' 	=> $groupLabel,
		'item_id' => 'Profile',
		'data' => $data
	);

	return $exportItems;
}


# --------------------------------------------
# Export of SP post data
# --------------------------------------------

# Specific forum profile data filter hook
add_filter('sp_privacy_forum_data', 'sp_privacy_forum_export', 21, 7);

function sp_register_forum_exporter($exporters) {
	$exporters['simple_press_forum'] = array(
		'exporter_friendly_name' => SP()->primitives->admin_text('Simple Press Forum'),
		'callback' => 'sp_forum_exporter'
	);
	return $exporters;
}

# Main SP Forum Data Exporter function
function sp_forum_exporter($email_address, $page = 0) {
	$ops = SP()->options->get('spPrivacy');
	# Limit us to avoid timing out
	if (!empty($ops['number'])) {
		$number = $ops['number'];
	} else {
		$number = 200;
	}
	$page = (int) $page;
	$done = false;
	$data = array();

	# get users ID
	$userID = SP()->DB->table(SPUSERS, "user_email='$email_address'", 'ID');
	if (!empty($userID)) {
		$exportItems = array();
		$spUserData = SP()->user->get($userID);
		$groupID = SP()->primitives->admin_text('Forum Post Group');
		$groupLabel = SP()->primitives->admin_text('Forum Posts');

		# call generic forum profile filter hook		
		$exportItems = apply_filters('sp_privacy_forum_data', $exportItems, $spUserData, $groupID, $groupLabel, $page, $number, $done);
		# call new section filter hook
		$exportItems = apply_filters('sp_privacy_forum_section_data', $exportItems, $spUserData, $groupID, $groupLabel, $page, $number, $done);

		return array(
			'data' => $exportItems,
			'done' => true
		);
	}
}

# Specific profile data exporter using general profile filter hook
function sp_privacy_forum_export($exportItems, $spUserData, $groupID, $groupLabel, $page, $number, $done) {
	$ops = SP()->options->get('spPrivacy');
	if($ops['posts'] == false) return $exportItems;

	$data = array();
	
	# Select forum posts
	$query				= new stdClass();
	$query->type		= 'set';
	$query->table		= SPPOSTS;
	$query->fields		= 'post_date, post_content, topic_name';
	$query->join		= SPTOPICS.' ON '.SPPOSTS.'.topic_id = '.SPTOPICS.'.topic_id';
	$query->where		= SPPOSTS.".user_id = $spUserData->ID.";
	$query->orderby		= SPPOSTS.'.post_id';
	$query->limit		= $page.', '.$number;
	$posts = SP()->DB->select($query);
	
	if (empty($posts) && $page==0) {
		$data[] = array(
			'name'	=>	SP()->primitives->admin_text('No forum posts'),
			'value'	=>	'',
			'done'	=> true
		);
		$done = true;
	} elseif (empty($posts)) {
		$data[] = array(
			'name'	=>	'',
			'value'	=>	'',
			'done'	=> true
		);
		$done = true;
	} else {
		foreach($posts as $post) {
			$nameValue = SP()->displayFilters->title($post->topic_name.' - '.$post->post_date);
			$data[] = array(
				'name'	=> $nameValue,
				'value'	=> SP()->displayFilters->content($post->post_content)
			);
		}
	}

	# Now to export the forum post data
	$exportItems[] = array(
		'group_id'		=> $groupID,
		'group_label' 	=> $groupLabel,
		'item_id' => 'Posts',
		'data' => $data,
		'done' => $done
	);

	return $exportItems;
}


# --------------------------------------------
# Erasure of SP related data
# --------------------------------------------

# Register the forum data eraser
function sp_register_forum_eraser($erasers) {
	$erasers['simple-press'] = array(
		'eraser_friendly_name' => SP()->primitives->admin_text('Forum Data'),
		'callback'             => 'sp_data_eraser'
    );
  return $erasers;
}

# Perform the actual erasure
# This calls the SP standard user deletion
# code which converts all forum posts to 'Guest' 
# and removes all other traces of the user.
function sp_data_eraser($email_address, $page = 1) {
	# get users ID
	$userID = SP()->DB->table(SPUSERS, "user_email='$email_address'", 'ID');
	$message = '';
	if (!empty($userID)) {
		$ops = SP()->options->get('spPrivacy');
		if ($ops['erase'] == 2) {
			SP()->user->delete_data($userID, '', 'spdelete', 0);
			$message = SP()->primitives->admin_text('Forum data has been erased for this user');
		} else {
			SP()->user->delete_data($userID, '', 'spguest', 0, $ops['mess']);
			$message = SP()->primitives->admin_text('Forum data anonymised for this user');
		}
	}
	return array( 'items_removed' => 0,
	'items_retained' => false,
	'messages' => array($message),
	'done' => true,
  );
}

// spdelete