<?php
/*
Simple:Press
Edit Tools - Move Topic/Move Post
$LastChangedDate: 2018-12-20 16:57:31 -0600 (Thu, 20 Dec 2018) $
$Rev: 15862 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');
sp_forum_ajax_support();

# get out of here if no action specified
if (empty($_GET['targetaction'])) die();
$action = SP()->filters->str($_GET['targetaction']);

# check the autocomplete task before the nonce check
if ($action == 'notify-search') sp_search_user();

# now check the nonce
if (!sp_nonce('spForumTools')) die();

if ($action == 'edit-title') sp_edit_title_popup();
if ($action == 'move-topic') sp_move_topic_popup();
if ($action == 'move-post') sp_move_post_popup();
if ($action == 'reassign') sp_reassign_post_popup();
if ($action == 'properties') sp_show_properties();
if ($action == 'sort-forum') sp_forum_sort_order();
if ($action == 'sort-topic') sp_topic_sort_order();
if ($action == 'notify') sp_notify_user();
if ($action == 'order-pins') sp_order_topic_pins();
if ($action == 'delete-post') sp_post_delete();
if ($action == 'delete-topic') sp_topic_delete();
if ($action == 'pin-post') sp_pin_post();
if ($action == 'pin-topic') sp_pin_topic();
if ($action == 'lock-topic') sp_lock_topic();

die();

function sp_edit_title_popup() {
	$defs = array('tagClass'		=> 'spForumToolsPopup',
	              'formClass'		=> 'spPopupForm',
				  'titleClass'		=> 'spHeaderName',
				  'controlClass'	=> 'spControl',
				  'buttonClass'		=> 'spSubmit'
				 );

	$data = array();
	if (file_exists(SPTEMPLATES.'data/popup-form-data.php')) {
		include SPTEMPLATES.'data/popup-form-data.php';
		$data = sp_edit_title_popup_data();
	}

	$a = wp_parse_args($data, $defs);
	extract($a, EXTR_SKIP);
	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$formClass		= esc_attr($formClass);
	$titleClass		= esc_attr($titleClass);
	$controlClass	= esc_attr($controlClass);
	$buttonClass	= esc_attr($buttonClass);

	$topicid = SP()->filters->integer($_GET['topicid']);
	$forumid = SP()->filters->integer($_GET['forumid']);
	$userid  = SP()->filters->integer($_GET['userid']);
	$thistopic = SP()->DB->table(SPTOPICS, "topic_id=$topicid", 'row');

	if (!(SP()->auths->get('edit_own_topic_titles', $forumid) && $userid == SP()->user->thisUser->ID) && !SP()->auths->get('edit_any_topic_titles', $forumid)) die();

	$thisforum = SP()->DB->table(SPFORUMS, "forum_id=$thistopic->forum_id", 'row');
    $page = sp_get_page_from_url();

	$out = "<div id='spMainContainer' class='$tagClass'>";
	$out.= '<form class="'.$formClass.'" action="'.SP()->spPermalinks->build_url($thisforum->forum_slug, '', $page, 0).'" method="post" name="edittopicform">';
	$out.= '<input type="hidden" name="tid" value="'.$thistopic->topic_id.'" />';
    $out.= '<div class="spCenter">';
	$out.= "<div class='$titleClass'>".SP()->primitives->front_text('Topic Title').':</div>';
	$out.= "<div><textarea class='$controlClass' name='topicname' rows='2'>".esc_textarea($thistopic->topic_name).'</textarea></div>';

	$s = (SP()->user->thisUser->admin) ? '' : " style='display:none;'";
	$out.= "<div class='$titleClass' $s>".SP()->primitives->front_text('Topic Slug').':</div>';
	$out.= "<div><textarea class='$controlClass' $s name='topicslug' rows='2'>".esc_textarea($thistopic->topic_slug).'</textarea></div>';

    $out = apply_filters('sph_topic_title_edit' , $out, $thistopic);
	$out.= '<div class="spCenter"><br />';
	$out.= "<input type='submit' class='$buttonClass' name='edittopic' value='".SP()->primitives->front_text('Save')."' />";
	$out.= "<input type='button' class='$buttonClass spCancelScript' name='cancel' value='".SP()->primitives->front_text('Cancel')."' />";

	$out.= '</div>';
    $out.= '</div>';
	$out.= '</form>';
	$out.= '</div>';
    echo $out;
}

function sp_move_topic_popup() {
	$defs = array('tagClass'		=> 'spForumToolsPopup',
	              'formClass'		=> 'spPopupForm',
				  'titleClass'		=> 'spForumToolsHeaderTitle',
				  'highlightClass'	=> 'spForumToolsHeaderTitle',
				  'controlClass'	=> 'spControl',
				  'buttonClass'		=> 'spSubmit'
				 );

	$data = array();
	if (file_exists(SPTEMPLATES.'data/popup-form-data.php')) {
		include SPTEMPLATES.'data/popup-form-data.php';
		$data = sp_move_topic_popup_data();
	}

	$a = wp_parse_args($data, $defs);
	extract($a, EXTR_SKIP);
	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$formClass		= esc_attr($formClass);
	$titleClass		= esc_attr($titleClass);
	$highlightClass	= esc_attr($highlightClass);
	$controlClass	= esc_attr($controlClass);
	$buttonClass	= esc_attr($buttonClass);

	$topicid = SP()->filters->integer($_GET['topicid']);
	$forumid = SP()->filters->integer($_GET['forumid']);
	if (!SP()->auths->get('move_topics', $forumid)) die();

	$thistopic = SP()->DB->table(SPTOPICS, "topic_id=$topicid", 'row');
	$thisforum = SP()->DB->table(SPFORUMS, "forum_id=$forumid", 'row');
    if (empty($thistopic) || empty($thisforum)) die();

	require_once SPBOOT.'core/sp-core-support-functions.php';

	$out = "<div id='spMainContainer' class='$tagClass'>";
	$out.= "<div class='spForumToolsHeader'>";
	$out.= "<div class='$titleClass'>".SP()->primitives->front_text('Select new forum for this topic')."</div>";
	$out.= "<div class='$highlightClass'>".SP()->displayFilters->title($thistopic->topic_name)."</div>";
	$out.= '</div>';
	$out.= "<form classs='$formClass' action='".SP()->spPermalinks->build_url($thisforum->forum_slug, '', 1, 0)."' method='post' name='movetopicform'>";
	$out.= "<input type='hidden' name='currenttopicid' value='$topicid' />";
	$out.= "<input type='hidden' name='currentforumid' value='$forumid' />";
	$out.= "<div class='spCenter'>";
	$out.= sp_render_group_forum_select(false, false, true, true, SP()->primitives->front_text('Select forum'), 'forumid', 'spSelect $controlClass');
	$out.= sp_InsertBreak('echo=0');
	$out.= "<input type='button' class='$buttonClass spCancelScript' name='cancel' value='".SP()->primitives->front_text('Cancel')."' />";
	$out.= "<input type='submit' class='$buttonClass' name='maketopicmove' value='".SP()->primitives->front_text('Move Topic to Selected Forum')."' />";
	$out.= '</div></form></div>';

	echo $out;
}

function sp_reassign_post_popup() {
	$defs = array('tagClass'		=> 'spForumToolsPopup',
	              'formClass'		=> 'spPopupForm',
				  'titleClass'		=> 'spForumToolsHeaderTitle',
				  'controlClass'	=> 'spControl',
				  'buttonClass'		=> 'spSubmit'
				 );

	$data = array();
	if (file_exists(SPTEMPLATES.'data/popup-form-data.php')) {
		include SPTEMPLATES.'data/popup-form-data.php';
		$data = sp_reassign_post_popup_data();
	}

	$a = wp_parse_args($data, $defs);
	extract($a, EXTR_SKIP);
	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$formClass		= esc_attr($formClass);
	$titleClass		= esc_attr($titleClass);
	$controlClass	= esc_attr($controlClass);
	$buttonClass	= esc_attr($buttonClass);

	$thispost = SP()->filters->integer($_GET['pid']);
	$thisuser = SP()->filters->integer($_GET['uid']);
    $thistopic = SP()->filters->integer($_GET['id']);
    if (empty($thispost) || empty($thistopic)) die();

	$thistopic = SP()->DB->table(SPTOPICS, "topic_id=$thistopic", 'row');
	if (!SP()->auths->get('reassign_posts', $thistopic->forum_id)) die();

	$thisforum = SP()->DB->table(SPFORUMS, "forum_id=$thistopic->forum_id", 'row');

	$out = "<div id='spMainContainer' class='$tagClass'>";
	$out.= "<div class='spForumToolsHeader'>";
	$out.= "<div class='$titleClass'>".SP()->primitives->front_text('Reassign post to new user')." (".SP()->primitives->front_text('current ID').': '.$thisuser.")</div>";
	$out.= '</div>';
	$out.= "<form class='$formClass' action='".SP()->spPermalinks->build_url($thisforum->forum_slug, $thistopic->topic_slug, 0, $thispost)."' method='post' name='reassignpostform'>";
	$out.= '<div class="spCenter">';
	$out.= "<input type='hidden' name='postid' value='".$thispost."' />";
	$out.= "<input type='hidden' name='olduserid' value='".$thisuser."' />";
	$out.= SP()->primitives->front_text('New user ID');
	$out.= "<input type='text' class='$controlClass' size='80' name='newuserid' value='' /><br /><br />";
	$out.= "<input type='submit' class='$buttonClass' name='makepostreassign' value=".SP()->primitives->front_text('Reassign Post')."' />";
	$out.= "<input type='button' class='$buttonClass spCancelScript' name='cancel' value='".SP()->primitives->front_text('Cancel')."' />";
	$out.= '</div></form></div>';
	echo $out;
}

function sp_show_properties() {
	$defs = array('tableClass'		=> 'spPopupTable',
	              'labelClass'		=> 'spLabel',
				  'dataClass'		=> 'spLabel',
				  'buttonClass'		=> 'spSubmit'
				 );

	$data = array();
	if (file_exists(SPTEMPLATES.'data/popup-form-data.php')) {
		include SPTEMPLATES.'data/popup-form-data.php';
		$data = sp_properties_popup_data();
	}

	$a = wp_parse_args($data, $defs);
	extract($a, EXTR_SKIP);
	# sanitize before use
	$tableClass		= esc_attr($tableClass);
	$labelClass		= esc_attr($labelClass);
	$dataClass		= esc_attr($dataClass);
	$buttonClass	= esc_attr($buttonClass);

    $forumid = SP()->filters->integer($_GET['forum']);
    $topicid = SP()->filters->integer($_GET['topic']);
    if (empty($forumid) || empty($topicid)) die();

	$thistopic = SP()->DB->table(SPTOPICS, "topic_id=$topicid", 'row');

	if (!SP()->user->thisUser->admin && !SP()->user->thisUser->moderator) die();

	$thisforum = SP()->DB->table(SPFORUMS, "forum_id=$forumid", 'row');

	if (isset($_GET['post'])) {
		$groupid = SP()->filters->integer($thisforum->group_id);
		$thisgroup = SP()->DB->table(SPGROUPS, "group_id=$groupid", 'row');
	} else {
        $groupid = SP()->filters->integer($_GET['group']);
        if (empty($groupid)) die();
		$thisgroup = SP()->DB->table(SPGROUPS, "group_id=$groupid", 'row');
	}

	$posts = SP()->DB->table(SPPOSTS, "topic_id=$thistopic->topic_id", '', 'post_id');
	if ($posts) {
		$first = $posts[0]->post_id;
		$last  = $posts[count($posts) - 1]->post_id;
	}

	# set timezone onto the started date
	$topicstart = SP()->dateTime->apply_timezone($thistopic->topic_date);

	$out = "<div id='spMainContainer'>";
	$out.= "<table class='$tableClass'>";

	$out.= "<tr><td class='$labelClass' style='width:35%'>".SP()->primitives->front_text('Group ID')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".$thisgroup->group_id."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Group Title')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".SP()->displayFilters->title($thisgroup->group_name)."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Forum ID')."</td>";
	$out.= "<td class='$dataClass'>".$thisforum->forum_id."</td>";
	$out.= "<td class='sfdata'>".sp_rebuild_forum_form($thisforum->forum_id, $thistopic->topic_id, $thisforum->forum_slug, $thistopic->topic_slug, $buttonClass)."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Forum Title')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".SP()->displayFilters->title($thisforum->forum_name)."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Forum Slug')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".$thisforum->forum_slug."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Topics in Forum')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".$thisforum->topic_count."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Topic ID')."</td>";
	$out.= "<td class='$dataClass'>".$thistopic->topic_id."</td>";
	$out.= "<td class='sfdata'>".sp_rebuild_topic_form($thisforum->forum_id, $thistopic->topic_id, $thisforum->forum_slug, $thistopic->topic_slug, $buttonClass)."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Topic Title')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".SP()->displayFilters->title($thistopic->topic_name)."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Topic Slug')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".$thistopic->topic_slug."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Posts in Topic')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".$thistopic->post_count."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Topic Started')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".$topicstart."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('First Post ID')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".$first."</td></tr>";

	$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Last Post ID')."</td>";
	$out.= "<td colspan='2' class='$dataClass'>".$last."</td></tr>";

	if (isset($_GET['post'])) {
		$postid = SP()->filters->integer($_GET['post']);
		$post = SP()->DB->table(SPPOSTS, "post_id=$postid");

		$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('This Post ID')."</td>";
		$out.= "<td colspan='2' class='$dataClass'>".$postid."</td></tr>";
		$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Poster ID')."</td>";
		$out.= "<td colspan='2' class='$dataClass'>".$post[0]->user_id."</td></tr>";
		$out.= "<tr><td class='$labelClass'>".SP()->primitives->front_text('Poster IP')."</td>";
		$out.= "<td colspan='2' class='$dataClass'>".$post[0]->poster_ip."</td></tr>";
	}

	$out.= "</table></div>";
	echo $out;
}

# Support functions for the properties tool
function sp_rebuild_forum_form($forumid, $topicid, $forumslug, $topicslug, $class) {
	$out = '<form action="'.SP()->spPermalinks->build_url($forumslug, $topicslug, 1, 0).'" method="post" name="forumrebuild">'."\n";
	$out.= '<input type="hidden" name="forumid" value="'.$forumid.'" />'."\n";
	$out.= '<input type="hidden" name="topicid" value="'.$topicid.'" />'."\n";
	$out.= '<input type="hidden" name="forumslug" value="'.esc_attr($forumslug).'" />'."\n";
	$out.= '<input type="hidden" name="topicslug" value="'.esc_attr($topicslug).'" />'."\n";
	$out.= '<input type="submit" class="'.$class.'" name="rebuildforum" value="'.SP()->primitives->front_text('Verify').'" />';
	$out.= '</form>'."\n";
	return $out;
}

function sp_rebuild_topic_form($forumid, $topicid, $forumslug, $topicslug, $class) {
	$out = '<form action="'.SP()->spPermalinks->build_url($forumslug, $topicslug, 1, 0).'" method="post" name="topicrebuild">'."\n";
	$out.= '<input type="hidden" name="forumid" value="'.$forumid.'" />'."\n";
	$out.= '<input type="hidden" name="topicid" value="'.$topicid.'" />'."\n";
	$out.= '<input type="hidden" name="forumslug" value="'.esc_attr($forumslug).'" />'."\n";
	$out.= '<input type="hidden" name="topicslug" value="'.esc_attr($topicslug).'" />'."\n";
	$out.= '<input type="submit" class="'.$class.'" name="rebuildtopic" value="'.SP()->primitives->front_text('Verify').'" />';
	$out.= '</form>'."\n";
	return $out;
}

function sp_forum_sort_order() {
	$forumid = SP()->filters->integer($_GET['forumid']);
	if (!SP()->user->thisUser->admin) die();

    # make sure we have valid forum
	$thisforum = SP()->DB->table(SPFORUMS, "forum_id=$forumid", 'row');
    if (empty($thisforum)) die();

    # if already reversed remove flag or reverse if not
    $key = false;
	$sort_data = SP()->meta->get_value('sort_order', 'forum');
	if (empty($sort_data)) {
		$sort_data = array();  // $sort_data needs to be an array to avoid issues later below (especially around line #347 where it will error out if $sort_data is still a string).
	}
    if (!empty($sort_data)) {
	    $key = array_search($forumid, (array) $sort_data);
	}
    if ($key === false) {
        $sort_data[] = $forumid;
    } else {
        unset($sort_data[$key]);
        $sort_data = array_keys($sort_data);
		if (empty($sort_data)) $sort_data = '';
    }

	SP()->meta->delete(0, 'forum', 'sort_order');
    SP()->meta->add('sort_order', 'forum', $sort_data);

    SP()->primitives->redirect(SP()->spPermalinks->build_url($thisforum->forum_slug, '', 1));

    die();
}

function sp_topic_sort_order() {
	$topicid = SP()->filters->integer($_GET['topicid']);
	if (!SP()->user->thisUser->admin) die();

    # make sure we have valid forum
	$thistopic = SP()->DB->table(SPTOPICS, "topic_id=$topicid", 'row');
	$thisforum = SP()->DB->table(SPFORUMS, "forum_id=$thistopic->forum_id", 'row');
    if (empty($thistopic)) die();

    # if already reversed remove flag or reverse if not
    $key = false;
	$sort_data = SP()->meta->get_value('sort_order', 'topic');
	if (empty($sort_data)) {
		$sort_data = array();  // $sort_data needs to be an array to avoid issues later below (especially around line #381 where it will error out if $sort_data is still a string).
	}	
    if (!empty($sort_data)) {
	    $key = array_search($topicid, (array) $sort_data);
	}
    if ($key === false) {
        $sort_data[] = $topicid;
    } else {
        unset($sort_data[$key]);
        $sort_data = array_keys($sort_data);
		if (empty($sort_data)) $sort_data = '';
    }

	SP()->meta->delete(0, 'topic', 'sort_order');
    SP()->meta->add('sort_order', 'topic', $sort_data);

    SP()->primitives->redirect(SP()->spPermalinks->build_url($thisforum->forum_slug, $thistopic->topic_slug, 1));

    die();
}

function sp_move_post_popup() {
	$defs = array('tagClass'		=> 'spForumToolsPopup',
	              'formClass'		=> 'spPopupForm',
	              'setClass'		=> '',
	              'radioClass'		=> '',
				  'titleClass'		=> 'spForumToolsHeaderTitle',
				  'highlightClass'	=> 'spForumToolsHeaderTitle',
				  'controlClass'	=> 'spSelect',
				  'inputClass'		=> 'spControl',
				  'buttonClass'		=> 'spSubmit'
				 );

	$data = array();
	if (file_exists(SPTEMPLATES.'data/popup-form-data.php')) {
		include SPTEMPLATES.'data/popup-form-data.php';
		$data = sp_move_post_popup_data();
	}

	$a = wp_parse_args($data, $defs);
	extract($a, EXTR_SKIP);
	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$formClass		= esc_attr($formClass);
	$setClass		= esc_attr($setClass);
	$radioClass		= esc_attr($radioClass);
	$titleClass		= esc_attr($titleClass);
	$highlightClass	= esc_attr($highlightClass);
	$controlClass	= esc_attr($controlClass);
	$buttonClass	= esc_attr($buttonClass);

	$thispost = SP()->filters->integer($_GET['pid']);
	$topicid = SP()->filters->integer($_GET['id']);
	$thispostindex = SP()->filters->integer($_GET['pix']);
	$thistopic = SP()->DB->table(SPTOPICS, "topic_id=$topicid", 'row');
    if (empty($thispost) || empty($thistopic)) die();

	$thisforum = SP()->DB->table(SPFORUMS, "forum_id=$thistopic->forum_id", 'row');
	if (!SP()->auths->get('move_posts', $thistopic->forum_id)) die();

	$thisPostData = SP()->DB->table(SPPOSTS, "post_id=$thispost", 'row');

	$out = "<div id='spMainContainer' class='$tagClass'>";

	$out.= "<form class='$formClass' action='".SP()->spPermalinks->build_url($thisforum->forum_slug, $thistopic->topic_slug, 1, 0)."' method='post' name='movepostform'>";

	$out.= "<input type='hidden' name='postid' value='".$thispost."' />";
	$out.= "<input type='hidden' name='oldtopicid' value='".$topicid."' />";
	$out.= "<input type='hidden' name='oldforumid' value='".$thisforum->forum_id."' />";
	$out.= "<input type='hidden' name='oldpostindex' value='".$thispostindex."' />";

	$out.= "<fieldset class='$setClass'><legend>".SP()->primitives->front_text('Select Operation')."</legend>";

	do_action('sph_move_topic_form_top', $thisforum, $thistopic, $thisPostData);

	$out.= "<input type='radio' class='$radioClass' name='moveop' id='single' value='single' checked='checked' />";
	$out.= "<label for='single'>&nbsp;".SP()->primitives->front_text('Move this post only')."</label>";

	$out.= "<input type='radio' class='$radioClass' name='moveop' id='tostart' value='tostart' />";
	$out.= "<label for='tostart'>&nbsp;".SP()->primitives->front_text('Move this post and ALL preceding posts')."</label>";

	$out.= "<input type='radio' class='$radioClass' name='moveop' id='toend' value='toend' />";
	$out.= "<label for='toend'>&nbsp;".SP()->primitives->front_text('Move this post and ALL succeeding posts')."</label>";

	do_action('sph_move_topic_form_middle', $thisforum, $thistopic, $thisPostData);

	$out.= "<input type='radio' class='$radioClass' name='moveop' id='select' value='select' />";
	$out.= "<label for='select'>&nbsp;".SP()->primitives->front_text('Move the posts listed below').":</label>";

	do_action('sph_move_topic_form_bottom', $thisforum, $thistopic, $thisPostData);

	$out.= "<label class='$titleClass' for='idList'>".SP()->primitives->front_text('Post Numbers to move - separated by commas')."</label>";
	$out.= "<input type='text' class='spControl' name='idlist' value='".$thispostindex.",' /><br />";

	$out.= "<span>";
	$out.= "<input type='button' class='$buttonClass spStackBtnLong' id='movetonew' name='movetonew' value='".SP()->primitives->front_text('Move to a NEW topic')."' />";
	$out.= "<input type='button' class='$buttonClass spStackBtnLong' id='movetoold' name='movetoold' value='".SP()->primitives->front_text('Move to an EXISTING topic')."' />";
	$out.= "<input type='button' class='$buttonClass spStackBtnLong spCancelScript' name='cancel' value='".SP()->primitives->front_text('Cancel Move')."' />";
	$out.= "</span>";

	$out.= "</fieldset>";

	$out.= "<div id='newtopic' class='spCenter' style='display:none;'>";
	$out.= "<p class='$highlightClass spCenter' ><b>".SP()->primitives->front_text('Move to a NEW topic')."</b></p>";
	$out.= sp_render_group_forum_select(false, false, true, true, SP()->primitives->front_text('Select forum'), 'forumid', $controlClass);
	$out.= "<br /><br />";
	$out.= "<p class='$highlightClass spCenter'>".SP()->primitives->front_text('New topic name')."</p>";
	$out.= "<input type='text' class='$inputClass' size='80' name='newtopicname' value='' />";

	do_action('sph_move_post_form', $thispost, $topicid);

	$out.= "<input type='submit' class='$buttonClass' name='makepostmove1' value='".SP()->primitives->front_text('Move')."' />";
	$out.= "</div>";

	$out.= "<div id='oldtopic' class='spCenter' style='display:none;'>";
	$out.= "<p class='$highlightClass' ><b>".SP()->primitives->front_text('Move to a EXISTING topic')."</b></p>";
	$out.= "<p class='$highlightClass' >".SP()->primitives->front_text('Click on the Move button below and when the page refreshes navigate to the target topic to complete the move')."</p>";

	do_action('sph_move_post_form', $thispost, $topicid);

	$out.= "<input type='submit' class='$buttonClass' name='makepostmove2' value='".SP()->primitives->front_text('Move')."' />";
	$out.= "</div></form></div>";

	echo $out;
}

function sp_notify_user() {
	$thisPost = SP()->filters->integer($_GET['pid']);
    if (empty($thisPost)) die();
	if (!SP()->user->thisUser->admin && !SP()->user->thisUser->moderator) die();

	$defs = array('tagClass'		=> 'spForumToolsPopup',
	              'formClass'		=> 'spPopupForm',
				  'titleClass'		=> 'spLabel',
				  'controlClass'	=> 'spControl',
				  'buttonClass'		=> 'spSubmit'
				 );

	$data = array();
	if (file_exists(SPTEMPLATES.'data/popup-form-data.php')) {
		include SPTEMPLATES.'data/popup-form-data.php';
		$data = sp_notify_user_popup_data();
	}

	$a = wp_parse_args($data, $defs);
	extract($a, EXTR_SKIP);
	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$formClass		= esc_attr($formClass);
	$titleClass		= esc_attr($titleClass);
	$controlClass	= esc_attr($controlClass);
	$buttonClass	= esc_attr($buttonClass);

    $site = SPAJAXURL.'spForumTools&targetaction=notify-search&rand='.rand();
?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function() {
				$('#sp_notify_user').autocomplete({
					source : '<?php echo $site; ?>',
					disabled : false,
					delay : 200,
					minLength: 1,
				});
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>

<?php
	$out = "<div id='spMainContainer' class='$tagClass'>";
	$out.= "<form class='$formClass' action='".SP()->spPermalinks->permalink_from_postid($thisPost)."' method='post' name='notifyuserform'>";
	$out.= "<div class='spCenter'>";
	$out.= "<input type='hidden' name='postid' value='".$thisPost."' />";
	$out.= "<label class='$titleClass' for='sp_notify_user'>".SP()->primitives->front_text('User to notify').": </label>";
	$out.= "<input type='text' id='sp_notify_user' class='$controlClass' name='sp_notify_user' />";
	$out.= "<p class='$titleClass'>".SP()->primitives->front_text("Start typing a member's name above and it will auto-complete")."</p>";
	$out.= "<label class='$titleClass' for='sp_notify_user'>".SP()->primitives->front_text('Message').": </label>";
	$out.= "<input type='text' id='message' class='$controlClass' name='message' />";
	$out.= "<input type='submit' class='$buttonClass' name='notifyuser' value='".SP()->primitives->front_text('Notify')."' />";
	$out.= "<input type='button' class='$buttonClass spCancelScript' name='cancel' value='".SP()->primitives->front_text('Cancel')."' />";
	$out.= "</div></form></div>";
	echo $out;
}

function sp_search_user() {
    global $wpdb;

	$out = '[]';

	$query = SP()->filters->str($_GET['term']);
	$where = "display_name LIKE '%".SP()->filters->esc_sql($wpdb->esc_like($query))."%'";
	$users = SP()->DB->table(SPMEMBERS, $where, '', 'display_name DESC', 25);
	if ($users) {
		$primary = '';
		$secondary = '';
		foreach ($users as $user) {
			$uname = SP()->displayFilters->name($user->display_name);
			$cUser = array ('id' => $user->user_id, 'value' => $uname);
			if (strcasecmp($query, substr($uname, 0, strlen($query))) == 0) {
				$primary.= json_encode($cUser).',';
			} else {
				$secondary.= json_encode($cUser).',';
			}
		}
		if ($primary != '' || $secondary != '') {
			if ($primary != '') $primary = trim($primary, ',').',';
			if ($secondary != '') $secondary = trim($secondary, ',');
			$out = '['.trim($primary.$secondary, ',').']';
		}
	}
	echo $out;
	die();
}

function sp_order_topic_pins() {
	$forumid = SP()->filters->integer($_GET['forumid']);
	if (!SP()->auths->get('pin_topics', $forumid)) die();

	$defs = array('tagClass'		=> 'spForumToolsPopup',
				  'formClass'		=> '',
				  'tableClass'		=> 'spPopupTable',
	              'labelClass'		=> 'spLabel',
				  'dataClass'		=> 'spControl',
				  'buttonClass'		=> 'spSubmit'
				 );

	$data = array();
	if (file_exists(SPTEMPLATES.'data/popup-form-data.php')) {
		include SPTEMPLATES.'data/popup-form-data.php';
		$data = sp_order_pins_popup_data();
	}

	$a = wp_parse_args($data, $defs);
	extract($a, EXTR_SKIP);
	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$formClass		= esc_attr($formClass);
	$tableClass		= esc_attr($tableClass);
	$labelClass		= esc_attr($labelClass);
	$dataClass		= esc_attr($dataClass);
	$buttonClass	= esc_attr($buttonClass);

	$thisforum = SP()->DB->table(SPFORUMS, "forum_id=$forumid", 'row');
	$topics = SP()->DB->table(SPTOPICS, "forum_id=$forumid AND topic_pinned > 0", '', 'topic_pinned DESC');

    if (empty($topics) || empty($forumid)) die();

	$out = "<div id='spMainContainer' class='spForumToolsPopup'>";
	$out.= "<div class='spForumToolsHeader'>";
	$out.= "<div class='spForumToolsHeaderTitle'>".SP()->primitives->front_text('Please note: The HIGHER numbered topics will appear at the top of the list')."</div>";
	$out.= "</div>";
	$out.= "<form class='$formClass' action='".SP()->spPermalinks->build_url($thisforum->forum_slug, '', 1, 0)."' method='post' name='ordertopicpinsform'>";
	$out.= "<input type='hidden' name='orderpinsforumid' value='".$forumid."' />";
	$out.= "<table class='$tableClass'>";
	foreach ($topics as $topic) {
		$out.= "<tr><td class='$labelClass' style='width:85%;border:1px solid #ddd'>".SP()->displayFilters->title($topic->topic_name);
		$out.= "<input type='hidden' name='topicid[]' value='".$topic->topic_id."' /></td>";
		$out.= "<td style='border: 1px solid #ddd'>";
		$out.= "<input type='text' class='$dataClass' size='6' name='porder[]' value='".$topic->topic_pinned."' />";
		$out.= "</td></tr>";
	}
	$out.= "</table>";
	$out.= "<div class='spCenter'>";
	$out.= "<input type='submit' class='$buttonClass' name='ordertopicpins' value='".SP()->primitives->front_text('Save Pin Order Changes')."' />";
	$out.= "<input type='button' class='$buttonClass spCancelScript' name='cancel' value='".SP()->primitives->front_text('Cancel')."' />";
	$out.= "</div></form></div>";
	echo $out;
}

function sp_post_delete() {
    sp_delete_post(SP()->filters->integer($_GET['killpost']));

	if ((int) $_GET['count'] == 1) {
    	$forumslug = SP()->DB->table(SPFORUMS, 'forum_id='.SP()->filters->integer($_GET['killpostforum']), 'forum_slug');
       	$topicslug = SP()->DB->table(SPTOPICS, 'topic_id='.SP()->filters->integer($_GET['killposttopic']), 'topic_slug');
        $page = SP()->filters->integer($_GET['page']);
        if ($page == 1) {
            $returnURL = SP()->spPermalinks->build_url($forumslug, '', 0);
        } else {
            $page = $page - 1;
            $returnURL = SP()->spPermalinks->build_url($forumslug, $topicslug, $page);
        }
		echo $returnURL;
    }
    die();
}

function sp_topic_delete() {

    sp_delete_topic(SP()->filters->integer($_GET['killtopic']), SP()->filters->integer($_GET['killtopicforum']), false);

    $view = SP()->filters->str($_GET['view']);
    if ($view == 'topic') {
      	$forumslug = SP()->DB->table(SPFORUMS, 'forum_id='.SP()->filters->integer($_GET['killtopicforum']), 'forum_slug');
        $returnURL = SP()->spPermalinks->build_url($forumslug, '', 0);
        echo $returnURL;
    } else if ((int) $_GET['count'] == 1) {
      	$forumslug = SP()->DB->table(SPFORUMS, 'forum_id='.SP()->filters->integer($_GET['killtopicforum']), 'forum_slug');
        $page = SP()->filters->integer($_GET['page']);
        if ($page == 1) {
            $returnURL = SP()->spPermalinks->build_url($forumslug, '', 0);
        } else {
            $page = $page - 1;
            $returnURL = SP()->spPermalinks->build_url($forumslug, '', $page);
        }
        echo $returnURL;
    }

    die();
}

function sp_pin_post() {
     sp_pin_post_toggle(SP()->filters->integer($_GET['post']), SP()->filters->integer($_GET['forum']));
     die();
}

function sp_pin_topic() {
     sp_pin_topic_toggle(SP()->filters->integer($_GET['topic']), SP()->filters->integer($_GET['forum']));
     die();
}

function sp_lock_topic() {
    sp_lock_topic_toggle(SP()->filters->integer($_GET['topic']), SP()->filters->integer($_GET['forum']));
    die();
}

function sp_get_page_from_url() {
	$s = strpos($_SERVER['HTTP_REFERER'], '/page-');
	$p = ($s) ? (int) substr($_SERVER['HTTP_REFERER'], ($s+6)) : 1;
	return $p;
}
