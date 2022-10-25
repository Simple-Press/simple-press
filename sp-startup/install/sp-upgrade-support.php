<?php
/*
  Simple:Press
  Install & Upgrade Support Routines
  $LastChangedDate: 2018-08-19 14:28:38 -0500 (Sun, 19 Aug 2018) $
  $Rev: 15712 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================
#
# GLOBAL UPDATE/INSTALL ROUTINES
#
# ==========================================

# Called at the end of each upgrade section
function sp_response($section, $die = true, $status = 'success', $error = '', $message = '') {
	global $current_user;

	$response = array(
		'status'	 => '',
		'type'		 => '',
		'section'	 => '',
		'response'	 => '',
		'message'	 => '',
		'error'		 => '');

	# log the build section and status in the response
	echo "Build upgrade section $section executing.  Status: $status <br />";
	if ($status == 'error' && !empty($error)) echo "Error: $error <br />";

	# build the response
	$response['status']		 = $status;
	$response['type']		 = 'upgrade';
	$response['section']	 = $section;
	$response['error']		 = $error;
	$response['message']	 = $message; # intended for use by the last section of an upgrade
	$response['response']	 = ob_get_contents();

	# save as log meta data if table exists! (Need to check if installed yet sadly)
	$go = SP()->DB->tableExists(SPLOGMETA);
	if ($go) {
		$sql = '
			INSERT INTO '.SPLOGMETA." (version, log_data)
			VALUES (
			'".SPVERSION."',
			'".serialize($response)."')";
		SP()->DB->execute($sql);
	}

	ob_end_clean();

	# send the response (mark with tags so we can extract only the response)
	echo '%%%marker%%%';
	print json_encode($response);
	echo '%%%marker%%%';

	# and if this is the last update in build finish off...
	if (SPBUILD == $section) {
		# let plugins know
		do_action('sph_upgrade_done', SPBUILD);
		# Finished Upgrades ===============================================================================
		sp_log_event(SPRELEASE, SPVERSION, SPBUILD, $current_user->ID);

		delete_option('sfInstallID'); # use wp option table
		# and some final cleanuop tasks
		SP()->auths->reset_cache();

		SP()->plugin->clear_css_cache('all');
		SP()->plugin->clear_css_cache('mobile');
		SP()->plugin->clear_css_cache('tablet');

		SP()->plugin->clear_scripts_cache('desktop');
		SP()->plugin->clear_scripts_cache('mobile');
		SP()->plugin->clear_scripts_cache('tablet');

		SP()->cache->flush('all');
		SP()->memberData->reset_plugin_data();

		SP()->options->update('sfforceupgrade', false);

		# force a rewrite rules flush on next page load
		SP()->options->update('sfflushrules', true);
	}

	if ($die) die();
}

# Called to update build nimber wjen no other tasks to perform
# such as the start and end of each release section (since 5.5.1)
function sp_bump_build($build, $section) {
	if ($build < $section) {
		# bump the build number
		sp_response($section);
	}
}

# Silent Upgrade ------------
function sp_silent_upgrade() {
	# build the response
	$response['status']		 = 'success';
	$response['type']		 = 'upgrade';
	$response['section']	 = SPBUILD;
	$response['error']		 = '';
	$response['response']	 = 'silent';

	# save as log meta data if table exists! (Need to check if installed yet sadly)
	$go = SP()->DB->tableExists(SPLOGMETA);
	if ($go) {
		$sql = '
			INSERT INTO '.SPLOGMETA." (version, log_data)
			VALUES (
			'".SPVERSION."',
			'".serialize($response)."')";
		SP()->DB->execute($sql);
	}

	# and if this is the last update in build finish off...
	# let plugins know
	do_action('sph_upgrade_done', SPBUILD);

	sp_log_event(SPRELEASE, SPVERSION, SPBUILD, 0);

	# and some final cleanup tasks
	SP()->auths->reset_cache();

	SP()->plugin->clear_css_cache('all');
	SP()->plugin->clear_css_cache('mobile');
	SP()->plugin->clear_css_cache('tablet');

	SP()->plugin->clear_scripts_cache('desktop');
	SP()->plugin->clear_scripts_cache('mobile');
	SP()->plugin->clear_scripts_cache('tablet');

	SP()->cache->flush('all');

	SP()->memberData->reset_plugin_data();

	SP()->options->update('sfforceupgrade', false);
}

# ==========================================
#
# Specific Upgrade Support Routines
#
# ==========================================
#

