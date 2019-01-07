<?php

/**
 * Core class used for date and time functionality.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * set_timezone()
 * apply_timezone($date, $return, $userid)
 * nice_date($postdate)
 * lastvisit_to_timezone($last, $options)
 * registration_to_timezone($register)
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
class spcDateTime {
	/**
	 * This method sets the server timezone.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function set_timezone() {
		$tz = get_option('timezone_string');
		if (empty($tz) || substr($tz, 0, 3) == 'UTC') $tz = 'UTC';
		date_default_timezone_set($tz);
	}

	/**
	 * This method takes a date and modifies the date to reflect a user's timezone.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string|int $date   php date string or unix timestamp
	 * @param string     $return type of date to return (timestamp, display or mysql)
	 * @param int        $userid user id of user - if empty current user will be used
	 *
	 * @return string|int    updated date to user timezone
	 */
	public function apply_timezone($date, $return = 'display', $userid = 0) {
		# convert timestamp to php date string
		if (!is_numeric($date)) $date = strtotime($date);

		# set timezone onto the started date
		if (!empty($userid)) {
			$opts = SP()->memberData->get($userid, 'user_options');
			$zone = (isset($opts['timezone'])) ? $opts['timezone'] : 0;
		} else {
			$zone = (isset(SP()->user->thisUser->timezone)) ? SP()->user->thisUser->timezone : 0;
		}

		# adjust date for timezone delta
		if (empty($zone)) $zone = 0;
		if ($zone < 0) {
			$date = $date - (abs($zone) * 3600);
		} else if ($zone > 0) {
			$date = $date + (abs($zone) * 3600);
		}

		# Do we need to return as string date?
		if ($return == 'display') {
			$date = date_i18n(SPDATES, $date).' - '.date_i18n(SPTIMES, $date);
		} else if ($return == 'mysql') {
			$date = date('Y-m-d H:i:s', $date);
		}

		return $date;
	}

	/**
	 * This method takes a date and generates a nice display date with a reference to the date
	 * as so many hours/days/weeks/etc in the past.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $postdate date as unix timestamp
	 *
	 * @return string    text display of data with reference to time in past
	 */
	public function nice_date($postdate) {
		# Passed in post date/time
		if (empty($postdate)) return '';

		$unix_date = strtotime($postdate);

		# Get current server date/time and adjust for users local timezone
		$now        = time();
		$now        = $this->apply_timezone($now, 'timestamp');
		$difference = $now - $unix_date;

		$lengths = array('60', '60', '24', '7', '4.35', '12', '10');

		for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
			$difference /= $lengths[$j];
		}
		$difference = round($difference);

		# handle plurality
		if ($difference == 1) {
			$periods = array(SP()->primitives->front_text('second'), SP()->primitives->front_text('minute'), SP()->primitives->front_text('hour'), SP()->primitives->front_text('yesterday'), SP()->primitives->front_text('week'), SP()->primitives->front_text('month'), SP()->primitives->front_text('year'), SP()->primitives->front_text('decade'));
		} else {
			$periods = array(SP()->primitives->front_text('seconds'), SP()->primitives->front_text('minutes'), SP()->primitives->front_text('hours'), SP()->primitives->front_text('days'), SP()->primitives->front_text('weeks'), SP()->primitives->front_text('months'), SP()->primitives->front_text('years'), SP()->primitives->front_text('decades'));
		}

		# Special conditions
		if ($difference == 1 && $j == 3) {
			$nicedate = $periods[$j];
		} else {
			$tense    = SP()->primitives->front_text('ago');
			$nicedate = "$difference $periods[$j] $tense";
			$nicedate = apply_filters('sph_nicedate', $nicedate, $difference, $periods[$j], $tense);
		}

		return $nicedate;
	}

	/**
	 * This method takes a member's last visit time and converts it to server timezone.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int   $last    users last visi time
	 * @param array $options users profile options
	 *
	 * @return string    date in users timezone
	 */
	public function lastvisit_to_timezone($last, $options) {
		# bail if no user timezone set in profile
		if (empty($options['timezone'])) return $last;

		# massage lastvisit date back to server timezone
		$dts  = strtotime($last);
		$zone = $options['timezone'];
		if ($zone < 0) {
			$dts = $dts + (abs($zone) * 3600);
		} else if ($zone > 0) {
			$dts = $dts - (abs($zone) * 3600);
		}

		# put in date string format
		$date = date('Y-m-d H:i:s', $dts);

		return $date;
	}

	/**
	 * This method takes a member's registration date and converts it to server timezone.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int   $register users registration date
	 * @param array $options  users profile options
	 *
	 * @return string    date in users timezone
	 */
	public function registration_to_timezone($register) {
		# massage reg date back to server timezone
		$dts  = strtotime($register);
		$zone = get_option('gmt_offset');
		if ($zone < 0) {
			$dts = $dts - (abs($zone) * 3600);
		} else if ($zone > 0) {
			$dts = $dts + (abs($zone) * 3600);
		}

		# put in date string format
		$date = date('Y-m-d H:i:s', $dts);

		return $date;
	}

	public function format_date($type, $data) {
		if ($type == 'd') {
			return date_i18n(SPDATES, mysql2date('U', $data, false));
		} else {
			return date_i18n(SPTIMES, mysql2date('U', $data, false));
		}
	}
}