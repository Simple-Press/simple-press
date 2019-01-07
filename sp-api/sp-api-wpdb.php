<?php
/*
Simple:Press
Database Support Routine Library
$LastChangedDate: 2016-09-14 06:04:46 -0500 (Wed, 14 Sep 2016) $
$Rev: 14587 $
*/

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	SP Database Handling - $wpdb layer
#
# ==================================================================

# ------------------------------------------------------------------
# spdb_table()
#
# Version: 5.0
# DATABASE SELECT QUERY HANDLER WHEN SINGLE TABLE QUERY
#
# Returns the query results if good
# Returns false if good but no records
# Returns false if failed and displays error if sql invalid
# Calls spdb_select()
#
#	$table:		Fully prefixed WP table name
#	$where:		Complete Where clause
#	$varcol:	Set to:
#					1: Field name to perform 'var' query
#					2: word 'row' to perform 'row' query
#					3: empty to return a 'set' query
#	$order:		Column(s) to order results by
#	$limit:		Limit values
#	$type:		Return Type:
#				ARRAY_A, ARRAY_N, OBJECT (default OBJECT)
# ------------------------------------------------------------------
function spdb_table($table, $where='', $varcol='', $order='', $limit='', $rettype=OBJECT) {
	global $spIsForumAdmin;
	$selectfrom  = ' *';
	$whereclause = '';
	$orderby 	 = '';
	$qtype  	 = 'set';

	if ($varcol != '') {
		if ($varcol == 'row') {
			$qtype = 'row';
		} else {
			$selectfrom = ' '.$varcol;
			$qtype = 'var';
		}
	}
	if ($where != '') $whereclause = " WHERE $where";
	if ($order != '') $orderby = " ORDER BY $order";
	if ($limit != '') $limit = " LIMIT $limit";

	$result = '';
	if (empty($result)) {
		$sql = "SELECT $selectfrom FROM $table$whereclause$orderby$limit";
		$result = spdb_select($qtype, $sql, $rettype);
	}
	return $result;
}

# ------------------------------------------------------------------
# spdb_count()
#
# Version: 5.0
# DATABASE COUNT QUERY HANDLER
#
# Returns the count of record or zero if none
# Calls spdb_select()
#
#	$table:		Fully prefixed WP table name
#	$where:		Complete Where clause
# ------------------------------------------------------------------
function spdb_count($table, $where='') {
	$whereclause = '';
	if ($where != '') $whereclause = " WHERE $where";

	$sql = "SELECT COUNT(*) FROM $table$whereclause";

	$c = spdb_select('var', $sql);
	if (!$c) $c = 0;

	return $c;
}

# ------------------------------------------------------------------
# spdb_sum()
#
# Version: 5.0
# DATABASE SUM QUERY HANDLER
#
# Returns the sum of column values or zero if none
# Calls spdb_select()
#
#	$table:		Fully prefixed WP table name
#	$column:	Column mame to sum
#	$where:		Complete Where clause
# ------------------------------------------------------------------
function spdb_sum($table, $column, $where='') {
	$whereclause = '';
	if ($where != '') $whereclause = " WHERE $where";

	$sql = "SELECT SUM($column) FROM $table$whereclause";

	$c = spdb_select('var', $sql);
	if (!$c) $c = 0;

	return $c;
}

# ------------------------------------------------------------------
# spdb_max()
#
# Version: 5.0
# DATABASE MAX VALUE QUERY HANDLER
#
# Returns the max (highest number) of the field being queried
# Calls spdb_select()
#
#	$table:		Fully prefixed WP table name
#	$field:		Name of the column to be queried
#	$where:		Complete Where clause
# ------------------------------------------------------------------
function spdb_max($table, $field, $where='') {
	$whereclause= '';
	if ($where != '') $whereclause = " WHERE $where";

	$sql = "SELECT MAX($field) FROM $table$whereclause";

	$c = spdb_select('var', $sql);
	if (!$c) $c = 0;

	return (int) $c;
}

