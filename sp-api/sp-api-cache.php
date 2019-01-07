<?php
/*
Simple:Press
cache support
$LastChangedDate: 2017-11-11 15:57:00 -0600 (Sat, 11 Nov 2017) $
$Rev: 15578 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	SITE: This file is loaded at SITE
#	SP Cache Handling Routines
#
# ==================================================================

# ------------------------------------------------------------------
# sp_set_cache_type()
#
# Version: 5.4.2
# Called by other cache functions to set up the data key, life and
# whether the cache type needs deleting when a new add is made
# NOTE: Add new cache types into the case statement as required
#	$type:		string cache type
#	----------	---------------------------------------------
#	search:		search results per user
#	url			holds url - usually for a return redirect
#	bookmark	currently just used for a topic page bookmark
#	group		group view forum query
#	post		post content on a validation/save failure
# ------------------------------------------------------------------
function sp_set_cache_type($type) {
	global $spThisUser;

	$t = array();

	switch($type) {
		case 'xml':
			$t['datakey'] = 'xml';
			$t['lifespan'] = 3600;
			$t['deleteBefore'] = true;
			$t['deleteAfter'] = false;
			break;

		case 'search':
		case 'url':
		case 'bookmark':
		case 'plugin':
			$t['datakey'] = sp_get_ip();
			$t['lifespan'] = 3600;
			$t['deleteBefore'] = true;
			$t['deleteAfter'] = false;
			break;

		case 'group':
			$t['datakey'] = $spThisUser->ID;
			$t['lifespan'] = 3600;
			$t['deleteBefore'] = true;
			$t['deleteAfter'] = false;
			break;

		case 'post':
			$t['datakey'] = sp_get_ip();
			$t['lifespan'] = 120;
			$t['deleteBefore'] = true;
			$t['deleteAfter'] = true;
			break;

		case 'topic':
			$t['datakey'] = sp_get_ip();
			$t['lifespan'] = 120;
			$t['deleteBefore'] = true;
			$t['deleteAfter'] = false;
			break;

		case 'floodcontrol':
			$t['datakey'] = sp_get_ip();
			$t['lifespan'] = sp_get_option('floodcontrol');
			$t['deleteBefore'] = true;
			$t['deleteAfter'] = false;
			break;
	}

	$t['datakey'].= '*'.$type;
	return $t;
}

# ------------------------------------------------------------------
# sp_add_cache()
#
# Version: 5.4.2
# Adds a new record to the sfcache table
#	$type:		cache type
#	$value:		Expected - array
# ------------------------------------------------------------------
function sp_add_cache($type, $value) {
    global $spStatus;

    if ($spStatus != 'ok') return false;

	if (empty($type) || empty($value)) return false;

	$t = sp_set_cache_type($type);

	$now = (time() + $t['lifespan']);

    $sqlcom = ($t['deleteBefore']) ? 'REPLACE' : 'INSERT';
	$sql =  "$sqlcom INTO ".SFCACHE.
			"(cache_id, cache_out, cache)
			VALUES
			('".$t['datakey']."', $now, '".wp_slash(serialize($value))."')";
	spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_get_cache()
#
# Version: 5.4.2
# Gets a record(s) from the sfcache table
#	$type:		The unique cache type name
# ------------------------------------------------------------------
function sp_get_cache($type) {
    global $spStatus;

    if ($spStatus != 'ok') return false;

	$t = sp_set_cache_type($type);

	$sql = 'SELECT cache FROM '.SFCACHE." WHERE cache_id = '".$t['datakey']."'";
	$r = spdb_select('var', $sql);

	if ($t['deleteAfter']) {
		$sql = "DELETE FROM ".SFCACHE." WHERE cache_id = '".$t['datakey']."'";
		spdb_query($sql);
	}

	return wp_unslash(unserialize($r));
}

# ------------------------------------------------------------------
# sp_delete_cache()
#
# Version: 5.5
# Deletes all cache records matching type
# ------------------------------------------------------------------
function sp_delete_cache($type) {
    global $spStatus;

    if ($spStatus != 'ok') return false;

	$t = sp_set_cache_type($type);

	$sql = "DELETE FROM ".SFCACHE." WHERE cache_id = '".$t['datakey']."'";
	spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_clean_cache()
#
# Version: 5.4.2
# Version: 5.5 renamed from sp_delete_cache())
# Deletes all cache records that have timed out
# ------------------------------------------------------------------
function sp_clean_cache() {
    global $spStatus;

    if ($spStatus != 'ok') return false;

	$now = time();
	$sql = 'DELETE FROM '.SFCACHE." WHERE cache_out < $now";
	spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_flush_cache()
#
# Version: 5.4.2
# Deletes all cache records dependent upon type
# ------------------------------------------------------------------
function sp_flush_cache($type='all') {
    global $spStatus;

    if ($spStatus != 'ok') return false;

	if ($type == 'all') {
		spdb_query('TRUNCATE '.SFCACHE);
	} else {
        global $wpdb;
		spdb_query("DELETE FROM ".SFCACHE." WHERE cache_id LIKE '%*".sp_esc_sql($wpdb->esc_like($type))."'");
	}
}

# ------------------------------------------------------------------
# sp_rebuild_topic_cache()
#
# Version: 5.5.1
# Rebuilds the sfmeta new topic cache list
# ------------------------------------------------------------------
function sp_rebuild_topic_cache() {
	$size = sp_get_option('topic_cache');
	if (!isset($size) || $size == 0) sp_add_option('topic_cache', 200);

	$spdb = new spdbComplex;
		$spdb->table          = SFPOSTS;
		$spdb->distinctrow    = true;
		$spdb->fields	      = 'forum_id, topic_id, post_id, post_status';
		$spdb->limits	      = $size;
		$spdb->orderby	      = 'post_id DESC';
	$spdb = apply_filters('sph_topic_cache_select', $spdb);
	$topics = $spdb->select('set', ARRAY_N);

	# let's just be absolutely sure there are no dupes
	$cache = sp_get_sfmeta('topic_cache', 'new');
	if (!empty($cache) && count($cache) > 1) {
		spdb_query("DELETE FROM ".SFMETA." WHERE meta_type='topic_cache'");
	}
	# and then add the new, single record back
	if ($topics) sp_add_sfmeta('topic_cache', 'new', $topics, true);

	# Delete group level cache for good measure
	spdb_query("DELETE FROM ".SFCACHE." WHERE cache_id LIKE '%*group'");
	return $topics;
}

?>