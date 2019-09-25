<?php

/**
 * Core class used for primitive functions.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
class spcPrimitives {
	public function admin_text($text) {
		return esc_attr(__($text, 'spa'));
	}

	public function admin_etext($text) {
		echo esc_attr(__($text, 'spa'));
	}

	public function admin_text_noesc($text) {
		return __($text, 'spa');
	}

	public function front_text($text) {
		return esc_attr(__($text, 'sp'));
	}

	public function front_etext($text) {
		echo esc_attr(__($text, 'sp'));
	}

	public function front_text_noesc($text) {
		return __($text, 'sp');
	}

	public function is_groupview() {
		return SP()->rewrites->pageData['pageview'] == 'group';
	}

	public function is_forumview() {
		return SP()->rewrites->pageData['pageview'] == 'forum';
	}

	public function is_topicview() {
		return SP()->rewrites->pageData['pageview'] == 'topic';
	}

	public function is_profileview() {
		return (SP()->rewrites->pageData['pageview'] == 'profileedit' || SP()->rewrites->pageData['pageview'] == 'profileshow');
	}

	public function is_listview() {
		return SP()->rewrites->pageData['pageview'] == 'list';
	}

	public function is_searchview() {
		return SP()->rewrites->pageData['searchpage'] == 1;
	}

	public function is_forumpage() {
		return (!empty(SP()->rewrites->pageData['page']));
	}

	public function redirect($url) {
		?>
        <script>
			(function(spj, $, undefined) {
				window.location = "<?php echo $url; ?>";
			}(window.spj = window.spj || {}, jQuery));
		</script>
		<?php
		exit();
	}

	public function check_url($url) {
		if ($url == 'http://' || $url == 'https://') $url = '';

		return $url;
	}

	public function array_insert(&$array, $value, $offset) {
		if (is_array($array)) {
			$array  = array_values($array);
			$offset = intval($offset);
			if ($offset < 0 || $offset >= count($array)) {
				array_push($array, $value);
			} else if ($offset == 0) {
				array_unshift($array, $value);
			} else {
				$temp = array_slice($array, 0, $offset);
				array_push($temp, $value);
				$array = array_slice($array, $offset);
				$array = array_merge($temp, $array);
			}
		} else {
			$array = array($value);
		}

		return count($array);
	}

	public function strpos_array($haystack, $needle) {
		if (!is_array($needle)) $needle = array($needle);
		foreach ($needle as $what) {
			if (($pos = strpos($haystack, $what)) !== false) return $pos;
		}

		return false;
	}

	public function array_search_multi($array, $key, $value) {
		$results = array();

		if (is_array($array)) {
			if (isset($array[$key]) && $array[$key] == $value) $results[] = $array;

			foreach ($array as $subarray) {
				$results = array_merge($results, $this->array_search_multi($subarray, $key, $value));
			}
		}

		return $results;
	}

	public function array_msort($array, $cols) {
		$colarr = array();
		foreach ($cols as $col => $order) {
			$colarr[$col] = array();
			foreach ($array as $k => $row) {
				$colarr[$col]['_'.$k] = strtolower($row[$col]);
			}
		}
		$params = array();
		foreach ($cols as $col => $order) {
			$params[] = &$colarr[$col];
			$params   = array_merge($params, (array)$order);
		}
		call_user_func_array('array_multisort', $params);
		$ret   = array();
		$keys  = array();
		$first = true;
		foreach ($colarr as $col => $arr) {
			foreach ($arr as $k => $v) {
				if ($first) $keys[$k] = substr($k, 1);
				$k = $keys[$k];
				if (!isset($ret[$k])) $ret[$k] = $array[$k];
				$ret[$k][$col] = $array[$k][$col];
			}
			$first = false;
		}

		return $ret;
	}

	function copy_dir($src, $dst) { 
		// open the source directory 
		$dir = opendir($src); 

		// Make the destination directory if not exist 
		@mkdir($dst); 

		// Loop through the files in source directory 
		while( $file = readdir($dir) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					// Recursively calling custom copy function 
					// for sub directory 
					$this->copy_dir($src . '/' . $file, $dst . '/' . $file); 
				} else { 
					copy($src . '/' . $file, $dst . '/' . $file); 
				} 
			} 
		}
		closedir($dir); 
	} 


	public function remove_dir($dir) {
		if (is_dir($dir)) {
			foreach (glob($dir.'/*') as $file) {
				if (is_dir($file)) {
					$this->remove_dir($file);
				} else {
					@unlink($file);
				}
			}
			@rmdir($dir);
		}
	}

	public function get_image_size($file, $replace = false) {
		$size = array();
		# if allow_url_fopen is off then need to ignore
		if (ini_get('allow_url_fopen') == true) {
			set_error_handler(array($this, 'suppress_error'));
			if ($replace) {
				$size = getimagesize(str_replace(' ', '%20', $file));
			} else {
				$size = getimagesize($file);
			}
			restore_error_handler();
		}

		return $size;
	}

	public function suppress_error($errno, $errstr) {
		# do nothing
		return;
	}

	public function create_name_extract($name, $length = 40) {
		$name = SP()->displayFilters->title($name);
		if (strlen($name) > $length) $name = substr($name, 0, $length).'&#8230;';

		return $name;
	}

	public function truncate_name($name, $length) {
		if ($length > 0) {
			if (strlen($name) > $length) $name = substr($name, 0, $length).'&#8230;';
		}

		return $name;
	}
}