<?php
/*
Simple:Press
New User Email (SPF Option)
$LastChangedDate: 2015-11-10 02:22:52 -0600 (Tue, 10 Nov 2015) $
$Rev: 13563 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# = NEW USER EMAIL REPLACEMENT ================
if (!function_exists('wp_new_user_notification')):
function wp_new_user_notification($user_id, $deprecated = null, $notify='both') {
	$user = new WP_User($user_id);
	$sflogin = sp_get_option('sflogin');
	$eol = "\r\n";
	$user_login = $user->user_login;
	$user_email = $user->user_email;
	$message = '';
	$message.= sp_text_noesc('New user registration on your website').': '.get_option('blogname').$eol.$eol;
	$message.= sp_text_noesc('Username').': '.$user_login.$eol;
	$message.= sp_text_noesc('E-mail').': '.$user_email.$eol;
	$message.= sp_text_noesc('Registration IP').': '.sp_get_ip().$eol;

	$address = apply_filters('sph_admin_new_user_email_addrress', get_option('admin_email'), $user_id);
	$subject = apply_filters('sph_admin_new_user_email_subject', get_option('blogname').' '.sp_text_noesc('New User Registration'), $user_id);
	$msg = apply_filters('sph_admin_new_user_email_msg', $message, $user_id);
	sp_send_email($address, $subject, $msg);

	if ('admin' === $notify || empty($notify)) return;

	# Generate something random for a password reset key.
	$key = wp_generate_password(20, false);

	/** This action is documented in wp-login.php */
	do_action('retrieve_password_key', $user_login, $key);

	# Now insert the key, hashed, into the DB.
	if (empty($wp_hasher)) {
		require_once ABSPATH.WPINC.'/class-phpass.php';
		$wp_hasher = new PasswordHash(8, true);
	}
	$hashed = time().':'.$wp_hasher->HashPassword($key);
    global $wpdb;
	$wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));

	$mailoptions = sp_get_option('sfnewusermail');
	$subject = stripslashes($mailoptions['sfnewusersubject']);
	$body = stripslashes($mailoptions['sfnewusertext']);
	if ((empty($subject)) || (empty($body))) {
		$subject = get_option('blogname').' '.sp_text_noesc('Your username').$eol.$eol;
		$body = sp_text_noesc('Username').': '.$user_login.$eol;
		$body.= sp_text_noesc('Login URL').': '.$sflogin['sfloginemailurl'].$eol;
		$body.= sp_text_noesc('Password Reset URL').': '.network_site_url("wp-login.php?action=rp&key=$key&login=".rawurlencode($user_login), 'login').$eol;
	} else {
		$blogname = get_bloginfo('name');
		$subject = str_replace('%USERNAME%', $user_login, $subject);
		$subject = str_replace('%BLOGNAME%', $blogname, $subject);
		$subject = str_replace('%SITEURL%', sp_url(), $subject);
		$subject = str_replace('%LOGINURL%', $sflogin['sfloginemailurl'], $subject);
		$subject = str_replace('%PWURL%', network_site_url("wp-login.php?action=rp&key=$key&login=".rawurlencode($user_login), 'login'), $subject);
		$body = str_replace('%USERNAME%', $user_login, $body);
		$body = str_replace('%BLOGNAME%', $blogname, $body);
		$body = str_replace('%SITEURL%', sp_url(), $body);
		$body = str_replace('%LOGINURL%', $sflogin['sfloginemailurl'], $body);
		$body = str_replace('%PWURL%', network_site_url("wp-login.php?action=rp&key=$key&login=".rawurlencode($user_login), 'login'), $body);
		$body = str_replace('%NEWLINE%', $eol, $body);
	}
	str_replace('<br />', $eol, $body);

	$address = apply_filters('sph_user_new_user_email_addrress', $user_email, $user_id);
	$subject = apply_filters('sph_user_new_user_email_subject', get_option('blogname').' '.sp_text_noesc('New User Registration'), $user_id);
	$msg = apply_filters('sph_user_new_user_email_msg', $body, $user_id);
	sp_send_email($user_email, $subject, $msg);
}
endif;
?>