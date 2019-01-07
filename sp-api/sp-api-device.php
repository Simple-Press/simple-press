<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2017-06-04 12:32:53 -0500 (Sun, 04 Jun 2017) $
$Rev: 15403 $
*/

/*
* Based upon - Categorizr Version 1.1
* http://www.brettjankord.com/2012/01/16/categorizr-a-modern-device-detection-script/
* Written by Brett Jankord - Copyright © 2011
* Thanks to Josh Eisma for helping with code review
*/
function sp_detect_device() {

	$d = 'desktop';
	$m = 'mobile';
	$t = 'tablet';

	# check for empty $_SERVER['HTTP_USER_AGENT']
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		return $d;
	}

	# Check user agents
	# Set User Agent = $ua
	$ua = $_SERVER['HTTP_USER_AGENT'];

	if (strpos($ua, ' CrOS ')) {
		# ua for a Google Chromebook
		return $d;
	} else if ((preg_match('/iP(a|ro)d/i', $ua)) || (preg_match('/tablet/i', $ua)) && (!preg_match('/RX-34/i', $ua)) || (preg_match('/FOLIO/i', $ua))) {
		# user agent is a Tablet
		return $t;
	} else if ((preg_match('/Linux/i', $ua)) && (preg_match('/Android/i', $ua)) && (!preg_match('/Fennec|mobi|HTC.Magic|HTCX06HT|Nexus.One|SC-02B|fone.945/i', $ua))) {
		# user agent is an Android Tablet
		return $t;
	} else if ((preg_match('/Kindle/i', $ua)) || (preg_match('/Mac.OS/i', $ua)) && (preg_match('/Silk/i', $ua))) {
		# user agent is a Kindle or Kindle Fire
		return $t;
	} else if ((preg_match('/GT-P10|SC-01C|SHW-M180S|SGH-T849|SCH-I800|SHW-M180L|SPH-P100|SGH-I987|zt180|HTC(.Flyer|\_Flyer)|Sprint.ATP51|ViewPad7|pandigital(sprnova|nova)|Ideos.S7|Dell.Streak.7|Advent.Vega|A101IT|A70BHT|MID7015|Next2|nook/i', $ua)) || (preg_match('/MB511/i', $ua)) && (preg_match('/RUTEM/i', $ua))) {
		# user agent is a pre Android 3.0 Tablet
		return $t;
	} else if ((preg_match('/BOLT|Fennec|Iris|Maemo|Minimo|Mobi|mowser|NetFront|Novarra|Prism|RX-34|Skyfire|Tear|XV6875|XV6975|Google.Wireless.Transcoder/i', $ua))) {
		# user agent is unique Mobile User Agent
		return $m;
	} else if ((preg_match('/Opera/i', $ua)) && (preg_match('/Windows.NT.5/i', $ua)) && (preg_match('/HTC|Xda|Mini|Vario|SAMSUNG\-GT\-i8000|SAMSUNG\-SGH\-i9/i', $ua))) {
		# user agent is an odd Opera User Agent - http://goo.gl/nK90K
		return $m;
	} else if ((preg_match('/Windows.(NT|XP|ME|9)/', $ua)) && (!preg_match('/Phone/i', $ua)) || (preg_match('/Win(9|.9|NT)/i', $ua))) {
		# user agent is Windows Desktop
		return $d;
	} else if ((preg_match('/Macintosh|PowerPC/i', $ua)) && (!preg_match('/Silk/i', $ua))) {
		# user agent is Mac Desktop
		return $d;
	} else if ((preg_match('/Linux/i', $ua)) && (preg_match('/X11/i', $ua))) {
		# user agent is a Linux Desktop
		return $d;
	} else if ((preg_match('/Solaris|SunOS|BSD/i', $ua))) {
		# user agent is a Solaris, SunOS, BSD Desktop
		return $d;
	} else if ((preg_match('/Bot|Crawler|Spider|Yahoo|ia_archiver|Covario-IDS|findlinks|DataparkSearch|larbin|Mediapartners-Google|NG-Search|Snappy|Teoma|Jeeves|TinEye/i', $ua)) && (!preg_match('/Mobile/i', $ua))) {
		# user agent is a Desktop BOT/Crawler/Spider
		return $d;
	} else if ((preg_match('/GoogleTV|SmartTV|Internet.TV|NetCast|NETTV|AppleTV|boxee|Kylo|Roku|DLNADOC|CE\-HTML/i', $ua))) {
		# user agent is a smart TV - http://goo.gl/FocDk
		return $d;
	} else if ((preg_match('/Xbox|PLAYSTATION.3|Wii/i', $ua))) {
		# user agent is a TV Based Gaming Console
		return $d;
	} else {
		# assume it is a Mobile Device
		return $m;
	}
}

?>