# ------------------------------------------------------------------
# spdb_select()
#
# Version: 5.0
# DATABASE SELECT QUERY HANDLER
#
# Returns the query results if good
# Returns false if good but no records
# Returns false if failed and displays error if sql invalid
# Populates spVars['queryrows'] with number of records
#
#	$querytype	Type of recordset to return:
#				'set', 'row', 'col', 'var' (default 'set')
#	$sql:		SQL query statement
#	$type:		return type:
#				ARRAY_A, ARRAY_N, OBJECT (default OBJECT)
#				NOTE: Only applies to 'set and 'row' types
# ------------------------------------------------------------------
function spdb_select($querytype, $sql, $resulttype=OBJECT) {
	global $wpdb, $spVars;

	$spVars['queryrows'] = 0;

	$wpdb->hide_errors();

	switch ($querytype) {
		case 'row':
			$records = $wpdb->get_row($sql, $resulttype);
			break;
		case 'col':
			$records = $wpdb->get_col($sql);
			break;
		case 'var':
			$records = $wpdb->get_var($sql);
			break;
		case 'set':
		default:
			$records = $wpdb->get_results($sql, $resulttype);
			break;
	}

	if ($wpdb->last_error == '') {
		$spVars['queryrows'] = $wpdb->num_rows;
	} else {
		sp_construct_database_error($sql, $wpdb->last_error);
	}
	return $records;
}

# ------------------------------------------------------------------
# spdb_query()
#
# Version: 5.0
# DATABASE INSERT/UPDATE/DELETE ETC., QUERY HANDLER
#
# Returns true of successful
# Returns false if failed and displays error if sql invalid
# Populates spVars['affectedrows'] with number of records
# If INSERT populates spVars['insertid'] with auto number
#
#	$sql:		SQL query statement
# ------------------------------------------------------------------
function spdb_query($sql) {
	global $wpdb, $spVars;

	$spVars['affectedrows'] = 0;
	$spVars['insertid'] = 0;

	$wpdb->hide_errors();

	$wpdb->query($sql);

	if ($wpdb->last_error == '') {
		$spVars['affectedrows'] = $wpdb->rows_affected;
		if (substr($sql, 0, 6) == 'INSERT') $spVars['insertid'] = $wpdb->insert_id;
		return true;
	} else {
		sp_construct_database_error($sql, $wpdb->last_error);
		return false;
	}
}

# Version: 5.0
class spdbComplex {
	var $table = '';
	var $found_rows = false;
	var $distinct = false;
	var $distinctrow = false;
	var $fields = '';
	var $join = '';
	var $left_join = '';
	var $right_join = '';
	var $where = '';
	var $groupby = '';
	var $orderby = '';
	var $limits = '';
	var $data = '';
	var $show = false;
	var $inspect = '';

