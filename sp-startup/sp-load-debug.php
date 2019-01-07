<?php
/*
Simple:Press
Debug
$LastChangedDate: 2015-07-01 05:25:22 -0500 (Wed, 01 Jul 2015) $
$Rev: 13064 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

add_action('admin_head', 'spdebug_admindev');
add_action('wp_head', 'spdebug_styles');
add_action('wp_footer', 'spdebug_stats');

function spdebug_admindev() {
	if (defined('SP_DEVFLAG') && SP_DEVFLAG == true) {
?>
		<style type="text/css">
		.wrap h1:before {content: "[D]: "; }
		</style>
<?php
	}
}

function spdebug_styles($force=false) {
	if ((defined('SP_DEVFLAG') && SP_DEVFLAG == true) || $force == true) {
?>
		<style type="text/css">
		.spdebug, #spMainContainer .spdebug { background-color: #CFE7FA; color: #000000; font-family: Verdana; border: 1px solid #444444; font-size: 13px; line-height: 1.2em; margin: 8px; padding: 10px; overflow: auto; word-wrap: break-word;}
		.spdebug pre, #spMainContainer .spdebug pre { background-color: #CFE7FA; color: #000000; }
		.spdebug code, #spMainContainer .spdebug code { font-family: Verdana; }
		.spdebug table td, #spMainContainer .spdebug table td { padding: 0 5px; }
		</style>
<?php
	}
}

function spdebug_stats() {
	global $wpdb, $spdebug_stats, $spdebug_queries;
	if (defined('SP_DEVFLAG') && SP_DEVFLAG == true && isset($spdebug_stats)) {
		$out = "\n\n<div class='spdebug'>\n";
		$out.= "\t<table>\n";
		if (isset($spdebug_stats['total_time'])) {
			$out.= "\t\t<tr>\n";
			$out.= "\t\t\t<td>Target section</td>\n";
			$out.= "\t\t\t<td>".$spdebug_stats['total_query']." queries</td>\n";
			$out.= "\t\t\t<td>".number_format($spdebug_stats['total_time'], 3). " seconds</td>\n";
			$out.= "\t\t</tr>\n";
		}
		$out.= "\t\t<tr>\n";
		$out.= "\t\t\t<td>Total page</td>\n";
		$out.= "\t\t\t<td>".(get_num_queries() - $spdebug_queries)." queries</td>\n";
		$out.= "\t\t\t<td>".timer_stop(0)." seconds</td>\n";
		$out.= "\t\t</tr>\n";
		$out.= "\t</table>\n";
		$out.= "</div>\n\n";
		echo $out;
		show_log();
		show_control();
	}
}

# ------------------------------------------
# starts partial query count
# ------------------------------------------
function spdebug_start_stats($showQueries=false) {
	global $spdebug_stats, $spdebug_queries;

	$spdebug_stats['timer'] = 0;
	$mtime = explode(' ', microtime());
	$spdebug_stats['start_time'] = $mtime[1] + $mtime[0];
	$spdebug_stats['start_query'] = get_num_queries();

	$spdebug_queries = $showQueries;
}

# ------------------------------------------
# ends partial query count
# ------------------------------------------
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

# ------------------------------------------
# display a formatted array
# ------------------------------------------
function ashow($what, $user=-1, $title='') {
	global $spThisUser;
	if ($user == -1 || $user == $spThisUser->ID) {
		spdebug_styles(true);
		echo '<div class="spdebug">';
		if ($title) echo sp_text('Inspect').': <strong>'.$title.'</strong><hr>';
		echo '<pre><code>';
        if (is_string($what)) $what = htmlentities($what);
		print_r($what);
		echo '</code></pre>';
		echo '</div>';
	}
}

# ------------------------------------------
# display an individual variable
# ------------------------------------------
function vshow($what='HERE', $user=-1, $ent=true) {
	global $spThisUser;
	if ($user == -1 || $user == $spThisUser->ID) {
		echo '<div class="spdebug">';
        if ($ent) $what = htmlentities($what);
		echo $what;
		echo '</div>';
	}
}

# ------------------------------------------
# display backtrace and vshow result
# ------------------------------------------
function bshow($nest=3, $user=-1) {
	global $spThisUser;
	if ($user == -1 || $user == $spThisUser->ID) {
		$mess = '';
		$trace = debug_backtrace();
		for ($x = 1; $x < ($nest + 1); $x++) {
            if (isset($trace[$x])) {
    			$traceitem = $trace[$x];
    			$mess.= '<p><small>';
    			$mess.= '<b>'.$traceitem['function']."</b>&nbsp;&nbsp;&nbsp;";
    			if (isset($traceitem['file'])) $mess.= '[...'.substr($traceitem['file'], -56).' - ';
    			if (isset($traceitem['line'])) $mess.= $traceitem['line'].']</small><br /></p><hr />';
            }
		}
		vshow($mess, -1, false);
	}
}

# ------------------------------------------
# places a value in global 'sfdebug'
# ------------------------------------------
function addglobal($data) {
	$GLOBALS['sfdebug'] = $GLOBALS['sfdebug'].$data.'<br />';
}

# ------------------------------------------
# returns global 'sfdebug' for display
# ------------------------------------------
function showglobal() {
	return $GLOBALS['sfdebug'];
}

# ------------------------------------------
# display SPF files included
# ------------------------------------------
function show_includes() {
	echo '<div class="spdebug">';
	echo '<b>SP Files Included on this page</b><br /><br />';

	$filelist = get_included_files();
	foreach ($filelist as $f) {
		if (strpos($f, '/plugins/simple-press') || strpos($f, '/sp-resources/')) echo strrchr($f , '/' ).'<br />';
	}
	echo '</div>';
}

# ------------------------------------------
# Create test control array
# ------------------------------------------
function set_control($action) {
	global $control;
	$control[] = $action;
}

# ------------------------------------------
# display test control array
# ------------------------------------------
function show_control() {
	global $control;
	if (defined('SP_DEVFLAG') && SP_DEVFLAG == true) {
		if ($control) ashow($control);
	}
}

# collect sql data
function log_query($sql) {
	global $querylog;
	$mess = '';
	$trace = debug_backtrace();
	$mess.= 'function: '.$trace[2]['function'].' ('.$trace[2]['line'].')';
	for ($x = 3; $x < count($trace); $x++) {
		$thistrace = $trace[$x]['function'];
		if ($thistrace != 'include_once' && $thistrace != 'require_once' && $thistrace != 'require' && $thistrace != 'include') {
			if (isset($trace[$x]['line'])) {
				$mess.= ' -> '.$thistrace.' ('.$trace[$x]['line'].')';
			} else {
				$mess.= ' -> '.$thistrace.' (none)';
			}
		}
	}
	$mess.= '<br /><b>'.$sql.'</b><br /><hr />';

	# write to query log
	$querylog.= $mess;
}

function show_log() {
	global $querylog;
	vshow($querylog);
}

?>