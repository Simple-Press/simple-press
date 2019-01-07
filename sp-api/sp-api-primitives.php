<?php
/*
Simple:Press
DESC: API Primitive Routines
$LastChangedDate: 2017-06-04 13:39:58 -0500 (Sun, 04 Jun 2017) $
$Rev: 15407 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	Primitive functions dealing with Options/Members/Meta tables
#	and assorted base routines
#
# ==================================================================

# ==================================================================
# Translation text dimain functions - front and back end
# ==================================================================

# ------------------------------------------------------------------
# spa_text()
# spa_etext()
#
# Version: 5.0
# Admin translation routines
# run using the 'spa' domain
# ------------------------------------------------------------------
function spa_text($text) {
	return esc_attr(__($text, 'spa'));
}

# Version: 5.0
function spa_etext($text) {
	echo esc_attr(__($text, 'spa'));
}

# Version: 5.0
function spa_text_noesc($text) {
	return __($text, 'spa');
}

# ------------------------------------------------------------------
# sp_text()
# sp_etext()
#
# Version: 5.0
# Forum translation routines
# run using the 'sp' domain
# ------------------------------------------------------------------
function sp_text($text) {
	return esc_attr(__($text, 'sp'));
}

# Version: 5.0
function sp_etext($text) {
	echo esc_attr(__($text, 'sp'));
}

# Version: 5.0
function sp_text_noesc($text) {
	return __($text, 'sp');
}

# ==================================================================
# option record handlers - uses table SFOPTIONS
# ==================================================================

# ------------------------------------------------------------------
# sp_get_option()
# Version: 5.0
# Retrieves SP option record - loads options into the $spAllOptions
# array if not already done
#
#	$option_name:		unique name of option record
#
# returns value or false if option_name doesn't exist
# ------------------------------------------------------------------
function sp_get_option($option_name) {
	global $spAllOptions;

	# check if the all options array is loaded
	if (empty($spAllOptions)) $spAllOptions = sp_load_alloptions();

	# set return value if exists
	if (!empty($spAllOptions) && $spAllOptions && array_key_exists($option_name, $spAllOptions)) {
		$value = sp_option_check($spAllOptions[$option_name]);
		$value = maybe_unserialize($value);
	} else {
		$value = false;
	}
	return $value;
}

# ------------------------------------------------------------------
# sp_add_option()
# Version: 5.0
# Adds new SP option record - If option_name alrewady exists then
# the current setting is updated instead
#
# note: this function expects data to be sanitized before calling
#
#	$option_name:		unique name of option record
#	$value:				the value for the new option_name
#
# returns nothing
# ------------------------------------------------------------------
function sp_add_option($option_name, $value='') {
	global $wpdb, $spAllOptions;

	# make sure $spAllOptions has been populated before we try and use it
	if (empty($spAllOptions)) $spAllOptions = sp_load_alloptions();

	$value = maybe_serialize($value);
	$result = $wpdb->query('INSERT INTO '.SFOPTIONS." (option_name, option_value) VALUES ('$option_name', '$value') ON DUPLICATE KEY UPDATE option_name=VALUES(option_name), option_value=VALUES(option_value)");
	if ($result) {
        $spAllOptions[$option_name] = $value;
        return true;
    } else {
        return false;
    }
}

# ------------------------------------------------------------------
# sp_update_option()
# Version: 5.0
# Updates a SP option record - If option_name doesn't exists then
# a new option record is created
#
# note: this function expects data to be sanitized before calling
#
#	$option_name:		unique name of option record
#	$newvalue:			the value for the updated option_name
#
# returns true (success) false (failure or no data change)
# ------------------------------------------------------------------
function sp_update_option($option_name, $newvalue) {
	global $wpdb, $spAllOptions;

	$oldvalue = sp_get_option($option_name);
	if ($newvalue === $oldvalue) return false;

	if (!isset($spAllOptions[$option_name])) {
		$result = sp_add_option($option_name, $newvalue);
		return $result;
	}

	$newvalue = maybe_serialize($newvalue);
	$result = $wpdb->update(SFOPTIONS, array('option_value' => $newvalue), array('option_name' => $option_name) );
	if ($result) {
		$spAllOptions[$option_name] = $newvalue;
		return true;
	} else {
        return false;
    }
}

# ------------------------------------------------------------------
# sp_delete_option()
# Version: 5.0
# Deletes a SP option record if it exsists
#
#	$option_name:		unique name of option record
#
# returns true (success) false (failure or no option found)
# ------------------------------------------------------------------
function sp_delete_option($option_name) {
	global $spAllOptions;

    $option = spdb_select('row', 'SELECT option_id FROM '.SFOPTIONS." WHERE option_name = '$option_name'");
	if (is_null($option)) return false;
	$result = spdb_query('DELETE FROM '.SFOPTIONS." WHERE option_name = '$option_name'");
	if ($result) {
        unset($spAllOptions[$option_name]);
        return true;
    } else {
        return false;
    }
}

# ------------------------------------------------------------------
# sp_load_alloptions()
# Version: 5.0
# Loads all option data into the all $spAllOptions array
#
# returns array
# ------------------------------------------------------------------
function sp_load_alloptions() {
	global $spGlobals;
    # see if options table exists first
	$optionstable = spdb_select('var', "SHOW TABLES LIKE '".SF_PREFIX."sfoptions'");
	if (empty($optionstable)) return array();

	$alloptions = array();
	$alloptions_db = spdb_select('set', 'SELECT option_name, option_value FROM '.SFOPTIONS);
	foreach ((array) $alloptions_db as $opt) {
		$alloptions[$opt->option_name] = $opt->option_value;
	}
	return $alloptions;
}

# ------------------------------------------------------------------
# sp_option_check()
# Version: 5.0
# Ensures that null vaslues are not returned bu option records
#
# returns value (success) false (failure)
# ------------------------------------------------------------------
function sp_option_check($value=false) {
	if (isset($value) && (!empty($value) || $value == 0)) {
		return $value;
	} else {
		return false;
	}
}

# ==================================================================
# member record handlers - uses table SFMEMBERS
# ==================================================================

# ------------------------------------------------------------------
# sp_get_member_row()
#
# Version: 5.0
# returns the members table content for specified user.
# This us returned as an array - columns that require ot are
# NOT unserialized.
#	$userid:		User to lookup
# ------------------------------------------------------------------
function sp_get_member_row($userid) {
	global $spStatus;

	if ($spStatus != 'ok') return;

	$userid = (int) $userid;

	$member = spdb_table(SFMEMBERS, "user_id=$userid", 'row', '', '', ARRAY_A);
	return $member;
}

# ------------------------------------------------------------------
# sp_get_member_item()
#
# Version: 5.0
# returns a specified column from members table for specified user.
# This us returned as an var - columns that require it are
# returned unserialized.
#	$userid:		User to lookup
#	$item:			column name
# ------------------------------------------------------------------
function sp_get_member_item($userid, $item) {
	global $wpdb, $spThisUser;

	$userid = (int) $userid;

	$thisitem = $wpdb->get_var("SELECT $item FROM ".SFMEMBERS." WHERE user_id = $userid");
	$thisitem = maybe_unserialize($thisitem);
	$thisitem = apply_filters('sph_memberdata_item', $thisitem, $userid, $item);

	return $thisitem;
}

# ------------------------------------------------------------------
# sp_update_member_item()
#
# Version: 5.0
# updates a specified column from members table for specified user.
# Data requiring serialization must be passed as an array
# 'checktime' and 'lastvisit' are set to now() by the update code
#	$userid:		User to lookup
#	$itemname:		column name
#	$itemdata:		singe var or array
# ------------------------------------------------------------------
function sp_update_member_item($userid, $itemname, $itemdata) {
	global $wpdb, $spThisUser, $current_user;

	$userid = (int) $userid;

	# hive off for cache updating if current user
	$itemdata = "'".maybe_serialize($itemdata)."'";

	# set 'lastvisit' or 'checktime' to 'now'
	if ($itemname == 'lastvisit' || $itemname == 'checktime') {
		sp_set_server_timezone();
		$itemdata = "'".sp_apply_timezone(time(), 'mysql', $userid)."'";
	}

	$sql = 'UPDATE '.SFMEMBERS." SET $itemname = $itemdata WHERE user_id=$userid";
	$sql = apply_filters('sph_memberdata_update_query', $sql, $itemname, $itemdata, $userid);
	$updateditem = $wpdb->query($sql);

    # allow plugins to add data
    do_action('sph_memberdata_update', $userid, $itemname, $itemdata);

    return $updateditem;
}

# ------------------------------------------------------------------
# sp_reset_member_plugindata()
#
# Version: 5.5.5
# Clears all or a members plugin_data field - plugin data cache
#	$userid:		User whose data to clear
# ------------------------------------------------------------------
# Version: 5.5.5
function sp_reset_member_plugindata($userid='') {
	# reset all the members plugin data
	$where = '';
	if (!empty($userid)) $where = ' WHERE user_id='.$userid;

	spdb_query('UPDATE '.SFMEMBERS." SET plugin_data=''".$where);
}

# = USER ACTIVITY TABLE HANDLERS ====================
#
#	Activity Type Numbers need to be carefully assigned.
#
#	1	-	Watches
#	2	-	Give Post Thanks
#	3	-	Received Post Thanks
#	4	-	Mentions
#	5	-	Posts Rated
#	6	-	Topic Subscriptions
#	7	-	Forum Subscriptions
#   8   -   Reputation
#	9	-	Reserved - do not reuse
#	10	-	Anonymous poster
#
# ------------------------------------------------------------------
# sp_add_activity()
#
# Version: 5.4.1
# Adds a new record to the user activity table
#	$userid		ID of the user
#	$type:		The type of the activity record
#	$value:		value (integer - usually an ID pointer)
#	$meta:		any other misc value needed
#	$doCheck	Default true. If false do not check if exists
# ------------------------------------------------------------------
function sp_add_activity($userid, $type, $value, $meta='', $doCheck=true) {
	$userid = (int) $userid;
	if (empty($userid) || empty($type) || empty($value)) return false;

	# maybe need to check record does not already exist
	if ($doCheck) {
		if (sp_get_activity($userid, $type, $value, $meta) == true) return true;
	}

	# reset users plugin data
	sp_reset_member_plugindata($userid);

	$mCol = empty($meta) ? '' : ', meta_id';
	$mVal = empty($meta) ? '' : ', '.$meta;

	# so insert new record
	$sql = 'INSERT INTO '.SFUSERACTIVITY."(user_id, type_id, item_id $mCol) VALUES ($userid, $type, $value $mVal)";
	return spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_get_activity()
#
# Version: 5.4.1
# Gets an user activity status check
#	$userid		ID of the user
#	$type:		The type of the activity record
#	$value:		value (integer - usually an ID pointer)
#
#	Returns a true if record exists else a false
# ------------------------------------------------------------------
function sp_get_activity($userid, $type, $value, $meta='') {
	$userid = (int) $userid;
	if (empty($userid) || empty($type) || empty($value)) return false;

	$m = empty($meta) ? ' IS NULL' : ' = '.$meta;

	$sql = 'SELECT id FROM '.SFUSERACTIVITY." WHERE user_id=$userid AND type_id=$type AND item_id=$value AND meta_id $m";
	$check = spdb_select('var', $sql);
	if ($check) return true;
	return false;
}

# ------------------------------------------------------------------
# sp_get_all_activity()
#
# Version: 5.4.1
# Gets all records from the user activity table of specific type
#	$userid		ID of the user
#	$type:		The type of the activity record
#
#	Returns a default object array of item IDs
# ------------------------------------------------------------------
function sp_get_all_activity($userid, $type) {
	$userid = (int) $userid;
	if (empty($userid) || empty($type)) return false;

	$sql = 'SELECT item_id FROM '.SFUSERACTIVITY." WHERE user_id=$userid AND type_id=$type";
	return spdb_select('col', $sql);
}

# ------------------------------------------------------------------
# sp_get_activity_records()
#
# Version: 5.4.2
# Gets all records from activity table for specific user and type
#	$userid		ID of the user
#	$type:		The type of the activity record
#	$limit		(Optional) linit number of rows
#	$order		(Optional) use ASC instead of default DESC
#
#	Returns a default object array of item IDs
# ------------------------------------------------------------------
function sp_get_activity_records($userid, $type, $limit='', $order='DESC') {
	$userid = (int) $userid;
	if (empty($userid) || empty($type)) return false;

	return spdb_table(SFUSERACTIVITY, "user_id=$userid AND type_id=$type", '', "id $order", $limit);
}

# ------------------------------------------------------------------
# sp_get_activity_count()
#
# Version: 5.4.2
# Gets count of records from the user activity table of specific type
#	$userid		ID of the user
#	$type:		The type of the activity record
#
#	Returns the number of rows
# ------------------------------------------------------------------
function sp_get_activity_count($userid, $type) {
	$userid = (int) $userid;
	if (empty($userid) || empty($type)) return false;

	return spdb_count(SFUSERACTIVITY, "user_id=$userid AND type_id=$type");
}

# ------------------------------------------------------------------
# sp_get_activity_exists()
#
# Version: 5.5.3
# Gets count of records from the user activity table of specific type
# and key value
#	$type:		The type of the activity record
#	$value;		The key of $type
#
#	Returns the number of rows
# ------------------------------------------------------------------
function sp_get_activity_exists($type, $value) {
	$value = (int) $value;
	if (empty($value) || empty($type)) return false;

	return spdb_count(SFUSERACTIVITY, "item_id=$value AND type_id=$type");
}

# ------------------------------------------------------------------
# sp_get_activity_value()
#
# Version: 5.4.1
# Gets an array of user_ids by specific type and value
#	$type:		The type of the activity record
#	$value:		value (integer - usually an ID pointer)
#
#	Returns a true if record exists else a false
# ------------------------------------------------------------------
function sp_get_activity_value($type, $value) {
	if (empty($type) || empty($value)) return false;

	$sql = 'SELECT user_id FROM '.SFUSERACTIVITY." WHERE type_id=$type AND item_id=$value";
    return spdb_select('col', $sql);
}

# ------------------------------------------------------------------
# sp_get_activity_member_list()
#
# Version: 5.4.2
# Gets an array of user_ids by specific type and value
#	$type:		The type of the activity record
#	$value:		value (integer - usually an ID pointer)
#
#	Returns am object of user ids and display names
# ------------------------------------------------------------------
function sp_get_activity_member_list($type, $value) {
	if (empty($type) || empty($value)) return false;

	$sql =  'SELECT '.SFUSERACTIVITY.'.user_id, display_name
			 FROM '.SFUSERACTIVITY.'
			 JOIN '.SFMEMBERS.' ON '.SFUSERACTIVITY.'.user_id='.SFMEMBERS.'.user_id
			 WHERE type_id='.$type.' AND item_id='.$value;
	return spdb_select('set', $sql);
}

# ------------------------------------------------------------------
# sp_get_activity_type_ids()
#
# Version: 5.4.2
# Gets a col array of activity ids by specific WHERE clause
#	$where:		The where clause to use in the sql query
#
#	Returns a column array of activity ids
# ------------------------------------------------------------------
function sp_get_activity_type_ids($where) {
	if (empty($where)) return false;

	$sql =  'SELECT '.SFUSERACTIVITY.'.id
			 FROM '.SFUSERACTIVITY."
			 WHERE $where";
	return spdb_select('col', $sql);
}

# ------------------------------------------------------------------
# sp_delete_activity_id()
#
# Version: 5.4.2
# Deletes a records from the user activity table equal to id
#	$id:		Activity id
# ------------------------------------------------------------------
function sp_delete_activity_id($id) {
	if (empty($id)) return false;

	$sql = 'DELETE FROM '.SFUSERACTIVITY." WHERE id = $id";
	return spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_delete_activity()
#
# Version: 5.4.1
# Deletes a record from the user activity table
#	$userid		ID of the user
#	$type:		The type of the activity record
#	$value:		value (integer - usually an ID pointer)
# ------------------------------------------------------------------
function sp_delete_activity($userid, $type, $value) {
	$userid = (int) $userid;
	if (empty($userid) || empty($type) || empty($value)) return false;

	# reset users plugin data
	sp_reset_member_plugindata($userid);

    $sql = 'DELETE FROM '.SFUSERACTIVITY." WHERE user_id=$userid AND type_id=$type AND item_id=$value";
	return spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_delete_activity_value()
#
# Version: 5.4.2
# Deletes all records from the user activity table equal to
# type and value
#	$type:		The type of the activity record
#	$value:		value (integer - usually an ID pointer)
# ------------------------------------------------------------------
function sp_delete_activity_value($type, $value) {
	if (empty($type) || empty($value)) return false;

    $sql = 'DELETE FROM '.SFUSERACTIVITY." WHERE type_id=$type AND item_id=$value";
	return spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_delete_activity_meta()
#
# Version: 5.5.1
# Deletes all records from the user activity table equal to
# type and meta
#	$type:		The type of the activity record
#	$meta:		value (integer - usually an ID pointer)
# ------------------------------------------------------------------
function sp_delete_activity_meta($type, $meta) {
	if (empty($type) || empty($meta)) return false;

    $sql = 'DELETE FROM '.SFUSERACTIVITY." WHERE type_id=$type AND meta_id=$meta";
	return spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_delete_activity_type()
#
# Version: 5.4.2
# Deletes all records from the user activity table equal to
# type
#	$type:		The type of the activity record
# ------------------------------------------------------------------
function sp_delete_activity_type($type) {
	if (empty($type)) return false;

    $sql = 'DELETE FROM '.SFUSERACTIVITY." WHERE type_id=$type";
	return spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_delete_all_activity()
#
# Version: 5.4.1
# Deletes all records from the user activity table of specific type
#	$userid		ID of the user
#	$type:		The type of the activity record
# ------------------------------------------------------------------
function sp_delete_all_activity($userid, $type) {
	$userid = (int) $userid;
	if (empty($userid) || empty($type)) return false;

	# reset users plugin data
	sp_reset_member_plugindata($userid);

    $sql = 'DELETE FROM '.SFUSERACTIVITY." WHERE user_id=$userid AND type_id=$type";
	return spdb_query($sql);
}

# = META TABLE HANDLERS ====================
# ------------------------------------------------------------------
# sp_add_sfmeta()
#
# Version: 5.0
# Adds a new record to the sfmeta table
#	$type:		The type of the meta record
#	$key:		The unique key name
#	$value:		value (MUST be escaped by caller if needed)
#	$autoload	Automatically load into $spGlobals
# ------------------------------------------------------------------
function sp_add_sfmeta($type, $key, $value, $autoload=0) {
	if (empty($type) || empty($key)) return false;

	# Check if already exists
	$sql = 	'SELECT meta_id FROM '.SFMETA." WHERE meta_type='$type' AND meta_key='$key'";
	$check = spdb_select('var', $sql);

	# so - does it?
	if ($check) {
		# yes - so needs to be an update call
		sp_update_sfmeta($type, $key, $value, $check, $autoload);
	} else {
		$sql = 'INSERT INTO '.SFMETA.
			   "(meta_type, meta_key, meta_value, autoload)
			   VALUES
			   ('$type', '$key', '".wp_slash(maybe_serialize($value))."',$autoload)";
		spdb_query($sql);
	}
}

# ------------------------------------------------------------------
# sp_update_sfmeta()
#
# Version: 5.0
# Updates a record in the sfmeta table
#	$type:		The type of the meta record
#	$key:		The unique key name
#	$value:		value (MUST be escaped by caller if needed)
#	$id:		The meta records ID
# ------------------------------------------------------------------
function sp_update_sfmeta($type, $key, $value, $id, $autoload=0) {
	$sql =	'UPDATE '.SFMETA." SET
			 meta_type='$type',
			 meta_key='$key',
			 meta_value='".wp_slash(maybe_serialize($value))."',
			 autoload=$autoload
			 WHERE meta_id=$id";
	spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_get_sfmeta()
#
# Version: 5.0
# Gets a record(s) from the sfmeta table
#	$type:		The type of the meta record
#	$key:		The unique key name - can be false to get all of type
#	$id:		If set then returns by id (one row regardless of $key)
# ------------------------------------------------------------------
function sp_get_sfmeta($type, $key=false, $id=0) {
	$WHERE = " meta_type='$type'";

	if ($id) {
		$WHERE.= " AND meta_id=$id";
	} else {
		if ($key) $WHERE.= " AND meta_key='$key'";
	}
	$sql =  'SELECT * FROM '.SFMETA." WHERE $WHERE ORDER BY meta_id";
	$records = spdb_select('set', $sql, ARRAY_A);
    if ($records) {
        foreach ($records as &$record) {
            $record['meta_value'] = wp_unslash(maybe_unserialize($record['meta_value']));
        }
    }
	return $records;
}

# ------------------------------------------------------------------
# sp_get_sfmeta_key()
#
# Version: 5.4.2
# Returns the meta_id of a specific sfmeta record
#	$type:		The type of the meta record
#	$key:		The unique key name - can be false to get all of type
# ------------------------------------------------------------------
function sp_get_sfmeta_key($type, $key) {
	$sql = 'SELECT meta_id FROM '.SFMETA.
		   " WHERE meta_type = '$type' AND meta_key = '$key'";
	return spdb_select('var', $sql);
}

# ------------------------------------------------------------------
# sp_delete_sfmeta()
#
# Can delete by ID, by key or by key/type combination
#
# Version: 5.2.1
# Deletes a record in the sfmeta table
#	$id:		The meta records ID
#	$key:		The meta records key
#	$type:		The meta records type
# ------------------------------------------------------------------
function sp_delete_sfmeta($id=0, $key='', $type='') {
    $sql = '';
    if (!empty($id)) {
    	$sql = 'DELETE FROM '.SFMETA." WHERE meta_id=$id";
    } else if (!empty($key) && (!empty($type))) {
    	$sql = 'DELETE FROM '.SFMETA." WHERE meta_key='$key' AND meta_type='$type'";
    } else if (!empty($type)) {
    	$sql = 'DELETE FROM '.SFMETA." WHERE meta_type='$type'";
    } else if (!empty($key)) {
    	$sql = 'DELETE FROM '.SFMETA." WHERE meta_key='$key'";
    }
	if (!empty($sql)) spdb_query($sql);
}

# = SPECIAL RANKS HANDLING ====================
# Version 5.3.2
function sp_add_special_rank($userid, $rank) {
	$userid = (int) $userid;

	if (!sp_get_special_rank($userid, $rank)) {
		spdb_query('INSERT INTO '.SFSPECIALRANKS.' (user_id, special_rank) VALUES ('.$userid.', "'.$rank.'")');
	}
}

function sp_get_special_rank($userid, $rank='') {
	$userid = (int) $userid;

	$where = ' WHERE user_id='.$userid;
	if ($rank != '') $where.= ' AND special_rank="'.$rank.'"';
	return spdb_select('col', 'SELECT special_rank FROM '.SFSPECIALRANKS.$where);
}

function sp_delete_special_rank($userid, $rank='') {
	$userid = (int) $userid;

	$where = ' WHERE user_id='.$userid;
	if ($rank != '') $where.= ' AND special_rank="'.$rank.'"';
	spdb_query('DELETE FROM '.SFSPECIALRANKS.$where);
}

# = SPAM MATH HANDLING ========================
# Version: 5.0
function sp_math_spam_build() {
	$spammath[0] = rand(1, 12);
	$spammath[1] = rand(1, 12);

	# Calculate result
	$result = $spammath[0] + $spammath[1];

	# Add name of the weblog:
	$result.= get_bloginfo('name');
	# Add date:
	$result.= date('j').date('ny');
	# Get MD5 and reverse it
	$enc = strrev(md5($result));
	# Get only a few chars out of the string
	$enc = substr($enc, 26, 1).substr($enc, 10, 1).substr($enc, 23, 1).substr($enc, 3, 1).substr($enc, 19, 1);

	$spammath[2] = $enc;
	return $spammath;
}

# Version: 5.0
function sp_spamcheck() {
	$spamcheck = array();
	$spamcheck[0] = false;

	# Check dummy input field
	if (array_key_exists ('url', $_POST)) {
		if (!empty($_POST['url'])) {
			$spamcheck[0] = true;
			$spamcheck[1] = sp_text('Form not filled by human hands!');
			return $spamcheck;
		}
	}

	# Check math question
	$uKey = sp_get_option('spukey');
	$correct = sp_esc_str($_POST[$uKey.'2']);
	$test = sp_esc_str($_POST[$uKey.'1']);
	$test = preg_replace('/[^0-9]/','',$test);

	if ($test == '') {
		$spamcheck[0] = true;
		$spamcheck[1] = sp_text('No answer was given to the math question');
		return $spamcheck;
	}

	# Add name of the weblog:
	$test.= get_bloginfo('name');
	# Add date:
	$test.= date('j').date('ny');
	# Get MD5 and reverse it
	$enc = strrev(md5($test));
	# Get only a few chars out of the string
	$enc = substr($enc, 26, 1).substr($enc, 10, 1).substr($enc, 23, 1).substr($enc, 3, 1).substr($enc, 19, 1);

	if ($enc != $correct) {
		$spamcheck[0] = true;
		$spamcheck[1] = sp_text('The answer to the math question was incorrect');
		return $spamcheck;
	}
	return $spamcheck;
}

/* 	=====================================================================================

	sp_is_groupview()
	returns true if the current page being viewed is the spf group view (ie list of forums)
	parameters:
		none
 	===================================================================================*/