	function select($type='set', $resulttype=OBJECT) {
		if (empty($this->table)) return '';
		$table = $this->table;

		$found_rows = (empty($this->found_rows)) ? '' : ' SQL_CALC_FOUND_ROWS';
		$distinct = (empty($this->distinct)) ? '' : ' DISTINCT';
		$distinctrow = (empty($this->distinctrow)) ? '' : ' DISTINCTROW';
		$where = (empty($this->where)) ? '' : " WHERE $this->where";
		$limits = (empty($this->limits)) ? '' : " LIMIT $this->limits";
		$fields = (empty($this->fields)) ? ' *' : " $this->fields";

		$join = '';
		if (!empty($this->join)) {
			if (is_array($this->join)) {
				foreach ($this->join as $j) {
					$join.= " JOIN $j";
				}
			} else {
				$join = " JOIN $this->join";
			}
		}

		$left_join = '';
		if (!empty($this->left_join)) {
			if (is_array($this->left_join)) {
				foreach ($this->left_join as $j) {
					$left_join.= " LEFT JOIN $j";
				}
			} else {
				$left_join = " LEFT JOIN $this->left_join";
			}
		}

		$right_join = '';
		if (!empty($this->right_join)) {
			if (is_array($this->right_join)) {
				foreach ($this->right_join as $j) {
					$right_join.= " RIGHT JOIN $j";
				}
			} else {
				$right_join = " RIGHT JOIN $this->right_join";
			}
		}

		$groupby = '';
		if (!empty($this->groupby)) {
			if (is_array($this->groupby)) {
				$groupby = ' GROUP BY';
				foreach ($this->groupby as $i => $g) {
					$groupby = ($i == 0) ? ' ' : ', ';
					$groupby.= $g;
				}
			} else {
				$groupby = " GROUP BY $this->groupby";
			}
		}

		$orderby = '';
		if (!empty($this->orderby)) {
			if (is_array($this->orderby)) {
				$orderby = ' ORDER BY';
				foreach ($this->orderby as $i => $o) {
					$orderby = ($i == 0) ? ' ' : ', ';
					$orderby.= $o;
				}
			} else {
				$orderby = " ORDER BY $this->orderby";
			}
		}

		$sql = "SELECT $found_rows$distinct$distinctrow$fields FROM $table$join$left_join$right_join$where$groupby$orderby$limits";
		if ($this->show) spdb_show_result($sql, $this->inspect);
		$records = spdb_select($type, $sql, $resulttype);
		return $records;
	}

	function update() {
		if (empty($this->table) || empty($this->fields) || empty($this->data) || !is_array($this->data) || ! is_array($this->fields)) return false;
		$table = $this->table;

		if (!empty($this->where)) $where = " WHERE $this->where";

		$dbfields = array();
		foreach ($this->fields as $index => $col) {
			$value = $this->data[$index];
			if (!is_numeric($value)) $value = "'$value'";
			$dbfields[] = "$col = $value";
		}

		$sql = "UPDATE $table SET ".implode(', ', $dbfields).$where;
		if ($this->show) spdb_show_result($sql, $this->inspect);
		$result = spdb_query($sql);
		return $result;
	}

	function insert() {
		if (empty($this->table) || empty($this->fields) || empty($this->data) || !is_array($this->data) || ! is_array($this->fields)) return false;
		$table = $this->table;

		$values = array();
		foreach ($this->data as $val) {
			# check if special ASCII 254; - means numbers to be treated as string
			if(substr($val, 0, 1) == chr(254)) {
				$val = "'".substr($val, 1)."'";
			} elseif (!is_numeric($val)) {
				$val = "'".$val."'";
			}
			$values[] = $val;
		}

		$sql = "INSERT INTO $table (".implode(', ', $this->fields).') VALUES ('.implode(', ', $values).')';
		if ($this->show) spdb_show_result($sql, $this->inspect);
		$result = spdb_query($sql);
		return $result;
	}
}

# ------------------------------------------------------------------
# spdb_flush()
#
# Version: 5.0
# Performs a flush of the $wpdb object
# ------------------------------------------------------------------
function spdb_flush() {
	global $wpdb;
	$wpdb->flush();
}

# ------------------------------------------------------------------
# spdb_charset()
#
# Version: 5.0
# returns users CHARSET for table creates
# ------------------------------------------------------------------
function spdb_charset() {
	global $wpdb;

	$charset='';

	if (!empty($wpdb->charset)) $charset = "DEFAULT CHARACTER SET $wpdb->charset";
	if (!empty($wpdb->collate)) $charset.= " COLLATE $wpdb->collate";

	return $charset;
}

