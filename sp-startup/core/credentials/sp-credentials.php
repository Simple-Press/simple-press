<?php
/**
 * Login/Registration credentials
 *
 * $LastChangedDate: 2018-11-02 16:17:56 -0500 (Fri, 02 Nov 2018) $
 * $Rev: 15797 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function grabs the display name for just logged in user and stores in cookie.
 * Will be later checked for guest as non logged in user.
 *
 * @since 6.0
 *
 * @param string	$login_name		username for user just logged in
 *
 * @return void
 */
function sp_post_login_check($login_name) {
	$dname = SP()->displayFilters->name(sp_get_login_display_name($login_name));
	$cookiepath = preg_replace('|https?://[^/]+|i', '', user_trailingslashit(SPSITEURL));
	setcookie('sforum_'.COOKIEHASH, $dname, time() + 30000000, $cookiepath, false);
}

/**
 * This function gets the Simple Press display name for the logged in user.
 *
 * @since 6.0
 *
 * @param string	$login_name	username for logged in user
 *
 * @return string
 */
function sp_get_login_display_name($login_name) {
	return SP()->DB->select('SELECT '.SPMEMBERS.'.display_name
			 FROM '.SPMEMBERS.'
			 JOIN '.SPUSERS.' ON '.SPUSERS.'.ID = '.SPMEMBERS.".user_id
			 WHERE user_login='$login_name'", 'var');
}

/**
 * This function checks if a redirect is needed after a user logs in.
 *
 * @since 6.0
 *
 * @param string	$redirect		current redirect
 * @param string	$redirecttag	unused
 * @param string	$user			unused
 *
 * @return string	updated login redirect url
 */
function sp_login_redirect($redirect, $redirectarg, $user) {
	$sflogin = SP()->options->get('sflogin');
	if (!empty($sflogin['sfloginurl']) && empty($redirect)) $redirect = $sflogin['sfloginurl'];
	$redirect = apply_filters('sph_login_redirect', $redirect);
	return $redirect;
}

/**
 * This function checks if a redirect is needed after a user registers.
 *
 * @since 6.0
 *
 * @param string	$redirect	current registration redirect
 *
 * @return string	updated registration redirect url
 */
function sp_register_redirect($redirect) {
	$sflogin = SP()->options->get('sflogin');
	if (!empty($sflogin['sfregisterurl'])) $redirect = $sflogin['sfregisterurl'];
	$redirect = apply_filters('sph_register_redirect', $redirect);
	return $redirect;
}

/**
 * This function checks if a redirect is needed after a user logs out.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_logout_redirect() {
	sp_forum_ajax_support();

	$sflogin = SP()->options->get('sflogin');
	if (!empty($sflogin['sflogouturl'])) {
		$sfadminoptions = SP()->memberData->get(SP()->user->thisUser->ID, 'admin_options');
		if (SP()->user->thisUser->moderator && (isset($sfadminoptions['bypasslogout']) && $sfadminoptions['bypasslogout'])) {
			$_REQUEST['redirect_to'] = esc_url(wp_login_url());
		} else {
			$_REQUEST['redirect_to'] = $sflogin['sflogouturl'];
		}
	}
	$redirect = isset($_REQUEST['redirect_to']) ? sanitize_text_field($_REQUEST['redirect_to']) : '';
	$_REQUEST['redirect_to'] = apply_filters('sph_logout_redirect', $redirect);
}
