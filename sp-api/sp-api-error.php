<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2017-11-11 15:57:00 -0600 (Sat, 11 Nov 2017) $
$Rev: 15578 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
#	CORE: This file is loaded at CORE
#	SP Error Handling and reporting
#
#	If the constant SP_SHOWNOTICES is set to true, NOTICE errors
#	will also be written to the php error log
#
# ==================================================================

# ------------------------------------------------------------------
# sp_construct_database_error()
#
# Version: 5.0
# DATABASE ERROR MESSAGE CONSTRUCTOR
#
# Creates database error message and sends to error log function
#
#	$sql:		the original sql statement
#	$sqlerror:	the reported mysql error text
# ------------------------------------------------------------------
function sp_construct_database_error($sql, $sqlerror) {
	global $spStatus, $spGlobals, $wpdb;
	if ($spGlobals['record-errors'] == false) return;

	if (spdb_connection() == false) return;

	$mess = '';

	if (version_compare(PHP_VERSION, '5.2.4', '<')) {
		$trace = debug_backtrace();
	} else {
		$trace = debug_backtrace(true, 4);
	}
	$traceitem = $trace[2];
	$mess.= 'file: '.$traceitem['file'].'<br />';
	$mess.= 'line: '.$traceitem['line'].'<br />';
	$mess.= 'function: '.$traceitem['function'].'<br />';
	$mess.= "error: $sqlerror<br /><br />";
	$mess.= $sql;

	$keyCheck = substr(E_ERROR . $traceitem['line'] . substr($traceitem['file'], -30, 30), 0, 45);

	# write out error to our toolbox log if it doesn't exist already
	$e = spdb_table(SFERRORLOG, 'keycheck="'.$keyCheck.'" AND error_type="database"', 'error_count');
	if (empty($e) || $e == 0) {
		@sp_write_error('database', $mess, E_ERROR, $keyCheck);
	} else {
		@sp_update_error($keyCheck, $e);
	}

	# create display message
	include_once SPAPI.'sp-api-cache.php';
	sp_notify(SPFAILURE, sp_text('Invalid database query'));
}

# ------------------------------------------------------------------
# sp_construct_php_error()
#
# Version: 5.0
# PHP ERROR MESSAGE CONSTRUCTOR (at least those catchable ones)
#
# Creates php error message and sends to error log function
#
#	$errno:		Error Type
#	$errstr:	Error message text
#	$errfile:	Error File
#	$errline:	Error Line Number in file
# ------------------------------------------------------------------
function sp_construct_php_error($errno, $errstr, $errfile, $errline) {
	global $spPaths, $spStatus, $spGlobals, $wpdb;
	if ($spGlobals['record-errors'] == false) return;
	if (spdb_connection() == false) return;

	# only interested in SP errors
	$errfile = str_replace('\\','/',$errfile); # sanitize for Win32 installs
	$pos = strpos($errfile, '/plugins/simple-press/');
	$pos1 = strpos($errfile, $spPaths['plugins']);
	if ($pos === false && $pos1 === false) return false;

	# For now remove the 'undefined' (index/variable) notices
	if ($errno == E_NOTICE && $spGlobals['notices-off'] == true) return false;

	$errortype = array (
		E_ERROR				 => 'Error',
		E_WARNING			 => 'Warning',
		E_PARSE				 => 'Parsing Error',
		E_NOTICE			 => 'Notice',
		E_CORE_ERROR		 => 'Core Error',
		E_CORE_WARNING		 => 'Core Warning',
		E_COMPILE_ERROR		 => 'Compile Error',
		E_COMPILE_WARNING	 => 'Compile Warning',
		E_USER_ERROR		 => 'User Error',
		E_USER_WARNING		 => 'User Warning',
		E_USER_NOTICE		 => 'User Notice',
		E_STRICT			 => 'Runtime Notice',
		E_RECOVERABLE_ERROR	 => 'Catchable Fatal Error'
	);

	if ($errno == E_NOTICE || $errno == E_RECOVERABLE_ERROR || $errno == E_WARNING || $errno == E_USER_WARNING) {
		$mess = '';
		$trace = debug_backtrace();
		$traceitem = $trace[1];
		unset($trace);

		if ($traceitem['function'] == 'spHandleShutdown' && $errno == E_NOTICE) return;

		$keyCheck = substr($errortype[$errno] . $errline . substr($errfile, -30, 30), 0, 45);

		$file = 'file: '.substr($errfile, $pos + 8, strlen($errfile));
		$line = "line: $errline";
		$func = 'function: '.$traceitem['function'];
		$type = $errortype[$errno].' | '.$errstr;

		# write out error to our toolbox log if it doesn't exist already
		$e = spdb_table(SFERRORLOG, 'keycheck="'.$keyCheck.'" AND error_type="php"', 'error_count');
		if (empty($e) || $e == 0) {
			@sp_write_error('php', $file.'<br />'.$line.'<br />'.$func.'<br />'.$type, $errno, $keyCheck);
		} else {
			@sp_update_error($keyCheck, $e);
		}

		# do we need to send error to php log (wp-config setting)
		if ((defined('SP_SHOWNOTICES') && SP_SHOWNOTICES == true) || ($errno != E_NOTICE)) {
			# wrtie error out to php error log (its still suppressed from the screen)
			error_log('PHP '.$errortype[$errno].' - '.$errstr.' - '.$file.' - '.$line.' - '.$func."\n", 0);
		}
	}
	return false;
}

