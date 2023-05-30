<?php

/** -----------------------------------------------------------------
 * Core class used for error recording
 *
 * This class is used for all php and database error recording
 *
 * @since 6.0
 *
 * Public methods available
 *
 *    errorPHP($errno, $errstr, $errfile, $errline)
 *    errorSQL($sql, $sqlerror)
 *    errorWrite($errortype, $errortext, $errno, $keyCheck)
 *    errorUpdate($keyCheck, $e)
 *    setRecording($status)
 *    setNotices($status)
 *
 * -----------------------------------------------------------------
 *
 * $LastChangedDate: 2016-12-21 10:19:56 +0000 (Wed, 21 Dec 2016) $
 * $Rev: 14843 $
 *
 */
class spcError {
	/**
	 * Indicates whether error recording is on or not.
	 *
	 * @access private
	 * @var bool
	 */
	private $record = true;

	/**
	 * Indicates whether notices should be recorded or not.
	 *
	 * @access private
	 * @var bool
	 */
	private $notices = true;

	/** -----------------------------------------------------------------
	 * Constructor
	 *
	 * @access public
	 * @since 6.0
	 *
	 * registers the php shutdown callback funtion
	 * -----------------------------------------------------------------
	 */
	public function __construct() {
		register_shutdown_function(array($this, 'shutdown'));
	}

	/** -----------------------------------------------------------------
	 * PHP Shutdown error trap
	 *
	 * @access public
	 * @since 6.0
	 *
	 * Passes on the shutdown error to the reporting function
	 * -----------------------------------------------------------------
	 */
	public function shutdown() {
		if (!$this->record) return;

		$error = error_get_last();
		if ($error !== null) $this->errorPHP($error['type'], $error['message'], $error['file'], $error['line']);
	}

	/** -----------------------------------------------------------------
	 * Constructs a PHP Error record
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @global object $wpdb    WordPress database abstraction object
	 *
	 * @param integer $errno   The error type identifier
	 * @param string  $errstr  The reported PHP error text
	 * @param string  $errfile The source file containing the error
	 * @param string  $errline The line number within the file
	 *
	 * @return void
	 * -----------------------------------------------------------------
	 */
	public function errorPHP($errno, $errstr, $errfile, $errline) {
		if (!$this->record) return;

		# make sure needed classes exist
		if (!SP()->DB) SP()->DB = new spcDB();
		if (!SP()->plugin) SP()->plugin = new spcPlugin();

		if (SP()->DB->connectionExists() == false) return;

		# only interested in SP errors
		$errfile = str_replace('\\', '/', $errfile); # sanitize for Win32 installs
		$posCore = strpos($errfile, '/plugins/'.SP_FOLDER_NAME.'/');
		$storage_plugin = isset(SP()->plugin->storage['plugins']) ? SP()->plugin->storage['plugins'] : "";
		$posPlug = strpos($errfile, $storage_plugin);
		if ($posCore === false && $posPlug === false) return;

		# Do not record notices if turned off
		if ($errno == E_NOTICE && $this->notices) return;

		$errortype = array(E_ERROR => 'Error', E_WARNING => 'Warning', E_PARSE => 'Parsing Error', E_NOTICE => 'Notice', E_CORE_ERROR => 'Core Error', E_CORE_WARNING => 'Core Warning', E_COMPILE_ERROR => 'Compile Error', E_COMPILE_WARNING => 'Compile Warning', E_USER_ERROR => 'User Error', E_USER_WARNING => 'User Warning', E_USER_NOTICE => 'User Notice', E_STRICT => 'Runtime Notice', E_RECOVERABLE_ERROR => 'Catchable Fatal Error');

		if ($errno == E_NOTICE || $errno == E_RECOVERABLE_ERROR || $errno == E_WARNING || $errno == E_USER_WARNING) {
			if (version_compare(PHP_VERSION, '5.2.4', '<')) {
				$trace = debug_backtrace();
			} else {
				$trace = debug_backtrace(true, 4);
			}
			$traceitem = $trace[1];
			unset($trace);

			if ($traceitem['function'] == 'spHandleShutdown' && $errno == E_NOTICE) return;

			$keyCheck = substr($errortype[$errno].$errline.substr($errfile, -30, 30), 0, 45);

			if ($posCore != false) {
				$file = 'file: '.substr($errfile, $posCore + 8, strlen($errfile));
			} else {
				$file = 'file: '.substr($errfile, $posPlug + 12, strlen($errfile));
			}
			$line = "line: $errline";
			$func = 'function: '.$traceitem['function'];
			$type = $errortype[$errno].' | '.$errstr;

			# write out error to our toolbox log if it doesn't exist already
			$e = SP()->DB->table(SPERRORLOG, 'keycheck="'.$keyCheck.'" AND error_type="php"', 'error_count');
			if (empty($e) || $e == 0) {
				@$this->errorWrite('php', $file.'<br />'.$line.'<br />'.$func.'<br />'.$type, $errno, $keyCheck);
			} else {
				@$this->errorUpdate($keyCheck, $e);
			}

			# do we need to send error to php log (wp-config setting)
			if ((defined('SP_SHOWNOTICES') && SP_SHOWNOTICES == true) || ($errno != E_NOTICE)) {
				# wrtie error out to php error log (its still suppressed from the screen)
				error_log('PHP '.$errortype[$errno].' - '.$errstr.' - '.$file.' - '.$line.' - '.$func."\n", 0);
			}
		}

		return;
	}

