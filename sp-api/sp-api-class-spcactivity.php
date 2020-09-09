<?php

/**
 * Core class used for user activity.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * add_type($name)
 * get_type($name)
 * delete_type($name)
 * add($userid, $type, $value, $meta, $doCheck)
 * delete($args)
 * get($args)
 * get_col($args)
 * get_users($type, $item)
 * exist($args)
 * count ($args)
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcActivity {
	/** = USER ACTIVITY TABLE HANDLERS ====================
	*
	*	Activity Type Numbers need to be carefully assigned
	*
	*	1	-	Watches
	*	2	-	Give Post Thanks
	*	3	-	Received Post Thanks
	*	4	-	Mentions
	*	5	-	Posts Rated
	*	6	-	Topic Subscriptions
	*	7	-	Forum Subscriptions
	*   8   -   Reputation
	*	9	-	Reserved - do not reuse
	*	10	-	Anonymous poster
	*
	*/

	/**
	 * Holds a cached list of activity types.
	 *
	 * @var array
	 */
	private $activity_types = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * This method allows for creation/registration of new activity types.
	 * If the activity being registered already exists, the current activity ID is returned.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $name name of the activity type being registered
	 *
	 * @returns    bool    activity ID if already exists or created, otherwise false
	 */
	public function add_type($name) {
		# see if the activity type already exists - if so, return the ID
		$activity_id = $this->get_type($name);
		if (!$activity_id) {
			$name = SP()->saveFilters->title($name);

			# insert the new activity type into the database
			$query         = new stdClass();
			$query->table  = SPUSERACTIVITYTYPE;
			$query->fields = array('activity_name');
			$query->data   = array($name);
			$success       = SP()->DB->insert($query);

			# if created, return the new ID, othewise return false
			if ($success) {
				# retrieve the new activity type ID
				$activity_id = SP()->rewrites->pageData['insertid'];

				# fill the activity type cache with the new activity type
				$this->activity_types[$name]                = new stdClass();
				$this->activity_types[$name]->activity_name = $name;
				$this->activity_types[$name]->activity_id   = $activity_id;
			} else {
				$activity_id = false;
			}
		}

		return $activity_id;
	}

	/**
	 * This method allows for retrieving the activity ID for a previously registered activity type.
	 * If the activity type being requested does not exist, false will be returned.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $name name of the activity type being requested
	 *
	 * @returns    bool    activity ID of the specified activity if it exists, otherwise false
	 */
	public function get_type($name) {
		# check if we need to fill the activity type cache from db
		if (empty($this->activity_types)) $this->load();

		$name = SP()->saveFilters->title($name);

		# see if the activity type already exists - if so, return the ID
		$activity_id = (isset($this->activity_types[$name])) ? $this->activity_types[$name]->activity_id : false;

		return $activity_id;
	}

	/**
	 * This method allows for deleting an activity type.
	 * If the activity type does not exist, false will be returned.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $name name of the activity type being requested
	 *
	 * @returns    bool    activity ID of the specified activity if it exists, otherwise false
	 */
	public function delete_type($name) {
		# check if we need to fill the activity type cache from db
		if (empty($this->activity_types)) $this->load();

		$activity_id = $this->get_type($name);
		if ($activity_id) {
			$name = SP()->saveFilters->title($name);

			# delete the activity type from the database
			$query        = new stdClass();
			$query->table = SPUSERACTIVITYTYPE;
			$query->where = "activity_name = '$name'";
			$success      = SP()->DB->delete($query);

			# remove the cache item as well
			if ($success) unset($this->activity_types[$name]);
		} else {
			$success = false;
		}

		return $success;
	}

	/**
	 * This method adds a new user activity entry into the activity database
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int  $userid  ID of the user for th eactivity
	 * @param int  $type    :        type of the activity record
	 * @param int  $item    :        value of the activty
	 * @param int  $meta    :        other meta (additional) value for for the activity
	 * @param bool $doCheck Default true. If false do not check if exists
	 *
	 * @returns bool    true if the activity entry was successful, otherwise false
	 */
	public function add($userid, $type, $item, $meta = '', $doCheck = true) {
		# make sure that we have integer userid and values for userid, type and vlaue
		if (empty($userid) || empty($type) || empty($item)) return false;

		# if doing existence check and it exists, just return true
		if ($doCheck && $this->exist("type=$type&uid=$userid&item=$item&meta=$meta")) return true;

		if (empty($meta)) $meta = 'NULL';

		# insert new record
		$query         = new stdClass();
		$query->table  = SPUSERACTIVITY;
		$query->fields = array('user_id', 'type_id', 'item_id');
		$query->data   = array($userid, $type, $item);
		if (!empty($meta)) {
			$query->fields[] = 'meta_id';
			$query->data[]   = $meta;
		}
		$success = SP()->DB->insert($query);

		# reset users plugin data if successful
		if ($success) SP()->memberData->reset_plugin_data($userid);

		return $success;
	}

	/**
	 * This method deletes activity entries based upon the specific argument criteria.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $args array of arguments to specify what to delete
	 *
	 * @returns bool    true if the activities deletion was successful, otherwise false
	 */
	public function delete($args) {
		$defaults = array('id' => '', 'uid' => '', 'type' => '', 'item' => '', 'meta' => '');

		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);

		# now lets figure out what we want to delete
		$success = false;
		if (!empty($id)) {
			$query        = new stdClass();
			$query->table = SPUSERACTIVITY;
			$query->where = "id = $id";
			$success      = SP()->DB->delete($query);
		} else if (!empty($uid) && (!empty($type))) {
			$query        = new stdClass();
			$query->table = SPUSERACTIVITY;
			$query->where = "user_id=$uid AND type_id=$type";
			if (!empty($item)) $query->where .= " AND item_id=$item";
			$success = SP()->DB->delete($query);
		} else if (!empty($type)) {
			$query        = new stdClass();
			$query->table = SPUSERACTIVITY;
			$query->where = "type_id=$type";
			if (!empty($item)) $query->where .= " AND item_id=$item";
			if (!empty($meta)) $query->where .= " AND meta_id=$meta";
			$success = SP()->DB->delete($query);
		}

		# reset member plugin data - if no $uid passed in, will reset for all members
		if ($success) SP()->memberData->reset_plugin_data($uid);

		return $success;
	}

	/**
	 * This method gets a set of data for user activity.
	 * Supported for type/uid requests.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $args array of arguments to specify set of data to get
	 *
	 * @returns array    set of data from user activity
	 */
	public function get($args) {
		$defaults = array('uid' => '', 'type' => '', 'limit' => '', 'order' => 'DESC');

		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);

		if (empty($uid) || empty($type)) return array();

		$query          = new stdClass();
		$query->type    = 'set';
		$query->table   = SPUSERACTIVITY;
		$query->fields  = '*';
		$query->where   = "user_id=$uid AND type_id=$type";
		$query->orderby = "id $order";
		$query->limit   = $limit;
		$data           = SP()->DB->select($query);

		return $data;
	}

	/**
	 * This method gets a column of data for user activity.
	 * item ids returned for type/uid requests.
	 * user ids returned for type/item requests.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $args array of arguments to specify column of data to get
	 *
	 * @returns array    ids (user ids or item ids currently)
	 */
	public function get_col($args) {
		$defaults = array('col' => 'id', 'uid' => '', 'type' => '', 'item' => '', 'limit' => '', 'order' => 'DESC');

		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);

		$data = array();
		if ($col = 'item' && !empty($uid) && !empty($type)) {
			$query         = new stdClass();
			$query->type   = 'col';
			$query->table  = SPUSERACTIVITY;
			$query->fields = 'item_id';
			$query->where  = "user_id=$uid AND type_id=$type";
			$data          = SP()->DB->select($query);
		} else if ($col = 'uid' && !empty($type) && !empty($item)) {
			$query         = new stdClass();
			$query->type   = 'col';
			$query->table  = SPUSERACTIVITY;
			$query->fields = 'user_id';
			$query->where  = "type_id=$type AND item_id=$item";
			$data          = SP()->DB->select($query);
		} else if ($col = 'id' && !empty($uid) && !empty($type)) {
			$query          = new stdClass();
			$query->type    = 'var';
			$query->table   = SPUSERACTIVITY;
			$query->fields  = 'id';
			$query->where   = "user_id=$uid AND type_id=$type";
			$query->orderby = "id $order";
			$query->limit   = $limit;
			$data           = SP()->DB->select($query);
		}

		return $data;
	}

	/**
	 * This method gets user id and display name pair for the specified activity.
	 * user ids returned for type/item requests.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $args array of arguments to specify column of data to get
	 *
	 * @returns array    ids (user ids or item ids currently)
	 */
	public function get_users($type, $item) {
		if (empty($type) || empty($item)) return array();

		$query         = new stdClass();
		$query->type   = 'set';
		$query->table  = SPUSERACTIVITY;
		$query->fields = SPUSERACTIVITY.'.user_id, display_name';
		$query->join   = array(SPMEMBERS.' ON '.SPUSERACTIVITY.'.user_id='.SPMEMBERS.'.user_id');
		$query->where  = "type_id=$type AND item_id=$item";
		$data          = SP()->DB->select($query);

		return $data;
	}

	/**
	 * This method checks for the existence of the specified activity.
	 * user ids returned for type/item requests.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $args array of arguments to specify existence check
	 *
	 * @return bool
	 */
	public function exist($args) {
		$defaults = array('uid' => '', 'type' => '', 'item' => '', 'meta' => '');

		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);

		if (empty($uid) || empty($type) || empty($item)) return false;

		$query         = new stdClass();
		$query->type   = 'var';
		$query->table  = SPUSERACTIVITY;
		$query->fields = 'id';
		$query->where  = "user_id=$uid AND type_id=$type AND item_id=$item";
		if (!empty($meta)) $query->where .= " AND meta_id=$meta";
		$exist = SP()->DB->select($query);

		if ($exist) return true;

		return false;
	}

	/**
	 * This method checks for the existince of an activity record.
	 * Existence checking for uid/type/item and uid/type/item/meta supported.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $args array of arguments to specify what to count
	 *
	 * @returns int        number of activity entries found matcing the criteria
	 */
	public function count($args) {
		$defaults = array('uid' => '', 'type' => '', 'item' => '',);

		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);

		# now lets figure out what we want to count
		$count = 0;
		if (!empty($uid) && !empty($type)) {
			$count = SP()->DB->count(SPUSERACTIVITY, "user_id=$uid AND type_id=$type");
		} else if (empty($item) || empty($type)) {
			$count = SP()->DB->count(SPUSERACTIVITY, "item_id=$item AND type_id=$type");
		}

		return $count;
	}

	/**
	 * Loads cached object of all activity types.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function load() {
		# make sure the activity type table exists
		$exist = SP()->DB->tableExists(SPUSERACTIVITYTYPE);
		if (!$exist) return;

		# grab the activity_type from the database
		$query                = new stdClass();
		$query->resultType    = OBJECT_K;
		$query->type          = 'set';
		$query->table         = SPUSERACTIVITYTYPE;
		$query->fields        = 'activity_name, activity_id';
		$this->activity_types = SP()->DB->select($query);
	}
}