<?php

/**
 * Common template control functions creating sections, columns etc.
 *
 * @since 6.0
 *
 * Public functions available:
 *---------------------------
 * sp_SectionStart($args, $sectionName)
 * sp_SectionEnd($args, $sectionName)
 * sp_SubSectionStart($args, $sectionName)
 * sp_SubSectionEnd($args, $sectionName)
 *
 * $LastChangedDate: 2017-01-08 19:38:29 +0000 (Sun, 08 Jan 2017) $
 * $Rev: 15002 $
 *
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');


include_once SP_PLUGIN_DIR.'/admin/library/spa-iconsets.php';

/**
 * Starts a new main section within a template
 *
 * @since 6.0
 *
 * @param string $args
 * @param string $sectionName
 *
 * @return mixed|string|void
 */
function sp_SectionStart($args = '', $sectionName = '') {
	$defs = array('tagClass' => 'spPlainSection',
	              'tagId'    => '',
	              'context'  => '',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SectionStart_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$tagId       = esc_attr($tagId);
	$context     = esc_attr($context);
	$echo        = (int) $echo;
	$sectionName = esc_attr($sectionName);

	# notify custom code before we start the section code
	do_action('sph_BeforeSectionStart', $sectionName, $a);
	do_action('sph_BeforeSectionStart_'.$sectionName, $a);

	# check for context. At this stage only 'postLoop' is active
	if ($context == 'postLoop') {
		$tagId = $tagId.SP()->forum->view->thisPost->post_id;
	}

	$rowClass = '';
	$rowId    = '';

	# specific formatting based on 'pre-defined' section names
	# NOTE: The second case name in all cases below supports V1 themes (pre-V6.0 core)
	switch ($sectionName) {
		case 'eachGroup':
		case 'group':
			if (isset(SP()->forum->view->groups)) $rowClass .= (SP()->forum->view->groups->currentGroup % 2) ? ' spOdd' : ' spEven';
			if (isset(SP()->forum->view->thisGroup)) $rowId .= "eachGroup".SP()->forum->view->thisGroup->group_id;
			break;

		case 'groupViewForums':
		case 'forumlist':
			if (isset(SP()->forum->view->thisGroup)) $rowId .= "groupViewForums".SP()->forum->view->thisGroup->group_id;
			break;

		case 'eachForum':
		case 'forum':
			if (isset(SP()->forum->view->groups)) $rowClass .= (SP()->forum->view->groups->currentForum % 2) ? ' spOdd' : ' spEven';
			if (isset(SP()->forum->view->thisForum)) {
				if (SP()->forum->view->thisForum->forum_status) $rowClass .= ' spLockedForum';
				if (isset(SP()->forum->view->thisForum->unread) && SP()->forum->view->thisForum->unread) $rowClass .= ' spUnreadPosts';
				$rowId .= "eachForum".SP()->forum->view->thisForum->forum_id;
			}
			break;

		case 'subForumView':
		case 'subforumlist':
			if (isset(SP()->forum->view->thisForum)) $rowId .= "subforumlist".SP()->forum->view->thisForum->forum_id;
			break;

		case 'eachSubForum':
		case 'subForum':
			if (isset(SP()->forum->view->forums)) $rowClass .= (SP()->forum->view->forums->currentChild % 2) ? ' spOdd' : ' spEven';
			if (isset(SP()->forum->view->thisSubForum)) {
				if (SP()->forum->view->thisSubForum->forum_status) $rowClass .= ' spLockedForum';
				if (SP()->forum->view->thisSubForum->unread) $rowClass .= ' spUnreadPosts';
				$rowId .= "eachSubForum".SP()->forum->view->thisSubForum->forum_id;
			}
			break;

		case 'forumViewTopics':
		case 'topiclist':
			if (isset(SP()->forum->view->thisForum)) $rowId .= "forumViewTopics".SP()->forum->view->thisForum->forum_id;
			break;

		case 'eachTopic':
		case 'topic':
			if (isset(SP()->forum->view->forums)) $rowClass .= (SP()->forum->view->forums->currentTopic % 2) ? ' spOdd' : ' spEven';
			if (isset(SP()->forum->view->thisTopic)) {
				if (SP()->forum->view->thisTopic->topic_status) $rowClass .= ' spLockedTopic';
				if (SP()->forum->view->thisTopic->topic_pinned) $rowClass .= ' spPinnedTopic';
				if (SP()->forum->view->thisTopic->unread) $rowClass .= ' spUnreadPosts';
				$rowId .= "eachTopic".SP()->forum->view->thisTopic->topic_id;
			}
			break;

		case 'topicViewPosts':
		case 'postlist':
			if (isset(SP()->forum->view->thisTopic)) $rowId .= "topicViewPosts".SP()->forum->view->thisTopic->topic_id;
			break;

		case 'eachPost':
		case 'post':
			if (isset(SP()->forum->view->topics)) $rowClass .= (SP()->forum->view->topics->currentPost % 2) ? ' spOdd' : ' spEven';
			if (isset(SP()->forum->view->thisPost)) {
				if (SP()->forum->view->thisPost->post_pinned) $rowClass .= ' spPinnedPost';
				if (SP()->forum->view->thisPost->new_post) $rowClass .= ' spUnreadPosts';
				if (SP()->forum->view->thisPost->post_index == 1) $rowClass .= ' spFirstPost';
				$rowClass .= ' spType-'.SP()->forum->view->thisPost->postUser->usertype;
				if (!empty(SP()->forum->view->thisPost->postUser->rank)) $rowClass .= ' spRank-'.sp_create_slug(SP()->forum->view->thisPost->postUser->rank[0]['name'], false);
				if (!empty(SP()->forum->view->thisPost->postUser->special_rank)) {
					foreach (SP()->forum->view->thisPost->postUser->special_rank as $rank) {
						$rowClass .= ' spSpecialRank-'.sp_create_slug($rank['name'], false);
					}
				}
				if (!empty(SP()->forum->view->thisPost->postUser->memberships)) {
					foreach (SP()->forum->view->thisPost->postUser->memberships as $membership) {
						$rowClass .= ' spUsergroup-'.sp_create_slug($membership['usergroup_name'], false);
					}
				}
				if (SP()->forum->view->thisPost->user_id) {
					if (SP()->forum->view->thisPost->user_id == SP()->user->thisUser->ID) {
						$rowClass .= ' spCurUserPost';
					} else {
						$rowClass .= ' spUserPost';
					}
					if (SP()->forum->view->thisTopic->topic_starter == SP()->forum->view->thisPost->user_id) $rowClass .= ' spAuthorPost';
				} else {
					$rowClass .= ' spGuestPost';
				}
				$rowId .= "eachPost".SP()->forum->view->thisPost->post_id;
			}
			break;

		case 'eachUserGroup':
		case 'memberGroup':
			if (isset(SP()->forum->view->thisMemberGroup)) $rowClass .= ' spUsergroup-'.sp_create_slug(SP()->forum->view->thisMemberGroup->usergroup_name, false);
			break;

		case 'eachMember':
		case 'member':
			if (isset(SP()->forum->view->members)) $rowClass .= (SP()->forum->view->members->currentMember % 2) ? ' spOdd' : ' spEven';
			break;

		case 'eachListTopic':
		case 'list':
			if (isset(SP()->forum->view->listTopics)) $rowClass .= (SP()->forum->view->listTopics->currentTopic % 2) ? ' spOdd' : ' spEven';
			if (isset(SP()->forum->view->thisListTopic)) $rowId .= "eachListTopic".SP()->forum->view->thisListTopic->topic_id;
			break;

		default:
			if (!empty($tagId)) $rowId .= $tagId;
			break;
	}

	# allow filtering of the row class
	$rowClass = apply_filters('sph_SectionStartRowClass', $rowClass, $sectionName, $a);
	$rowId    = apply_filters('sph_SectionStartRowID', $rowId, $sectionName, $a);

	# output section starting div
	$class = '';
	if (!empty($rowId)) $rowId = " id='$rowId'";
	if (!empty($tagClass) || !empty($rowClass)) $class = " class='$tagClass$rowClass'";
	$out = "<div$class$rowId>";

	$out = apply_filters('sph_SectionStart', $out, $sectionName, $a);

	if ($echo) {
		echo $out;

		# notify custom code that section has started
		# only valid if content is echoed out ($display=1)
		do_action('sph_AfterSectionStart', $sectionName, $a);
		do_action('sph_AfterSectionStart_'.$sectionName, $a);

		return;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SectionEnd()
#	Closes a previously started container section (div)
#	Scope:	Forum
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_SectionEnd($args = '', $sectionName = '') {
	$defs = array('tagClass' => '',
	              'tagId'    => '',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SectionEnd_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	if (!empty($tagId)) $tagId = " id='".esc_attr($tagId)."'";
	if (!empty($tagClass)) $tagClass = " class='".esc_attr($tagClass)."'";
	$echo        = (int) $echo;
	$sectionName = esc_attr($sectionName);

	# notify custom code before we end the section code
	do_action('sph_BeforeSectionEnd', $sectionName, $a);
	do_action('sph_BeforeSectionEnd_'.$sectionName, $a);

	$out = '';
	if (!empty($tagClass) || !empty($tagId)) $out .= "<div$tagId$tagClass></div>";

	$out = apply_filters('sph_SectionEnd', $out, $sectionName, $a);
	do_action('sph_SectionEnd_'.$sectionName, $a);

	# close the section begin
	$out .= "</div>";

	if ($echo) {
		echo $out;

		# notify custom code that section has ended
		# only valid if content is echoed out ($show=1)
		do_action('sph_AfterSectionEnd', $sectionName, $a);
		do_action('sph_AfterSectionEnd_'.$sectionName, $a);

		return;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ColumnStart()
#	Defines a new column (div) in all list views
#	Scope:	Forum
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_ColumnStart($args = '', $columnName = '') {
	$defs = array('tagClass' => 'spColumnSection',
	              'tagId'    => '',
	              'width'    => 'auto',
	              'height'   => 'auto',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ColumnStart_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass = esc_attr($tagClass);
	if (!empty($tagId)) $tagId = " id='".esc_attr($tagId).rand()."'";
	$width  = esc_attr($width);
	$height = esc_attr($height);
	$echo   = (int) $echo;

	# notify custom code before we start the column code
	do_action('sph_BeforeColumnStart', $columnName, $a);
	do_action('sph_BeforeColumnStart_'.$columnName, $a);

	# specific formatting based on 'defined' names
	$colClass = '';
	switch ($columnName) {
		default:
			break;
	}

	# allow filtering of the column class
	$colClass = apply_filters('sph_ColumnStartColumnClass', $colClass, $columnName);

	($width != 0) ? $wStyle = "width: $width;" : $wStyle = '';
	($height != 0) ? $hStyle = "min-height: $height;" : $hStyle = '';

	$out = "<div class='$tagClass$colClass'$tagId";
	if ($wStyle != '' || $hStyle != '') $out .= " style='$wStyle $hStyle'";
	$out .= ">";

	$out = apply_filters('sph_ColumnStart', $out, $columnName, $a);

	if ($echo) {
		echo $out;

		# notify custom code that column has ended
		# only valid if content is echoed out ($show=1)
		do_action('sph_AfterColumnStart', $columnName, $a);
		do_action('sph_AfterColumnStart_'.$columnName, $a);

		return;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ColumnEnd()
#	Closes a previously started column (div)
#	Scope:	Forum
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_ColumnEnd($args = '', $columnName = '') {
	$defs = array('tagClass' => '',
	              'tagId'    => '',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ColumnEnd_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	if (!empty($tagClass)) $tagClass = " class='".esc_attr($tagClass)."'";
	if (!empty($tagId)) $tagId = " id='".esc_attr($tagId)."'";
	$echo = (int) $echo;

	# notify custom code before we end the column code
	do_action('sph_BeforeColumnEnd', $columnName, $a);
	do_action('sph_BeforeColumnEnd_'.$columnName, $a);

	$out = '';
	if (!empty($tagClass) || !empty($tagId)) $out .= "<div id='$tagId' class='$tagClass'></div>";

	$out = apply_filters('sph_ColumnEnd', $out, $columnName, $a);
	do_action('sph_ColumnEnd_'.$columnName, $a);

	# close the column start
	$out .= "</div>";

	if ($echo) {
		echo $out;

		# notify custom code that column has ended
		# only valid if content is echoed out ($show=1)
		do_action('sph_AfterColumnEnd', $columnName, $a);
		do_action('sph_AfterColumnEnd_'.$columnName, $a);

		return;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_InsertBreak()
#	Defines a Break (CSS Clear)
#	Scope:	Forum
#	Version: 5.0
#		5.2 - Added Spacer argument for determining height of clear
# --------------------------------------------------------------------------------------

function sp_InsertBreak($args = '') {
	$defs = array('tagClass'  => '',
	              'tagId'     => '',
	              'direction' => 'both',
	              'spacer'    => '1px',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_InsertBreak_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	if (!empty($tagId)) $tagId = " id='".esc_attr($tagId)."'";
	if (!empty($tagClass)) {
		$tagClass = " class='".esc_attr($tagClass)."'";
	} else if (!empty($direction)) {
		$tagClass = " style='clear: $direction; height:$spacer;'";
	} else {
		$tagClass = '';
	}
	$echo = (int) $echo;

	# notify custom code before we insert the break
	do_action('sph_BeforeInsertBreak', $a);

	$out = '';
	if (!empty($tagClass) || !empty($tagId)) $out .= "<div$tagId$tagClass></div>";

	$out = apply_filters('sph_InsertBreak', $out, $a);

	if ($echo) {
		echo $out;

		# notify custom code that break has been inserted
		# only valid if content is echoed out ($show=1)
		do_action('sph_AfterInsertBreak', $a);

		return;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_InsertLineBreak()
#	Displays a Line Break (HTML 'br') - saves littering up a template with echo's
#	Scope:	Forum
#	Version: 5.2
#
# --------------------------------------------------------------------------------------

function sp_InsertLineBreak() {
	echo '<div class="spLineBreak"><br /></div>';
}


# --------------------------------------------------------------------------------------
#
#	sp_InsertRule()
#	Displays a Rule (HTML 'hr') - saves littering up a template with echo's
#	Scope:	Forum
#	Version: 5.2
#
# --------------------------------------------------------------------------------------

function sp_InsertRule() {
	echo '<hr />';
}

# --------------------------------------------------------------------------------------
#
#	sp_InsertNBSpace()
#	Defines a span filled with nin-breakng spaces to $spaces
#	Scope:	Forum
#	Version: 6.0
#
# --------------------------------------------------------------------------------------
function sp_InsertNBSpace($args = '') {
	$defs = array('tagClass'	=> 'spNBSpace',
	              'spaces'		=> 2,
	              'echo'		=> 1,
				 );
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_InsertNBSpace_args', $a);
	extract($a, EXTR_SKIP);

	$tagClass	=	esc_attr($tagClass);
	$spaces		=	(int) $spaces;
	$echo		=	(int) $echo;

	$out = "<span class=$tagClass>";
	for ($i=1; $i <> $spaces; $i++) {
		$out.= '&nbsp;';
	}
	$out.= '</span>';

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ------------------------------------------------------------------
# sp_open_grid()
# sp_close_grid()
# sp_open_grid_cell()
# sp_close_grid_cell()
#
# Version: 5.6
# Opens and closes popup grid cell
# ------------------------------------------------------------------

function sp_open_grid() {
	if (current_theme_supports('sp-theme-responsive')) {
		global $spCell;
		$spCell = 1;

		return '<div id="spGrid">';
	}

	return;
}

function sp_close_grid() {
	if (current_theme_supports('sp-theme-responsive')) {
		return '</div>';
	}

	return;
}

function sp_open_grid_cell() {
	if (current_theme_supports('sp-theme-responsive')) {
		$out = '';
		$out .= '<div class="spGridCell">';

		return $out;
	}

	return;
}

function sp_close_grid_cell() {
	if (current_theme_supports('sp-theme-responsive')) {
		global $spCell;

		$out = '</div>';
		if ($spCell == 3) $spCell = 0;
		$spCell++;

		return $out;
	}

	return;
}

# --------------------------------------------------------------------------------------
#
#	sp_MobileMenuStart()
#	Starts a Mobile Menu
#	Scope:	Forum
#	Version: 5.2
#	Change log:
#		Added 5.6:
#		tagClass, context, icon, iconClass
#
# --------------------------------------------------------------------------------------

function sp_MobileMenuStart($args = '', $header = '') {
	$defs = array('tagId'     => 'spMobileMenuId',
	              'tagClass'  => '',
	              'context'   => '',
	              'icon'      => 'sp_MobileMenu.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MobileMenu_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$context   = esc_attr($context);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$echo      = (int) $echo;

	# check for context. At this stage only 'postLoop' is active
	if ($context == 'postLoop') {
		$tagId = $tagId.SP()->forum->view->thisPost->post_id;
	}

	$out    = '';
	$source = '#'.$tagId;
	$out .= "<a class='$tagClass spMobileMenuOpen' title='".esc_attr($header)."' href='#' data-source='$source'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($header)) $out .= $header;
	$out .= "</a>";

	$out .= "<div id='$tagId' class='spAdminLinksPopup' style='display:none;'>";
	$out .= "<div class='spAdminLinksPopup'>";

	$out .= '<div class="spForumToolsHeader">';
	$out .= '<div class="spForumToolsHeaderTitle">'.$header.'</div>';
	$out .= '</div>';

	$out .= sp_open_grid();

	$out = apply_filters('sph_MobileMenuStart', $out, $a);

	if ($echo) {
		echo $out;

		return;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MobileMenuEnd()
#	Ends a Mobile Menu
#	Scope:	Forum
#	Version: 5.2
#
# --------------------------------------------------------------------------------------

function sp_MobileMenuEnd($args = '') {
	$defs = array('echo' => 1);
	$a    = wp_parse_args($args, $defs);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$echo = (int) $echo;

	$out = '';
	$out = apply_filters('sph_MobileMenuEnd_before', $out);

	$out .= sp_close_grid();
	$out .= '</div></div>';

	$out = apply_filters('sph_MobileMenuEnd_after', $out);

	if ($echo) {
		echo $out;

		return;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_OpenCloseControl()
#	Generic Display Open and Close function
#	Scope:	Anywhere
#	Version: 5.4.2
#
#	default values= 'open', 'closed'
#
#	Change Log:
#		5.6		Argument 'setCookie' added
#				Argument 'asLabel' added
#				Argument 'context' added
#				Argument 'labelClass' added
#
# --------------------------------------------------------------------------------------

function sp_OpenCloseControl($args = '', $toolTipOpen = '', $toolTipClose = '') {
	$defs = array('targetId'  => '',
	              'tagId'     => 'sp_OpenCloseControl',
	              'tagClass'  => 'spIcon',
	              'context'   => '',
	              'openIcon'  => 'sp_ControlOpen.png',
	              'closeIcon' => 'sp_ControlClose.png',
	              'default'   => 'open',
	              'setCookie' => 1,
	              'asLabel'   => 0,
	              'linkClass' => 'spButton',
	              'echo'      => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_OpenCloseControl_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$targetId     = esc_attr($targetId);
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$context      = esc_attr($context);
	$openIcon     = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($openIcon));
	$closeIcon    = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($closeIcon));
	$toolTipOpen  = esc_attr($toolTipOpen);
	$toolTipClose = esc_attr($toolTipClose);
	$default      = esc_attr($default);
	$setCookie    = (int) $setCookie;
	$asLabel      = (int) $asLabel;
	$linkClass    = esc_attr($linkClass);
	$echo         = (int) $echo;

	if (isset($_COOKIE[$targetId]) && $setCookie) $default = $_COOKIE[$targetId];
	$icon    = ($default == 'open') ? $closeIcon : $openIcon;
	$toolTip = ($default == 'open') ? $toolTipClose : $toolTipOpen;

	# check for context. At this stage only 'postLoop' is active
	if ($context == 'postLoop') {
		$targetId = $targetId.SP()->forum->view->thisPost->post_id;
		$tagId    = $tagId.SP()->forum->view->thisPost->post_id;
	}

	if ($default == 'closed') {
		echo '<style>#'.$targetId.' {display:none;}</style>';
	}
	$out = "<span id='$tagId' class='$linkClass spOpenClose' data-targetid='$targetId' data-tagid='$tagId' data-tagclass='$tagClass' data-openicon='$openIcon' data-closeicon='$closeIcon' data-tipopen='$toolTipOpen' data-tipclose='$toolTipClose' data-setcookie='$setCookie' data-label='$asLabel' data-linkclass='$linkClass'>";
	if ($asLabel) {
		$out .= $toolTip;
	} else {
		$out .= "<img class='$tagClass' title='$toolTip' src='$icon' alt='' />";
	}
	$out .= "</span>";

	$out = apply_filters('sph_OpenCloseControl', $out, $a);

	if ($echo) {
		echo $out;

		return;
	} else {
		return $out;
	}
}