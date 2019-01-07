<?php
/**
 * 3rd party RPX (Janrain) framework functions
 *
 * $LastChangedDate: 2018-11-02 16:17:56 -0500 (Fri, 02 Nov 2018) $
 * $Rev: 15797 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_rpx_loginform($id, $width = '373px', $fieldset = false) {
	$out = '';
	$sfrpx = SP()->options->get('sfrpx');
	$realm = $sfrpx['sfrpxrealm'];
	$turl = sp_rpx_token_url();
	if (isset($_REQUEST['return_to'])) $turl .= '&amp;goback='.$_REQUEST['return_to'];
	$params = '&flags=hide_sign_in_with&language_preference='.spa_get_language_code();
	$iframe_src = 'https://'.$realm.'/openid/embed?token_url='.urlencode($turl).$params;

	if ($id == 'registerform') {
		$logtext = apply_filters('sph_rpx_regform_login_text', SP()->primitives->front_text('Register a Site Account'));
		$regtext = apply_filters('sph_rpx_regform_register_text', SP()->primitives->front_text('Register with 3rd Party Account'));
	} else {
		$logtext = apply_filters('sph_rpx_logform_login_text', SP()->primitives->front_text('Sign in with Site Password'));
		$regtext = apply_filters('sph_rpx_logform_register_text', SP()->primitives->front_text('Sign in with 3rd Party Account'));
	}

	$out .= '
    <script>
    <!--
    window.onload = function()
    {
        var rpx_lf = document.getElementById("'.$id.'");
        rpx_lf.style.width = "'.$width.'";
        rpx_lf.style.margin = "0 auto";
        rpx_lf.style.paddingBottom = "46px";

        var rpx_up = document.createElement("DIV");
        rpx_up.style.paddingBottom = "10px";
        if (document.getElementById("spMainContainer"))
        {
            rpx_lf.style.paddingBottom = "0";
            rpx_lf.style.margin = "10px auto";
            if (document.getElementById("spMainContainer").offsetWidth < 625)
            {
                rpx_up.style.clear = "both";
                rpx_up.style.paddingTop = "20px";
            }
        }
        rpx_up.innerHTML = "<span id=\"sp_rpx_password\"><h2 style=\"text-align:center\">'.$logtext.'</h2></span>";
        rpx_lf.insertBefore(rpx_up, rpx_lf.firstChild );

        var rpx_wrap = document.createElement("DIV");
        rpx_wrap.id = "sp_rpx_wrap";
        rpx_lf.insertBefore(rpx_wrap, rpx_lf.firstChild);

        var sign_in = document.createElement("H2");
        sign_in.id = "sp_rpx_signin";
        sign_in.innerHTML = "'.$regtext.'";
        sign_in.style.paddingBottom = "10px";
        sign_in.style.textAlign = "center";
        rpx_wrap.appendChild(sign_in);
    ';
	if ($fieldset) {
		$out .= '
            if (document.getElementById("spMainContainer"))
            {
                if (document.getElementById("spMainContainer").offsetWidth >= 625)
                {
                    rpx_wrap.style.styleFloat = "left";
                    rpx_wrap.style.cssFloat = "left";
                    rpx_wrap.style.marginRight = "20px";
                    rpx_wrap.style.marginLeft = "10px";
                } else {
                    rpx_wrap.style.textAlign = "center";
                }
            }

            var rpx_fieldset = document.createElement("FIELDSET");
            rpx_fieldset.id = "sp_rpx_fieldset";
            rpx_fieldset.style.height = "240px";
            rpx_fieldset.style.margin = "0";
            rpx_fieldset.style.padding = "0";
            rpx_wrap.appendChild(rpx_fieldset);
        ';
	} else {
		$out .= '
            var rpx_fieldset = rpx_wrap;
        ';
	}
	$out .= '
        var rpx_iframe = document.createElement("IFRAME");
        rpx_iframe.id = "sp_rpx_iframe";
        rpx_iframe.src = "'.$iframe_src.'";
        rpx_iframe.style.width = "373px";
        rpx_iframe.style.height = "240px";
        rpx_iframe.scrolling = "no";
        rpx_iframe.frameBorder = "no";
        rpx_fieldset.appendChild(rpx_iframe);
    }
    //-->
    </script>
    ';
	return $out;
}

function sp_rpx_login_head($arg = '') {
	# dont do rpx stuff when we fire the login head action
	if ($arg == 'sploginform') return;

	# dont do rpx stuff for register or lost password
	if (!isset($_GET['action']) || (isset($_GET['action']) && (sanitize_text_field($_GET['action']) == 'login' || sanitize_text_field($_GET['action']) == 'register'))) {
		if (isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'register') {
			echo sp_rpx_loginform('registerform');
		} else {
			echo sp_rpx_loginform('loginform');
		}
	}
}

function sp_rpx_iframe($style = '', $token_url_params = '') {
	$sfrpx = SP()->options->get('sfrpx');
	$realm = $sfrpx['sfrpxrealm'];
	$params = '&flags=hide_sign_in_with&language_preference='.spa_get_language_code();
	$turl = sp_rpx_token_url().$token_url_params.$params;
	$iframe_src = 'https://'.$realm.'/openid/embed?token_url='.urlencode($turl);
	echo '<iframe src="'.$iframe_src.'" scrolling="no" frameBorder="no" style="width:373px;height:240px;'.$style.'"></iframe>';
}

function sp_rpx_token_url() {
	$url = SP()->spPermalinks->get_url();
	$token_url = SP()->spPermalinks->get_query_url($url).'rpx_response=1';
	$sfrpx = SP()->options->get('sfrpx');
	if (!empty($sfrpx['sfrpxredirect'])) {
		$goback = $sfrpx['sfrpxredirect'];
	} else {
		if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') == false) {
			# if we're not at the login page, define a goback to the page we are on
            $goback = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8').htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
		} else {
			# otherwise, get the redirect or go to the admin page
			if (isset($_GET['redirect_to'])) {
				$goback = SP()->filters->str($_GET['redirect_to']);
			} else {
				$goback = $url;
			}
		}
	}
	return $token_url.'&goback='.$goback;
}

function sp_rpx_process_token() {
	$sfrpx = SP()->options->get('sfrpx');
	if (empty($_REQUEST['rpx_response']) || empty($_REQUEST['token'])) return;
	$post_data = array(
		'token'	 => sanitize_text_field($_REQUEST['token']),
		'apiKey' => $sfrpx['sfrpxkey'],
		'format' => 'json');
	$raw_response = sp_rpx_http_post('https://rpxnow.com/api/v2/auth_info', $post_data);

	# parse the json or xml response into an associative array
	$auth_info = sp_rpx_parse_auth_info($raw_response);

	# process the auth_info response
	if ($auth_info['stat'] == 'ok') {
		sp_rpx_process_auth_info($auth_info);
	} else {
		SP()->primitives->front_etext('An error occured');
	}
}

function sp_rpx_process_auth_info($auth_info) {
	# a user is already signed in and is changing their OpenID
	if (isset($_REQUEST['attach_to'])) {
		$wpuid = sanitize_text_field($_REQUEST['attach_to']);

		# make sure the actually initiated the sign-in request
		$wpuser = wp_get_current_user();
		if ($wpuser && $wpuid == $wpuser->ID) update_user_meta($wpuid, 'rpx_identifier', $auth_info['profile']['identifier']);
	} else {
		sp_rpx_signin_user($auth_info);
	}
}

function sp_rpx_signin_user($auth_info) {
	$identifier = $auth_info['profile']['identifier'];
	$wpuid = sp_rpx_get_wpuid_by_identifier($identifier);

	# if we don't have the identifier mapped to wp user, create a new one
	if (!$wpuid) {
		$wpuid = sp_rpx_create_wp_user($auth_info);
		if (is_wp_error($wpuid)) {
			SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Sorry, cannot create account as the username or email address already exists'));
			wp_redirect(SP()->spPermalinks->get_url());
			die();
		}
	}

	# sign the user in
	wp_set_auth_cookie($wpuid, true, false);
	wp_set_current_user($wpuid);

	# redirect them back to the page they were originally on
	wp_redirect(sanitize_text_field($_GET['goback']));
	die();
}

function sp_rpx_get_wpuid_by_identifier($identifier) {
	$sql = 'SELECT user_id FROM '.SPUSERMETA." WHERE meta_key = 'rpx_identifier' AND meta_value = '$identifier'";
	$r = SP()->DB->select($sql, 'var');
	if ($r) {
		return $r;
	} else {
		return null;
	}
}

function sp_rpx_get_identifier_by_wpuid($wpuid) {
	return get_user_meta($wpuid, 'rpx_identifier', true);
}

function sp_rpx_get_user_login_name($identifier) {
	return 'rpx'.md5($identifier);
}

function sp_rpx_username_taken($username) {
	$user = get_user_by('login', $username);
	return $user != false;
}

# create a new user based on the

function sp_rpx_create_wp_user($auth_info) {
	$p = $auth_info['profile'];
	$rid = $p['identifier'];
	$username = $p['preferredUsername'];
	if (!$username || sp_rpx_username_taken($username)) $username = sp_rpx_get_user_login_name($rid);
	$last_name = null;
	$first_name = null;
	if (!empty($p['name'])) {
		$first_name = $p['name']['givenName'];
		$last_name = $p['name']['familyName'];
	}

	$email = 'dummy@simple-press.com';
	if (!empty($p['email'])) $email = SP()->saveFilters->email($p['email']);

	$userdata = array(
		'user_pass'		 => wp_generate_password(),
		'user_login'	 => $username,
		'display_name'	 => SP()->saveFilters->name($p['displayName']),
		'user_url'		 => $p['url'],
		'user_email'	 => $email,
		'first_name'	 => $first_name,
		'last_name'		 => $last_name,
		'nickname'		 => $p['displayName']);

	# try to create new user
	$wpuid = wp_insert_user($userdata);
	if ($wpuid && !is_wp_error($wpuid)) {
		update_user_meta($wpuid, 'rpx_identifier', $rid);

		# remove temp email?
		if ($email == 'dummy@simple-press.com') SP()->DB->execute('UPDATE '.SPUSERS." SET user_email='' WHERE ID=$wpuid");
	}
	return $wpuid;
}

function sp_rpx_edit_user_page() {
	$user = wp_get_current_user();
	$rpx_identifier = $user->rpx_identifier;
	echo '<h3 id="rpx">'.SP()->primitives->front_text('Sign-in Provider').'</h3>';
	if ($rpx_identifier) {
		# extract the provider domain
		$pieces = explode('/', $rpx_identifier);
		$host = $pieces[2];
		echo '<p>'.SP()->primitives->front_text('You are currently using').' <b>'.$host.'</b> '.SP()->primitives->front_text('as your sign-in provider. You may change this by choosing a different provider or OpenID below and clicking Sign-In.').'</p>';
	} else {
		echo '<p>'.SP()->primitives->front_text('You can sign in to this blog without a password by choosing a provider below.').'</p>';
	}

	$token_url_params = '&attach_to='.$user->ID;
	sp_rpx_iframe('border:1px solid #aaa;padding:2em;background-color:white;', $token_url_params);
}

function sp_rpx_has_curl() {
	return function_exists('curl_init');
}

function sp_rpx_http_post($url, $post_data) {
	$response = wp_remote_post($url, array(
		'method'		 => 'POST',
		'timeout'		 => 45,
		'redirection'	 => 5,
		'sslverify'		 => false,
		'httpversion'	 => '1.0',
		'blocking'		 => true,
		'headers'		 => array(),
		'body'			 => $post_data,
		'cookies'		 => array()
		)
	);
	if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) return '';
	$body = wp_remote_retrieve_body($response);
	if (!$body) return '';
	return $body;
}

function sp_rpx_parse_auth_info($raw) {
	if (function_exists('json_decode')) {
		return json_decode($raw, true);
	} else {
		require_once ABSPATH.'wp-includes/class-json.php';
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $json->decode($raw);
	}
}

function sp_rpx_parse_lookup_rp($raw) {
	if (function_exists('json_decode')) {
		return json_decode($raw, true);
	} else {
		require_once ABSPATH.'wp-includes/class-json.php';
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $json->decode($raw);
	}
}
