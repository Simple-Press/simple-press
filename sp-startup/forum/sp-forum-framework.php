<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2017-04-15 09:51:19 -0500 (Sat, 15 Apr 2017) $
$Rev: 15346 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	FORUM PAGE
#	This file loads for forum pages only - Framework rendering functions
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_load_forum_scripts()
#
# Enqueue's necessary javascript and inline header script
# ------------------------------------------------------------------
function sp_load_forum_scripts() {
	global $spVars, $spThisUser, $spMobile, $spDevice, $spGlobals;

	# Older themes (Unified) define a constant SP_MOBILE_THEME now being deprecated.
	# if this constant exists (custom Unfied theme) swap for theme cap registration
	if (defined('SP_MOBILE_THEME') && SP_MOBILE_THEME) add_theme_support('sp-theme-responsive');

	# some definitions
	$footer = (sp_get_option('sfscriptfoot')) ? true : false;
	$tooltips = (defined('SP_TOOLTIPS') && SP_TOOLTIPS == false) ? 0 : 1;

	do_action('sph_scripts_start', $footer, $tooltips);

	# load up forum front end javascript
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'sp-forum-dev.js' : SFJSCRIPT.'sp-forum.js';
	sp_plugin_enqueue_script('spforum', $script, array('jquery', 'jquery-form'), false, $footer);

	# load up forum front end event handlers
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'sp-forum-events-dev.js' : SFJSCRIPT.'sp-forum-events.js';
	sp_plugin_enqueue_script('spforumevents', $script, array('jquery', 'spforum', 'spcommon'), false, $footer);

	# load up common javascript for front end
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFCJSCRIPT.'sp-common-dev.js' : SFCJSCRIPT.'sp-common.js';
	sp_plugin_enqueue_script('spcommon', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog', 'jquery-ui-autocomplete', 'jquery-effects-slide'), false, $footer);

	# load up forum vars to be localized for use in javascript
	$strings = array(
		'problem'		=> sp_text('Unable to save'),
		'noguestname'	=> sp_text('No guest username entered'),
		'noguestemail'	=> sp_text('No guest email Entered'),
		'notopictitle'	=> sp_text('No topic title entered'),
		'nomath'		=> sp_text('Spam math unanswered'),
		'nocontent'		=> sp_text('No post content entered'),
		'rejected'		=> sp_text('This post is rejected because it contains embedded formatting, probably pasted in form MS Word or other WYSIWYG editor'),
		'iframe'		=> sp_text('This post contains an iframe which are disallowed'),
		'savingpost'	=> sp_text('Saving post'),
		'nosearch'		=> sp_text('No search text entered'),
		'allwordmin'	=> sp_text('Minimum number of characters that can be used for a search word is'),
		'somewordmin'	=> sp_text('Not all words can be used for the search as minimum word length is'),
		'wait'			=> sp_text('Please wait'),
		'deletepost'	=> sp_text('Are you sure you want to delete this post?'),
		'deletetopic'	=> sp_text('Are you sure you want to delete this topic?'),
		'topicdeleted'	=> sp_text('Topic deleted'),
		'postdeleted'	=> sp_text('Post deleted'),
		'markread'		=> sp_text('All topics marked as read'),
		'markforumread' => sp_text('All topics in forum marked as read'),
		'pinpost'		=> sp_text('Post pin status toggled'),
		'pintopic'		=> sp_text('Topic pin status toggled'),
		'locktopic'		=> sp_text('Topic lock status toggled'),
		'lostentry'		=> sp_text('You have entered text into the post editor which may be lost')
	);
	$strings = apply_filters('sph_forum_vars', $strings);
	sp_plugin_localize_script('spforum', 'sp_forum_vars', $strings);

	# load the print this script for topics
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'print-this/printThis-dev.js' : SFJSCRIPT.'print-this/printThis.js';
	sp_plugin_enqueue_script('sfprintthis', $script, array('jquery'), false, $footer);

	# load up few other miscellaneous scripts
	if ($spDevice != 'desktop') {
		sp_plugin_enqueue_script('jquery-touch-punch', false, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse'), false, $footer);
	} else {
		if ($tooltips) sp_plugin_enqueue_script('jquery-ui-tooltip', false, array('jquery', 'jquery-ui-core', 'jquery-ui-widget'), false, $footer);
	}
	sp_plugin_enqueue_script('jquery.tools', SFJSCRIPT.'jquery-tools/jquery.tools.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget'), false, $footer);

	# tell plugins to enqueue their scripts
	do_action('sph_print_plugin_scripts', $footer);

	# either enqueue the combines js script cache (checks for udpates first) )or enqueue individual scripts
	$combine_js = sp_get_option('combinejs');
	if ($combine_js) { # use compressed scripts
		sp_combine_plugin_script_files();
	} else { # use individual scripts
		global $sp_plugin_scripts, $wp_scripts;
		if (!empty($sp_plugin_scripts)) {
			foreach ($sp_plugin_scripts->queue as $handle) {
				# enqueue with wp
				wp_enqueue_script($handle, $sp_plugin_scripts->registered[$handle]->src, $sp_plugin_scripts->registered[$handle]->deps, false, (!empty($sp_plugin_scripts->registered[$handle]->extra['group'])));

				# too late to localize scripts since already formatted - so just set the wp script data equal it our localized data
				$data = $sp_plugin_scripts->get_data($handle, 'data');
				$wp_scripts->registered[$handle]->extra['data'] = $data;
			}
		}
	}

	do_action('sph_scripts_end', $footer, $tooltips);
}

function sp_load_forum_footer_scripts() {
	global $spThisUser, $spMobile, $spDevice, $spVars, $spGlobals;

	$tooltips = (defined('SP_TOOLTIPS') && SP_TOOLTIPS == false) ? 0 : 1;

	do_action('sph_scripts_footer_start', $tooltips);

	# load up quicklinks dropdown js
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'msdropdown/msdropdown-dev.js' : SFJSCRIPT.'msdropdown/msdropdown.js';
	wp_enqueue_script('jquery.msdropdown', $script, array('jquery'), false, true);

	# load up forum front end javascript that must be in footer
	$footer_dep = array('jquery');
	if ($tooltips) $footer_dep = array_merge($footer_dep, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-tooltip'));
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'sp-forum-footer-dev.js' : SFJSCRIPT.'sp-forum-footer.js';
	wp_enqueue_script('spforumfooter', $script, $footer_dep, false, true);

	# load up some variables for javascript use
	$target = (isset($spVars['forumid'])) ? $spVars['forumid'] : 'global';
	$iframe = (sp_get_auth('can_use_iframes', $target, $spThisUser->ID)) ? 'no' : 'yes';

	$sfauto = sp_get_option('sfauto');
	$timer = ($sfauto['sfautotime'] * 1000);
	$arg = '';

	if ($sfauto['sfautoupdate'] && isset($spGlobals['autoupdate'])) {
		$autoup = $spGlobals['autoupdate'];
		foreach ($autoup as $up) {
			$thisUp = $up[0].','.SPAJAXURL.$up[1];
			if ($arg != '') $arg.= '%';
			$arg.= $thisUp;
		}
	}

	# set up tooltip location with default of below and to the right
	if (!defined('SP_TOOLTIP_MY')) define('SP_TOOLTIP_MY', 'left+20 top');
	if (!defined('SP_TOOLTIP_AT')) define('SP_TOOLTIP_AT', 'left bottom+10');

	# sp_platform_vars is not static so cannot be in combined js cache but can be localized
	$platform = array(
		'focus'				=> 'forum',
		'mobile'			=> $spMobile,
		'device'			=> $spDevice,
		'tooltips'			=> $tooltips,
		'mobiletheme'		=> (current_theme_supports('sp-theme-responsive')) ? 1 : 0,
		'headpadding'		=> ($spMobile) ? 0 : sp_get_option('spheaderspace'),
		'saveprocess'		=> 0,
		'checkiframe'		=> $iframe,
		'pageview'			=> $spVars['pageview'],
		'editor'			=> $spGlobals['editor'],
		'waitimage'			=> sp_paint_file_icon(SPFIMAGES, 'sp_Wait.png'),
		'successimage'		=> sp_paint_file_icon(SPFIMAGES, 'sp_Success.png'),
		'failimage'			=> sp_paint_file_icon(SPFIMAGES, 'sp_Failure.png'),
		'customizertest'	=> isset($_GET['sp-customizer-test']),
		'autoupdate'		=> $sfauto['sfautoupdate'],
		'autoupdatelist'	=> $arg,
		'autoupdatetime'	=> $timer,
		'tooltipmy'			=> SP_TOOLTIP_MY,
		'tooltipat'			=> SP_TOOLTIP_AT
	);
	$platform = apply_filters('sph_platform_vars', $platform);
	wp_localize_script('spforumfooter', 'sp_platform_vars', $platform);

	do_action('sph_scripts_footer_end', $tooltips);
}

function sp_load_plugin_styles($ajaxCall = false) {
	global $spCSSLoaded, $spDevice, $spPaths, $spGlobals;

	if (!sp_get_option('sfwpheadbypass') && $spCSSLoaded) return;

	$curTheme = $spGlobals['theme'];
	$curTheme = apply_filters('sph_theme', $curTheme);
	$vars = '';
	$overlay = '';

	# handle chiild themes
	$parentTheme = (!empty($curTheme['parent'])) ? sp_get_theme_data(SPTHEMEBASEDIR.$curTheme['parent'].'/spTheme.txt') : '';

	# handle color overlays
	if (current_theme_supports('sp-theme-child-overlays')) {
		# can use - so might be - a child theme overlay
		$overlay = (!empty($curTheme['color'])) ? $curTheme['color'] : '';
		if (!empty($parentTheme) && !empty($curTheme['color'])) {
			# must be a child with an overlay selected so...
			# is the overlay in the child theme or not?
			if (file_exists(SPTHEMEBASEDIR.$curTheme['theme'].'/styles/overlays/'.$curTheme['color'].'.php')) {
				$theme = $curTheme['theme'];
			} elseif(!empty($curTheme['color'])) {
				$theme = $curTheme['parent'];
			}
		} else {
			$theme = $curTheme['theme'];
		}
	} else {
		# no support for child overlays so do it the old eway
		$overlay = (!empty($curTheme['color'])) ? $curTheme['color'] : '';
		if (!empty($parentTheme) && !empty($curTheme['color'])) {
			$theme = $curTheme['parent'];
		} else {
			$theme = $curTheme['theme'];
		}
	}

	$vars = (!empty($overlay)) ? '?overlay='.esc_attr($overlay).'&theme='.esc_attr($theme) : '';

	if (isset($_GET['sp-customizer-test'])) {
		$vars = (!empty($vars)) ? "$vars&sp-customizer-test=".time() : "?sp-customizer-test=on".time();
	}

	# add device query var string
	$vars = (!empty($vars)) ? "$vars&device=$spDevice" : "?device=$spDevice";

	# set the images path array
	sp_set_image_array($curTheme);

	$spCSSLoaded = true;

	# if called from the Ajax init routine then leave now...
	if ($ajaxCall) return;

	# handle RTL
	if (is_rtl()) $vars = (!empty($vars)) ? "$vars&rtl=1" : '?rtl=1';

	$reset = (is_rtl()) ? 'reset.rtl.css' : 'reset,css';

	# handle multisite and site id
	$oldstore = (strpos(SPTHEMEBASEDIR, 'blogs.dir') !== false) ? '&oldstore=1' : '';
	$site = (is_multisite()) ? get_current_blog_id() : 0;
	$vars = (!empty($vars)) ? "$vars&site=$site$oldstore" : "?site=$site$oldstore";

	# enqueue the main theme css
	if (sp_is_plugin_active('user-selection/sp-user-selection-plugin.php')) {
		if (!empty($parentTheme)) {
			$cssTheme = (strpos($parentTheme['Stylesheet'], '.css')) ? true : false;
			if ($cssTheme && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/'.$reset)) {
				wp_enqueue_style('sp-parent-reset', SPTHEMEBASEURL.$curTheme['parent'].'/styles/'.$reset);
			}
			wp_enqueue_style('sp-parent', SPTHEMEBASEURL.$curTheme['parent'].'/styles/'.$parentTheme['Stylesheet'].$vars);
			if (is_rtl() && $cssTheme && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/rtl.css')) {
				wp_enqueue_style('sp-parent-rtl', SPTHEMEBASEURL.$curTheme['parent'].'/styles/rtl.css');
			}
		}
		$cssTheme = (strpos(SPTHEMECSS, '.css')) ? true : false;
		if ($cssTheme && file_exists(SPTHEMEDIR.$reset)) {
			wp_enqueue_style('sp-theme-reset', SPTHEMECSSEXTRA.$reset);
		}
		wp_enqueue_style('sp-theme', SPTHEMECSS.$vars);
		if (is_rtl() && $cssTheme && file_exists(SPTHEMEDIR.'rtl.css')) {
			wp_enqueue_style('sp-theme-rtl', SPTHEMECSSEXTRA.'rtl.css');
		}
	} else {
		if (!empty($parentTheme)) {
			$cssTheme = (strpos($parentTheme['Stylesheet'], '.css')) ? true : false;
			if ($cssTheme && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/'.$reset)) {
				sp_plugin_enqueue_style('sp-parent-reset', SPTHEMEBASEURL.$curTheme['parent'].'/styles/'.$reset);
			}
			sp_plugin_enqueue_style('sp-parent', SPTHEMEBASEURL.$curTheme['parent'].'/styles/'.$parentTheme['Stylesheet'].$vars);
			if (is_rtl() && $cssTheme && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/rtl.css')) {
				sp_plugin_enqueue_style('sp-parent-rtl', SPTHEMEBASEURL.$curTheme['parent'].'/styles/rtl.css');
			}
		}
		$cssTheme = (strpos(SPTHEMECSS, '.css')) ? true : false;
		if ($cssTheme && file_exists(SPTHEMEDIR.$reset)) {
			sp_plugin_enqueue_style('sp-theme-reset', SPTHEMECSSEXTRA.$reset);
		}
		sp_plugin_enqueue_style('sp-theme', SPTHEMECSS.$vars);
		if (is_rtl() && $cssTheme && file_exists(SPTHEMEDIR.'rtl.css')) {
			sp_plugin_enqueue_style('sp-theme-rtl', SPTHEMECSSEXTRA.'rtl.css');
		}
	}

	# concat (if needed) and enqueue the plugin css
	do_action('sph_print_plugin_styles');

	$combine_css = sp_get_option('combinecss');
	if ($combine_css) {
		sp_combine_plugin_css_files();
	} else {
		global $sp_plugin_styles;
		if (!empty($sp_plugin_styles)) {
			foreach ($sp_plugin_styles->queue as $handle) {
				wp_enqueue_style($handle, $sp_plugin_styles->registered[$handle]->src);
			}
		}
	}

	do_action('sph_styles_end');
}

# ------------------------------------------------------------------
# sp_forum_header()
#
# Constructs the header for the forum - Javascript and CSS
# ------------------------------------------------------------------
function sp_forum_header() {
	global $wp_query, $spGlobals, $spVars, $spStatus, $spMobile, $spDevice;

	do_action('sph_head_start');

	# check if upgrade needed
	if ($spStatus != 'ok') return;

	while ($x = has_filter('the_content', 'wpautop')) {
		remove_filter('the_content', 'wpautop', $x);
	}
	remove_filter('the_content', 'convert_smilies');

	# do meta stuff
	sp_setup_meta_tags();

	# any custom CSS to be in inserted?
	$theme = $spGlobals['theme']['theme'];
	# do we need the new post flag?
	$sfc = sp_get_option('sfcontrols');

	if (!empty($spGlobals['css'][$theme]) || $sfc['flagsuse']) {
		?>
		<style type="text/css">
		<?php
		if (!empty($spGlobals['css'][$theme])) echo($spGlobals['css'][$theme]);
		if ($sfc['flagsuse']) { ?>
			#spMainContainer a.spNewFlag,
			#spMainContainer .spNewFlag {
				font-size: 70% !important;
				color: #<?php echo($sfc['flagscolor']); ?> !important;
				background-color: #<?php echo($sfc['flagsbground']); ?>;
				margin: 0px 8px 0 0 !important;
				padding: 0px 2px 0px 2px;
				border-radius: 3px;
				display: inline;
				line-height: 1.4em;
			}
	<?php } ?>
		</style>
		<?php
	}

	do_action('sph_head_end');
}

# ------------------------------------------------------------------
# sp_forum_footer()
#
# Constructs the footer for the forum - Javascript
# ------------------------------------------------------------------

function sp_forum_footer() {
	global $spVars, $spGlobals, $spThisUser, $spMobile, $spDevice, $spStatus;

	if ($spStatus != 'ok') return;

	do_action('sph_footer_start');

	do_action('sph_footer_end');
}

# ------------------------------------------------------------------
# sp_render_forum()
#
# Central Control of forum rendering
# Called by the_content filter
#	$content:	The page content
# ------------------------------------------------------------------
function sp_render_forum($content) {
	global $spIsForum, $spContentLoaded, $spVars, $spGlobals, $spThisUser, $spStatus;

	# make sure we are at least in the html body before outputting any content
	if (!sp_get_option('sfwpheadbypass') && !did_action('wp_head')) return '';

	if ($spIsForum && !post_password_required(get_post(sp_get_option('sfpage')))) {
	   # Limit forum display to within the wp loop?
		if (sp_get_option('sfinloop') && !in_the_loop()) return $content;

		# Has forum content already been loaded and are we limiting?
		if (!sp_get_option('sfmultiplecontent') && $spContentLoaded) return $content;
		$spContentLoaded = true;

		sp_set_server_timezone();

		# offer a way for forum display to be short circuited but always show for admins unless an upgrade
		$message = sp_abort_display_forum();
		$content.= $message;
		if (!empty($message) && (!$spThisUser->admin || $spStatus != 'ok')) return $content;

		# process query arg actions
		# check for edit operation. Need tp check for '_x' in case using mobile as buttin is an image
		if (isset($_POST['editpost']) || isset($_POST['editpost_x'])) sp_save_edited_post();
		if (isset($_POST['edittopic'])) sp_save_edited_topic();
		if (isset($_POST['ordertopicpins'])) sp_promote_pinned_topic();
		if (isset($_POST['makepostreassign'])) sp_reassign_post();
		if (isset($_POST['approvepost'])) sp_approve_post(false, sp_esc_int($_POST['approvepost']), $spVars['topicid']);
		if (isset($_POST['unapprovepost'])) sp_unapprove_post(sp_esc_int($_POST['unapprovepost']));
		if (isset($_POST['doqueue'])) sp_remove_waiting_queue();
		if (isset($_POST['notifyuser'])) sp_post_notification(sp_esc_str($_POST['sp_notify_user']), sp_esc_str($_POST['message']), sp_esc_int($_POST['postid']));

		# move a topic and redirect to that topic
		if (isset($_POST['maketopicmove'])) {
			if (empty($_POST['forumid'])) {
				sp_notify(SPFAILURE, sp_text('Destination forum not selected'));
				return;
			}

			sp_move_topic();
			$forumslug = spdb_table(SFFORUMS, 'forum_id='.sp_esc_int(sp_esc_int($_POST['forumid'])), 'forum_slug');
			$topicslug = spdb_table(SFTOPICS, 'topic_id='.sp_esc_int(sp_esc_int($_POST['currenttopicid'])), 'topic_slug');
			$returnURL = sp_build_url($forumslug, $topicslug, 0);
			sp_redirect($returnURL);
		}

		# move a post and redirect to the post
		if (isset($_POST['makepostmove1']) || isset($_POST['makepostmove2']) || isset($_POST['makepostmove3'])) {
			sp_move_post();
			if (isset($_POST['makepostmove1'])) {
				$returnURL = sp_permalink_from_postid(sp_esc_int($_POST['postid']));
				sp_redirect($returnURL);
			}
		}

		# cancel a post move
		if (isset($_POST['cancelpostmove'])) {
			$meta = sp_get_sfmeta('post_move', 'post_move');
			if ($meta) {
				$id = $meta[0]['meta_id'];
				sp_delete_sfmeta($id);
				unset($spGlobals['post_move']);
			}
		}

		# rebuild the forum and post indexes
		if (isset($_POST['rebuildforum']) || isset($_POST['rebuildtopic'])) {
			sp_build_post_index(sp_esc_int($_POST['topicid']), true);
			sp_build_forum_index(sp_esc_int($_POST['forumid']), false);
		}

		# Set display mode if topic view (for editing posts)
		if ($spVars['pageview'] == 'topic' && isset($_POST['postedit'])) {
			$spVars['displaymode'] = 'edit';
			$spVars['postedit'] = $_POST['postedit'];
		} else {
			$spVars['displaymode'] = 'posts';
		}

		# clean cache of timed our records
		sp_clean_cache();

#--Scratch Pad Area---Please Leave Here---------

#--End Scratch Pad Area-------------------------

		# let other plugins check for posted actions
		do_action('sph_setup_forum');

		# do we use output buffering?
		$ob = sp_get_option('sfuseob');
		if ($ob) ob_start();

		# set up some stuff before wp page content
		$content.= sp_display_banner();
		$content = apply_filters('sph_before_wp_page_content', $content);

		# run any other wp filters on page content but exclude ours
		if (!$ob) {
			remove_filter('the_content', 'sp_render_forum', 1);
			$content = apply_filters('the_content', $content);
			$content = wpautop($content);
			add_filter('the_content', 'sp_render_forum', 1);
		}

		# set up some stuff after wp page content
		$content = apply_filters('sph_after_wp_page_content', $content);
		$content.= '<div id="dialogcontainer" style="display:none;"></div>';
		$content.= sp_js_check();

		# echo any wp page content
		echo $content;

		# now add our content
		do_action('sph_before_template_processing');
		sp_process_template();
		do_action('sph_after_template_processing');

		# Return if using output buffering
		if ($ob) {
			$forum = ob_get_contents();
			ob_end_clean();
			return $forum;
		}
	}

	# not returning any content since we output it already unless password needed
	if (post_password_required(get_post(sp_get_option('sfpage')))) return $content;
}

?>