# Version: 5.0
function sp_is_groupview() {
	global $spVars;
	return $spVars['pageview'] == 'group';
}

/* 	=====================================================================================

	sp_is_forumview()
	returns true if the current page being viewed is the spf forum view (ie list of topics)
	parameters:
		none
 	===================================================================================*/

# Version: 5.0
function sp_is_forumview() {
	global $spVars;
	return $spVars['pageview'] == 'forum';
}

/* 	=====================================================================================
	sp_is_topicview()
	returns true if the current page being viewed is the spf topic view (ie list of posts)
 	===================================================================================*/
# Version: 5.0
function sp_is_topicview() {
	global $spVars;
	return $spVars['pageview'] == 'topic';
}

/* 	=====================================================================================
	sp_is_profileview()
	returns true if the current page being viewed is the spf profile view
 	===================================================================================*/
# Version: 5.0
function sp_is_profileview() {
	global $spVars;
	return ($spVars['pageview'] == 'profileedit' || $spVars['pageview'] == 'profileshow');
}

/* 	=====================================================================================
	sp_is_listview()
	returns true if the current page being viewed is a spf members list page
 	===================================================================================*/
# Version: 5.0
function sp_is_listview() {
	global $spVars;
	return $spVars['pageview'] == 'list';
}

/* 	=====================================================================================
	sp_is_searchview()
	returns true if the current page being viewed is the spf is from search results
 	===================================================================================*/
