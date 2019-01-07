<?php
/*
Simple:Press
Forum Topic/Post New Post SUpport routines
$LastChangedDate: 2017-04-22 11:00:08 -0500 (Sat, 22 Apr 2017) $
$Rev: 15363 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================================
# NOTIFICATION EMAILS
# ==================================================================================
# Send emails to Admin (if needed) ---------------------------------
function sp_email_notifications($newpost) {
	global $spGlobals, $spThisUser, $spVars;
	$out = '';
	$email_status = array();
	$eol = "\r\n";
	$tab = "\t";

	# create the email address list for admin nptifications
	$admins_email = array();
	$admins = spdb_table(SFMEMBERS, 'admin = 1 OR moderator = 1');
	if ($admins) {
		foreach ($admins as $admin) {
			if ($admin->user_id != $newpost['userid']) {
				$admin_opts = unserialize($admin->admin_options);
				if(empty($admin_opts['sfnotify'])) $admin_opts['sfnotify']=false;
				if ($admin_opts['sfnotify'] && sp_get_auth('moderate_posts', $newpost['forumid'], $admin->user_id)) {
					$email = spdb_table(SFUSERS, "ID = ".$admin->user_id, 'user_email');
					$admins_email[$admin->user_id] = $email;
				}
			}
		}
	}
    $admins_email = apply_filters('sph_admin_email_addresses', $admins_email);

	# send the emails
	if (!empty($admins_email)) {
		# clean up the content for the plain text email - go get it from database so not in 'save' mode
		$post_content = spdb_table(SFPOSTS, 'post_id='.$newpost['postid'], 'post_content');
		$post_content = sp_filter_email_content($post_content);

		# create message body
		$msg  = sp_text('New forum post on your site').': '.get_option('blogname').$eol.$eol;
		$msg .= sp_text('From') . ': '.$tab . $newpost['postername'].' ['.$newpost['posteremail'].']'.', '.sp_text('Poster IP').': '.$newpost['posterip'].$eol.$eol;
		$msg .= sp_text('Group'). ':'.$tab . sp_filter_title_display($newpost['groupname']) . $eol;
		$msg .= sp_text('Forum'). ':'.$tab . sp_filter_title_display($newpost['forumname']) . $eol;
		$msg .= sp_text('Topic'). ':'.$tab . sp_filter_title_display($newpost['topicname']) . $eol;
		$msg .= urldecode($newpost['url']) . $eol;
		
		if ($newpost['poststatus'] != 0) {
			$msg .= $eol . sp_text('*** This post is awaiting moderation ***') . $eol;
		}
		
		$msg .= sp_text('Post') . ':'.$eol . $post_content . $eol.$eol;

		foreach ($admins_email as $id=>$email) {
			$newmsg = apply_filters('sph_admin_email', $msg, $newpost, $id, 'admin');
			$replyto = apply_filters('sph_email_replyto', '', $newpost);
            $subject = $newpost['emailprefix'].sp_text('Forum Post').' - '.get_option('blogname').': ['.sp_filter_title_display($newpost['topicname']).']';
            $subject = apply_filters('sph_email_subject', $subject, $newpost);
			sp_send_email($email, $subject, $newmsg, $replyto);
		}
		$out = '- '.sp_text('Notified: Administrators/Moderators');
	}

	$out = apply_filters('sph_new_post_notifications', $out, $newpost);

	return $out;
}

# = SPAM MATH CHECK ===========================
function sp_check_spammath($forumid) {
	# Spam Check
	$spamtest = array();
	$spamtest[0] = false;
	$usemath = true;
	if (sp_get_auth('bypass_math_question', $forumid) == false) {
		$spamtest = sp_spamcheck();
	}
	return $spamtest;
}

# = COOKIE HANDLING ===========================
function sp_write_guest_cookie($guestname, $guestemail) {
	$cookiepath = '/';
	setcookie('guestname_'.COOKIEHASH, $guestname, time() + 30000000, $cookiepath, false);
	setcookie('guestemail_'.COOKIEHASH, $guestemail, time() + 30000000, $cookiepath, false);
	setcookie('sflast_'.COOKIEHASH, time(), time() + 30000000, $cookiepath, false);
}

?>