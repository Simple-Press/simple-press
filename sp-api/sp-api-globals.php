<?php
/*
Simple:Press
Global defs
$LastChangedDate: 2015-12-20 04:40:58 -0600 (Sun, 20 Dec 2015) $
$Rev: 13724 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	Sets up the $spGlobals array and the $spBootCache
#
# ==================================================================

# ------------------------------------------------------
# sp_initialize_globals()
# Version: 5.0
#
# ------------------------------------------------------
function sp_initialize_globals() {
	global $spBootCache, $spStatus, $spGlobals;
	if ($spBootCache['site_auths'] && $spBootCache['ranks'] && $spBootCache['globals']) return;

	if ($spStatus == 'ok') {
		sp_setup_globals();
		$spGlobals['forum-admins'] = sp_get_admins();
		sp_build_site_auths_cache();

        do_action('sph_globals_initialized');
    }
}

# ------------------------------------------------------
# sp_setup_globals()
#
# Version: 5.0
# some global system level defs used here and there
# NOTE: This array is initialized in sf-includes
# ------------------------------------------------------
function sp_setup_globals() {
	global $spGlobals, $current_user, $spBootCache, $spMeta, $spDevice;

	if ($spBootCache['globals'] == true) return;

	# Main admin options
	$spGlobals['admin'] = sp_get_option('sfadminsettings');
	$spGlobals['lockdown'] = sp_get_option('sflockdown');

	$spGlobals['editor'] = 0;

	$spGlobals['defAvatars'] = sp_get_option('spDefAvatars');

	# Display array
	$spGlobals['display'] = sp_get_option('sfdisplay');

	# Current theme data
	$spGlobals['theme'] = sp_get_current_sp_theme();

	# if mobile device then force integrated editor toolbar to on
	if ($spDevice == 'mobile' || $spDevice == 'tablet') {
		$spGlobals['display']['editor']['toolbar'] = true;
		if ($spDevice == 'mobile') {
			$spGlobals['mobile-display'] = sp_get_option('sp_mobile_theme');
		} else {
			$spGlobals['mobile-display'] = sp_get_option('sp_tablet_theme');
		}
	}

	# Load up sfmeta
	$spMeta = spdb_table(SFMETA, 'autoload=1');
	if (!empty($spMeta)) {
		foreach ($spMeta as $s) {
			if (!empty($s)) $spGlobals[$s->meta_type][$s->meta_key] = maybe_unserialize($s->meta_value);
		}
	}

	# create topic/post cache if found to be missing!
	if (!isset($spGlobals['topic_cache']['new']) || empty($spGlobals['topic_cache']['new'])) {
		$spGlobals['topic_cache']['new'] = sp_rebuild_topic_cache();
	}

	# Pre-define a few others
	$spGlobals['canonicalurl'] = false;

    # set up array of disabled forums
    $spGlobals['disabled_forums'] = spdb_select('col', 'SELECT forum_id FROM '.SFFORUMS.' WHERE forum_disabled=1', ARRAY_A);

	$spBootCache['globals'] = true;
}

# ------------------------------------------------------
# sp_filter_globals()
#
# Version: 5.0
# a special function that will allow plugins to filter
# the $spGlobals array with the $spMeta data present
# NOTE: The global $spMeta array is then unset
# ------------------------------------------------------
function sp_filter_globals() {
	global $spGlobals, $spMeta;
	$spGlobals = apply_filters('sph_load_globals', $spGlobals, $spMeta);
//	unset($spMeta);
	$spMeta='';
}

# ------------------------------------------------------
# sf_build_auths_cache()
#
# Version: 5.0
# load auths table into cache
# ------------------------------------------------------
function sp_build_site_auths_cache() {
	global $spGlobals, $spBootCache;

	if ($spBootCache['site_auths'] == true) return;

    $auths = spdb_table(SFAUTHS);
    foreach ($auths as $auth) {
    	# is auth active?
    	if ($auth->active) {
	        # save auth name to auth id mapping for quick ref
	        $spGlobals['auths_map'][$auth->auth_name] = $auth->auth_id;

	        # save off all auth info
	        $spGlobals['auths'][$auth->auth_id] = $auth;
        }
    }

	$spBootCache['site_auths'] = true;
}

# Version: 5.0
function sp_php_overrides() {
	global $is_IIS;

	# hack for some IIS installations
	if ($is_IIS && @ini_get('error_log') == '') @ini_set('error_log', 'syslog');

	# try to increase backtrack limit
	if ((int) @ini_get('pcre.backtrack_limit') < 10000000000) @ini_set('pcre.backtrack_limit', 10000000000);

	# try to increase php memory
	if (function_exists('memory_get_usage') && ((int) @ini_get('memory_limit') < abs(intval('64M')))) @ini_set('memory_limit', '64M');

	# try to increase cpu time
	if ((int) @ini_get('max_execution_time') < 120) @ini_set('max_execution_time', '120');
}

?>