	/** -----------------------------------------------------------------
	 * Constructs a SQL Database Error record
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @param string $sql      The original sql statement causing the error
	 * @param string $sqlError The reported SQL error text
	 * -----------------------------------------------------------------
	 */
	public function errorSQL($sql, $sqlerror) {
		if (!$this->record) return;

		# make sure needed classes exist
		if (!SP()->DB) SP()->DB = new spcDB();
		if (!SP()->notifications) SP()->notifications = new spcNotifications();
		if (!SP()->primitives) SP()->primitives = new spcPrimitives();

		if (SP()->DB->connectionExists() == false) return;

		$mess = '';

		if (version_compare(PHP_VERSION, '5.2.4', '<')) {
			$trace = debug_backtrace();
		} else {
			$trace = debug_backtrace(true, 4);
		}
		$traceitem = $trace[2];
		unset($trace);

		$mess .= 'file: '.$traceitem['file'].'<br />';
		$mess .= 'line: '.$traceitem['line'].'<br />';
		$mess .= 'function: '.$traceitem['function'].'<br />';
		$mess .= "error: $sqlerror<br /><br />";
		$mess .= $sql;

		$keyCheck = substr(E_ERROR.$traceitem['line'].substr($traceitem['file'], -30, 30), 0, 45);

		# write out error to our toolbox log if it doesn't exist already
		$e = SP()->DB->table(SPERRORLOG, 'keycheck="'.$keyCheck.'" AND error_type="database"', 'error_count');
		if (empty($e) || $e == 0) {
			@$this->errorWrite('database', $mess, E_ERROR, $keyCheck);
		} else {
			@$this->errorUpdate($keyCheck, $e);
		}

		# create display message
		SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Invalid database query'));
	}

	/** -----------------------------------------------------------------
	 * Writes new error details to the error log
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @global object $wpdb      WordPress database abstraction object
	 *
	 * @param string  $errortype 'database' or 'php'
	 * @param string  $errortext pre-formatted text
	 * @param number  $errno     The error type identifier
	 * @param string  $keyCheck  key details for new/exist checking
	 * -----------------------------------------------------------------
	 */
	public function errorWrite($errortype, $errortext, $errno = E_ERROR, $keyCheck = 'unset_keycheck') {
		global $wpdb;

		if (!$this->record) return;

		# make sure needed classes exist
		if (!SP()->DB) SP()->DB = new spcDB();
		if (!SP()->filters) SP()->filters = new spcFilters();

		if (SP()->DB->connectionExists() == false) return;

		$cat = $errno;
		if ($errortype == 'security') {
			$cat = 'spaSecNotice';
		} else if ($errno == E_ERROR) {
			$cat = 'spaErrError';
		} else if ($errno == E_WARNING) {
			$cat = 'spaErrWarning';
		} else if ($errno == E_NOTICE) {
			$cat = 'spaErrNotice';
		} else if ($errno == E_STRICT) {
			$cat = 'spaErrStrict';
		}

		$now = "'".current_time('mysql')."'";
		$sql = 'INSERT INTO '.SPERRORLOG;
		$sql .= ' (error_date, error_type, error_cat, keycheck, error_count, error_text) ';
		$sql .= 'VALUES (';
		$sql .= $now.', ';
		$sql .= "'".$errortype."', ";
		$sql .= "'".$cat."', ";
		$sql .= "'".$keyCheck."', ";
		$sql .= '1, ';
		$sql .= "'".SP()->filters->esc_sql($errortext)."')";
		$wpdb->query($sql);

		# leave just last 50 entries
		if ($wpdb->insert_id > 51) {
			$sql = 'DELETE FROM '.SPERRORLOG.' WHERE
					id < '.($wpdb->insert_id - 50);
			$wpdb->query($sql);
		}
	}

	/** -----------------------------------------------------------------
	 * Updates exiting error details to the error log (i.e., adds 1 to count)
	 *
	 * @access private
	 * @since 6.0
	 *
	 * @global object $wpdb     WordPress database abstraction object
	 *
	 * @param string  $keyCheck key details for new/exist checking
	 * @param number  $e        number of prior occurencies of error
	 * -----------------------------------------------------------------
	 */
	private function errorUpdate($keyCheck, $e) {
		global $wpdb;

		if (!$this->record) return;

		# make sure needed classes exist
		if (!SP()->DB) SP()->DB = new spcDB();

		if (SP()->DB->connectionExists() == false) return;

		$now = "'".current_time('mysql')."'";
		$e++;
		$sql = 'UPDATE '.SPERRORLOG.' SET
				error_date = '.$now.',
				error_count = '.$e.' WHERE
				keycheck = "'.$keyCheck.'"';
		$wpdb->query($sql);
	}

	/** -----------------------------------------------------------------
	 * Sets error recording state to on or off
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @param bool $status true/false sets state of error recording
	 * -----------------------------------------------------------------
	 */
	public function setRecording($status) {
		$this->record = (bool)$status;
	}

	/** -----------------------------------------------------------------
	 * Sets error recording state of notices to on or off
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @param bool $status true/false sets state of notice error recording
	 * -----------------------------------------------------------------
	 */
	public function setNotices($status) {
		$this->notices = (bool)$status;
	}
}