# Version: 5.0
function sp_is_searchview() {
	global $spVars;
	return $spVars['searchpage'] == 1;
}

/* 	=====================================================================================
	sp_is_forumpage()
	returns true if the current page being viewed is an spf page
 	===================================================================================*/
# Version: 5.0
function sp_is_forumpage() {
	global $spVars;
    return (!empty($spVars['page']));
}

# ------------------------------------------------------------------
# sp_create_slug()
#
# Create a new slug
#	$title:		Forum or Topic title
#	$checkdup	Check for duplicates (optional)
#	$table:		db table for dupe check
#	$column:	db column for dupe check
# ------------------------------------------------------------------
# Version: 5.0
function sp_create_slug($title, $checkdup=true, $table='', $column='') {
	$slug = sanitize_title($title);
	if ($checkdup) $slug = sp_check_slug_unique($slug, $table, $column);
	$slug = apply_filters('sph_create_slug', $slug, $table, $column);
	return $slug;
}

# ------------------------------------------------------------------
# sp_check_slug_unique()
#
# Version: 5.0
# Check new slug is unique and not used. Add numeric suffix if
# exists. If slug receved is empty then return empty.
#	$title:		Forum or Topic title new slug
#	$table:		db table for dupe check
#	$column:	db column for dupe check
# ------------------------------------------------------------------
function sp_check_slug_unique($title, $table, $column) {
	if (empty($title) || empty($table) || empty($column)) return '';

	$suffix = 1;
	$testtitle = $title;
	while (1) {
		$check = '';
		$check = spdb_table($table, "$column='$testtitle'", $column);
        if (empty($check)) break;
		$testtitle = $title.'-'.$suffix;
		$suffix++;
	}
	return $testtitle;
}

