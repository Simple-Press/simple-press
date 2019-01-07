<?php

/**
 * Core class used for accessing member data in the database (sfmembers table).
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * get($userid, $item)
 * update($userid, $itemname, $itemdata)
 * reset_plugin_data($userid)
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcMemberData {
	/**
	 * This method provides for fetching a whole row of member data or just a single item.
	 * if the function argument $item is ommited, the whole row is fetched.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int    $userid ID of user to fetch member data for
	 * @param string $item   the single item of member data to fetch
	 *
	 * @returns    bool    member data for specified user/item
	 */
	public function get($userid, $item = '') {
		if (SP()->core->status != 'ok') return false;

		$userid = (int)$userid;

		$query         = new stdClass();
		$query->table  = SPMEMBERS;
		$query->fields = $item;
		$query->where  = "user_id=$userid";
		if (empty($item)) {
			$query->resultType = ARRAY_A;
			$query->type       = 'row';
			$data              = SP()->DB->select($query);
		} else {
			$query->type = 'var';
			$data        = SP()->DB->select($query);
			$data        = maybe_unserialize($data);
		}

		return $data;
	}

	/**
	 * This method provides for updating a user's member data item.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int    $userid   ID of user to udpate member data
	 * @param string $itemname the single item of member data to update
	 * @param mixed  $itemdata value of memberdata to be updated
	 *
	 * @returns    bool    returns true if update successful, otherwise false
	 */
	public function update($userid, $itemname, $itemdata) {
		$userid = (int)$userid;

		# set 'lastvisit' or 'checktime' to 'now'
		if ($itemname == 'lastvisit' || $itemname == 'checktime') {
			SP()->dateTime->set_timezone();
			$itemdata = SP()->dateTime->apply_timezone(time(), 'mysql', $userid);
		}

		$query         = new stdClass;
		$query->table  = SPMEMBERS;
		$query->fields = array($itemname);
		$query->data   = array(maybe_serialize($itemdata));
		$query->where  = "user_id=$userid";
		$query         = apply_filters('sph_memberdata_update_query', $query, $itemname, $itemdata, $userid);
		$success       = SP()->DB->update($query);

		# allow plugins to add data
		do_action('sph_memberdata_update', $userid, $itemname, $itemdata);

		return $success;
	}

	/**
	 * This method provides for clearing the member plugin data.
	 * If user id is specified, cleared only for that user.  Otherwise, cleared for all users.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $userid ID of user to clear plugin data
	 *
	 * @returns    bool    returns true if update successful, otherwise false
	 */
	public function reset_plugin_data($userid = '') {
		# reset all the members plugin data
		$query         = new stdClass;
		$query->table  = SPMEMBERS;
		$query->fields = array('plugin_data');
		$query->data   = array('');
		if (!empty($userid)) $query->where = "user_id=$userid";
		$success = SP()->DB->update($query);

		return $success;
	}
}