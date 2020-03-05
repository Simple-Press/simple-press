<?php

class spcView {
	public $groups;

	public $thisGroup;

	public $forums;

	public $thisForum;

	public $thisSubForum;

	public $thisForumSubs;

	public $topics;

	public $thisTopic;

	public $thisPost;

	public $thisPostUser;

	public $thisSearch;

	public $members;

	public $thisMemberGroup;

	public $thisMember;

	public $listTopics;

	public $thisListTopic;

	public $listPosts;

	public $thisListPost;

	/*
	 * Group View Class stuff
	 */
	public function has_groups($ids = '', $idOrder = false) {
		$this->groups = new spcGroupView($ids, true, $idOrder);

		return $this->groups->has_groups();
	}

	public function loop_groups() {
		return $this->groups->loop_groups();
	}

	public function the_group() {
		$this->thisGroup = $this->groups->the_group();
	}

	public function has_forums() {
		return $this->groups->has_forums();
	}

	public function loop_forums() {
		return $this->groups->loop_forums();
	}

	public function the_forum() {
		$this->thisForum     = $this->groups->the_forum();
		$this->thisForumSubs = $this->groups->forumDataSubs;
	}

	/*
	 * Forum View Class stuff
	 */
	public function this_forum($id = 0, $page = 0) {
		$this->forums        = new spcForumView($id, $page);
		$this->thisForum     = $this->forums->this_forum();
		$this->thisForumSubs = (isset($this->thisForum->subforums)) ? $this->thisForum->subforums : '';

		return $this->thisForum;
	}

	public function has_subforums() {
		return $this->forums->has_subforums();
	}

	public function loop_subforums() {
		return $this->forums->loop_subforums();
	}

	public function the_subforum() {
		$this->thisSubForum = $this->forums->the_subforum();
		if ($this->thisSubForum->parent == $this->thisForum->forum_id) {
			$this->forums->currentChild++;
		}
	}

	public function has_topics() {
		return $this->forums->has_topics();
	}

	public function loop_topics() {
		return $this->forums->loop_topics();
	}

	public function the_topic() {
		$this->thisTopic = $this->forums->the_topic();
	}

	public function is_child_subforum() {
		return ($this->thisForum->forum_id == $this->thisSubForum->parent);
	}

	/*
	 * Topic View Class stuff
	 */
	public function this_topic($id = 0, $page = 0) {
		$this->topics    = new spcTopicView($id, $page);
		$this->thisTopic = $this->topics->this_topic();

		return $this->thisTopic;
	}

	public function has_posts() {
		return $this->topics->has_posts();
	}

	public function loop_posts() {
		return $this->topics->loop_posts();
	}

	public function the_post() {
		$this->thisPost     = $this->topics->the_post();
		$this->thisPostUser = $this->thisPost->postUser;
		sp_display_inspector('tv_thisPostUser', $this->thisPostUser);
	}

	/*
	 * Members View Class stuff
	 */
	public function has_member_groups($groupBy = 'usergroup', $orderBy = 'id', $sortBy = 'asc', $number = 15, $limitUG = false, $ugids = '') {
		$this->members = new spcMembersList($groupBy, $orderBy, $sortBy, $number, $limitUG, $ugids);

		return $this->members->has_member_groups();
	}

	public function loop_member_groups() {
		return $this->members->loop_member_groups();
	}

	public function the_member_group() {
		$this->thisMemberGroup = $this->members->the_member_group();
	}

	public function has_members() {
		return $this->members->has_members();
	}

	public function loop_members() {
		return $this->members->loop_members();
	}

	public function the_member() {
		$this->thisMember = $this->members->the_member();
	}

	/*
	 * Topic List Class stuff
	 */
	public function has_topiclist() {
		return $this->listTopics->has_topiclist();
	}

	public function loop_topiclist() {
		return $this->listTopics->loop_topiclist();
	}

	public function the_topiclist() {
		$this->thisListTopic = $this->listTopics->the_topiclist();
	}

	/*
	 * Post List Class stuff
	 */
	public function has_postlist() {
		return $this->listPosts->has_postlist();
	}

	public function loop_postlist() {
		return $this->listPosts->loop_postlist();
	}

	public function the_postlist() {
		$this->thisListPost = $this->listPosts->the_postlist();
	}
	
	public function forum_count() {
		return $this->groups->forum_count();
	}
}