<?php
/*
Simple:Press
Ajax call save Profile data
$LastChangedDate: 2018-11-02 16:17:56 -0500 (Fri, 02 Nov 2018) $
$Rev: 15797 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

// ========= NEEDS TO BE TURNED OFF UNTIL ALL PLUGIN ADMIN FORMS ARE CHANGED TO USE THE NEW NONCE CODE
//if (!sp_nonce('profile-save')) die();
// ===================================================================================================

# workaround function for php installs without exif.  leave original function since this is slower.
if (!function_exists('exif_imagetype')) {
	function exif_imagetype($filename) {
		if ((list($width, $height, $type, $attr) = @getimagesize(str_replace(' ', '%20', $filename))) !== false) return $type;

		return false;
	}
}

do_action('sph_ProfileSaveStart');

$message = sp_UpdateProfile();

$response            = array('type' => '', 'message' => '');
$response['type']    = $message['type'];
$response['message'] = $message['text'];

print json_encode($response);

die();

##############################

function sp_UpdateProfile() {
	# make sure nonce is there
	check_admin_referer('forum-profile', 'forum-profile');

	$message = array();

	# dont update forum if its locked down
	if (SP()->core->forumData['lockdown']) {
		$message['type'] = 'error';
		$message['text'] = SP()->primitives->front_text('This forum is currently locked - access is read only - profile not updated');

		return $message;
	}

	# do we have a form to update?
	if (isset($_GET['form'])) {
		$thisForm = SP()->filters->str($_GET['form']);
	} else {
		$message['type'] = 'error';
		$message['text'] = SP()->primitives->front_text('Profile update aborted - no valid form');

		return $message;
	}

	# do we have an actual user to update?
	if (isset($_GET['userid'])) {
		$thisUser = SP()->filters->integer($_GET['userid']);
	} else {
		$message['type'] = 'error';
		$message['text'] = SP()->primitives->front_text('Profile update aborted - no valid user');

		return $message;
	}

	# Check the user ID for current user of admin edit
	if ($thisUser != SP()->user->thisUser->ID && !SP()->user->thisUser->admin) {
		$message['type'] = 'error';
		$message['text'] = SP()->primitives->front_text('Profile update aborted - no valid user');

		return $message;
	}

	if (isset(SP()->user->thisUser->sp_change_pw) && SP()->user->thisUser->sp_change_pw) {
		$pass1 = $pass2 = '';
		if (isset($_POST['pass1'])) $pass1 = SP()->filters->str($_POST['pass1']);
		if (isset($_POST['pass2'])) $pass2 = SP()->filters->str($_POST['pass2']);
		if (empty($pass1) || empty($pass2) || ($pass1 != $pass2)) {
			$message['type'] = 'error';
			$message['text'] = SP()->primitives->front_text('Cannot save profile until password has been changed');

			return $message;
		}
	}

	# form save filter
	$thisForm = apply_filters('sph_profile_save_thisForm', $thisForm);

	# valid save attempt, so lets process the save
	switch ($thisForm) {
		case 'show-memberships': # update memberships
			# any usergroup removals?
			if (isset($_POST['usergroup_leave'])) {
				foreach ($_POST['usergroup_leave'] as $membership) {
					SP()->user->remove_membership(SP()->filters->str($membership), $thisUser);
				}
			}

			# any usergroup joins?
			if (isset($_POST['usergroup_join'])) {
				foreach ($_POST['usergroup_join'] as $membership) {
					SP()->user->add_membership(SP()->filters->integer($membership), $thisUser);
				}
			}

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileMemberships', $message, $thisUser);

			# output update message
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Memberships updated');
			}

			break;

		case 'account-settings': # update account settings
			# check for password update
			$pass1 = $pass2 = '';
			if (isset($_POST['pass1'])) $pass1 = SP()->filters->str($_POST['pass1']);
			if (isset($_POST['pass2'])) $pass2 = SP()->filters->str($_POST['pass2']);
			if (!empty($pass1) || !empty($pass2)) {
				if ($pass1 != $pass2) {
					$message['type'] = 'error';
					$message['text'] = SP()->primitives->front_text('Please enter the same password in the two password fields');

					return $message;
				} else {
					# update the password
					$user            = new stdClass();
					$user->ID        = (int)$thisUser;
					$user->user_pass = $pass1;
					wp_update_user(get_object_vars($user));
					if (isset(SP()->user->thisUser->sp_change_pw) && SP()->user->thisUser->sp_change_pw) delete_user_meta(SP()->user->thisUser->ID, 'sp_change_pw');
				}
			}

			# now check the email is valid and unique
			$update = apply_filters('sph_ProfileUserEmailUpdate', true);
			if ($update) {
				$curEmail = SP()->saveFilters->email($_POST['curemail']);
				$email    = SP()->saveFilters->email($_POST['email']);
				if ($email != $curEmail) {
					if (empty($email)) {
						$message['type'] = 'error';
						$message['text'] = SP()->primitives->front_text('Please enter a valid email address');

						return $message;
					} elseif (($owner_id = email_exists($email)) && ($owner_id != $thisUser)) {
						$message['type'] = 'error';
						$message['text'] = SP()->primitives->front_text('The email address is already registered. Please choose another one');

						return $message;
					}

					# save new email address
					$sql = 'UPDATE '.SPUSERS." SET user_email='$email' WHERE ID=".$thisUser;
					SP()->DB->execute($sql);
				}
			}

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileSettings', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Account settings updated');
			}

			break;

		case 'edit-profile': # update profile settings
			# validate any username change
			$update = apply_filters('sph_ProfileUserDisplayNameUpdate', true);
			if ($update) {
				$spProfile = SP()->options->get('sfprofile');
				if ($spProfile['nameformat'] || SP()->user->thisUser->admin) {
					$display_name = (!empty($_POST['display_name'])) ? sanitize_text_field(trim($_POST['display_name'])) : SP()->DB->table(SPUSERS, "ID=$thisUser", 'user_login');
					$display_name = SP()->saveFilters->name($display_name);

					# make sure display name isnt already used
					if (sanitize_text_field($_POST['oldname']) != $display_name) {
						$records = SP()->DB->table(SPMEMBERS, "display_name='$display_name'");
						if ($records) {
							foreach ($records as $record) {
								if ($record->user_id != $thisUser) {
									$message['type'] = 'error';
									$message['text'] = $display_name.' '.SP()->primitives->front_text('is already in use - please choose a different display name');

									return $message;
								}
							}
						}

						# validate display name
						$errors             = new WP_Error();
						$user               = new stdClass();
						$user->display_name = $display_name;
						SP()->user->validate_display_name($errors, true, $user);
						if ($errors->get_error_codes()) {
							$message['type'] = 'error';
							$message['text'] = SP()->primitives->front_text('The display name you have chosen is not allowed on this site');

							return $message;
						}

						# now save the display name
						SP()->memberData->update($thisUser, 'display_name', $display_name);

						# Update new users list with changed display name
						SP()->user->update_new_name(SP()->saveFilters->name($_POST['oldname']), $display_name);

						# do we need to sync display name with wp?
						$options = SP()->memberData->get($thisUser, 'user_options');
						if ($options['namesync']) SP()->DB->execute('UPDATE '.SPUSERS.' SET display_name="'.$display_name.'" WHERE ID='.$thisUser);
					}
				}
			}

			# save the url
			$update = apply_filters('sph_ProfileUserWebsiteUpdate', true);
			if ($update) {
				$url = SP()->saveFilters->url($_POST['website']);
				$sql = 'UPDATE '.SPUSERS.' SET user_url="'.$url.'" WHERE ID='.$thisUser;
				SP()->DB->execute($sql);
			}

			# update first name, last name, location and biorgraphy
			$update = apply_filters('sph_ProfileUserFirstNameUpdate', true);
			if ($update) update_user_meta($thisUser, 'first_name', SP()->saveFilters->name(trim($_POST['first_name'])));
			$update = apply_filters('sph_ProfileUserLastNameUpdate', true);
			if ($update) update_user_meta($thisUser, 'last_name', SP()->saveFilters->name(trim($_POST['last_name'])));
			$update = apply_filters('sph_ProfileUserLocationUpdate', true);
			if ($update) update_user_meta($thisUser, 'location', SP()->saveFilters->title(trim($_POST['location'])));
			$update = apply_filters('sph_ProfileUserBiographyUpdate', true);
			if ($update) update_user_meta($thisUser, 'description', SP()->saveFilters->kses($_POST['description']));

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileProfile', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Profile settings updated');
			}

			break;

		case 'edit-identities': # update identity settings
			# update the user identities
			$update = apply_filters('sph_ProfileUserAIMUpdate', true);
			
			$aim        = sanitize_text_field(filter_input(INPUT_POST, 'aim', FILTER_UNSAFE_RAW));
			$yim        = sanitize_text_field(filter_input(INPUT_POST, 'yim', FILTER_UNSAFE_RAW));
			$jabber     = sanitize_text_field(filter_input(INPUT_POST, 'jabber', FILTER_UNSAFE_RAW));
			$msn        = sanitize_text_field(filter_input(INPUT_POST, 'msn', FILTER_UNSAFE_RAW));
			$icq        = sanitize_text_field(filter_input(INPUT_POST, 'icq', FILTER_UNSAFE_RAW));
			$skype      = sanitize_text_field(filter_input(INPUT_POST, 'skype', FILTER_UNSAFE_RAW));
			$facebook   = sanitize_text_field(filter_input(INPUT_POST, 'facebook', FILTER_UNSAFE_RAW));
			$myspace    = sanitize_text_field(filter_input(INPUT_POST, 'myspace', FILTER_UNSAFE_RAW));
			$twitter    = sanitize_text_field(filter_input(INPUT_POST, 'twitter', FILTER_UNSAFE_RAW));
			$linkedin   = sanitize_text_field(filter_input(INPUT_POST, 'linkedin', FILTER_UNSAFE_RAW));
			$youtube    = sanitize_text_field(filter_input(INPUT_POST, 'youtube', FILTER_UNSAFE_RAW));
			$googleplus = sanitize_text_field(filter_input(INPUT_POST, 'googleplus', FILTER_UNSAFE_RAW));
			$instagram  = sanitize_text_field(filter_input(INPUT_POST, 'instagram', FILTER_UNSAFE_RAW));
			
			if ($update) update_user_meta($thisUser, 'aim', SP()->saveFilters->title(trim($aim)));
			$update = apply_filters('sph_ProfileUserYahooUpdate', true);
			if ($update) update_user_meta($thisUser, 'yim', SP()->saveFilters->title(trim($yim)));
			$update = apply_filters('sph_ProfileUserGoogleUpdate', true);
			if ($update) update_user_meta($thisUser, 'jabber', SP()->saveFilters->title(trim($jabber)));
			$update = apply_filters('sph_ProfileUserMSNUpdate', true);
			if ($update) update_user_meta($thisUser, 'msn', SP()->saveFilters->title(trim($msn)));
			$update = apply_filters('sph_ProfileUserICQUpdate', true);
			if ($update) update_user_meta($thisUser, 'icq', SP()->saveFilters->title(trim($icq)));
			$update = apply_filters('sph_ProfileUserSkypeUpdate', true);
			if ($update) update_user_meta($thisUser, 'skype', SP()->saveFilters->title(trim($skype)));
			$update = apply_filters('sph_ProfileUserFacebookUpdate', true);
			if ($update) update_user_meta($thisUser, 'facebook', SP()->saveFilters->title(trim($facebook)));
			$update = apply_filters('sph_ProfileUserMySpaceUpdate', true);
			if ($update) update_user_meta($thisUser, 'myspace', SP()->saveFilters->title(trim($myspace)));
			$update = apply_filters('sph_ProfileUserTwitterUpdate', true);
			if ($update) update_user_meta($thisUser, 'twitter', SP()->saveFilters->title(trim($twitter)));
			$update = apply_filters('sph_ProfileUserLinkedInUpdate', true);
			if ($update) update_user_meta($thisUser, 'linkedin', SP()->saveFilters->title(trim($linkedin)));
			$update = apply_filters('sph_ProfileUserYouTubeUpdate', true);
			if ($update) update_user_meta($thisUser, 'youtube', SP()->saveFilters->title(trim($youtube)));
			$update = apply_filters('sph_ProfileUserGooglePlusUpdate', true);
			if ($update) update_user_meta($thisUser, 'googleplus', SP()->saveFilters->title(trim($googleplus)));
			$update = apply_filters('sph_ProfileUserInstagramUpdate', true);		
			if ($update) update_user_meta($thisUser, 'instagram', SP()->saveFilters->title(trim($instagram)));
			
			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileIdentities', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Identities updated');
			}

			break;

		case 'avatar-upload': # upload avatar
			# did we get an avatar to upload?
			if (empty($_FILES['avatar-upload']['name'])) {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Sorry, the avatar filename was empty');

				return $message;
			}

			# Verify the file extension
			$uploaddir = SP_STORE_DIR.'/'.SP()->plugin->storage['avatars'].'/';
			$filename  = basename($_FILES['avatar-upload']['name']);
			$path      = pathinfo($filename);
			$ext       = strtolower($path['extension']);
			if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'gif' && $ext != 'png') {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Sorry, only JPG, JPEG, PNG, or GIF files are allowed');

				return $message;
			}

			# check image file mimetype
			$mimetype = exif_imagetype($_FILES['avatar-upload']['tmp_name']);
			if (empty($mimetype) || $mimetype == 0 || $mimetype > 3) {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Sorry, the avatar file is an invalid format');

				return $message;
			}

			# make sure file extension and mime type actually match
			if (($mimetype == 1 && $ext != 'gif') || ($mimetype == 2 && ($ext != 'jpg' && $ext != 'jpeg')) || ($mimetype == 3 && $ext != 'png')) {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Sorry, the file mime type does not match file extension');

				return $message;
			}

			# Clean up file name just in case
			$filename   = date('U').SP()->saveFilters->filename(basename($_FILES['avatar-upload']['name']));
			$uploadfile = $uploaddir.$filename;

			# check for existence
			if (file_exists($uploadfile)) {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Sorry, the avatar file already exists');

				return $message;
			}

			# check file size against limit if provided
			$spAvatars = SP()->options->get('sfavatars');
			if ($_FILES['avatar-upload']['size'] > $spAvatars['sfavatarfilesize']) {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Sorry, the avatar file exceeds the maximum allowed size');

				return $message;
			}

			# valid avatar, so try moving the uploaded file to the avatar storage directory
			if (move_uploaded_file($_FILES['avatar-upload']['tmp_name'], $uploadfile)) {
				@chmod("$uploadfile", 0644);

				# do we need to resize?
				$sfavatars = SP()->options->get('sfavatars');
				if ($sfavatars['sfavatarresize']) {
					$editor = wp_get_image_editor($uploadfile);
					if (is_wp_error($editor)) {
						@unlink($uploadfile);
						$message['type'] = 'error';
						$message['text'] = SP()->primitives->front_text('Sorry, there was a problem resizing the avatar');

						return $message;
					} else {
						$editor->resize($sfavatars['sfavatarsize'], $sfavatars['sfavatarsize'], true);
						$imageinfo = $editor->save($uploadfile);
						$filename  = $imageinfo['file'];
					}
				}

				# update member avatar data
				$avatar             = SP()->memberData->get($thisUser, 'avatar');
				$avatar['uploaded'] = $filename;
				SP()->memberData->update($thisUser, 'avatar', $avatar);
			} else {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Sorry, the avatar file could not be moved to the avatar storage location');

				return $message;
			}

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileAvatarUpload', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Uploaded avatar updated');
			}

			break;

		case 'avatar-pool': # pool avatar
			# get pool avatar name
			$filename = SP()->saveFilters->filename($_POST['spPoolAvatar']);

			# error if no pool avatar provided
			if (empty($filename)) {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Sorry, you must select a pool avatar before trying to save it');

				return $message;
			}

			# save the pool avatar
			$avatar         = SP()->memberData->get($thisUser, 'avatar');
			$avatar['pool'] = $filename;
			SP()->memberData->update($thisUser, 'avatar', $avatar);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileAvatarPool', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Pool avatar updated');
			}

			break;

		case 'avatar-remote': # remote avatar
			# get remote avatar name
			$filename         = SP()->saveFilters->url($_POST['spAvatarRemote']);
			$avatar           = SP()->memberData->get($thisUser, 'avatar');
			$avatar['remote'] = $filename;
			SP()->memberData->update($thisUser, 'avatar', $avatar);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileAvatarRemote', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Remote avatar updated');
			}

			break;

		case 'edit-signature': # save signature
			# Check if maxmium links has been exceeded
			$numLinks  = substr_count($_POST['postitem'], '</a>');
			$spFilters = SP()->options->get('sffilters');
			if (!SP()->auths->get('create_links', 'global', $thisUser) && $numLinks > 0 && !SP()->user->thisUser->admin) {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('You are not allowed to put links in signatures');

				return $message;
			}
			if (SP()->auths->get('create_links', 'global', $thisUser) && $spFilters['sfmaxlinks'] != 0 && $numLinks > $spFilters['sfmaxlinks'] && !SP()->user->thisUser->admin) {
				$message['type'] = 'error';
				$message['text'] = SP()->primitives->front_text('Maximum number of allowed links exceeded in signature').': '.$spFilters['sfmaxlinks'].' '.SP()->primitives->front_text('allowed');

				return $message;
			}
			$sig = SP()->saveFilters->text($_POST['postitem']);

			SP()->memberData->update($thisUser, 'signature', $sig);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileSignature', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Signature updated');
			}

			break;

		case 'edit-photos': # save photos
			$photos           = array();
			$spProfileOptions = SP()->options->get('sfprofile');
			for ($x = 0; $x < $spProfileOptions['photosmax']; $x++) {
				if (!empty($_POST['photo'.$x])) {
					$photos[] = SP()->saveFilters->url($_POST['photo'.$x]);
				}
			}
			update_user_meta($thisUser, 'photos', $photos);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfilePhotos', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Photos updated');
			}

			break;

		case 'edit-global-options': # save global options
			$options               = SP()->memberData->get($thisUser, 'user_options');
			$options['hidestatus'] = (isset($_POST['hidestatus'])) ? true : false;
			$update                = apply_filters('sph_ProfileUserSyncNameUpdate', true);
			if ($update) $options['namesync'] = (isset($_POST['namesync'])) ? true : false;
			SP()->memberData->update($thisUser, 'user_options', $options);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileGlobalOptions', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Global options updated');
			}

			break;

		case 'edit-posting-options': # save posting options
			$update = apply_filters('sph_ProfileUserEditorUpdate', true);
			if ($update) {
				$options = SP()->memberData->get($thisUser, 'user_options');
				if (isset($_POST['editor'])) $options['editor'] = SP()->filters->integer($_POST['editor']);
				SP()->memberData->update($thisUser, 'user_options', $options);
			}

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfilePostingOptions', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Posting options updated');
			}

			break;

		case 'edit-display-options': # save display options
			$options = SP()->memberData->get($thisUser, 'user_options');
			if (isset($_POST['timezone'])) {
				if (preg_match('/^UTC[+-]/', sanitize_text_field($_POST['timezone']))) {
					# correct for manual UTC offets
					$userOffset = preg_replace('/UTC\+?/', '', SP()->filters->str($_POST['timezone'])) * 3600;
				} else {
					# get timezone offset for user
					$date_time_zone_selected = new DateTimeZone(SP()->filters->str($_POST['timezone']));
					$userOffset              = timezone_offset_get($date_time_zone_selected, date_create());
				}

				# get timezone offset for server based on wp settings
				$wptz = get_option('timezone_string');
				if (empty($wptz)) {
					$serverOffset = get_option('gmt_offset');
				} else {
					$date_time_zone_selected = new DateTimeZone($wptz);
					$serverOffset            = timezone_offset_get($date_time_zone_selected, date_create());
				}

				# calculate time offset between user and server
				$options['timezone']        = (int)round(($userOffset - $serverOffset) / 3600, 2);
				$options['timezone_string'] = SP()->filters->str($_POST['timezone']);
			} else {
				$options['timezone']        = 0;
				$options['timezone_string'] = 'UTC';
			}

			if (isset($_POST['unreadposts'])) {
				$sfcontrols             = SP()->options->get('sfcontrols');
				$options['unreadposts'] = is_numeric($_POST['unreadposts']) ? max(min(SP()->filters->integer($_POST['unreadposts']), $sfcontrols['sfmaxunreadposts']), 0) : $sfcontrols['sfdefunreadposts'];
			}

			$options['topicASC'] = isset($_POST['topicASC']);
			$options['postDESC'] = isset($_POST['postDESC']);

			SP()->memberData->update($thisUser, 'user_options', $options);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileDisplayOptions', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = SP()->primitives->front_text('Display options updated');
			}

			break;

		default:
			break;
	}

	# let plugins do their thing on success
	$message = apply_filters('sph_ProfileFormSave_'.$thisForm, $message, $thisUser, $thisForm);
	do_action('sph_UpdateProfile', $thisUser, $thisForm);

	# reset the plugin_data just in case
	SP()->memberData->reset_plugin_data($thisUser);

	# done saving - return the messages
	return $message;
}
