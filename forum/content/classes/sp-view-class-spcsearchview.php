<?php
/*
Simple:Press
Search View Class
$LastChangedDate: 2017-11-14 20:09:27 -0600 (Tue, 14 Nov 2017) $
$Rev: 15585 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

class spcSearchView {
	# Search View DB query result set
	public $searchData = array();

	# Count of topic records
	public $searchCount = 0;

	# How many to show per page
	public $searchShow = 0;

	# Some search values from pageData
	public $searchTerm = '';

	# the original, raw term
	public $searchTermRaw = '';

	# Permalink
	public $searchPermalink = '';

	# Forum where clause
	public $forumWhere = '';

	# search include
	public $searchInclude = 0;

	# search type
	public $searchType = 0;

	# limit
	public $limit = 0;

	# Run in class instantiation - populates data
	public function __construct($count = 0) {
		$this->searchPermalink = $this->search_url();
		$this->searchData      = $this->control($count);
		sp_display_inspector('sv_search', $this->searchData);
	}

	# --------------------------------------------------------------------------------------
	#
	#	control()
	#	Builds the data structure for the Searchview data object
	#
	# --------------------------------------------------------------------------------------
	private function control($count) {
		$searchType    = SP()->rewrites->pageData['searchtype'];
		$searchInclude = SP()->rewrites->pageData['searchinclude'];

		# (LIMIT) how many topics per page?
		if (!$count) $count = 30;
		$this->searchShow = $count;
		if (SP()->rewrites->pageData['searchpage'] == 1) {
			$startlimit = 0;
		} else {
			$startlimit = (((SP()->rewrites->pageData['searchpage'] - 1) * $count));
		}
		# For this page?
		$this->limit = $startlimit.', '.$count;

		# (WHERE) All or specific forum?
		if (SP()->rewrites->pageData['forumslug'] == 'all') {
			# create forumIds list and where clause
			$forumIds = SP()->user->visible_forums('post-content');
			if (empty($forumIds)) return array();
			$this->forumWhere = 'forum_id IN ('.implode(',', $forumIds).') ';
		} else {
			# check we can see this forum and create where clause
			if (!SP()->auths->get('view_forum', SP()->rewrites->pageData['forumid'])) return array();
			$this->forumWhere = 'forum_id='.SP()->rewrites->pageData['forumid'];
		}

		if (empty(SP()->rewrites->pageData['searchvalue'])) return array();
		if ($searchType == 4 || $searchType == 5) {
			$this->searchTermRaw = SP()->memberData->get((int) SP()->rewrites->pageData['searchvalue'], 'display_name');
		} else {
			$this->searchTermRaw = SP()->rewrites->pageData['searchvalue'];
		}

		$this->searchTerm = $this->search_term(SP()->rewrites->pageData['searchvalue'], $searchType, $searchInclude);

		# if search type is 1,2 or 3 (i.e., normal data searches) and we are looking for page 1 then we need to run
		# the query. Note - if posts and titles then we need to run it twice!
		# If we are not loading page 1 however then we can grab the results from the cache.
		# For all other searchtypes - just rin the standard routine
		if ($searchType > 3) {
			$r = $this->query($searchType, $searchInclude);

			return $r;
		}

		if (SP()->rewrites->pageData['searchpage'] == 1 && SP()->rewrites->pageData['newsearch'] == true) {
			$r = $this->query($searchType, $searchInclude);
			# Remove dupes and re-sort
			if ($r) {
				$r = array_unique($r);
				rsort($r, SORT_NUMERIC);

				# Now hive off into a transient
				$d         = array();
				$d['url']  = $this->searchPermalink;
				$d['page'] = SP()->rewrites->pageData['searchpage'];
				$t         = array();
				$t[0]      = $d;
				$t[1]      = $r;

				SP()->cache->add('search', $t);
			}
		} else {
			# Get the data from the cache if not page 1 for first time
			$r = SP()->cache->get('search');
			if ($r) {
				$d         = $r[0];
				$r         = $r[1];
				$d['url']  = $this->searchPermalink;
				$d['page'] = SP()->rewrites->pageData['searchpage'];
				$t         = array();
				$t[0]      = $d;
				$t[1]      = $r;

				# update the transient with the new url
				SP()->cache->add('search', $t);
			}
		}

		# Now work out which part of the $r array to return
		if ($r) {
			SP()->rewrites->pageData['searchresults'] = count($r);
			$this->searchCount                          = SP()->rewrites->pageData['searchresults'];
			$this->searchInclude                        = $searchInclude;
			$this->searchType                           = $searchType;

			return array_slice($r, $startlimit, $count);
		}

		return array();
	}

	private function query($searchType, $searchInclude) {
		# some defaults
		$useLimit = true;
		$TABLE    = '';
		$JOIN     = '';
		$FIELDS   = SPPOSTS.'.topic_id';
		$WHERE    = '';
		$ORDERBY  = SPPOSTS.'.topic_id DESC';

		# (WHERE) Post content search criteria
		if ($searchType == 1 || $searchType == 2 || $searchType == 3) {
			$useLimit = false;

			# Standard forum search
			if ($searchInclude == 1) {
				# Include = 1 - posts
				$WHERE = $this->searchTerm;
				$TABLE = SPPOSTS;
			} elseif ($searchInclude == 2) {
				# Include = 2 - titles
				$WHERE   = $this->searchTerm;
				$TABLE   = SPTOPICS;
				$FIELDS  = SPTOPICS.'.topic_id';
				$ORDERBY = SPTOPICS.'.topic_id DESC';
			} elseif ($searchInclude == 3) {
				# Include = 3 - posts and titles
				$WHERE = $this->searchTerm;
				$TABLE = SPPOSTS;
				$JOIN  = array(SPTOPICS.' ON '.SPPOSTS.'.topic_id = '.SPTOPICS.'.topic_id');
			} else {
				# Plugns can set an alternate TABLE and MATCH statement based on the 'Include' parameter
				$TABLE = apply_filters('sph_search_type_table', SPTOPICS, $searchType, $searchInclude);
				$WHERE = apply_filters('sph_search_include_where', '', $this->searchTerm, $searchType, $searchInclude);
			}
		} elseif ($searchType == 4) {
			# Member 'posted in'
			$WHERE = "user_id=$this->searchTerm";
			$TABLE = SPPOSTS;
		} elseif ($searchType == 5) {
			# Member 'started'
			$WHERE = "user_id=$this->searchTerm AND post_index=1";
			$TABLE = SPPOSTS;
		} else {
			# Plugns can set an alternate TABLE and WHERE clause based on the 'Type' parameter
			$TABLE = apply_filters('sph_search_type_table', SPTOPICS, $searchType, $searchInclude);
			$WHERE = apply_filters('sph_search_type_where', '', $this->searchTerm, $searchType, $searchInclude);
		}

		# check if the WHERE clause is empty - probably comes from a legacy url
		if (empty($WHERE)) {
			SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Unable to complete this search request'));

			return array();
		}

		# Query
		$query         = new stdClass();
		$query->table  = $TABLE;
		$query->fields = $FIELDS;
		if (!empty($JOIN)) $query->join = $JOIN;
		$query->distinct   = true;
		$query->found_rows = true;
		$query->where      = $WHERE.' AND '.$TABLE.'.'.$this->forumWhere;
		$query->orderby    = $ORDERBY;
		if ($useLimit) $query->limits = $this->limit;
		# Plugins can alter the final SQL
		$query = apply_filters('sph_search_query', $query, $this->searchTerm, $searchType, $searchInclude);
		if (!empty(SP()->user->thisUser->inspect['q_SearchView'])) {
			$query->inspect = 'q_SearchView';
			$query->show    = true;
		}
		$query->type = 'col';
		$records     = SP()->DB->select($query);

		SP()->rewrites->pageData['searchresults'] = SP()->DB->select('SELECT FOUND_ROWS()', 'var');
		$this->searchCount                          = SP()->rewrites->pageData['searchresults'];
		$this->searchInclude                        = $searchInclude;
		$this->searchType                           = $searchType;

		return $records;
	}

	#------------------------------------------------------------
	private function search_term($term, $type, $include) {
		global $wpdb;

		$original   = $term;
		$searchterm = '';
		$col        = array();

		# get the search terms(s) in format required
		if ($type == 1 || $type == 2 || $type == 3) {
			if ($include == 1) $col = array('post_content');
			if ($include == 2) $col = array('topic_name');
			if ($include == 3) $col = array('post_content',
			                                'topic_name');

			if ($type == 1 || $type == 3) $op = ' OR ';
			if ($type == 2) $op = ' AND ';
			if ($type == 1 || $type == 2) $word = explode(' ', $term);
			if ($type == 3) $word = array($term);

			$firstcol = true;
			foreach ($col as $c) {
				$firstword = true;
				$searchterm .= ($firstcol) ? '(' : ') OR (';
				foreach ($word as $w) {
					if (!$firstword) $searchterm .= $op;
					$firstword = false;
					$searchterm .= "$c LIKE '%".SP()->filters->esc_sql($wpdb->esc_like($w))."%'";
				}
				if (count($col) == 1 || !$firstcol) $searchterm .= ')';
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
	# search_url()
	#
	# Builds a forum search url with the query vars
	# ------------------------------------------------------------------
	private function search_url() {
		$s            = array();
		$s['forum']   = (isset($_GET['forum'])) ? $_GET['forum'] : '';
		$s['value']   = SP()->rewrites->pageData['searchvalue'];
		$s['type']    = SP()->rewrites->pageData['searchtype'];
		$s['include'] = SP()->rewrites->pageData['searchinclude'];

		$s = apply_filters('sph_build_search_url', $s);

		return add_query_arg($s, SP()->spPermalinks->get_url());
	}
}
