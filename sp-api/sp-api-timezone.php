<?php
/*
Simple:Press
timezone support
$LastChangedDate: 2015-03-03 01:35:28 -0600 (Tue, 03 Mar 2015) $
$Rev: 12531 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	SITE: This file is loaded at SITE
#	SP Timezone Handling Routines
#
# ==================================================================

# ------------------------------------------------------------------
# sp_set_server_timezone()
#
# Version: 5.0
# Run to ensure that the system timezone is correctly set to
# the WP server timezone setting. Some plugins seem to reset this
# and not set it back again when they have done their stuff.
# ------------------------------------------------------------------
function sp_set_server_timezone() {
	$tz = get_option('timezone_string');
	if (empty($tz) || substr($tz, 0, 3) == 'UTC') $tz = 'UTC';
	date_default_timezone_set($tz);
}

# ------------------------------------------------------------------
# sp_apply_timezone()
#
# Version: 5.0
# Massages a date passed by the current users timezone
#	$date:	Can be either a string date or a timestamp
#	$return:
#	'display' = pre-formtatted display date to users SP settings (default)
#	'timestamp' = returned as unix timestamp
#	'mysql' = returned as a mysql formatted date
#	$userid = if passed will use usserid's timezone else current user
# ------------------------------------------------------------------
function sp_apply_timezone($date, $return='display', $userid=0) {
	global $spThisUser;

	# Do we have a timestamp?
	if (!is_numeric($date)) $date = strtotime($date);

	# set timezone onto the started date
	if ($userid) {
		$opts = sp_get_member_item($userid, 'user_options');
		$zone = (isset($opts['timezone'])) ? $opts['timezone'] : 0;
	} else {
		$zone = (isset($spThisUser->timezone)) ? $spThisUser->timezone : 0;
	}
	if (empty($zone)) $zone = 0;

	if ($zone < 0) $date = $date - (abs($zone) * 3600);
	if ($zone > 0) $date = $date + (abs($zone) * 3600);
	# Do we need to return as string date?
	if ($return == 'display') $date = date_i18n(SFDATES, $date).' - '.date_i18n(SFTIMES, $date);
	if ($return == 'mysql') $date = date('Y-m-d H:i:s', $date);

	return $date;
}

# ------------------------------------------------------------------
# sp_nicedate()
#
# Version: 5.0
# Displays the date as so many hours/days/weeks etc in the past
#	$postdate:		Normal mysql date/time timestamp
# ------------------------------------------------------------------
function sp_nicedate($postdate) {
	# Passed in post date/time
	if (empty($postdate)) {
		return;
	} else {
		$unix_date = strtotime($postdate);
	}

	# Get current server date.time and adjust for users local timezone
	$now = time();
	$now = sp_apply_timezone($now, 'timestamp');
	$difference = $now - $unix_date;

	# set up period labels
	$periods = array(sp_text('second'), sp_text('minute'), sp_text('hour'), sp_text('yesterday'), sp_text('week'), sp_text('month'), sp_text('year'), sp_text('decade'));
	$lengths = array('60', '60', '24', '7', '4.35', '12', '10');
	$tense = sp_text('ago');

	for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
		$difference/= $lengths[$j];
	}
	$difference = round($difference);

	if ($difference != 1) {
		$periods = array(sp_text('seconds'), sp_text('minutes'), sp_text('hours'), sp_text('days'), sp_text('weeks'), sp_text('months'), sp_text('years'), sp_text('decades'));
	}
	# Special conditions
	if ($difference == 1 && $j == 3) {
		return $periods[$j];
	} else {
		$nd = "$difference $periods[$j] {$tense}";
		return apply_filters('sph_nicedate', $nd, $difference, $periods[$j], $tense);
	}
}

# ------------------------------------------------------------------
# sp_member_lastvisit_to_server_tz()
#
# Version: 5.0
# Converts a menbers last visit date to timezone of server
#	$d		last visit date
#	$opt	The members user_options array
# ------------------------------------------------------------------
function sp_member_lastvisit_to_server_tz($d, $opt) {
	if (!isset($opt['timezone'])) return $d;

	# massage lastvisit date back to server timezone
	if ($opt['timezone']==0 || empty($opt['timezone'])) return $d;
	$dts = strtotime($d);
	$z = $opt['timezone'];
	if ($z < 0) $dts = $dts + (abs($z) * 3600);
	if ($z > 0) $dts = $dts - (abs($z) * 3600);
	$d = date('Y-m-d H:i:s', $dts);
	return $d;
}

# ------------------------------------------------------------------
# sp_member_registration_to_server_tz()
#
# Version: 5.0
# Converts a menbers registration date to timezone of server
#	$d		registration date
# ------------------------------------------------------------------
function sp_member_registration_to_server_tz($d) {
	# massage reg date back to server timezone
	$dts = strtotime($d);
	$z = get_option('gmt_offset');
	if ($z < 0) $dts = $dts - (abs($z) * 3600);
	if ($z > 0) $dts = $dts + (abs($z) * 3600);
	$d = date('Y-m-d H:i:s', $dts);
	return $d;
}

?>