# = CENTRAL EMAIL ROUTINE =====================
# Version: 5.0
function sp_send_email($mailto, $mailsubject, $mailtext, $replyto='', $from='') {
	global $spGlobals;

	$spGlobals['fromAddress'] = $replyto;
    $sfmail = sp_get_option('sfmail');
    if ((isset($sfmail['sfmailuse']) && $sfmail['sfmailuse']) || (!empty($from))) {
		add_filter('wp_mail_from', 		'sp_mail_filter_from', 100);
		add_filter('wp_mail_from_name', 'sp_mail_filter_name', 100);
    }

    # reply-to goes in headers if provided
    $headers = (!empty($replyto)) ? "reply-to: $replyto" : '';

    # alert plugins before sending email
	do_action('sph_email_send_before');

	$email = wp_mail($mailto, $mailsubject, $mailtext, $headers);

    # alert plugins after sending email
	do_action('sph_email_send_after');

    # clear global from address
	$spGlobals['fromAddress'] = '';

    # prepare email response
	$email_sent = array();
	if ($email == false) {
		$email_sent[0] = false;
		$email_sent[1] = sp_text('Email notification failed');
	} else {
		$email_sent[0] = true;
		$email_sent[1] = sp_text('Email notification sent');
	}

	return $email_sent;
}