# ------------------------------------------------------------------
# spHandleShutdown()
#
# Version: 5.0
# FATAL (CRASH) ERROR RECORDING HANDLER
#
# Creates fatal error warning and passes to main error handler
# ------------------------------------------------------------------
register_shutdown_function('spHandleShutdown');
function spHandleShutdown() {
	global $spStatus, $spGlobals;
	if ($spGlobals['record-errors'] == false) return;
	$error = error_get_last();
	if ($error !== null) sp_construct_php_error($error['type'], $error['message'], $error['file'], $error['line']);
}

# ------------------------------------------------------------------
# sp_write_error()
#
# Version: 5.0
# ERROR RECORDING HANDLER
#
# Creates entry in table sferrorlog
#
#	$errortyoe:	'database'
#	$errortext:	pre-formatted error details
# ------------------------------------------------------------------
function sp_write_error($errortype, $errortext, $errno=E_ERROR, $keyCheck='unset_keycheck') {
	global $spStatus, $spGlobals, $spVars, $wpdb;
	if ($spGlobals['record-errors'] == false) return;
	if (spdb_connection() == false) return;

	$cat = $errno;
	if ($errno == E_ERROR) {
		$cat = 'spaErrError';
	} else if ($errno == E_WARNING) {
		$cat = 'spaErrWarning';
	} else if ($errno == E_NOTICE) {
		$cat = 'spaErrNotice';
	} else if ($errno == E_STRICT) {
		$cat = 'spaErrStrict';
	} else if ($errno == 'Security Alert') {
		$cat = 'spaSecNotice';
	}

	$now = "'".current_time('mysql')."'";
	$sql = 'INSERT INTO '.SFERRORLOG;
	$sql.= ' (error_date, error_type, error_cat, keycheck, error_count, error_text) ';
	$sql.= 'VALUES (';
	$sql.= $now.', ';
	$sql.= "'".$errortype."', ";
	$sql.= "'".$cat."', ";
	$sql.= "'".$keyCheck."', ";
	$sql.= '1, ';
	$sql.= "'".sp_esc_sql($errortext)."')";
	$wpdb->query($sql);

	# leave just last 50 entries
	if ($wpdb->insert_id > 51) {
		$sql = 'DELETE FROM '.SFERRORLOG.' WHERE id < '.($wpdb->insert_id - 50);
		$wpdb->query($sql);
	}
}

# ------------------------------------------------------------------
# sp_update_error()
#
# Version: 5.3.2
# ERROR RECORDING HANDLER
#
# Updates entry in table sferrorlog with new date and count
#
#	$keyCheck:	Unique shirtened key for ID purposes
#	$e:			number of prior occurencies of error
# ------------------------------------------------------------------
function sp_update_error($keyCheck, $e) {
	global $spStatus, $spGlobals;

	if ($spGlobals['record-errors'] == false) return;
	if (spdb_connection() == false) return;

	$now = "'".current_time('mysql')."'";
	$e++;
	$sql = 'UPDATE '.SFERRORLOG.' SET
			error_date = '.$now.',
			error_count = '.$e.' WHERE
			keycheck = "'.$keyCheck.'"';
	spdb_query($sql);
}

?>