<?php
/*
Simple:Press
Login (etc) Form Actions and Filters
$LastChangedDate: 2016-06-25 08:14:16 -0500 (Sat, 25 Jun 2016) $
$Rev: 14331 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_post_login_check($login_name) {
	$dname = sp_filter_name_display(sp_get_login_display_name($login_name));
	$cookiepath = preg_replace('|https?://[^/]+|i', '', user_trailingslashit(SFSITEURL));
	setcookie('sforum_'.COOKIEHASH, $dname, time() + 30000000, $cookiepath, false);
}

function sp_get_login_display_name($login_name) {
	return spdb_select('var',
			'SELECT '.SFMEMBERS.'.display_name
			 FROM '.SFMEMBERS.'
			 JOIN '.SFUSERS.' ON '.SFUSERS.'.ID = '.SFMEMBERS.".user_id
			 WHERE user_login='$login_name'");
}

function sp_login_redirect($redirect, $redirectarg, $user) {
	$sflogin = sp_get_option('sflogin');
	if (!empty($sflogin['sfloginurl']) && empty($redirect)) $redirect = $sflogin['sfloginurl'];
	$redirect = apply_filters('sph_login_redirect', $redirect);
	return $redirect;
}

function sp_register_redirect($redirect) {
	$sflogin = sp_get_option('sflogin');
	if (!empty($sflogin['sfregisterurl'])) $redirect = $sflogin['sfregisterurl'];
	$redirect = apply_filters('sph_register_redirect', $redirect);
	return $redirect;
}

function sp_logout_redirect() {
	sp_forum_ajax_support();
	global $spThisUser;

	$sflogin = sp_get_option('sflogin');
	if (!empty($sflogin['sflogouturl'])) {
		$sfadminoptions = sp_get_member_item($spThisUser->ID, 'admin_options');
		if ($spThisUser->moderator && $sfadminoptions['bypasslogout']) {
			$_REQUEST['redirect_to'] = esc_url(wp_login_url());
		} else {
			$_REQUEST['redirect_to'] = $sflogin['sflogouturl'];
		}
	}
	$redirect = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
	$_REQUEST['redirect_to'] = apply_filters('sph_logout_redirect', $redirect);
}

?>