# ------------------------------------------------------------------
# spdb_zone_datetime()
#
# Version: 5.0
# Sets timezone altered date time for sql queries
# Used mainly to set the post_date to the users timezone
#	$d:		sql field being queried
# ------------------------------------------------------------------
function spdb_zone_datetime($d, $addAs=true) {
	global $spThisUser;

	$addField = ($addAs == true) ? 'as '.$d : '';

	$zone = (isset($spThisUser->timezone)) ? $spThisUser->timezone : 0;

	if ($zone == 0) return $d;
	if ($zone < 0) {
		$out = 'DATE_SUB('.$d.', INTERVAL '.abs($zone).' HOUR) '.$addField;
	} else {
		$out = 'DATE_ADD('.$d.', INTERVAL '.abs($zone).' HOUR) '.$addField;
	}
	return $out;
}

# ------------------------------------------------------------------
# spdb_zone_mysql_checkdate()
#
# Version: 5.0
# Sets time zone altered compare date time for sql queries
# Used by the newpost list building queries
#	$d:		date to be altered (last_visit or check_time)
# ------------------------------------------------------------------
function spdb_zone_mysql_checkdate($d) {
	global $spThisUser;
	$zone = (isset($spThisUser->timezone)) ? $spThisUser->timezone : 0;

	if ($zone == 0) return $d;
	$ud = strtotime($d);
	if ($zone < 0 ? $ud = $ud + (abs($zone * 3600)) : $ud = $ud - (abs($zone * 3600)));

	return date('Y-n-d H:i:s', $ud);
}

# ------------------------------------------------------------------
# spdb_column_exists()
#
# Version: 5.1.4
# Checks if $column exists within $table
# ------------------------------------------------------------------
function spdb_column_exists($table, $column) {
	global $wpdb;
	return $wpdb->get_row('SHOW COLUMNS FROM '.$table." LIKE '".$column."'");
}

# ------------------------------------------------------------------
# spdb_connection()
#
# Version: 5.4.2
# Checks if we still have a database connection
# ------------------------------------------------------------------
function spdb_connection() {
	global $wpdb;
    $connection = (is_object($wpdb)) ? $wpdb->check_connection(false): false;
    return $connection;
}

# ------------------------------------------------------------------
# spdb_show_result()
#
# Version: 5.5.5
# Display the SQL in an inspector section
# ------------------------------------------------------------------
function spdb_show_result($sql, $inspect) {
	spdebug_styles(true);
	echo '<div class="spdebug">';
	echo sp_text('Inspect Query').': <strong>'.$inspect.'</strong><br><hr>';
	echo '<pre><code>';
	$k = array( "\t",
				"\n",
				'SELECT ',
				' DISTINCT ',
				'FROM ',
				'LEFT JOIN ',
				'RIGHT JOIN ',
				' JOIN ',
				'WHERE ',
				'ORDER BY ',
				'GROUP BY ',
				'LIMIT ',
				' ON ',
				' IN ',
				' DESC ',
				' ASC ',
				' DESC, ',
				' ASC, ',
				' AS ',
				' OR ',
				' AND ',
				' LIKE '
			);
	$r = array( '',
				'',
				"\n<b>SELECT</b> ",
				' <b>DISTINCT</b> ',
				"\n<b>FROM</b> ",
				"\n<b>LEFT JOIN</b> ",
				"\n<b>RIGHT JOIN</b> ",
				" \n<b>JOIN</b> ",
				"\n<b>WHERE</b> ",
				"\n<b>ORDER BY</b> ",
				"\n<b>GROUP BY</b> ",
				"\n<b>LIMIT</b> ",
				' <b>ON</b> ',
				' <b>IN</b> ',
				' <b>DESC</b> ',
				' <b>ASC</b> ',
				' <b>DESC</b>, ',
				' <b>ASC</b>, ',
				' <b>AS</b> ',
				' <b>OR</b> ',
				' <b>AND</b> ',
				' <b>LIKE</b> '
			);
	$sql = str_replace ($k , $r , $sql);
	echo $sql;
	echo '</code></pre>';
	echo '</div>';
}

?>
