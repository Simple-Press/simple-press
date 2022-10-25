<?php
/**
 * Forum framework funtcions
 * This file loads at forum level - all Simple Press page loads on front end
 * These function form the framework of the page loads setting up WP header and footer actions
 * and the main rendering of the forum via the WP the_content hook.
 *
 *  $LastChangedDate: 2018-12-20 17:32:38 -0600 (Thu, 20 Dec 2018) $
 *  $Rev: 15863 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function enqueues and loads forum required javascript.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_load_forum_scripts() {
	# Older themes (Unified) define a constant SP_MOBILE_THEME now being deprecated.
	# if this constant exists (custom Unified theme) swap for theme cap registration
	if (defined('SP_MOBILE_THEME') && SP_MOBILE_THEME) add_theme_support('sp-theme-responsive');

	# some definitions
	$footer = (SP()->options->get('sfscriptfoot')) ? true : false;
	$tooltips = (defined('SP_TOOLTIPS') && SP_TOOLTIPS == false) ? 0 : 1;

	do_action('sph_scripts_start', $footer, $tooltips);

	# load up forum front end javascript
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPJSCRIPT.'sp-forum.js' : SPJSCRIPT.'sp-forum.min.js';
	SP()->plugin->enqueue_script('spforum', $script, array(
		'jquery',
		'jquery-form'), SP_SCRIPTS_VERSION, $footer);

	# load up forum front end event handlers
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPJSCRIPT.'sp-forum-events.js' : SPJSCRIPT.'sp-forum-events.min.js';
	SP()->plugin->enqueue_script('spforumevents', $script, array(
		'jquery',
		'spforum',
		'spcommon'), SP_SCRIPTS_VERSION, $footer);

	# load up common javascript for front end
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPCJSCRIPT.'sp-common.js' : SPCJSCRIPT.'sp-common.min.js';
	SP()->plugin->enqueue_script('spcommon', $script, array(
		'jquery',
		'jquery-ui-core',
		'jquery-ui-widget',
		'jquery-ui-dialog',
		'jquery-ui-autocomplete',
		'jquery-effects-slide'), SP_SCRIPTS_VERSION, $footer);

	# load up forum vars to be localized for use in javascript
	$strings = array(
		'problem'		 => SP()->primitives->front_text('Unable to save'),
		'noguestname'	 => SP()->primitives->front_text('No guest username entered'),
		'noguestemail'	 => SP()->primitives->front_text('No guest email Entered'),
		'notopictitle'	 => SP()->primitives->front_text('No topic title entered'),
		'nomath'		 => SP()->primitives->front_text('Spam math unanswered'),
		'nocontent'		 => SP()->primitives->front_text('No post content entered'),
		'rejected'		 => SP()->primitives->front_text('This post is rejected because it contains embedded formatting, probably pasted in form MS Word or other WYSIWYG editor'),
		'iframe'		 => SP()->primitives->front_text('This post contains an iframe which are disallowed'),
		'object_tag'	 => SP()->primitives->front_text('This post contains an OBJECT tag which are disallowed'),
		'embed_tag'		 => SP()->primitives->front_text('This post contains an EMBED tag which are disallowed'),
		'savingpost'	 => SP()->primitives->front_text('Saving post'),
		'nosearch'		 => SP()->primitives->front_text('No search text entered'),
		'allwordmin'	 => SP()->primitives->front_text('Minimum number of characters that can be used for a search word is'),
		'somewordmin'	 => SP()->primitives->front_text('Not all words can be used for the search as minimum word length is'),
		'wait'			 => SP()->primitives->front_text('Please wait'),
		'deletepost'	 => SP()->primitives->front_text('Are you sure you want to delete this post?'),
		'deletetopic'	 => SP()->primitives->front_text('Are you sure you want to delete this topic?'),
		'topicdeleted'	 => SP()->primitives->front_text('Topic deleted'),
		'postdeleted'	 => SP()->primitives->front_text('Post deleted'),
		'markread'		 => SP()->primitives->front_text('All topics marked as read'),
		'markforumread'	 => SP()->primitives->front_text('All topics in forum marked as read'),
		'pinpost'		 => SP()->primitives->front_text('Post pin status toggled'),
		'pintopic'		 => SP()->primitives->front_text('Topic pin status toggled'),
		'locktopic'		 => SP()->primitives->front_text('Topic lock status toggled'),
		'lostentry'		 => SP()->primitives->front_text('You have entered text into the post editor which may be lost')
	);
	$strings = apply_filters('sph_forum_vars', $strings);
	SP()->plugin->localize_script('spforum', 'sp_forum_vars', $strings);

	# load the print this script for topics
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPJSCRIPT.'print-this/printThis.js' : SPJSCRIPT.'print-this/printThis.min.js';
	SP()->plugin->enqueue_script('sfprintthis', $script, array(
		'jquery'), SP_SCRIPTS_VERSION, $footer);

	# load up few other miscellaneous scripts
	if (SP()->core->device != 'desktop') {
		SP()->plugin->enqueue_script('jquery-touch-punch', false, array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-mouse'), false, $footer);
	} else {
		if ($tooltips) SP()->plugin->enqueue_script('jquery-ui-tooltip', false, array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-widget'), false, $footer);
	}
	SP()->plugin->enqueue_script('jquery.tools', SPJSCRIPT.'jquery-tools/jquery.tools.min.js', array(
		'jquery',
		'jquery-ui-core',
		'jquery-ui-widget'), false, $footer);

	# tell plugins to enqueue their scripts
	do_action('sph_print_plugin_scripts', $footer);

	# either enqueue the combines js script cache (checks for updates first) )or enqueue individual scripts
	$combine_js = SP()->options->get('combinejs');
	if ($combine_js) { # use compressed scripts
		SP()->plugin->combine_scripts();
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

/**
 * This function enqueues forum required javascript that must be loaded in the footer.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_load_forum_footer_scripts() {
	$tooltips = (defined('SP_TOOLTIPS') && SP_TOOLTIPS == false) ? 0 : 1;

	do_action('sph_scripts_footer_start', $tooltips);

	# load up quicklinks dropdown js
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPJSCRIPT.'msdropdown/msdropdown.js' : SPJSCRIPT.'msdropdown/msdropdown.min.js';
	wp_enqueue_script('jquery.msdropdown', $script, array(
		'jquery'), SP_SCRIPTS_VERSION, true);

	# load up forum front end javascript that must be in footer
	$footer_dep = array(
		'jquery');
	if ($tooltips) $footer_dep = array_merge($footer_dep, array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-tooltip'));
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPJSCRIPT.'sp-forum-footer.js' : SPJSCRIPT.'sp-forum-footer.min.js';
	wp_enqueue_script('spforumfooter', $script, $footer_dep, SP_SCRIPTS_VERSION, true);

	# load up some variables for javascript use
	$target = (isset(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : 'global';
	$iframe = (SP()->auths->get('can_use_iframes', $target, SP()->user->thisUser->ID)) ? 'no' : 'yes';

	$sfauto = SP()->options->get('sfauto');
	$timer = ($sfauto['sfautotime'] * 1000);
	$arg = '';

	$auto_data = SP()->meta->get_values('autoupdate');
	if ($sfauto['sfautoupdate'] && !empty($auto_data)) {
		foreach ($auto_data as $up) {
			$thisUp = $up[0].','.SPAJAXURL.$up[1];
			if ($arg != '') $arg .= '%';
			$arg .= $thisUp;
		}
	}

	# set up tooltip location with default of below and to the right
	if (!defined('SP_TOOLTIP_MY')) define('SP_TOOLTIP_MY', 'left+20 top');
	if (!defined('SP_TOOLTIP_AT')) define('SP_TOOLTIP_AT', 'left bottom+10');

	# sp_platform_vars is not static so cannot be in combined js cache but can be localized
	$platform = array(
		'focus'			 => 'forum',
		'mobile'		 => SP()->core->mobile,
		'device'		 => SP()->core->device,
		'tooltips'		 => $tooltips,
		'mobiletheme'	 => (current_theme_supports('sp-theme-responsive')) ? 1 : 0,
		'headpadding'	 => (SP()->core->mobile) ? 0 : SP()->options->get('spheaderspace'),
		'saveprocess'	 => 0,
		'checkiframe'	 => $iframe,
		'pageview'		 => SP()->rewrites->pageData['pageview'],
		'editor'		 => SP()->core->forumData['editor'],
		'waitimage'		 => SP()->theme->paint_file_icon(SPFIMAGES, 'sp_Wait.png'),
		'successimage'	 => SP()->theme->paint_file_icon(SPFIMAGES, 'sp_Success.png'),
		'failimage'		 => SP()->theme->paint_file_icon(SPFIMAGES, 'sp_Failure.png'),
		'customizertest' => isset($_GET['sp-customizer-test']),
		'autoupdate'	 => $sfauto['sfautoupdate'],
		'autoupdatelist' => $arg,
		'autoupdatetime' => $timer,
		'tooltipmy'		 => SP_TOOLTIP_MY,
		'tooltipat'		 => SP_TOOLTIP_AT
	);
	$platform = apply_filters('sph_platform_vars', $platform);
	wp_localize_script('spforumfooter', 'sp_platform_vars', $platform);

	do_action('sph_scripts_footer_end', $tooltips);
}

/**
 * This function enqueues and loads forum required CSS styles in the head tags.
 *
 * @since 6.0
 *
 * @param bool	$ajaxCall	for ajax loads, no actual styles need to be loaded, so we bail early
 * @return void
 */