# ------------------------------------------------------------------
# sp_mail_filter_from()
#
# Version: 5.0
# Filter Call
# Sets up the 'from' email options
#	$from:		Passed in to filter
# ------------------------------------------------------------------
function sp_mail_filter_from($from) {
	global $spGlobals;

	$replyAddress = $spGlobals['fromAddress'];
	if (empty($replyAddress)) {
		$sfmail = sp_get_option('sfmail');
		$mailfrom = isset($sfmail['sfmailfrom']) ? $sfmail['sfmailfrom'] : '';
		$maildomain = isset($sfmail['sfmaildomain']) ? $sfmail['sfmaildomain'] : '';
		if ((!empty($mailfrom)) && (!empty($maildomain))) $from = $mailfrom.'@'.$maildomain;
	} else {
		$from = $replyAddress;
	}

    # remove the fitler that got us here
	remove_filter('wp_mail_from', 		'sp_mail_filter_from', 100);

	return $from;
}

# ------------------------------------------------------------------
# sp_mail_filter_name()
#
# Version: 5.0
# Filter Call
# Sets up the 'from' email options
#	$from:		Passed in to filter
# ------------------------------------------------------------------
function sp_mail_filter_name($from) {
	$sfmail = sp_get_option('sfmail');
	$mailsender = isset($sfmail['sfmailsender']) ? $sfmail['sfmailsender'] : '';
	if (!empty($mailsender)) $from = $mailsender;

    # remove the fitler that got us here
	remove_filter('wp_mail_from_name',  'sp_mail_filter_name', 100);

	return $from;
}

