<?php

/**
 * Core class used for user activity.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * add($type, $key, $value)
 * update($type, $key, $value, $id)
 * get($type, $key, $id)
 * get_value($type, $key, $id)
 * get_id($type, $key)
 * delete($id, $key, $type)
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcMeta {
	/**
	 * Holds a list of all meta data in database row format.
	 *
	 * @var array
	 */
	private $meta = array();

	/**
	 * indicated meta cache has been filled.
	 *
	 * @var bool
	 */
	private $meta_loaded = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Loads all meta.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function load() {
		# make sure the options table exists
		$exist = SP()->DB->tableExists(SPMETA);
		if (!$exist) return;

		# grab the options from the database
		$query             = new stdClass();
		$query->type       = 'set';
		$query->resultType = ARRAY_A;
		$query->table      = SPMETA;
		$this->meta        = SP()->DB->select($query);

		# unserialize the data in cache so we dont have to do on every get method
		if (!empty($this->meta)) {
			foreach ($this->meta as &$entry) {
				$entry['meta_value'] = wp_unslash(maybe_unserialize($entry['meta_value']));
			}
		}

		# meta now loaded
		$this->meta_loaded = true;

		# create topic/post cache if found to be missing
		if (empty($this->meta['topic_cache']['new'])) $this->rebuild_topic_cache();
	}

	/**
	 * This method rebuilds the topic cache.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @returns    void
	 */
	public function rebuild_topic_cache() {
		$size = SP()->options->get('topic_cache');
		if (!isset($size) || $size == 0) SP()->options->add('topic_cache', 200);

		$query              = new stdClass();
		$query->table       = SPPOSTS;
		$query->distinctrow = true;
		$query->fields      = 'forum_id, topic_id, post_id, post_status';
		$query->limits      = $size;
		$query->orderby     = 'post_id DESC';
		$query->resultType  = ARRAY_N;
		$query              = apply_filters('sph_topic_cache_select', $query);
		$topics             = SP()->DB->select($query);

		# update topic cache with current topics
		$id = $this->get_id('topic_cache', 'new');
		if (!empty($id)) {
			$this->update('topic_cache', 'new', $topics, $id);
		} else {
			# first time
			$this->add('topic_cache', 'new', $topics);
		}

		# Delete group level cache for good measure
		SP()->cache->flush('group');
	}

	/**
	 * This method gets an existing meta id for a $key/type pari
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type meta type
	 * @param string $key  meta key
	 *
	 * @returns mixed        id of the meta key/type pair, empty string if not found
	 */
	public function get_id($type, $key) {
		if (empty($type) && empty($key)) return false;

		# check if we need to fill the cache from db
		if (!$this->meta_loaded) $this->load();

		# get the meta id from the cache
		$data = array_values(wp_list_filter($this->meta, array('meta_type' => $type, 'meta_key' => $key)));
		$id   = (!empty($data)) ? $data[0]['meta_id'] : '';

		return $id;
	}

	/**
	 * This method updates an existing meta entry
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type  meta type
	 * @param string $key   meta key
	 * @param mixed  $value meta value
	 *
	 * @returns bool    true if the meta entry was successfully updated, otherwise false
	 */
	public function update($type, $key, $value, $id) {
		# update database
		$query         = new stdClass;
		$query->table  = SPMETA;
		$query->fields = array('meta_type', 'meta_key', 'meta_value');
		$query->data   = array($type, $key, wp_slash(maybe_serialize($value)));
		$query->where  = "meta_id=$id";
		$success       = SP()->DB->update($query);

		# if updated in db, lets add to cache
		if ($success) {
			# check if we need to fill the cache from db
			if (!$this->meta_loaded) $this->load();

			# update cache
			$entry              = array('meta_id' => $id, 'meta_type' => $type, 'meta_key' => $key, 'meta_value' => $value);
			$data               = wp_list_filter($this->meta, array('meta_id' => $id));
			$index              = key($data); # grab index into cache
			$this->meta[$index] = $entry;
		}

		return $success;
	}

	/**
	 * This method adds a new meta entry
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type  meta type
	 * @param string $key   meta key
	 * @param mixed  $value meta value
	 *
	 * @returns bool    true if the meta entry was successful, otherwise false
	 */
	public function add($type, $key, $value) {
		if (empty($type) || empty($key)) return false;

		# add to db
		$query                = new stdClass();
		$query->table         = SPMETA;
		$query->duplicate_key = true;
		$query->fields        = array('meta_type', 'meta_key', 'meta_value');
		$query->data          = array($type, $key, wp_slash(maybe_serialize($value)));
		$success              = SP()->DB->insert($query);

		# if added to db, lets add to cache
		if ($success) {
			# check if we need to fill the cache from db
			if (!$this->meta_loaded) $this->load();

			# add to cache
			$entry = array('meta_id' => SP()->rewrites->pageData['insertid'], 'meta_type' => $type, 'meta_key' => $key, 'meta_value' => $value);

			# does it exist in cache?
			$data = wp_list_filter($this->meta, array('meta_type' => $type, 'meta_key' => $key));
			if (!empty($data)) {
				$index              = key($data); # grab index into cache
				$this->meta[$index] = $entry;
			} else {
				$this->meta[] = $entry;
			}
		}

		return $success;
	}

	/**
	 * This method gets an existing meta entry
	 * if $id is set, returns one row regardless of $key
	 * if $key is omitted, will return all of type
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type meta type
	 * @param string $key  meta key
	 * @param int    $id   meta id
	 *
	 * @returns mixed        unserialized meta entries
	 */
	public function get($type, $key = false, $id = 0) {
		# check if we need to fill the cache from db
		if (!$this->meta_loaded) $this->load();

		# based on arguments, create the mysql where clause
		$search = $this->search_setup($type, $key, $id);

		# get the data from the cache
		$records = array_values(wp_list_filter($this->meta, $search));

		return $records;
	}

	/**
	 * This method gets a single existing meta_value array
	 * If the meta_type can have multiple meta_key entries, get_values() should be used instead
	 * if $id is set, returns one row regardless of $key
	 * if $key is omitted, will return all of type
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type meta type
	 * @param string $key  meta key
	 * @param int    $id   meta id
	 *
	 * @returns array        unserialized meta values
	 */
	public function get_value($type, $key = false, $id = 0) {
		# check if we need to fill the cache from db
		if (!$this->meta_loaded) $this->load();

		# based on arguments, create the mysql where clause
		$search = $this->search_setup($type, $key, $id);

		# get the data from the cache
		$record = array_values(wp_list_filter($this->meta, $search));

		# set up return value
		$value = (empty($record)) ? '' : $record[0]['meta_value'];

		return $value;
	}

	/**
	 * This method gets an existing meta_value entry array indexed by the meta_key
	 * if $id is set, returns one row regardless of $key
	 * if $key is omitted, will return all of type
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $type meta type
	 * @param string $key  meta key
	 * @param int    $id   meta id
	 *
	 * @returns array        unserialized meta values indexed by meta_key
	 */
	public function get_values($type, $key = false, $id = 0) {
		# check if we need to fill the cache from db
		if (!$this->meta_loaded) $this->load();

		# based on arguments, create the mysql where clause
		$search = $this->search_setup($type, $key, $id);

		# get the data from the cache
		$records = array_values(wp_list_filter($this->meta, $search));

		# now just grab all the meta values
		$values = array();
		if (!empty($records)) {
			foreach ($records as $entry) {
				$values[$entry['meta_key']] = $entry['meta_value'];
			}
		}

		return $values;
	}

	/**
	 * This method deletes an existing meta entry
	 * Can delete by ID, by key or by key/type combination
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int    $id   meta id
	 * @param string $key  meta key
	 * @param string $type meta type
	 *
	 * @returns bool    true if the meta entry was successfully deleted, otherwise false
	 */
	public function delete($id = 0, $key = '', $type = '') {
		# make sure we have somnething to delete
		if (empty($id) && empty($key) && empty($type)) return false;

		# get the delete mysql where clause based on function arguments
		$search = array();
		if (!empty($id)) {
			$where             = "meta_id=$id";
			$search['meta_id'] = $id;
		} else {
			if (!empty($key) && (!empty($type))) {
				$where               = "meta_key='$key' AND meta_type='$type'";
				$search['meta_key']  = $key;
				$search['meta_type'] = $type;
			} else if (!empty($type)) {
				$where               = "meta_type='$type'";
				$search['meta_type'] = $type;
			} else {
				$where              = "meta_key='$key'";
				$search['meta_key'] = $key;
			}
		}

		# delete from database
		$query        = new stdClass();
		$query->table = SPMETA;
		$query->where = $where;
		$success      = SP()->DB->delete($query);

		# if deleted to db, lets delete from cache
		if ($success) {
			# check if we need to fill the cache from db
			if (!$this->meta_loaded) $this->load();

			# delete from cache
			$data = wp_list_filter($this->meta, $search);
			if (!empty($data)) {
				$index = key($data); # grab index into raw cache
				unset($this->meta[$index]);
			}
		}

		return $success;
	}

	private function search_setup($type, $key, $id) {
		$search = array();
		$search['meta_type'] = $type;
		if ($id) {
			$search['meta_id'] = $id;
		} else if ($key) {
			$search['meta_key'] = $key;
		}

		return $search;
	}
}