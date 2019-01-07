<?php
/*
Simple:Press
Template handler
$LastChangedDate: 2017-08-21 04:25:50 -0500 (Mon, 21 Aug 2017) $
$Rev: 15517 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
# sp_process_template()
#
# The main control center for the loading up of the required templates.
# Uses the pageData 'pageview' to determine which template to load.
# Templates are always surrounded by the spMainContainer div
#
# --------------------------------------------------------------------------------------
function sp_process_template() {
	# grab the pageview, checking to see if its a search page
	$pageview = SP()->rewrites->pageData['pageview'];

	# determine page template to load
	switch ($pageview) {
		case 'group':
			$tempName = sp_process_group_view();
			break;

		case 'forum':
			$tempName = sp_process_forum_view();
			break;

		case 'topic':
			$tempName = sp_process_topic_view();
			break;

		case 'search':
			$tempName = sp_process_search_view();
			break;

		case 'members':
			$tempName = sp_process_members_view();
			break;

		case 'profileedit':
			$tempName = sp_process_profileedit_view();
			break;

		case 'profileshow':
			$tempName = sp_process_profileshow_view();
			break;

		case 'newposts':
			$tempName = sp_process_newposts_view();
			break;

		default:
			$tempName = sp_process_default_view($pageview);
			break;
	}

	# allow plugins/themes access to the template name
	$tempName = apply_filters('sph_TemplateName', $tempName, $pageview);

	# allow output prior to SP display
	do_action('sph_BeforeDisplayStart', $pageview, $tempName);

	# SP display starts here

	# Any control data item inspection needed
	if (SP()->auths->current_user_can('SPF Manage Toolbox') && !empty(SP()->user->thisUser->inspect)) sp_display_inspector('control', '');

	# forum top anchor
	echo '<a id="spForumTop"></a>';

	# Define the main forum container
	echo "\n\n<!-- Simple:Press display start -->\n\n";
	echo '<div id="spMainContainer">';

	# Create the sliding panel div needed for mobile display
	echo "<div id='spMobilePanel'></div>";

	# allow output before the SP display
	do_action('sph_AfterDisplayStart', $pageview, $tempName);

	# load the pageview template if valid
	sp_load_template($tempName);

	# allow output after the SP display
	do_action('sph_BeforeDisplayEnd', $pageview, $tempName);

	# Display any queued messages
	SP()->notifications->render_queued();

	echo '</div>';
	echo "\n\n<!-- Simple:Press display end -->\n\n";

	# forum bottom anchor
	echo '<a id="spForumBottom"></a>';

	# SP display ends here

	# allow output after the SP display
	do_action('sph_AfterDisplayEnd', $pageview, $tempName);

	# Post display processing
	sp_post_display_processing($pageview);
}

# --------------------------------------------------------------------------------------
#
# sp_process_group_view()
#
# Performs group view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_group_view() {
	return 'spGroupView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_forum_view()
#
# Performs forum view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_forum_view() {
	# Store the topic page so that we can get back to it later (breadcrumb usage)
	sp_push_topic_page(SP()->rewrites->pageData['forumid'], SP()->rewrites->pageData['page']);

	return 'spForumView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_topic_view()
#
# Performs topic view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_topic_view() {
	return 'spTopicView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_search_view()
#
# Performs search processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_search_view() {
	return 'spSearchView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_members_view()
#
# Performs members list view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_members_view() {
	return 'spMembersView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_profileedit_view()
#
# Performs profile edit view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_profileedit_view() {
	return 'spProfileEdit.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_profileshow_view()
#
# Performs profile show view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_profileshow_view() {
	if (!empty(SP()->rewrites->pageData['member'])) {
		$userid = (int ) SP()->rewrites->pageData['member'];
		$userid = SP()->DB->table(SPMEMBERS, "user_id=$userid", 'user_id');
	} else {
		$userid = SP()->user->thisUser->ID;
	}

	if (!SP()->auths->get('view_profiles') || empty($userid) || $userid < 0) {
		SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Invalid profile request'));

		return 'spDefault.php';
	} else {
		sp_SetupUserProfileData();

		return 'spProfileShow.php';
	}
}

# --------------------------------------------------------------------------------------
#
# sp_process_permissions_view()
#
# Performs pemissions view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_permissions_view() {
	return 'spPermissions.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_newposts_view()
#
# Performs new posts view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_newposts_view() {
	return 'spNewPostsView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_default_view()
#
# $pageview:	The current page view (likely plugin defined)
#
# Performs default and user defined view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_default_view($pageview) {
	# try building standard template name based on unknown pageview type
	$template = 'sp'.ucfirst($pageview).'.php';

	# let plugins change the template name
	$template = apply_filters('sph_DefaultViewTemplate', $template, $pageview);

	# if template doesnt exist, revert to default template
	if (!file_exists($template)) $template = 'spDefault.php';

	return $template;
}

# --------------------------------------------------------------------------------------
#
# sp_load_template()
#
# $tempName:	The template name.
#
# Opens and Includes the required template. Returns textual errors if the
# file is not found
#
# --------------------------------------------------------------------------------------
function sp_load_template($tempName) {

	# check if legacy level/version 1 theme support is needed
	if (!current_theme_supports('level-2-theme')) {
		include_once SP_PLUGIN_DIR.'/forum/content/legacy/sp-legacy-theme-support.php';
		include SP_PLUGIN_DIR.'/forum/content/legacy/sp-legacy-theme-globals.php';
	}
	
	# some beginning hooks
	$tempName = apply_filters('sph_template_load_name', $tempName);
	$thisTemplate = $tempName;
	do_action('sph_template_load_begin', $thisTemplate);
	do_action('sph_template_load_begin_'.$thisTemplate);

	# find the template
	$curTheme = SP()->core->forumData['theme'];
	if (!empty($tempName) && file_exists($tempName)) {
		require_once $tempName;
	} else if (!empty($tempName) && file_exists(SPTEMPLATES.$tempName)) {
		require_once SPTEMPLATES.$tempName;
	} else if (!empty($tempName) && !empty($curTheme['parent']) && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/templates/'.$tempName)) {
		require_once SPTHEMEBASEDIR.$curTheme['parent'].'/templates/'.$tempName;
	} else {
		$tempName = explode('/', $tempName);
		echo '<p class="spCenter spHeaderName">['.$tempName[count($tempName) - 1].'] - '.SP()->primitives->front_text('Template File Not Found').'</p>';
		echo '<div class="spHeaderMessage">';
		echo '<p>'.SP()->primitives->admin_text('Sorry, but the required template file could not be found or could not be opened.').'</p>';
		echo '<br/><p>';
		SP()->primitives->admin_etext('This can be caused by a missing/corrupt theme or theme file. Please check the Simple:Press Theme List admin panel and make sure a valid theme is selected. Or please check the location of the selected theme on your server and make sure the theme and the required template file exist.');
		echo '</p>';
		echo '</div>';
		$thisTemplate = $tempName[count($tempName) - 1];
	}
	# some ending hooks
	do_action('sph_template_load_end', $thisTemplate);
	do_action('sph_template_load_end_'.$thisTemplate);
}

# --------------------------------------------------------------------------------------
#
# sp_post_display_processing()
# Any tasks that ma be needed after the display os all rendered
#
# --------------------------------------------------------------------------------------
function sp_post_display_processing($pageview) {
	if ($pageview == 'topic' && !empty(SP()->forum->view->thisTopic)) {
		$tid = SP()->cache->get('topic');
		if (empty($tid) || $tid != SP()->forum->view->thisTopic->topic_id) {
			sp_update_opened(SP()->forum->view->thisTopic->topic_id);
			SP()->cache->add('topic', SP()->forum->view->thisTopic->topic_id);
		}
	}
}

# --------------------------------------------------------------------------------------
#
# sp_HeaderBegin()
# Fires a wp action to indicate SP header start for plugins
#
# --------------------------------------------------------------------------------------
function sp_HeaderBegin() {
	do_action('sph_HeaderBegin');
}

# --------------------------------------------------------------------------------------
#
# sp_HeaderEnd()
# Fires a wp action to indicate SP header end for plugins
#
# --------------------------------------------------------------------------------------
function sp_HeaderEnd() {
	do_action('sph_HeaderEnd');
}

# --------------------------------------------------------------------------------------
#
# sp_FooterBegin()
# Fires a wp action to indicate SP footer start for plugins
#
# --------------------------------------------------------------------------------------
function sp_FooterBegin() {
	do_action('sph_FooterBegin');
}

# --------------------------------------------------------------------------------------
#
# sp_FooterEnd()
# Fires a wp action to indicate SP footer end for plugins
#
# --------------------------------------------------------------------------------------
function sp_FooterEnd() {
	do_action('sph_FooterEnd');
}

# --------------------------------------------------------------------------------------
#
# __sp()
#
# $text		text string to be translated
# $domain	unique domain name of theme
#
# NOTES TO FOLLOW WHEN FUNCTION IS WRITTEN
#
# --------------------------------------------------------------------------------------

function __sp($text) {
	$domain = (isset(SP()->core->forumData['themedomain'])) ? SP()->core->forumData['themedomain'] : '';

	return __($text, $domain);
}
