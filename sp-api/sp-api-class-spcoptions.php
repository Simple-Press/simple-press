<?php

/**
 * Core class used for managing Simple Press options.
 *
 * This class is used to add, update, get and delete options used by the core
 * program and simple press plugins
 *
 * @since 6.0
 *
 * Public methods available:
 * get($option)
 * add($option, $value)
 * update ($option, value)
 * delete ($option)
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
class spcOptions {
	/**
	 * Holds a list of all options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * indicated options cache has been filled.
	 *
	 * @var bool
	 */
	private $options_loaded = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Retrieves an option value based on an option name.
	 *
	 * If the option does not exist or does not have a value, then the return value
	 * will be false.
	 *
	 * If the option was serialized then it will be unserialized when it is returned.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $option name of option to retrieve
	 *
	 * @return mixed    value of the option
	 */
	public function get($option) {
		# check if we need to fill the options from db
		if (!$this->options_loaded) $this->load();

		# set return value if exists
		if (!empty($this->options) && array_key_exists($option, $this->options)) {
			$value = maybe_unserialize($this->options[$option]->option_value);
		} else {
			$value = false;
		}

		return $value;
	}

	/**
	 * Add a new option.
	 *
	 * You do not need to serialize values. If the value needs to be serialized, then
	 * it will be serialized before it is inserted into the database.
	 *
	 * You can create options without values and then update the values later.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $option Name of option to add. Expected to not be SQL-escaped.
	 * @param mixed  $value  Optional. Option value. Must be serializable if non-scalar.
	 *
	 * @return bool     False if option was not added and true if option was added.
	 */
	public function add($option, $value = '') {
		# check if we need to fill the options from db
		if (!$this->options_loaded) $this->load();

		# store option into db
		$value = maybe_serialize($value);

		$query                = new stdClass();
		$query->table         = SPOPTIONS;
		$query->duplicate_key = true;
		$query->fields        = array('option_name', 'option_value');
		$query->data          = array($option, $value);
		$result               = SP()->DB->insert($query);

		if ($result) {
			$this->options[$option]               = new stdClass();
			$this->options[$option]->option_name  = $option;
			$this->options[$option]->option_value = $value;

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update the value of an option that was already added.
	 *
	 * You do not need to serialize values. If the value needs to be serialized, then
	 * it will be serialized before it is inserted into the database.
	 *
	 * If the option does not exist, then the option will be added with the option value
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $option Name of option to add.
	 * @param mixed  $value  Optional. Option value. Must be serializable if non-scalar.
	 *
	 * @return bool     False if option was not updated and true if option was updated.
	 */
	public function update($option, $value) {
		# bail if the old value of option is same as new update value
		$oldvalue = $this->get($option);

		if ($value === maybe_unserialize($oldvalue)) {
            return false;
        }

		# if the option doesnt exist, add instead of update
		if (!isset($this->options[$option])) {
            return $this->add($option, $value);
        }

		# udpate the option in the db
		$value = maybe_serialize($value);

		$query         = new stdClass;
		$query->table  = SPOPTIONS;
		$query->fields = array('option_value');
		$query->data   = (array) SP()->filters->esc_sql($value);
		$query->where  = "option_name = '$option'";
		$result        = SP()->DB->update($query);

		if ($result) {
			$this->options[$option]->option_value = $value;

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes option by name.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $option Name of option to remove.
	 *
	 * @return bool     True, if option is successfully deleted. False on failure.
	 */
	public function delete($option) {
		# just bail if the option doesnt exist
		$oldvalue = $this->get($option);
		if ($oldvalue === false) return false;

		# delete the option from db
		$query        = new stdClass();
		$query->table = SPOPTIONS;
		$query->where = "option_name = '$option'";
		$result       = SP()->DB->delete($query);
		if ($result) {
			unset($this->options[$option]);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Loads all options.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function load() {
		# make sure the options table exists
		$exist = SP()->DB->tableExists(SPOPTIONS);
		if (!$exist) return;

		# grab the options from the database
		$query             = new stdClass();
		$query->resultType = OBJECT_K;
		$query->type       = 'set';
		$query->table      = SPOPTIONS;
		$query->fields     = 'option_name, option_value';
		$this->options     = SP()->DB->select($query);

		$this->options_loaded = true;
	}
}