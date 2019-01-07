<?php
/*
Simple:Press
Forum Topic/Post New Post SUpport routines
$LastChangedDate: 2017-04-12 22:10:35 -0500 (Wed, 12 Apr 2017) $
$Rev: 15336 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================================
# NOTIFICATION EMAILS
# ==================================================================================
# Send emails to Admin (if needed) ---------------------------------
function sp_email_notifications($newpost) {
	$out = '';
	$eol = "\r\n";
	$tab = "\t";

	# create the email address list for admin nptifications
	$admins_email = array();
	$admins       = SP()->DB->table(SPMEMBERS, 'admin = 1 OR moderator = 1');
	if ($admins) {
		foreach ($admins as $admin) {
			if ($admin->user_id != $newpost['userid']) {
				$admin_opts = unserialize($admin->admin_options);
				if (empty($admin_opts['sfnotify'])) $admin_opts['sfnotify'] = false;
				if ($admin_opts['sfnotify'] && SP()->auths->get('moderate_posts', $newpost['forumid'], $admin->user_id)) {
					$email                         = SP()->DB->table(SPUSERS, "ID = ".$admin->user_id, 'user_email');
					$admins_email[$admin->user_id] = $email;
				}
			}
		}
	}
	$admins_email = apply_filters('sph_admin_email_addresses', $admins_email);

	# send the emails
	if (!empty($admins_email)) {
		# clean up the content for the plain text email - go get it from database so not in 'save' mode
		$post_content = SP()->DB->table(SPPOSTS, 'post_id='.$newpost['postid'], 'post_content');
		$post_content = SP()->filters->email_content($post_content);

		# create message body
		$msg = SP()->primitives->front_text('New forum post on your site').': '.get_option('blogname').$eol.$eol;
		$msg .= SP()->primitives->front_text('From').': '.$tab.$newpost['postername'].' ['.$newpost['posteremail'].']'.', '.SP()->primitives->front_text('Poster IP').': '.$newpost['posterip'].$eol.$eol;
		$msg .= SP()->primitives->front_text('Group').':'.$tab.SP()->displayFilters->title($newpost['groupname']).$eol;
		$msg .= SP()->primitives->front_text('Forum').':'.$tab.SP()->displayFilters->title($newpost['forumname']).$eol;
		$msg .= SP()->primitives->front_text('Topic').':'.$tab.SP()->displayFilters->title($newpost['topicname']).$eol;
		$msg .= urldecode($newpost['url']).$eol;

		if ($newpost['poststatus'] != 0) {
			$msg .= $eol.SP()->primitives->front_text('*** This post is awaiting moderation ***').$eol;
		}

		$msg .= SP()->primitives->front_text('Post').':'.$eol.$post_content.$eol.$eol;

		foreach ($admins_email as $id => $email) {
			$newmsg  = apply_filters('sph_admin_email', $msg, $newpost, $id, 'admin');
			$replyto = apply_filters('sph_email_replyto', '', $newpost);
			$subject = $newpost['emailprefix'].SP()->primitives->front_text('Forum Post').' - '.get_option('blogname').': ['.SP()->displayFilters->title($newpost['topicname']).']';
			$subject = apply_filters('sph_email_subject', $subject, $newpost);
			sp_send_email($email, $subject, $newmsg, $replyto);
		}
		$out = '- '.SP()->primitives->front_text('Notified: Administrators/Moderators');
	}

	$out = apply_filters('sph_new_post_notifications', $out, $newpost);

	return $out;
}

# = SPAM MATH CHECK ===========================
function sp_check_spammath($forumid) {
	# Spam Check
	$spamtest    = array();
	$spamtest[0] = false;
	if (SP()->auths->get('bypass_math_question', $forumid) == false) {
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
