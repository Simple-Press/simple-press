<?php
/*
Simple:Press
Search View Class
$LastChangedDate: 2017-11-14 20:09:50 -0600 (Tue, 14 Nov 2017) $
$Rev: 15586 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#	Version: 5.0
#
#	Search View Class
#	performs the sql work - passes off a topic list to List View
#
# ==========================================================================================

class spSearchView {
	# Search View DB query result set
	var $searchData = array();

	# Count of topic records
	var $searchCount = 0;

	# How many to show per page
	var $searchShow = 0;

	# Some search values from spVars
	var $searchTerm = '';

	# the original, raw term
	var $searchTermRaw = '';

	# Permalink
	var $searchPermalink = '';

	# Forum where clause
	var $forumWhere = '';

	# search include
	var $searchInclude = 0;

	# search type
	var $searchType = 0;

	# limit
	var $limit = 0;

	# Run in class instantiation - populates data
	function __construct($count=0) {
		$this->searchPermalink = $this->sp_build_search_url();
		$this->searchData = $this->sp_searchview_control($count);
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_searchview_control()
	#	Builds the data structure for the Searchview data object
	#
	# --------------------------------------------------------------------------------------
	function sp_searchview_control($count) {
		global $spVars;

		$searchType 	= $spVars['searchtype'];
		$searchInclude 	= $spVars['searchinclude'];

		# (LIMIT) how many topics per page?
		if (!$count) $count = 30;
		$this->searchShow = $count;
		if ($spVars['searchpage'] == 1) {
			$startlimit = 0;
		} else {
			$startlimit = ((($spVars['searchpage'] - 1) * $count));
		}
		# For this page?
		$this->limit = $startlimit.', '.$count;

		# (WHERE) All or specific forum?
		if ($spVars['forumslug'] == 'all') {
			# create forumIds list and where clause
			$forumIds = sp_user_visible_forums('post-content');
			if (empty($forumIds)) return;
			$this->forumWhere = 'forum_id IN ('.implode(',', $forumIds).') ';
		} else {
			# check we can see this forum and create where clause
			if (!sp_get_auth('view_forum', $spVars['forumid'])) return;
			$this->forumWhere = 'forum_id='.$spVars['forumid'];
		}

		if (empty($spVars['searchvalue'])) return '';
		if ($searchType == 4 || $searchType == 5) {
			$this->searchTermRaw = sp_get_member_item((int) $spVars['searchvalue'], 'display_name');
		} else {
			$this->searchTermRaw = $spVars['searchvalue'];
		}

		$this->searchTerm = $this->sp_construct_search_term($spVars['searchvalue'], $searchType, $searchInclude);

		# if search type is 1,2 or 3 (i.e., normal data searches) and we are looking for page 1 then we need to run
		# the query. Note - if posts and titles then we need to run it twice!
		# If we are not loading page 1 however then we can grab the results from the cache.
		# For all other searchtypes - just rin the standard routine
		if ($searchType > 3) {
			$r = $this->sp_searchview_query($searchType, $searchInclude);
			return $r;
		}

		if ($spVars['searchpage'] == 1 && $spVars['newsearch'] == true) {
			$r = $this->sp_searchview_query($searchType, $searchInclude);
			# Remove dupes and re-sort
			if ($r) {
				$r = array_unique($r);
				rsort($r, SORT_NUMERIC);

				# Now hive off into a transient
				$d = array();
				$d['url'] = $this->searchPermalink;
				$d['page'] = $spVars['searchpage'];
				$t = array();
				$t[0] = $d;
				$t[1] = $r;

				sp_add_cache('search', $t);
			}
		} else {
			# Get the data from the cache if not page 1 for first time
			$r = sp_get_cache('search');
			if ($r) {
				$d = $r[0];
				$r = $r[1];
				$d['url'] = $this->searchPermalink;
				$d['page'] = $spVars['searchpage'];
				$t = array();
				$t[0] = $d;
				$t[1] = $r;

				# update the transient with the new url
				sp_add_cache('search', $t);
			}
		}

		# Now work out which part of the $r array to return
		if ($r) {
			$spVars['searchresults'] = count($r);
			$this->searchCount = $spVars['searchresults'];
    		$this->searchInclude = $searchInclude;
    		$this->searchType = $searchType;
			return array_slice($r, $startlimit, $count);
		}
	}

	function sp_searchview_query($searchType, $searchInclude) {
		global $spVars, $spThisUser;

        # some defaults
		$useLimit = true;
        $TABLE = '';
 		$JOIN = '';
        $FIELDS = SFPOSTS.'.topic_id';
        $WHERE = '';
        $ORDERBY = SFPOSTS.'.topic_id DESC';

		# (WHERE) Post content search criteria
		if ($searchType == 1 || $searchType == 2 || $searchType == 3) {
			$useLimit = false;

			# Standard forum search
			if ($searchInclude == 1) {
				# Include = 1 - posts
				$WHERE = $this->searchTerm;
				$TABLE = SFPOSTS;
			} elseif ($searchInclude == 2) {
				# Include = 2 - titles
				$WHERE = $this->searchTerm;
				$TABLE = SFTOPICS;
                $FIELDS = SFTOPICS.'.topic_id';
                $ORDERBY = SFTOPICS.'.topic_id DESC';
			} elseif ($searchInclude == 3) {
				# Include = 3 - posts and titles
				$WHERE = $this->searchTerm;
				$TABLE = SFPOSTS;
				$JOIN = array(SFTOPICS.' ON '.SFPOSTS.'.topic_id = '.SFTOPICS.'.topic_id');
			} else {
				# Plugns can set an alternate TABLE and MATCH statement based on the 'Include' parameter
				$TABLE = apply_filters('sph_search_type_table', SFTOPICS, $searchType, $searchInclude);
				$WHERE = apply_filters('sph_search_include_where', '', $this->searchTerm, $searchType, $searchInclude);
			}
		} elseif ($searchType == 4) {
			# Member 'posted in'
			$WHERE = "user_id=$this->searchTerm";
			$TABLE = SFPOSTS;
		} elseif ($searchType == 5) {
			# Member 'started'
			$WHERE = "user_id=$this->searchTerm AND post_index=1";
			$TABLE = SFPOSTS;
		} else {
			# Plugns can set an alternate TABLE and WHERE clause based on the 'Type' parameter
			$TABLE = apply_filters('sph_search_type_table', SFTOPICS, $searchType, $searchInclude);
			$WHERE = apply_filters('sph_search_type_where', '', $this->searchTerm, $searchType, $searchInclude);
		}

		# check if the WHERE clause is empty - probably comes from a legacy url
		if (empty($WHERE)) {
			sp_notify(SPFAILURE, sp_text('Unable to complete this search request'));
			return;
		}

		# Query
		$spdb = new spdbComplex;
			$spdb->table = $TABLE;
			$spdb->fields = $FIELDS;
			if (!empty($JOIN)) $spdb->join = $JOIN;
			$spdb->distinct = true;
			$spdb->found_rows = true;
			$spdb->where = $WHERE.' AND '.$TABLE.'.'.$this->forumWhere;
			$spdb->orderby = $ORDERBY;
			if ($useLimit) $spdb->limits = $this->limit;

		# Plugins can alter the final SQL
		$spdb = apply_filters('sph_search_query', $spdb, $this->searchTerm, $searchType, $searchInclude, $this);

		if (!empty($spThisUser->inspect['q_spSearchView'])) {
			$spdb->inspect = 'q_spSearchView';
			$spdb->show = true;
		}

		$records = $spdb->select('col');

		$spVars['searchresults'] = spdb_select('var', 'SELECT FOUND_ROWS()');
		$this->searchCount = $spVars['searchresults'];
		$this->searchInclude = $searchInclude;
		$this->searchType = $searchType;

		return $records;
	}

	#------------------------------------------------------------
	function sp_construct_search_term($term, $type, $include) {
        global $wpdb;

        $original = $term;
        $searchterm = '';
        $col = array();

		# get the search terms(s) in format required
		if ($type == 1 || $type == 2 || $type == 3) {
			if ($include == 1) $col = array('post_content');
			if ($include == 2) $col = array('topic_name');
			if ($include == 3) $col = array('post_content', 'topic_name');

			if ($type == 1 || $type == 3)    $op = ' OR ';
			if ($type == 2)                  $op = ' AND ';
			if ($type == 1 || $type == 2)    $word = explode(' ', $term);
			if ($type == 3)                  $word = array($term);

			$firstcol = true;
			foreach ($col as $c) {
				$firstword = true;
				$searchterm.= ($firstcol) ? '(' : ') OR (';
				foreach ($word as $w) {
					if (!$firstword) $searchterm.= $op;
					$firstword = false;
					$searchterm.= "$c LIKE '%".sp_esc_sql($wpdb->esc_like($w))."%'";
				}
				if (count($col) == 1 || !$firstcol) $searchterm.= ')';
				$firstcol = false;
			}
            if ($include == 3) $searchterm = "($searchterm)";
		} elseif ($type == 4 || $type == 5) {
			$searchterm = (int) $term;
		} else {
			# Plugins can alter the search term
			$searchterm = apply_filters('sph_search_term_type', $term, $type, $include);
		}
		$searchterm = apply_filters('sph_search_term', $searchterm, $original, $type, $include);
		return $searchterm;
	}

	# ------------------------------------------------------------------
	# sp_build_search_url()
	#
	# Builds a forum search url with the query vars
	# ------------------------------------------------------------------
	function sp_build_search_url() {
		global $spVars;
		$s = array();
		$s['forum'] = (isset($_GET['forum'])) ? $_GET['forum'] : '';
		$s['value'] = $spVars['searchvalue'];
		$s['type'] = $spVars['searchtype'];
		$s['include'] = $spVars['searchinclude'];

		$s = apply_filters('sph_build_search_url', $s);

		return add_query_arg($s, sp_url());
	}
}

?>