<?php

/**
 * Core class used for user activity.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * add($type, $value)
 * delete($type)
 * get($type)
 * clean()
 * flush($type)
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcCache {
	private $data = array();

	/**
	 * This method allows for adding a cache entry for the specified type.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type type of cache entry to be made
	 *
	 * @returns    bool    true if entry added, otherwise false
	 */
	public function add($type, $value) {
		if (SP()->core->status != 'ok') return false;

		if (empty($type) || empty($value)) return false;

		$this->setup($type);

		$now = (time() + $this->data['lifespan']);

		$query          = new stdClass();
		$query->table   = SPCACHE;
		$query->replace = ($this->data['deleteBefore']);
		$query->fields  = array('cache_id', 'cache_out', 'cache');
		$query->data    = array($this->data['datakey'], $now, wp_slash(serialize($value)));
		$success        = SP()->DB->insert($query);

		return $success;
	}

	/**
	 * This method allows for deleting a cache entry for the specified type.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type type of cache entry to be deleted
	 *
	 * @returns    bool    true if entry deleted, otherwise false
	 */
	public function delete($type) {
		if (SP()->core->status != 'ok') return false;

		if (empty($type)) return false;

		$this->setup($type);

		$query        = new stdClass();
		$query->table = SPCACHE;
		$query->where = "cache_id = '".$this->data['datakey']."'";
		$success      = SP()->DB->delete($query);

		return $success;
	}

	/**
	 * This method allows getting a cache entry for the specified type.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type type of cache entry to get
	 *
	 * @returns    mixed    cached entry from database
	 */
	public function get($type) {
		if (SP()->core->status != 'ok') return false;

		if (empty($type)) return false;

		$this->setup($type);

		$query         = new stdClass();
		$query->type   = 'var';
		$query->table  = SPCACHE;
		$query->fields = 'cache';
		$query->where  = "cache_id = '".$this->data['datakey']."'";
		$record        = SP()->DB->select($query);

		# are we supposed to delete after fetching?
		if ($this->data['deleteAfter']) {
			$query        = new stdClass();
			$query->table = SPCACHE;
			$query->where = "cache_id = '".$this->data['datakey']."'";
			SP()->DB->delete($query);
		}

		return wp_unslash(unserialize($record));
	}

	/**
	 * This method removes all expired cache entries.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @returns    bool    true if cache cleaned, otherwise false
	 */
	public function clean() {
		if (SP()->core->status != 'ok') return false;

		$now = time();

		$query         = new stdClass();
		$query->table  = SPCACHE;
		$query->fields = 'cache';
		$query->where  = "cache_out < $now";
		$success       = SP()->DB->delete($query);

		return $success;
	}

	/**
	 * This method flushes (empties) the cache database table for the specified type.
	 * If type=all, all cache entries are removed.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type type of cache entry to be flushed
	 *
	 * @returns    bool    true if cache flushed, otherwise false
	 */
	public function flush($type = 'all') {
		if (SP()->core->status != 'ok') return false;

		if ($type == 'all') {
			$success = SP()->DB->truncate(SPCACHE);
		} else {
			global $wpdb;

			$query         = new stdClass();
			$query->table  = SPCACHE;
			$query->fields = 'cache';
			$query->where  = "cache_id LIKE '%*".SP()->filters->esc_sql($wpdb->esc_like($type))."'";
			$success       = SP()->DB->delete($query);
		}

		return $success;
	}

	/**
	 * This method flushes (empties) the cache database table for the specified type.
	 * If type=all, all cache entries are removed.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @param string $type type of cache entry to be set up
	 *
	 * @returns    void
	 */
	private function setup($type) {
		switch ($type) {
			case 'xml':
				$this->data['datakey']      = 'xml';
				$this->data['lifespan']     = 3600;
				$this->data['deleteBefore'] = true;
				$this->data['deleteAfter']  = false;
				break;

			case 'search':
			case 'url':
			case 'bookmark':
			case 'plugin':
				$this->data['datakey']      = sp_get_ip();
				$this->data['lifespan']     = 3600;
				$this->data['deleteBefore'] = true;
				$this->data['deleteAfter']  = false;
				break;

			case 'group':
				$this->data['datakey']      = SP()->user->thisUser->ID;
				$this->data['lifespan']     = 3600;
				$this->data['deleteBefore'] = true;
				$this->data['deleteAfter']  = false;
				break;

			case 'post':
				$this->data['datakey']      = sp_get_ip();
				$this->data['lifespan']     = 120;
				$this->data['deleteBefore'] = true;
				$this->data['deleteAfter']  = true;
				break;

			case 'topic':
				$this->data['datakey']      = sp_get_ip();
				$this->data['lifespan']     = 120;
				$this->data['deleteBefore'] = true;
				$this->data['deleteAfter']  = false;
				break;

			case 'floodcontrol':
				$this->data['datakey']      = sp_get_ip();
				$this->data['lifespan']     = SP()->options->get('floodcontrol');
				$this->data['deleteBefore'] = true;
				$this->data['deleteAfter']  = false;
				break;
		}

		$this->data['datakey'] .= '*'.$type;
	}
}