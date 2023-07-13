<?php
/*
Simple:Press
Members List Class
$LastChangedDate: 2017-11-12 17:27:02 -0600 (Sun, 12 Nov 2017) $
$Rev: 15583 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

class spcMembersList {
	# Status: 'data', 'no access', 'no data'
	public $membersListStatus = 'data';

	# True while the member loop is being rendered
	public $inMemberGroupsLoop = false;

	public $inMembersLoop      = false;

	# Members List DB query result set
	public $pageData       = array();

	public $pageMemberData = array();

	# Member single row object
	public $memberGroupData = '';

	public $memberData      = '';

	# Internal counter
	public $currentMemberGroup = 0;

	public $currentMember      = 0;

	# Count of member records
	public $memberGroupCount = 0;

	public $memberCount      = 0;

	# Count of all member records
	public $totalMemberCount = 0;

	# The groupby clause - can be 'usergroup' or 'user'
	public $membersGroupBy = array();

	# The orderby clause - can be 'id' or 'alpha'
	public $membersOrderBy = '';

	# The sorting clause - can be 'asc' or 'desc'
	public $membersSortBy = '';

	# The limit clause - number of members to show on single page
	public $membersNumber = 15;

	# only valid if groupby='uergroup'
	# allows limiting usergroups displayed to current user memberships
	public $membersLimitUG = false;

	# only valid if groupby='uergroup'
	# allows limiting usergroups displayed to set of usergroup IDs
	public $membersWhere = '';

	# Holds all of the user groups that wil appear in the view
	public $userGroups = array();

	# Run in class instantiation - populates data
	public function __construct($groupBy = 'usergroup', $orderBy = 'id', $sortBy = 'asc', $number = 15, $limitUG = false, $ugids = '') {
		$this->membersGroupBy = SP()->filters->str($groupBy);
		$this->membersOrderBy = SP()->filters->str($orderBy);
		$this->membersSortBy  = SP()->filters->str($sortBy);
		$this->membersNumber  = (int) $number;
		$this->membersLimitUG = ($groupBy == 'usergroup') ? SP()->filters->str($limitUG) : false;
		$this->membersWhere   = ($groupBy == 'usergroup' && !empty($ugids)) ? SP()->filters->str($ugids) : '';

		$data                   = $this->query($this->membersGroupBy, $this->membersOrderBy, $this->membersSortBy, $this->membersNumber, $this->membersLimitUG, $this->membersWhere);
		$this->pageData         = $data->records;

        /*
         * todo: find a better solution for this
         *
         * This needs to be refined, this is just a quickfix for solving the critical error that
         * breaks the forum when no results are returned the returned object is a stdClass
         */
        if (is_object($data->records)) {
            $this->memberGroupCount = 0;
        } else {
            $this->memberGroupCount = count($this->pageData);
        }
		$this->totalMemberCount = $data->count;
		sp_display_inspector('mv_members', $this);
	}

	# Populate the members list result set
	public function has_member_groups() {
		# Check for no access to members list or no data
		if ($this->membersListStatus != 'data') return false;

		reset($this->pageData);

		if ($this->memberGroupCount) {
			$this->inMemberGroupsLoop = true;

			return true;
		} else {
			return false;
		}
	}

	# Loop control on Members List records
	public function loop_member_groups() {
		if ($this->currentMemberGroup > 0) do_action_ref_array('sph_after_memeber_group', array(&$this));
		$this->currentMemberGroup++;
		if ($this->currentMemberGroup <= $this->memberGroupCount) {
			do_action_ref_array('sph_before_member_group', array(&$this));

			return true;
		} else {
			$this->inMemberGroupsLoop = false;

			return false;
		}
	}

	# Sets array pointer and returns current Member data
	public function the_member_group() {
		$this->memberGroupData = current($this->pageData);
		sp_display_inspector('mv_thisMemberGroup', $this->memberGroupData);
		next($this->pageData);

		return $this->memberGroupData;
	}

	# True if there are Member records
	public function has_members() {
		if ($this->memberGroupData->members) {
			$this->pageMemberData = $this->memberGroupData->members;
			$this->memberCount    = count($this->pageMemberData);
			$this->inMembersLoop  = true;

			return true;
		} else {
			return false;
		}
	}

	# Loop control on Member records
	public function loop_members() {
		if ($this->currentMember > 0) do_action_ref_array('sph_after_member', array(&$this));
		$this->currentMember++;
		if ($this->currentMember <= $this->memberCount) {
			do_action_ref_array('sph_before_member', array(&$this));

			return true;
		} else {
			$this->inMembersLoop = false;
			$this->currentMember = 0;
			$this->memberCount   = 0;
			unset($this->pageMemberData);

			return false;
		}
	}

	# Sets array pointer and returns current Member data
	public function the_member() {
		$this->memberData = current($this->pageMemberData);
		sp_display_inspector('mv_thisMember', $this->memberData);
		next($this->pageMemberData);

		return $this->memberData;
	}

	#	Builds the data structure for the Members List template
	private function query($groupBy, $orderBy, $sortBy, $number, $limitUG, $ugids) {
		# check for page
		$page = (isset($_GET['page'])) ? SP()->filters->integer($_GET['page']) : SP()->rewrites->pageData['page'];

		# check for member search
		$search = (!empty($_POST['msearch']) && !isset($_POST['allmembers'])) ? SP()->filters->str($_POST['msearch']) : '';
		$search = (!empty($_GET['msearch'])) ? SP()->filters->str($_GET['msearch']) : $search;

		# check for usergroup selection query arg
		$ug_select = (!empty($_POST['ug']) && !isset($_POST['allmembers'])) ? SP()->filters->integer($_POST['ug']) : '';
		$ug_select = (!empty($_GET['ug'])) ? SP()->filters->integer($_GET['ug']) : $ug_select;

		# check for constructor limiting usergroups
		if ($groupBy == 'usergroup' && !empty($ugids)) $ugids = explode(',', SP()->filters->str($ugids));

		$data          = new stdClass();
		$data->records = new stdClass();
		$data->count   = 0;
		if (SP()->user->thisUser->admin || SP()->auths->get('view_members_list')) {
			# default to 'no data'
			$this->membersListStatus = 'no data';

			# are we limiting member lists to user group memberships?
			$where = 'posts > -2';
			if ($groupBy == 'usergroup' && !SP()->user->thisUser->admin) {
				# if limiting to memberships, get usergroups current user has membership in
				if ($limitUG) {
					$ugs = SP()->user->get_memberships(SP()->user->thisUser->ID);
					if (empty($ugs)) {
						$value = SP()->meta->get('default usergroup', 'sfguests');
						$sql   = 'SELECT * FROM '.SPUSERGROUPS." WHERE usergroup_id={$value[0]['meta_value']}";
						$ugs   = SP()->DB->select($sql, 'set', ARRAY_A);
					}

					# Now add any moderator user groups who can moderate the current users forums
					$forums = SP()->user->get_forum_memberships(SP()->user->thisUser->ID);
					$forums = implode(',', $forums);
					$sql    = 'SELECT DISTINCT '.SPMEMBERSHIPS.'.usergroup_id, usergroup_name, usergroup_desc, usergroup_join, usergroup_badge FROM '.SPMEMBERSHIPS.'
					JOIN '.SPUSERGROUPS.' ON '.SPUSERGROUPS.'.usergroup_id = '.SPMEMBERSHIPS.'.usergroup_id
					JOIN '.SPPERMISSIONS.' ON '.SPPERMISSIONS.".forum_id IN ($forums)
					WHERE usergroup_is_moderator=1 ORDER BY ".SPMEMBERSHIPS.'.usergroup_id';
					$mugs   = SP()->DB->select($sql, 'set', ARRAY_A);
					if ($mugs) $ugs = array_merge($mugs, $ugs);
				} else {
					$ugs = SP()->DB->table(SPUSERGROUPS, '', '', '', '', ARRAY_A);
				}
				if (empty($ugs)) return $data;

				# now build the where clause
				$ug_ids = array();
				foreach ($ugs as $index => $ug) {
					if (empty($ugids) || in_array($ug['usergroup_id'], $ugids)) {
						$ug_ids[] = $ug['usergroup_id'];
					} else {
						unset($ugs[$index]);
					}
				}
				if (empty($ug_ids)) return $data;

				$this->userGroups = array_values($ugs);

				# create where clause based on user memberships
				if (!$limitUG && empty($ugids) && empty($ug_select)) {
					# not limiting by usergroup or specific ids so grab all users
					$where .= ' AND ('.SPMEMBERSHIPS.'.usergroup_id IN ('.implode(',', $ug_ids).') OR '.SPMEMBERSHIPS.'.usergroup_id IS NULL)';
				} else {
					if (empty($ug_select)) {
						# limiting by usergroup or specific ids, so only grab those users plus admins (skips users with no memmberships)
						$where .= ' AND ('.SPMEMBERSHIPS.'.usergroup_id IN ('.implode(',', $ug_ids).') OR admin=1)';
					} else {
						$where .= ' AND ('.SPMEMBERSHIPS.".usergroup_id = $ug_select AND ".SPMEMBERSHIPS.'.usergroup_id IN ('.implode(',', $ug_ids).'))';
					}
				}
			} else {
				if (!empty($ug_select)) $where .= ' AND '.SPMEMBERSHIPS.".usergroup_id = $ug_select";
				$this->userGroups = SP()->DB->table(SPUSERGROUPS, '', '', '', '', ARRAY_A);
			}

			if ($search != '') $where .= ' AND '.SPMEMBERS.'.display_name LIKE "'.SP()->filters->esc_sql($search).'%"';

			# how many members per page?
			$startlimit = 0;
			if ($page != 1) $startlimit = ((($page - 1) * $number));
			$limit = $startlimit.', '.$number;

			$order = '';
			if ($groupBy == 'usergroup' && $orderBy == 'id') $order .= "usergroup_id $sortBy, ".SPMEMBERS.".display_name $sortBy";
			if ($groupBy == 'usergroup' && $orderBy == 'alpha') $order .= "usergroup_name $sortBy, ".SPMEMBERS.".display_name $sortBy";
			if ($groupBy == 'user' && $orderBy == 'id') $order .= SPMEMBERS.".user_id $sortBy";
			if ($groupBy == 'user' && $orderBy == 'alpha') $order .= SPMEMBERS.".display_name $sortBy";

			$join = SPUSERS.' ON '.SPMEMBERS.'.user_id='.SPUSERS.'.ID ';
			if ($groupBy == 'usergroup') {
				$q = 'if ('.SPMEMBERS.'.admin=1, 0, IFNULL('.SPMEMBERSHIPS.'.usergroup_id, 99999999)) AS usergroup_id,
					  if ('.SPMEMBERS.'.admin=1, "'.SP()->primitives->front_text('Admins').'", IFNULL('.SPUSERGROUPS.'.usergroup_name, "'.SP()->primitives->front_text('No Memberships').'")) AS usergroup_name,
					  if ('.SPMEMBERS.'.admin=1, "'.SP()->primitives->front_text('Forum Administrators').'", IFNULL('.SPUSERGROUPS.'.usergroup_desc, "'.SP()->primitives->front_text('Members without any usergroup memberships').'")) AS usergroup_desc,
					  '.SPMEMBERS.'.user_id, '.SPMEMBERS.'.display_name, admin, avatar, posts, lastvisit, user_registered, user_email, user_url, user_options';
				$join .= 'LEFT JOIN '.SPMEMBERSHIPS.' ON '.SPMEMBERSHIPS.'.user_id='.SPMEMBERS.'.user_id
						 LEFT JOIN '.SPUSERGROUPS.' ON '.SPUSERGROUPS.'.usergroup_id='.SPMEMBERSHIPS.'.usergroup_id';
			} else {
				$q = SPMEMBERS.'.user_id, '.SPMEMBERS.'.display_name, admin, avatar, posts, lastvisit, user_registered, user_email, user_url, user_options';
			}
			# retrieve members list records
			$query             = new stdClass();
			$query->table      = SPMEMBERS;
			$query->fields     = $q;
			$query->found_rows = true;
			$query->distinct   = true;
			$query->left_join  = $join;
			$query->where      = $where;
			$query->orderby    = $order;
			$query->limits     = $limit;
			$query             = apply_filters('sph_members_list_query', $query);
			if (!empty(SP()->user->thisUser->inspect['q_MembersView'])) {
				$query->inspect = 'spMembersView';
				$query->show    = true;
			}
			$records = SP()->DB->select($query);

			if ($records) {
				$m     = array();
				$ugidx = -1;
				$midx  = 0;

				$data->count = SP()->DB->select('SELECT FOUND_ROWS()', 'var');
				foreach ($records as $r) {
					# for user list only, set up dummy usergroup
					if ($groupBy != 'usergroup') $ugidx = 0;

					# we have data
					$this->membersListStatus = 'data';

					# set up the usergroup outer data and member inner data
					if ($groupBy == 'usergroup' && ($ugidx == -1 || $m[$ugidx]->usergroup_id != $r->usergroup_id)) {
						$ugidx++;
						$midx                      = 0;
						$m[$ugidx]                 = new stdClass();
						$m[$ugidx]->usergroup_id   = $r->usergroup_id;
						$name                      = (!empty($r->usergroup_name)) ? SP()->displayFilters->title($r->usergroup_name) : SP()->primitives->front_text('No Memberships');
						$desc                      = (!empty($r->usergroup_desc)) ? SP()->displayFilters->title($r->usergroup_desc) : SP()->primitives->front_text('Members without any usergroup memberships');
						$m[$ugidx]->usergroup_name = $name;
						$m[$ugidx]->usergroup_desc = $desc;

						$m[$ugidx] = apply_filters('sph_members_list_records', $m[$ugidx], $r);
					}
					if (isset($r->user_id)) {
						$m[$ugidx]->members[$midx]                  = new stdClass();
						$m[$ugidx]->members[$midx]->user_id         = $r->user_id;
						$m[$ugidx]->members[$midx]->ID              = $r->user_id;
						$m[$ugidx]->members[$midx]->display_name    = SP()->displayFilters->title($r->display_name);
						$m[$ugidx]->members[$midx]->posts           = $r->posts;
						$m[$ugidx]->members[$midx]->user_url        = $r->user_url;
						$m[$ugidx]->members[$midx]->admin           = $r->admin;
						$m[$ugidx]->members[$midx]->avatar          = unserialize($r->avatar);
						$m[$ugidx]->members[$midx]->user_email      = $r->user_email;
						$m[$ugidx]->members[$midx]->user_options    = unserialize($r->user_options);
						$m[$ugidx]->members[$midx]->lastvisit       = SP()->dateTime->apply_timezone(SP()->dateTime->lastvisit_to_timezone($r->lastvisit, $m[$ugidx]->members[$midx]->user_options), 'mysql');
						$m[$ugidx]->members[$midx]->user_registered = SP()->dateTime->registration_to_timezone($r->user_registered);

						$m[$ugidx]->members[$midx] = apply_filters('sph_members_list_records', $m[$ugidx]->members[$midx], $r);
						$midx++;
					}
				}
				$data->records = $m;
			}
		} else {
			$this->membersListStatus = 'no access';
		}

		return $data;
	}
}