# ------------------------------------------------------------------
# sp_check_url()
#
# Version: 5.0
# Check url has http (else browser will assume relative link
#	$url:		URL to be checked
# ------------------------------------------------------------------
function sp_check_url($url) {
	if ($url == 'http://' || $url == 'https://') $url = '';
	return $url;
}

# ------------------------------------------------------------------
# sp_get_ip()
#
# Version: 5.0
# Return the IP address of the current user
# Checks HTTP_X_FORWARDED_FOR in case of proxy or load balancer
# ------------------------------------------------------------------
function sp_get_ip() {
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { # used by proxies and load balancers
	    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	    $ip = $_SERVER['HTTP_CLIENT_IP']; # client IP set
	} else if (!empty($_SERVER['REMOTE_ADDR'])) {
	    $ip = $_SERVER['REMOTE_ADDR']; # remmote address set
	} else {
	    $ip = ''; # general case - just return empty string
	}

	if (strpos($ip, ',') !== false) {
        $ip_array = explode(',', $ip);
        $ip = array_pop($ip_array);

    }
    if ($ip != '') $ip = long2ip(ip2long($ip));

	return $ip;
}

# ------------------------------------------------------------------
# sp_array_insert()
#
# Version: 5.0
# Inserts a value into specified location within an array
# ------------------------------------------------------------------
function sp_array_insert(&$array, $value, $offset) {
    if (is_array($array)) {
        $array  = array_values($array);
        $offset = intval($offset);
        if ($offset < 0 || $offset >= count($array)) {
            array_push($array, $value);
        } else if ($offset == 0) {
            array_unshift($array, $value);
        } else {
            $temp  = array_slice($array, 0, $offset);
            array_push($temp, $value);
            $array = array_slice($array, $offset);
            $array = array_merge($temp, $array);
        }
    } else {
        $array = array($value);
    }
    return count($array);
}

