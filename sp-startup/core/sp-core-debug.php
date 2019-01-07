<?php
/**
 * Core debug support functions.
 * Loaded on all pages for both admin and front end.
 *
 * @since 6.0
 *
 * $LastChangedDate: 2018-11-13 20:41:56 -0600 (Tue, 13 Nov 2018) $
 * $Rev: 15817 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * For SP DEV sites, will output a [D] symbol in upper left corner.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spdebug_admindev() {
	if (defined('SP_DEVFLAG') && SP_DEVFLAG) {
		?>
		<style>
			.wrap h1:before {content: "[D]: "; }
		</style>
		<?php
	}
}

/**
 * Loads debug styles.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spdebug_styles($force = false) {
	if ((defined('SP_DEVFLAG') && SP_DEVFLAG) || $force) {
		?>
		<style>
			.spdebug, #spMainContainer .spdebug { background-color: #CFE7FA; color: #000000; font-family: Verdana; border: 1px solid #444444; font-size: 13px; line-height: 1.2em; margin: 8px; padding: 10px; overflow: auto; word-wrap: break-word;}
			.spdebug pre, #spMainContainer .spdebug pre { background-color: #CFE7FA; color: #000000; }
			.spdebug code, #spMainContainer .spdebug code { font-family: Verdana; }
			.spdebug table td, #spMainContainer .spdebug table td { padding: 0 5px; }
		</style>
		<?php
	}
}

/**
 * If using debug stats, this function will output the stats in the footer.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spdebug_stats() {
	global $spdebug_stats, $spdebug_queries;
	if (defined('SP_DEVFLAG') && SP_DEVFLAG && isset($spdebug_stats)) {
		$out = "\n\n<div class='spdebug'>\n";
		$out .= "\t<table>\n";
		if (isset($spdebug_stats['total_time'])) {
			$out .= "\t\t<tr>\n";
			$out .= "\t\t\t<td>Target section</td>\n";
			$out .= "\t\t\t<td>".$spdebug_stats['total_query']." queries</td>\n";
			$out .= "\t\t\t<td>".number_format($spdebug_stats['total_time'], 3)." seconds</td>\n";
			$out .= "\t\t</tr>\n";
		}
		$out .= "\t\t<tr>\n";
		$out .= "\t\t\t<td>Total page</td>\n";
		$out .= "\t\t\t<td>".(get_num_queries() - $spdebug_queries)." queries</td>\n";
		$out .= "\t\t\t<td>".timer_stop(0)." seconds</td>\n";
		$out .= "\t\t</tr>\n";
		$out .= "\t</table>\n";
		$out .= "</div>\n\n";
		echo $out;
		show_log();
		show_control();
	}
}

/**
 * Starts the debug stats recording.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spdebug_start_stats($showQueries = false) {
	global $spdebug_stats, $spdebug_queries;

	$spdebug_stats['timer'] = 0;
	$mtime = explode(' ', microtime());
	$spdebug_stats['start_time'] = $mtime[1] + $mtime[0];
	$spdebug_stats['start_query'] = get_num_queries();

	$spdebug_queries = $showQueries;
}

/**
 * Stops the debug stats recording.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spdebug_end_stats() {
	global $spdebug_stats, $spdebug_queries;

	$mtime = explode(' ', microtime());
	$time_end = $mtime[1] + $mtime[0];
	$spdebug_stats['end_time'] = $time_end;
	$spdebug_stats['total_time'] = ($spdebug_stats['end_time'] - $spdebug_stats['start_time']);
	$spdebug_stats['end_query'] = get_num_queries();
	$spdebug_stats['total_query'] = ($spdebug_stats['end_query'] - $spdebug_stats['start_query']);

	$spdebug_queries = false;
}

/**
 * Displays a formatted array for debugging.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function ashow($what, $user = -1, $title = '') {
	if ($user == -1 || $user == SP()->user->thisUser->ID) {
		spdebug_styles(true);
		echo '<div class="spdebug">';
		if ($title) echo SP()->primitives->front_text('Inspect').': <strong>'.$title.'</strong><hr>';
		echo '<pre><code>';
		if (is_string($what)) $what = htmlentities($what);
		print_r($what);
		echo '</code></pre>';
		echo '</div>';
	}
}

/**
 * Displays a single variable for debugging.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function vshow($what = 'HERE', $user = -1, $ent = true) {
	if ($user == -1 || $user == SP()->user->thisUser->ID) {
		echo '<div class="spdebug">';
		if ($ent) $what = htmlentities($what);
		echo $what;
		echo '</div>';
	}
}

/**
 * Displays a debug backtrace of the call stack.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function bshow($nest = 3, $user = -1) {
	if ($user == -1 || $user == SP()->user->thisUser->ID) {
		$mess = '';
		$trace = debug_backtrace();
		for ($x = 1; $x < ($nest + 1); $x++) {
			if (isset($trace[$x])) {
				$traceitem = $trace[$x];
				$mess .= '<p><small>';
				$mess .= '<b>'.$traceitem['function']."</b>&nbsp;&nbsp;&nbsp;";
				if (isset($traceitem['file'])) $mess .= '[...'.substr($traceitem['file'], -56).' - ';
				if (isset($traceitem['line'])) $mess .= $traceitem['line'].']</small><br /></p><hr />';
			}
		}
		vshow($mess, -1, false);
	}
}

/**
 * Places a variable in the GLOBALS data space.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function addglobal($data) {
	$GLOBALS['sfdebug'] = $GLOBALS['sfdebug'].$data.'<br />';
}

/**
 * Displays a variable from the global data space.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return string
 */
function showglobal() {
	return $GLOBALS['sfdebug'];
}

/**
 * displays a list of include Simple Press plugin filels for debugging.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function show_includes() {
	echo '<div class="spdebug">';
	echo '<b>SP Files Included on this page</b><br /><br />';

	$filelist = get_included_files();
	foreach ($filelist as $f) {
		if (strpos($f, '/plugins/'.SP_FOLDER_NAME) || strpos($f, '/sp-resources/')) echo strrchr($f, '/').'<br />';
	}
	echo '</div>';
}

/**
 * Creates the test control array.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function set_control($action) {
	global $control;
	$control[] = $action;
}

/**
 * Displays the test control array.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function show_control() {
	global $control;
	if (defined('SP_DEVFLAG') && SP_DEVFLAG == true) {
		if ($control) ashow($control);
	}
}

/**
 * Logs a mysql databse query.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function log_query($sql) {
	global $querylog;
	$mess = '';
	$trace = debug_backtrace();
	$mess .= 'function: '.$trace[2]['function'].' ('.$trace[2]['line'].')';
	for ($x = 3; $x < count($trace); $x++) {
		$thistrace = $trace[$x]['function'];
		if ($thistrace != 'include_once' && $thistrace != 'require_once' && $thistrace != 'require' && $thistrace != 'include') {
			if (isset($trace[$x]['line'])) {
				$mess .= ' -> '.$thistrace.' ('.$trace[$x]['line'].')';
			} else {
				$mess .= ' -> '.$thistrace.' (none)';
			}
		}
	}
	$mess .= '<br /><b>'.$sql.'</b><br /><hr />';

	# write to query log
	$querylog .= $mess;
}

/**
 * Displays the mysql database query log.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function show_log() {
	global $querylog;
	vshow($querylog);
}
