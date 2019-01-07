<?php
/*
Simple:Press
In Line Login
$LastChangedDate: 2017-06-04 13:29:51 -0500 (Sun, 04 Jun 2017) $
$Rev: 15405 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_inline_login_form($a) {
	global $spGlobals, $spDevice;

	extract($a, EXTR_SKIP);
	$user_login = '';
	$user_pass = '';
	$using_cookie = false;
	$sflogin = sp_get_option('sflogin');
	$redirect_to = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');

	$out = '';

	# Add a close button if using a mobile phone
	if ($spDevice == 'mobile') {
		$out.= "<div class='spRight'>";
		$out.= "<a id='spPanelClose' href='#'></a>";
		$out.= "</div>";
	}

	do_action('sph_login_head', 'sploginform');

	$message = '';
	$message = apply_filters('sf_filter_login_message', $message);
	if (!empty($message)) $out.= $message."\n";

   	$out.= "<fieldset class='$controlFieldset'>\n";
    $out.= "<form name='loginform' id='loginform' class='$tagClass' action='$loginLink' method='post'>\n";

    $sfrpx = sp_get_option('sfrpx');
    if ($sfrpx['sfrpxenable']) $out.= sp_rpx_loginform('spLoginForm', '100%', true);

	$out.= "<p><label for='log'>$labelUserName<br /><input type='text' class='$controlInput' tabindex='84' name='log' id='log' value='".esc_attr($user_login)."' size='11' /></label></p>\n";
	$out.= "<p><label for='login_password'>$labelPassword<br /><input type='password' class='$controlInput' tabindex='85' name='pwd' id='login_password' value='' size='11' /></label></p>\n";
	$out.= "<p><input type='checkbox' tabindex='86' id='rememberme' name='rememberme' value='forever' /><label for='rememberme'>$labelRemember</label></p>\n";

	$out.= do_action('login_form');

	$out.= "<p class='$tagClass'><input type='submit' class='$controlSubmit' name='submit' id='submit' value='$labelSubmit' tabindex='87' /></p>\n";
	$out.= "<input type='hidden' name='redirect_to' value='".esc_attr($redirect_to)."' />\n";

	$out.= "</form>\n";

	if (TRUE == get_option('users_can_register') && !$spGlobals['lockdown'] && $showRegister) {
	    $out.= "<a class='$controlLink' href='$registerLink'>$labelRegister</a>\n";
		$out.= $separator;
	}
	if ($showLostPass) {
	    $out.= "<a class='$controlLink' href='$passwordLink'>$labelLostPass</a>\n";
	}

   	$out.= "</fieldset>\n";
	return $out;
}

?>