# Version: 5.0
function sp_strpos_arr($haystack, $needle) {
    if (!is_array($needle)) $needle = array($needle);
    foreach ($needle as $what) {
        if (($pos = strpos($haystack, $what)) !== false) return $pos;
    }
    return false;
}

# Version 5.2.3
function sp_array_search_multi($array, $key, $value) {
    $results = array();

    if (is_array($array)) {
        if (isset($array[$key]) && $array[$key] == $value) $results[] = $array;

        foreach ($array as $subarray) {
            $results = array_merge($results, sp_array_search_multi($subarray, $key, $value));
        }
    }

    return $results;
}

# Version 5.2.3
function sp_array_msort($array, $cols) {
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) {
            $colarr[$col]['_'.$k] = strtolower($row[$col]);
        }
    }
    $params = array();
    foreach ($cols as $col => $order) {
        $params[]=& $colarr[$col];
        $params = array_merge($params, (array) $order);
    }
    call_user_func_array('array_multisort', $params);
    $ret = array();
    $keys = array();
    $first = true;
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            if ($first) $keys[$k] = substr($k,1);
            $k = $keys[$k];
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
        $first = false;
    }
    return $ret;
}

# ------------------------------------------------------------------
# sp_remove_dir()
#
# Version: 5.1.3
# Recursively removes a directory and its contents
# ------------------------------------------------------------------
function sp_remove_dir($dir) {
    if (is_dir($dir)) {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                sp_remove_dir($file);
            } else {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }
}

?>