function sp_load_plugin_styles($ajaxCall = false) {
	if (SP()->core->status != 'ok') return;

	if (!SP()->options->get('sfwpheadbypass') && (isset(SP()->core->forumData['cssloaded']) && SP()->core->forumData['cssloaded'])) return;

	$curTheme = SP()->core->forumData['theme'];
	$curTheme = apply_filters('sph_theme', $curTheme);

	# handle chiild themes
	$parentTheme = (!empty($curTheme['parent'])) ? SP()->theme->get_data(SPTHEMEBASEDIR.$curTheme['parent'].'/spTheme.txt') : '';

	# handle color overlays
	if (current_theme_supports('sp-theme-child-overlays')) {
		# can use - so might be - a child theme overlay
		$overlay = (!empty($curTheme['color'])) ? $curTheme['color'] : '';
		if (!empty($parentTheme) && !empty($curTheme['color'])) {
			# must be a child with an overlay selected so...
			# is the overlay in the child theme or not?
			if (file_exists(SPTHEMEBASEDIR.$curTheme['theme'].'/styles/overlays/'.$curTheme['color'].'.php')) {
				$theme = $curTheme['theme'];
			} elseif (!empty($curTheme['color'])) {
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
	$vars = (!empty($vars)) ? "$vars&device=".SP()->core->device : "?device=".SP()->core->device;

	# set the images path array
	SP()->theme->set_image_array($curTheme);

	SP()->core->forumData['cssloaded'] = true;

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
	if (SP()->plugin->is_active('user-selection/sp-user-selection-plugin.php')) {
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
				SP()->plugin->enqueue_style('sp-parent-reset', SPTHEMEBASEURL.$curTheme['parent'].'/styles/'.$reset);
			}
			SP()->plugin->enqueue_style('sp-parent', SPTHEMEBASEURL.$curTheme['parent'].'/styles/'.$parentTheme['Stylesheet'].$vars);
			if (is_rtl() && $cssTheme && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/rtl.css')) {
				SP()->plugin->enqueue_style('sp-parent-rtl', SPTHEMEBASEURL.$curTheme['parent'].'/styles/rtl.css');
			}
		}
		$cssTheme = (strpos(SPTHEMECSS, '.css')) ? true : false;
		if ($cssTheme && file_exists(SPTHEMEDIR.$reset)) {
			SP()->plugin->enqueue_style('sp-theme-reset', SPTHEMECSSEXTRA.$reset);
		}
		SP()->plugin->enqueue_style('sp-theme', SPTHEMECSS.$vars);
		if (is_rtl() && $cssTheme && file_exists(SPTHEMEDIR.'rtl.css')) {
			SP()->plugin->enqueue_style('sp-theme-rtl', SPTHEMECSSEXTRA.'rtl.css');
		}
	}

	# concat (if needed) and enqueue the plugin css
	do_action('sph_print_plugin_styles');

	$combine_css = SP()->options->get('combinecss');
	if ($combine_css) {
		SP()->plugin->combine_css();
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

/**
 * This function adds any forum specific tags (besides main javascript and styles) to the page head tags.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_forum_header() {
	do_action('sph_head_start');

	# check if upgrade needed
	if (SP()->core->status != 'ok') return;

	while ($x = has_filter('the_content', 'wpautop')) {
		remove_filter('the_content', 'wpautop', $x);
	}
	remove_filter('the_content', 'convert_smilies');

	# do meta stuff
	sp_setup_meta_tags();

	# any custom CSS to be in inserted?
	$theme = SP()->core->forumData['theme']['theme'];
	# do we need the new post flag?
	$sfc = SP()->options->get('sfcontrols');

	$inlineCSS = SP()->meta->get_value('css', $theme);
	if (!empty($inlineCSS) || $sfc['flagsuse']) {
		?>
		<style>
		<?php
		if (!empty($inlineCSS)) echo $inlineCSS;
		if ($sfc['flagsuse']) {
			?>
				#spMainContainer a.spNewFlag,
				#spMainContainer .spNewFlag {
					color: #<?php echo($sfc['flagscolor']); ?> !important;
					background-color: #<?php echo($sfc['flagsbground']); ?>;
				}
		<?php } ?>
		</style>
		<?php
	}

	do_action('sph_head_end');
}

/**
 * This function adds any forum specific code (besides main javascript) to the page footer section.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_forum_footer() {
	if (SP()->core->status != 'ok') return;

	do_action('sph_footer_start');

	do_action('sph_footer_end');
}

/**
 * This function drives the main rendering engine for Simple Press.
 * It is hooked into the WP the_content filter.
 *
 * @since 6.0
 *
 * @param string	$content	curernt WP page content
 *
 * @return string	new WP page content including forum display html
 */
function sp_render_forum($content) {
	# make sure we are at least in the html body before outputting any content
	if (!SP()->options->get('sfwpheadbypass') && !did_action('wp_head')) return '';

	if (SP()->isForum && !post_password_required(get_post(SP()->options->get('sfpage')))) {
		# Limit forum display to within the wp loop?
		if (SP()->options->get('sfinloop') && !in_the_loop()) return $content;

		# Has forum content already been loaded and are we limiting?
		if (!SP()->options->get('sfmultiplecontent') && SP()->forum->contentLoaded) return $content;
		SP()->forum->contentLoaded = true;

		SP()->dateTime->set_timezone();

		# offer a way for forum display to be short circuited but always show for admins unless an upgrade
		$message = sp_abort_display_forum();
		$content .= $message;
		if (!empty($message) && (!SP()->user->thisUser->admin || SP()->core->status != 'ok')) return $content;

		# process query arg actions
		# check for edit operation. Need tp check for '_x' in case using mobile as buttin is an image
		if (isset($_POST['editpost']) || isset($_POST['editpost_x'])) sp_save_edited_post();
		if (isset($_POST['edittopic'])) sp_save_edited_topic();
		if (isset($_POST['ordertopicpins'])) sp_promote_pinned_topic();
		if (isset($_POST['makepostreassign'])) sp_reassign_post();
		if (isset($_POST['approvepost'])) sp_approve_post(false, SP()->filters->integer($_POST['approvepost']), SP()->rewrites->pageData['topicid']);
		if (isset($_POST['unapprovepost'])) sp_unapprove_post(SP()->filters->integer($_POST['unapprovepost']));
		if (isset($_POST['doqueue'])) sp_remove_waiting_queue();
		if (isset($_POST['notifyuser'])) sp_post_notification(SP()->filters->str($_POST['sp_notify_user']), SP()->filters->str($_POST['message']), SP()->filters->integer($_POST['postid']));

		# move a topic and redirect to that topic
		if (isset($_POST['maketopicmove'])) {
			if (empty($_POST['forumid'])) {
				SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Destination forum not selected'));
				return '';
			}

			sp_move_topic();
			$forumslug = SP()->DB->table(SPFORUMS, 'forum_id='.SP()->filters->integer(SP()->filters->integer($_POST['forumid'])), 'forum_slug');
			$topicslug = SP()->DB->table(SPTOPICS, 'topic_id='.SP()->filters->integer(SP()->filters->integer($_POST['currenttopicid'])), 'topic_slug');
			$returnURL = SP()->spPermalinks->build_url($forumslug, $topicslug, 0);
			SP()->primitives->redirect($returnURL);
		}

		# move a post and redirect to the post
		if (isset($_POST['makepostmove1']) || isset($_POST['makepostmove2']) || isset($_POST['makepostmove3'])) {
			sp_move_post();
			if (isset($_POST['makepostmove1'])) {
				$returnURL = SP()->spPermalinks->permalink_from_postid(SP()->filters->integer($_POST['postid']));
				SP()->primitives->redirect($returnURL);
			}
		}

		# cancel a post move
		if (isset($_POST['cancelpostmove'])) {
			$meta = SP()->meta->delete(0, 'post_move');
		}

		# rebuild the forum and post indexes
		if (isset($_POST['rebuildforum']) || isset($_POST['rebuildtopic'])) {
			sp_build_post_index(SP()->filters->integer($_POST['topicid']), true);
			sp_build_forum_index(SP()->filters->integer($_POST['forumid']), false);
		}

		# Set display mode if topic view (for editing posts)
		if (SP()->rewrites->pageData['pageview'] == 'topic' && isset($_POST['postedit'])) {
			SP()->rewrites->pageData['displaymode'] = 'edit';
			SP()->rewrites->pageData['postedit'] = SP()->filters->integer($_POST['postedit']);
		} else {
			SP()->rewrites->pageData['displaymode'] = 'posts';
		}

		# clean cache of timed our records
		SP()->cache->clean();

		#--Scratch Pad Area---Please Leave Here---------

		#--End Scratch Pad Area-------------------------

		# let other plugins check for posted actions
		do_action('sph_setup_forum');

		# do we use output buffering?
		$ob = SP()->options->get('sfuseob');
		if ($ob) ob_start();

		# set up some stuff before wp page content
		$content .= sp_display_banner();
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
		$content .= '<div id="dialogcontainer" style="display:none;"></div>';
		$content .= sp_js_check();

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
	if (post_password_required(get_post(SP()->options->get('sfpage')))) return $content;

    return '';
}
