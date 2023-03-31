<?php
/** -----------------------------------------------------------------
 * Core class used for database queries - wrapper class around $wpdb
 *
 * This class is used for all Selects, Inserts, Updates and Deletes
 * plus some lesser used database functions
 *
 * Introduced in 5.0 it has now been extended
 *
 * @since 6.0
 *
 * Public methods available
 *
 * 	select(query args - or - sql statement)
 * 	insert(query args)
 * 	update(query args)
 * 	delete(query args)
 * 	execute(query statement)
 * 	table(table, where, varcol, order, limit, rettype)
 * 	count(table, where)
 * 	sum(table, column, where)
 * 	maxNumber(table, column, where)
 * 	timezone(column, addColumn)
 * 	charset()
 * 	tableExists(table)
 * 	columnExists(table, column)
 * 	truncate(table)
 * 	connectionExists()
 *
 * -----------------------------------------------------------------
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcDB {

	/** -----------------------------------------------------------------
	 * Constructs and performs a SELECT query
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @param string	$query		array of query components OR sql select statement
	 * @param string	$wpCommand	type of WP object - default is 'set'
	 * @param string	$wpType		format of data to return - default OBJECT
	 * @return mixed	db query data
	 * -----------------------------------------------------------------
	 *
	 * * Various information about argument.
     *
     * @since 3.1.0
     * @var array
     */
    public $args;

	public function select($query, $wpCommand = 'set', $wpType = OBJECT) {
		# if a complete SELECT statement then send it for execution
		if (!is_object($query) && substr(trim($query), 0, 6) == 'SELECT') {
			$records = $this->executeSelect(trim($query), $wpCommand, $wpType);
			return $records;
		}

		# So it is a query object
		$this->init();

		$this->args = wp_parse_args($query, $this->args);
		extract($this->args, EXTR_SKIP);

		if (empty($table)) return '';

		$found_rows	 = (empty($found_rows)) ? '' : ' SQL_CALC_FOUND_ROWS';
		$distinct	 = (empty($distinct)) ? '' : ' DISTINCT';
		$distinctrow = (empty($distinctrow)) ? '' : ' DISTINCTROW';
		$where		 = (empty($where)) ? '' : " WHERE $where";
		$limits		 = (empty($limits)) ? '' : " LIMIT $limits";
		$fields		 = (empty($fields)) ? ' *' : " $fields";

		$qJoin = '';
		if (!empty($join)) {
			if (is_array($join)) {
				foreach ($join as $j) {
					$qJoin .= " JOIN $j";
				}
			} else {
				$qJoin = " JOIN $join";
			}
		}

		$qLeft_join = '';
		if (!empty($left_join)) {
			if (is_array($left_join)) {
				foreach ($left_join as $j) {
					$qLeft_join .= " LEFT JOIN $j";
				}
			} else {
				$qLeft_join = " LEFT JOIN $left_join";
			}
		}

		$qRight_join = '';
		if (!empty($right_join)) {
			if (is_array($right_join)) {
				foreach ($right_join as $j) {
					$qRight_join .= " RIGHT JOIN $j";
				}
			} else {
				$qRight_join = " RIGHT JOIN $right_join";
			}
		}

		$qGroupby = '';
		if (!empty($groupby)) {
			if (is_array($groupby)) {
				$qGroupby = ' GROUP BY';
				foreach ($groupby as $i => $g) {
					$qGroupby	 = ($i == 0) ? ' ' : ', ';
					$qGroupby	 .= $g;
				}
			} else {
				$qGroupby = " GROUP BY $groupby";
			}
		}

		$qOrderby = '';
		if (!empty($orderby)) {
			if (is_array($orderby)) {
				$qOrderby = ' ORDER BY';
				foreach ($orderby as $i => $o) {
					$qOrderby	 = ($i == 0) ? ' ' : ', ';
					$qOrderby	 .= $o;
				}
			} else {
				$qOrderby = " ORDER BY $orderby";
			}
		}

		$sql	 = "SELECT $found_rows$distinct$distinctrow$fields FROM $table$qJoin$qLeft_join$qRight_join$where$qGroupby$qOrderby$limits";
		if ($show) $this->executeShow($sql, $inspect);
		$records = $this->executeSelect($sql, $type, $resultType);
		return $records;
	}

	/** -----------------------------------------------------------------
	 * Constructs and performs an INSERT query
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param string	$query	array of query components
	 * @return bool success or failure
	 * -----------------------------------------------------------------
	 */
	public function insert($query) {
		$this->init();

		$this->args = wp_parse_args($query, $this->args);
		extract($this->args, EXTR_SKIP);

		if (empty($table) || empty($fields) || empty($data) || !is_array($data) || !is_array($fields)) return false;

		$values = array();
		foreach ($data as $val) {
			# check if special ASCII 254; - means numbers to be treated as string
			if (substr($val, 0, 1) == chr(254)) {
				$val = "'".substr($val, 1)."'";
			} elseif (!is_numeric($val)) {
				$val = "'".$val."'";
			}
			$values[] = $val;
		}

		# Insert or Replace
		$operation = ($replace) ? 'REPLACE' : 'INSERT';

		$sql = "$operation INTO $table (".implode(', ', $fields).') VALUES ('.implode(', ', $values).')';

		# on duplicate key support
		if ($duplicate_key == true) {
			$sql .= ' ON DUPLICATE KEY UPDATE ';
			for ($i = 0; $i < count($fields); $i++) {
				if ($i > 0) $sql .= ', ';
				if (substr($data[$i], 0, 1) == chr(254)) {
					$thisVal = "'".substr($data[$i], 1)."'";
				} elseif (!is_numeric($data[$i])) {
					$thisVal = "'".$data[$i]."'";
				} else {
					$thisVal = $data[$i];
				}
				$sql .= $fields[$i]." = ($thisVal)";
			}
		}

		if ($show) $this->executeShow($sql, $inspect);
		$result = $this->execute($sql);
		return $result;
	}

	/** -----------------------------------------------------------------
	 * Constructs and performs an UPDATE query
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param string	$query	array of query components
	 * @return bool success or failure
	 * -----------------------------------------------------------------
	 */
	public function update($query) {
		$this->init();

		$this->args = wp_parse_args($query, $this->args);
		extract($this->args, EXTR_SKIP);

		if (empty($table) || empty($fields) || !is_array($data) || !is_array($fields)) return false;
		if (!empty($where)) $where = " WHERE $where";

		$dbfields = array();
		foreach ($fields as $index => $col) {
			$value		 = $data[$index];
			if (!is_numeric($value)) $value		 = "'$value'";
			$dbfields[]	 = "$col = $value";
		}

		$sql	 = "UPDATE $table SET ".implode(', ', $dbfields).$where;
		if ($show) $this->executeShow($sql, $inspect);
		$result	 = $this->execute($sql);
		return $result;
	}

	/** -----------------------------------------------------------------
	 * Constructs and performs a DELETE query
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param string	$query	array of query components
	 * @return bool success or failure
	 * -----------------------------------------------------------------
	 */
	public function delete($query) {
		$this->init();

		$this->args = wp_parse_args($query, $this->args);
		extract($this->args, EXTR_SKIP);

		if (empty($table) || empty($where)) return false;
		$where = " WHERE $where";

		$sql	 = "DELETE FROM $table$where";
		if ($show) $this->executeShow($sql, $inspect);
		$result	 = $this->execute($sql);
		return $result;
	}

	/** -----------------------------------------------------------------
	 * Executes a non-select query as used by above funtions
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @global object   $wpdb	WordPress database abstraction object
	 *
	 * @param string	$sql	The SQL query
	 * @return bool success or failure
	 * -----------------------------------------------------------------
	 */
	public function execute($sql) {
		global $wpdb;

		SP()->rewrites->pageData['affectedrows']	 = 0;
		SP()->rewrites->pageData['insertid']		 = 0;

		$wpdb->hide_errors();

		$wpdb->query($sql);

		if ($wpdb->last_error == '') {
			SP()->rewrites->pageData['affectedrows']	 = $wpdb->rows_affected;
			if (substr($sql, 0, 6) == 'INSERT') SP()->rewrites->pageData['insertid']		 = $wpdb->insert_id;
			return true;
		} else {
			SP()->error->errorSQL($sql, $wpdb->last_error);
			return false;
		}
	}

	/** -----------------------------------------------------------------
	 * Constructs a single table select query
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @param string	$table		Table that forms the query
	 * @param string	$where		The where selection clause
	 * @param string	$varcol		Optional use. Set to:
	 * 						1: Field name to perform 'var' query
	 * 						2: word 'row' to perform 'row' query
	 * 						3: empty to return a 'set' query
	 * @param   string	$order		Column(s) to order results by
	 * @param   string	$limit		Limit values
	 * @param	string	$type		Return Type ARRAY_A, ARRAY_N, OBJECT (default OBJECT)
     *
	 * @return			array|object	the query results if good
	 * @return			bool		    false if good but no records
	 * @return			bool		    false if failed and displays error if sql invalid
	 * -----------------------------------------------------------------
	 */
	public function table($table, $where = '', $varcol = '', $order = '', $limit = '', $rettype = OBJECT) {
		$selectfrom	 = ' *';
		$whereclause = '';
		$orderby	 = '';
		$qtype		 = 'set';

		if ($varcol != '') {
			if ($varcol == 'row') {
				$qtype = 'row';
			} else {
				$selectfrom	 = ' '.$varcol;
				$qtype		 = 'var';
			}
		}
		if ($where != '') $whereclause = " WHERE $where";
		if ($order != '') $orderby	 = " ORDER BY $order";
		if ($limit != '') $limit		 = " LIMIT $limit";

		$sql	 = "SELECT $selectfrom FROM $table$whereclause$orderby$limit";
		$records = $this->executeSelect($sql, $qtype, $rettype);
		return $records;
	}

	/** -----------------------------------------------------------------
	 * Executes a count of column or table
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param string	$table		The SQL table to count
	 * @patam string	$where		optional complete where clause
	 * @return int
	 * -----------------------------------------------------------------
	 */
	public function count($table, $where = '') {
		$whereclause = '';
		if ($where != '') $whereclause = " WHERE $where";
		$sql		 = "SELECT COUNT(*) FROM $table$whereclause";
		$c			 = $this->executeSelect($sql, 'var');
		if (!$c) $c			 = 0;
		return (int) $c;
	}

	/** -----------------------------------------------------------------
	 * Executes a sum of a column
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param string	$table		The SQL table to count
	 * @param string	$column		The one to sum
	 * @param string	$where		optional complete where clause
     *
	 * @return array|object     result
	 * -----------------------------------------------------------------
	 */
	public function sum($table, $column, $where = '') {
		$whereclause = '';
		if ($where != '') $whereclause = " WHERE $where";
		$sql		 = "SELECT SUM($column) FROM $table$whereclause";
		$c			 = $this->executeSelect($sql, 'var');
		if (!$c) $c			 = 0;
		return $c;
	}

	/** -----------------------------------------------------------------
	 * Returns the max (highest number) of the field being queried
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param string	$table		The SQL table to count
	 * @param string	$column		The one to sum
	 * @patam string	$where		optional complete where clause
	 * @return int
	 * -----------------------------------------------------------------
	 */
	public function maxNumber($table, $column, $where = '') {
		$whereclause = '';
		if ($where != '') $whereclause = " WHERE $where";
		$sql		 = "SELECT MAX($column) FROM $table$whereclause";
		$c			 = $this->executeSelect($sql, 'var');
		if (!$c) $c			 = 0;
		return (int) $c;
	}

	/** -----------------------------------------------------------------
	 * Returns the SQL function required in a query to base results
	 * on the users personal timezone
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param date.time	$d		date used as base for query
	 * @patam string	$addAs	will add column to fields listing
	 * @return string   sql funtion to embed
	 * -----------------------------------------------------------------
	 */
	public function timezone($d, $addAs = true) {
		$addField	 = ($addAs == true) ? 'as '.$d : '';
		$zone		 = (isset(SP()->user->thisUser->timezone)) ? SP()->user->thisUser->timezone : 0;
		if ($zone == 0) return $d;
		if ($zone < 0) {
			return 'DATE_SUB('.$d.', INTERVAL '.abs($zone).' HOUR) '.$addField;
		} else {
			return 'DATE_ADD('.$d.', INTERVAL '.abs($zone).' HOUR) '.$addField;
		}
	}

	/** -----------------------------------------------------------------
	 * Returns the charset being used by users database
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @global object   $wpdb	WordPress database abstraction object
	 *
	 * @return string   charset for table creation
	 * -----------------------------------------------------------------
	 */
	public function charset() {
		global $wpdb;
		$charset = '';
		if (!empty($wpdb->charset)) $charset = "DEFAULT CHARACTER SET $wpdb->charset";
		if (!empty($wpdb->collate)) $charset .= " COLLATE $wpdb->collate";
		return $charset;
	}

	/** -----------------------------------------------------------------
	 * Checks if a table exists
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param string	$table		table to check in
	 * @patam string	$column		column to check for
	 * @return bool		true/false
	 * -----------------------------------------------------------------
	 */
	public function tableExists($table) {
		$result = $this->executeSelect("SHOW TABLES LIKE '$table'", 'var');
		return (!empty($result)) ? 1 : 0;
	}

	/** -----------------------------------------------------------------
	 * Checks if a column exists
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @param string	$table		table to check in
	 * @patam string	$column		column to check for
	 * @return string as bool
	 * -----------------------------------------------------------------
	 */
	public function columnExists($table, $column) {
		$result = $this->tableExists($table);
		if ($result) {
			$result = $this->executeSelect("SHOW COLUMNS FROM $table LIKE '$column'");
		}
		return (!empty($result)) ? 1 : 0;
	}

	/** -----------------------------------------------------------------
	 * Run a TRUNCATE against a table
	 *
	 * @access public
	 * @since 6.0
	 *
	 * @global object   $wpdb	WordPress database abstraction object
	 *
	 * @param string	$table		table to truncatre (empty)
	 * @return string as bool
	 * -----------------------------------------------------------------
	 */
	public function truncate($table) {
		global $wpdb;
		$result = $wpdb->query("TRUNCATE TABLE $table");
		return $result;
	}

	/** -----------------------------------------------------------------
	 * checks that there is still a database connection
	 *
	 * @access public
	 * @since 5.0
	 *
	 * @global object   $wpdb	WordPress database abstraction object
	 *
	 * @return bool success or failure
	 * -----------------------------------------------------------------
	 */
	public function connectionExists() {
		global $wpdb;
		$connection = (is_object($wpdb)) ? $wpdb->check_connection(false) : false;
		return $connection;
	}

	#
	# ---- Private functions ------------
	#
	/** -----------------------------------------------------------------
	 * Initialises the sql component array for main 4 methods
	 *
	 * @access private
	 * @since 6.0
	 * -----------------------------------------------------------------
	 */

	private function init() {
		$this->args = array(
			'type'			 => 'set',
			'resultType'	 => OBJECT,
			'table'			 => '',
			'found_rows'	 => false,
			'duplicate_key'	 => false,
			'replace'		 => false,
			'distinct'		 => false,
			'distinctrow'	 => false,
			'fields'		 => '',
			'join'			 => '',
			'left_join'		 => '',
			'right_join'	 => '',
			'where'			 => '',
			'groupby'		 => '',
			'orderby'		 => '',
			'limits'		 => '',
			'data'			 => '',
			'show'			 => false,
			'inspect'		 => ''
		);
	}

	/** -----------------------------------------------------------------
	 * Executes a select as used by above main select function
	 *
	 * @access private
	 * @since 6.0
	 *
	 * @global object   $wpdb	WordPress database abstraction object
	 *
	 * @param string	$sql		The SQL select query
	 * @param string	$queryType	One of the WP select types (defaut 'set')
	 * @param string	$resultType type of $wpdb result object (default OBJECT)
	 * @return mixed	db query data
	 * -----------------------------------------------------------------
	 */
	private function executeSelect($sql, $queryType = 'set', $resultType = OBJECT) {
		global $wpdb;

		// PHP 7.4 fix: PHP Warning - Creating default object from empty value
		// Not sure why none of the components of SP() is unavailable here even though SP() itself is fine.  
		// Tracing through the code shows that SP()->rewrites = new spcRewrites(); is getting called before this function 
		// in file /sp-api/sp-load-class-spccoreloader.php around line 76.  So the fact that it's not already an object here 
		// sometimes is quite puzzling.
		if ( ! is_object(SP()->rewrites) ) {
			SP()->rewrites = new spcRewrites();
		}	

		SP()->rewrites->pageData['queryrows'] = 0;

		$wpdb->hide_errors();

		switch ($queryType) {
			case 'row':
				$records = $wpdb->get_row($sql, $resultType);
				break;
			case 'col':
				$records = $wpdb->get_col($sql);
				break;
			case 'var':
				$records = $wpdb->get_var($sql);
				break;
			case 'set':
			default:
				$records = $wpdb->get_results($sql, $resultType);
				break;
		}

		if ($wpdb->last_error == '') {
			SP()->rewrites->pageData['queryrows'] = $wpdb->num_rows;
		} else {
			SP()->error->errorSQL($sql, $wpdb->last_error);
		}
		return $records;
	}

	/** -----------------------------------------------------------------
	 * Display the SQL statement on screen for debugging
	 *
	 * @access private
	 * @since 5.5
	 *
	 * @global	object  $wpdb	WordPress database abstraction object
	 *
	 * @param	string	$sql		the sql query
	 * @param	string	$inpsect	name of the inspection if known
	 * @return	void
	 * -----------------------------------------------------------------
	 */
	private function executeShow($sql, $inspect) {
		spdebug_styles(true);
		echo '<div class="spdebug">';
		echo SP()->primitives->front_text('Inspect Query').': <strong>'.$inspect.'</strong><br><hr>';
		echo '<pre><code>';
		$k	 = array(
			"\t",
			"\n",
			'SELECT ',
			'UPDATE ',
			'INSERT ',
			'DELETE ',
			'TRUNCATE ',
			' DISTINCT ',
			' DISTINCTROW ',
			'FROM ',
			'LEFT JOIN ',
			'RIGHT JOIN ',
			' JOIN ',
			'WHERE ',
			'ORDER BY ',
			'GROUP BY ',
			'LIMIT ',
			'SET ',
			' ON ',
			' IN ',
			' DESC ',
			' ASC ',
			' DESC, ',
			' ASC, ',
			' AS ',
			' OR ',
			' AND ',
			' LIKE ');
		$r	 = array(
			'',
			'',
			"\n<b>SELECT</b> ",
			"\n<b>UPDATE</b> ",
			"\n<b>INSERT</b> ",
			"\n<b>DELETE</b> ",
			"\n<b>TRUNCATE</b> ",
			' <b>DISTINCT</b> ',
			' <b>DISTINCTROW</b> ',
			"\n<b>FROM</b> ",
			"\n<b>LEFT JOIN</b> ",
			"\n<b>RIGHT JOIN</b> ",
			" \n<b>JOIN</b> ",
			"\n<b>WHERE</b> ",
			"\n<b>ORDER BY</b> ",
			"\n<b>GROUP BY</b> ",
			"\n<b>LIMIT</b> ",
			"\n<b>SET</b> ",
			' <b>ON</b> ',
			' <b>IN</b> ',
			' <b>DESC</b> ',
			' <b>ASC</b> ',
			' <b>DESC</b>, ',
			' <b>ASC</b>, ',
			' <b>AS</b> ',
			' <b>OR</b> ',
			' <b>AND</b> ',
			' <b>LIKE</b> ');
		$sql = str_replace($k, $r, $sql);
		echo $sql;
		echo '</code></pre>';
		echo '</div>';
	}

}
