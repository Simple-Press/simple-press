<?php
/*
Simple:Press
In Line Login
$LastChangedDate: 2017-06-04 13:42:16 -0500 (Sun, 04 Jun 2017) $
$Rev: 15408 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_inline_login_form($a) {
	extract($a, EXTR_SKIP);  // See function sp_LoginForm() in sp-common-view-functions.php for a list of variables that this will produce.
	$user_login = '';
	$user_pass = '';
	$using_cookie = false;
	$sflogin = SP()->options->get('sflogin');
	$redirect_to = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');

	$out = '';

	# Add a close button if using a mobile phone
	if (SP()->core->device == 'mobile') {
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

    $sfrpx = SP()->options->get('sfrpx');
	if($sfrpx){
		if ($sfrpx['sfrpxenable']) $out.= sp_rpx_loginform('spLoginForm', '100%', true);
	}
	if ($title) {
		$out = $out . "<div id='".$titleClass."' class='".$titleClass."'>$title</div> \n" ;
	}

	$out.= "<label class='$labelClass' for='log'>$labelUserName<br /><input type='text' class='$controlInput' tabindex='84' name='log' id='log' value='".esc_attr($user_login)."' size='11' /></label>\n";
	$out.= sp_InsertBreak('echo=0&spacer=12px');
	$out.= "<label class='$labelClass' for='login_password'>$labelPassword<br /><input type='password' class='$controlInput' tabindex='85' name='pwd' id='login_password' value='' size='11' /></label>\n";
	$out.= sp_InsertBreak('echo=0&spacer=12px');
//	$out.= "<input type='checkbox' tabindex='86' id='rememberme' name='rememberme' value='forever' /><label class='$labelClass' for='rememberme'>$labelRemember</label>\n";

	$out.= do_action('login_form');

	$out.= sp_InsertBreak('echo=0&spacer=4px');

		$out.= "<input type='checkbox' tabindex='86' id='rememberme' name='rememberme' value='forever' /><label class='$labelClass' for='rememberme'>$labelRemember</label>\n";

	$out.= "<input type='submit' class='$controlSubmit' name='submit' id='submit' value='$labelSubmit' tabindex='87' style='float:right' />\n";
	$out.= "<input type='hidden' name='redirect_to' value='".esc_attr($redirect_to)."' />\n";

	$out.= "</form>\n";

	$out.= sp_InsertBreak('echo=0&spacer=8px');

	if (TRUE == get_option('users_can_register') && !SP()->core->forumData['lockdown'] && $showRegister) {
	    $out.= "<a class='$controlLink' href='$registerLink'>$labelRegister</a>\n";
		$out.= $separator;
	}
	if ($showLostPass) {
	    $out.= "<a class='$controlLink' href='$passwordLink'>$labelLostPass</a>\n";
	}

   	$out.= "</fieldset>\n";
	return $out;
}
