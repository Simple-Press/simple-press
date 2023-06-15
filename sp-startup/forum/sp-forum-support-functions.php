<?php
/**
 * Forum Support Functions
 * This file loads at forum level - all forum pages on front end.
 *
 *  $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 *  $Rev: 15704 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function calculates the true canonical url for the forum page since all content
 * appears on a single wP page (single url).
 *
 * @since 6.0
 *
 * @return string    forum page url
 */
function sp_canonical_url() {
	if (SP()->rewrites->pageData['pageview'] == 'profileshow' || SP()->rewrites->pageData['pageview'] == 'profileedit') {
		$url = SP()->spPermalinks->get_url('profile');
	} else if (SP()->rewrites->pageData['pageview'] == 'list') {
		$page = '';
		if (SP()->rewrites->pageData['page'] > 0) $page = '/page-'.SP()->rewrites->pageData['page'];
		$url = SP()->spPermalinks->get_url('members'.$page);
	} else {
		if (!empty(SP()->rewrites->pageData['topicslug'])) {
			$url = SP()->spPermalinks->build_url(SP()->rewrites->pageData['forumslug'], SP()->rewrites->pageData['topicslug'], SP()->rewrites->pageData['page'], 0);
		} else if (!empty(SP()->rewrites->pageData['forumslug'])) {
			$url = SP()->spPermalinks->build_url(SP()->rewrites->pageData['forumslug'], '', SP()->rewrites->pageData['page'], 0);
		} else {
			$url = SP()->spPermalinks->get_url();
		}
	}

	return apply_filters('sph_canonical_url', $url);
}

#
# get cononical url
# since 5.7
# ------------------------------------------------------------------

/**
 * This function returns the forum page url.  It is hooked into the WP function for getting canonical url.
 *
 * @since 6.0
 *
 * @param string $url current url
 *                    $param array        $post    WP current page array
 *
 * @return string    forum canonical url
 */
function sp_get_canonical_url($url, $post) {
	if (SP()->isForum) $url = sp_canonical_url();

	return $url;
}

/**
 * This function hooks into AIOSEO to make sure it outputs proper canonical url for the forum page.
 *
 * @since 6.0
 *
 * @param string $url current front page redirect url
 *
 * @return string    forum canonical url
 */
function sp_aioseo_canonical_url($url) {
	global $wp_query;

	if (SP()->isForum) {
		$url = sp_canonical_url();
	} else {
		# Do we need to change this from an SP perspective
		$wpPost = $wp_query->get_queried_object();
		$url    = apply_filters('sph_aioseo_canonical_url', $url, $wpPost);
	}
	SP()->core->forumData['canonicalurl'] = true;

	return $url;
}

/**
 * This function hooks in AIOSEO to ensure the meta description tags output match the
 * forum SEO options for description.
 *
 * @since 6.0
 *
 * @param string $aioseo_descr current description
 *
 * @return string    Updated meta description with forum description
 */
function sp_aioseo_description($aioseo_descr) {
	if (SP()->isForum) {
		SP()->core->forumData['metadescription'] = true;

		$description = sp_get_metadescription();
		if ($description != '') $aioseo_descr = $description;
	}

	return $aioseo_descr;
}

/**
 * This function hooks into AIOSEO to make sure the meta keywords output match the forum
 * SEO options for meta keywords.
 *
 * @since 6.0
 *
 * @param string $aioseo_keywords current meta keywords
 *
 * @return string    Updated meta keywords for forum
 */
function sp_aioseo_keywords($aioseo_keywords) {
	if (SP()->isForum) {
		SP()->core->forumData['metakeywords'] = true;
		$keywords                               = sp_get_metakeywords();
		if ($keywords != '') $aioseo_keywords = $keywords;
	}

	return $aioseo_keywords;
}

/**
 * This function hooks into AIOSEO to ensure the page title outpout matches the forum page title
 * set up in the SEO options.
 *
 * @since 6.0
 *
 * @param string $title current page title
 *
 * @return string    Updated page title for the forum
 */
function sp_aioseo_homepage($title) {
	if (SP()->isForum) {
		$sfseo = SP()->options->get('sfseo');
		$title = sp_setup_title($title, ' '.$sfseo['sfseo_sep'].' ');
	}

	return $title;
}

/**
 * This function hooks into Yoast WP SEO and removes some of its hooks that incorrectly
 * overwrite the forum meta tags since it assumes a single fixed WP page.
 *
 * @since 6.0
 *
 * @param string $url ???? (not used)
 *
 * @return void
 */
function sp_wp_seo_hooks($url) {
	if (!defined('SP_USE_WPSEO_HEAD') || 'SP_USE_WPSEO_HEAD' != true) {
		if (defined('WPSEO_VERSION')) {
			if ( ! is_callable( ['WPSEO_Frontend', 'get_instance']) ) {
				return;
			}
			$instance = WPSEO_Frontend::get_instance();
			remove_action('wpseo_head', [
                $instance,
                'canonical'
            ], 20);
			remove_action('wpseo_head', [
                $instance,
                'metadesc'
            ], 6);
			remove_action('wpseo_head', [
                $instance,
                'metakeywords'
            ], 11);
			remove_action('wpseo_head', [
                $instance,
                'publisher'
            ], 22);
			remove_action('wpseo_head', [
                (array_key_exists('wpseo_og', $GLOBALS) && $GLOBALS['wpseo_og'] !== '' ? $GLOBALS['wpseo_og'] : ''),
                'opengraph'
            ], 30);
			remove_action('wpseo_head', [
                'WPSEO_Twitter',
                'get_instance'
            ], 40);
		}
	}
}

/**
 * This function returns the header meta description based on forum SEO settings.
 *
 * @since 6.0
 *
 * @return string    meta description for forum pages
 */
function sp_get_metadescription() {
	$description = '';

	# do we need a meta description
	$sfmetatags = SP()->options->get('sfmetatags');
	switch ($sfmetatags['sfdescruse']) {
		case 1:  # no meta description
			break;

		case 2:  # use custom meta description on all forum pages
			$description = SP()->displayFilters->title($sfmetatags['sfdescr']);
			break;

		case 3:  # use custom meta description on group view and forum description on forum/topic pages
			if ((SP()->rewrites->pageData['pageview'] == 'forum' || SP()->rewrites->pageData['pageview'] == 'topic') && !isset($_GET['search'])) {
				$forum       = SP()->DB->table(SPFORUMS, "forum_slug='".SP()->rewrites->pageData['forumslug']."'", 'row');
				$description = SP()->displayFilters->title($forum->forum_desc);
			} else {
				$description = SP()->displayFilters->title($sfmetatags['sfdescr']);
			}
			break;

		case 4:  # use custom meta description on group view, forum description on forum pages and topic title on topic pages
		case 5:  # use custom meta description on group view, forum description on forum pages and first post excerpt on topic pages
			if (SP()->rewrites->pageData['pageview'] == 'forum' && !isset($_GET['search'])) {
				$forum = SP()->DB->table(SPFORUMS, "forum_slug='".SP()->rewrites->pageData['forumslug']."'", 'row');
				if ($forum) $description = SP()->displayFilters->title($forum->forum_desc);
			} else if (SP()->rewrites->pageData['pageview'] == 'topic' && !isset($_GET['search'])) {
				if ($sfmetatags['sfdescruse'] == 4) {
					$topic = SP()->DB->table(SPTOPICS, "topic_slug='".SP()->rewrites->pageData['topicslug']."'", 'row');
					if ($topic) $description = SP()->displayFilters->title($topic->topic_name);
				} else {
					$content     = SP()->DB->table(SPPOSTS, 'topic_id='.SP()->rewrites->pageData['topicid'], 'post_content', 'post_id ASC', 1);
					$description = wp_html_excerpt($content, 120);
				}
			} else {  # must be group or other
				$description = SP()->displayFilters->title($sfmetatags['sfdescr']);
			}
			break;
	}

	return apply_filters('sph_meta_description', $description);
}

/**
 * This function returns the header meta keywords based on the forum SEO options.
 *
 * @since 6.0
 *
 * @return string    meta keywords for forum pages
 */
function sp_get_metakeywords() {
	$keywords                 = '';
	$sfmetatags               = SP()->options->get('sfmetatags');
	$sfmetatags['sfkeywords'] = (isset($sfmetatags['sfkeywords'])) ? $sfmetatags['sfkeywords'] : '';

	if (!empty($sfmetatags['sfusekeywords'])) {
		if ($sfmetatags['sfusekeywords'] == 3) {
			if ((SP()->rewrites->pageData['pageview'] == 'forum' || SP()->rewrites->pageData['pageview'] == 'topic')) {
				$keywords = SP()->displayFilters->title(SP()->DB->table(SPFORUMS, 'forum_id='.SP()->rewrites->pageData['forumid'], 'keywords'));
			} else {
				$keywords = stripslashes($sfmetatags['sfkeywords']);
			}
		} else if ($sfmetatags['sfusekeywords'] == 2) {
			$keywords = stripslashes($sfmetatags['sfkeywords']);
		}
	}

	return apply_filters('sph_meta_keywords', $keywords);
}

/**
 * This function supports old style (pre WP 4.4) themes page title generation.
 *
 * @since 6.0
 *
 * @param string $title       current page title
 * @param string $sep         separator character between page elements
 * @param string $seplocation location of sep character (right or left)
 *
 * @return string    updated page title for forum pages
 */
function sp_setup_browser_title($title, $sep = '|', $seplocation = '') {
	global $wp_query;

	$post = $wp_query->get_queried_object();
	if (isset($post->ID) && $post->ID == SP()->options->get('sfpage')) {
		$sp_sep = ' '.$sep.' ';
		$title  = preg_replace(($seplocation == 'right') ? '/'.preg_quote($sp_sep).'$/' : '/^'.preg_quote($sp_sep).'/', '', $title);
		$sfseo  = SP()->options->get('sfseo');
		$title  = sp_setup_title($title, ' '.$sfseo['sfseo_sep'].' ');
	}
	$title = apply_filters('sph_browser_title', $title);

	return $title;
}

/**
 * This function supports modern style (WP 4.4+) themes page title generation.
 *
 * @since 6.0
 *
 * @param string $title current page title
 *
 * @return string    updated page title for forum pages
 */
function sp_browser_title($title) {
	global $wp_query;

	$post = $wp_query->get_queried_object();
	if (isset($post->ID) && $post->ID == SP()->options->get('sfpage')) {
		$sfseo = SP()->options->get('sfseo');
		$title = sp_setup_title($title, ' '.$sfseo['sfseo_sep'].' ');
	}

	$title = apply_filters('sph_browser_title', $title);

	return $title;
}

/**
 * This function is called by start of the wp loop action to output hook data before the page title.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_title_hook() {
	$out = apply_filters('sph_before_page_title', '');
	echo $out;
}

/**
 * This function is called update page title based on forum options.
 *
 * @since 6.0
 *
 * @param string $title current page title
 * @param int    $id    current WP page ID
 *
 * @return string    udpated page title for forum pages
 */
function sp_setup_page_title($title, $id) {
	if (trim($title) == trim(SPPAGETITLE)) {
		# dont modify nav menus
		if ($id != SP()->options->get('sfpage')) return $title;

		# if a 'get_the_title' call then change if required
		if (!empty(SP()->core->forumData['display']['pagetitle']['banner']) || (SP()->core->forumData['display']['pagetitle']['notitle'])) return '';

		$seo   = SP()->options->get('sfseo');
		if(!empty($seo)){
			$title = sp_setup_title($title, ' '.isset($seo['sfseo_sep']) ? $seo['sfseo_sep']:''.' ');
		}
	}

	return $title;
}

/**
 * This function removes our title filter while WP is processing nav menus.
 * NOTE this filter must match the_title filter usage in sp-load-class-spcforumloader.php
 *
 * @since 6.0
 *
 * @return void
 */
function sp_title_hook_remove() {
	remove_filter('the_title', 'sp_setup_page_title', 9999);

	return null;
}

/**
 * This function adds our title filter back in when WP is done processing nav menus.
 * NOTE this filter must match the_title filter usage in sp-load-class-spcforumloader.php
 *
 * @since 6.0
 *
 * @return array
 */
function sp_title_hook_add($nav_menu) {
	add_filter('the_title', 'sp_setup_page_title', 9999, 2);

	return $nav_menu;
}

/**
 * This function generates the forum page title.
 *
 * @since 6.0
 *
 * @param string $title current page title
 * @param string $sep   separation character
 *
 * @return string    updated page title for forum pages
 */
function sp_setup_title($title, $sep) {
	$pTitle = $title;
	$sfseo  = SP()->options->get('sfseo');
	if(!empty($sfseo)){
		if (isset($sfseo['sfseo_overwrite'])) $title = '';

		if (isset($sfseo['sfseo_blogname'])) $title = get_bloginfo('name').$sep.$title;

		if (isset($sfseo['sfseo_pagename'])) $title = single_post_title('', false).$sep.$title;
	}
	$page      = (!empty(SP()->rewrites->pageData['page']) && SP()->rewrites->pageData['page'] > 1) ? $sep.SP()->primitives->front_text('Page').' '.SP()->rewrites->pageData['page'] : '';
	$forumslug = (!empty(SP()->rewrites->pageData['forumslug'])) ? SP()->rewrites->pageData['forumslug'] : '';
	$topicslug = (!empty(SP()->rewrites->pageData['topicslug'])) ? SP()->rewrites->pageData['topicslug'] : '';

	if (!empty($forumslug) && !empty(SP()->rewrites->pageData['forumname']) && $forumslug != 'all' && $sfseo['sfseo_forum'] && (!$sfseo['sfseo_noforum'] || SP()->rewrites->pageData['pageview'] != 'topic')) {
		if (!empty($topicslug) && $sfseo['sfseo_topic']) {
			$title = SP()->rewrites->pageData['forumname'].$sep.$title;
		} else {
			$title = SP()->rewrites->pageData['forumname'].$page.$sep.$title;
		}
	}

	if (!empty($topicslug) && !empty(SP()->rewrites->pageData['topicname']) && $sfseo['sfseo_topic']) $title = SP()->displayFilters->title(SP()->rewrites->pageData['topicname']).$page.$sep.$title;

	if (isset($sfseo['sfseo_page'])) {
		$profile = (!empty(SP()->rewrites->pageData['profile'])) ? SP()->rewrites->pageData['profile'] : '';
		if (!empty($profile) && $profile == 'edit') $title = SP()->primitives->front_text('Edit Member Profile').$sep.$title;
		if (!empty($profile) && $profile == 'show') $title = SP()->primitives->front_text('Member Profile').$sep.$title;

		$list = (!empty(SP()->rewrites->pageData['members'])) ? $list = urlencode(SP()->rewrites->pageData['members']) : '';
		if (!empty($list) && $list == 'list') $title = SP()->primitives->front_text('Member List').$sep.$title;
	}

	if (!empty(SP()->rewrites->pageData['searchpage']) && SP()->rewrites->pageData['searchpage'] > 0) $title = SP()->primitives->front_text('Search').$sep.$title;

	# no separators at end
	$title = trim($title, $sep);

	if (empty($title) && SP()->rewrites->pageData['pageview'] == 'group' && $sfseo['sfseo_homepage']) $title = $pTitle;

	$title = apply_filters('sph_page_title', $title, $sep);

	SP()->rewrites->pageData['seotitle'] = $title;

	return $title;
}

/**
 * This function outputs the html display code for the forum meta tags in use in the page header.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_setup_meta_tags() {
	SP()->rewrites->pageData['seodescription'] = '';
	SP()->rewrites->pageData['seokeywords']    = '';
	SP()->rewrites->pageData['seourl']         = '';

	echo "\n\n";

	if (empty(SP()->core->forumData['metadescription'])) {
		$description = sp_get_metadescription();
		if ($description != '') {
			$description = str_replace('"', '', $description);
			echo "\t".'<meta name="description" content="'.$description.'" />'."\n";
		}
		# add to SEO data
		SP()->rewrites->pageData['seodescription'] = $description;
	}

	if (empty(SP()->core->forumData['metakeywords'])) {
		$keywords = sp_get_metakeywords();
		if ($keywords != '') {
			$keywords = str_replace('"', '', $keywords);
			echo "\t".'<meta name="keywords" content="'.$keywords.'" />'."\n";
		}
		SP()->rewrites->pageData['seokeywords'] = $keywords;
	}

	if (empty(SP()->core->forumData['canonicalurl'])) {
		# output the canonical url
		$url = sp_canonical_url();
		echo "\t".'<link rel="canonical" href="'.$url.'" />'."\n";
		SP()->rewrites->pageData['seourl'] = $url;
	}
}

/**
 * This function outputs the open graph namespace display html in the page header.
 *
 * @since 6.0
 *
 * @param string $out current display code
 *
 * @return string    updated display code to incldue og namespace
 */
function sp_og_namespace($out) {
	$sfseo = SP()->options->get('sfseo');
	if (!isset($sfseo)) return $out;
	if (isset($sfseo['sfseo_og']) && $sfseo['sfseo_og'] && strpos($out, 'prefix=') === false) {
		$out .= ' prefix="og: http://ogp.me/ns#"';
	}

	return $out;
}

/**
 * This function outputs the open graph meta tags display html in the page header.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_og_meta() {
	global $post;

	$sfseo = SP()->options->get('sfseo');
	if (!isset($sfseo)) return;

	if (isset($sfseo['sfseo_og']) && $sfseo['sfseo_og']) {
		$mp  = "\t<meta property=";
		$out = "\n";
		$out .= $mp.'"og:url" content="'.SP()->rewrites->pageData['seourl'].'"/>'."\n";
		$out .= $mp.'"og:title" content="'.SP()->rewrites->pageData['seotitle'].'"/>'."\n";
		$out .= $mp.'"og:site_name" content="'.get_option('blogname').'"/>'."\n";
		$out .= $mp.'"og:description" content="'.SP()->rewrites->pageData['seodescription'].'"/>'."\n";
		$out .= $mp.'"og:type" content="'.$sfseo['seo_og_type'].'"/>'."\n";
		$out .= $mp.'"og:locale" content="'.get_locale().'"/>'."\n";

		# image processing
		$link = '';
		if (SP()->rewrites->pageData['topicid'] && $sfseo['seo_og_attachment']) {
			# Topic View
			$link = apply_filters('sph_find_attachment', '');
		}
		# if no attachment then move on...
		if (empty($link)) {
			if (!empty(SP()->rewrites->pageData['featureimage'])) {
				$file = SP()->filters->str(SP()->rewrites->pageData['featureimage']);
				$link = SP_STORE_URL.'/'.SP()->plugin->storage['forum-images'].'/'.$file;
			} elseif (has_post_thumbnail($post->ID)) {
				$thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
				$link          = $thumbnail_src[0];
				if (!preg_match('/^https?:\/\//', $link)) {
					$link = site_url('/').ltrim($link, '/');
				}
			}
		}
		if ($link) $out .= $mp.'"og:image" content="'.$link.'"/>'."\n";

		echo $out."\n";
	}
}

/**
 * This function fires the hook for any editors to load up.
 *
 * @since 6.0
 *
 * @param bool $override    allows for specific editor to be forced to load
 * @param bool $supportOnly load editor support stuff only - not any editor scripts
 *
 * @return void
 */
function sp_load_editor($override = 0, $supportOnly = 0) {
	# load editor if required

	if ($override != 0 && $override != '') SP()->core->forumData['editor'] = $override;

	# allow plugins to control editor choice
	SP()->core->forumData['editor'] = apply_filters('sph_this_editor', SP()->core->forumData['editor'], $override, $supportOnly);

	# load editor support
	do_action('sph_load_editor_support', SP()->core->forumData['editor']);

	# only load editor itself on required pages and if not a support only call
	$editorPage = apply_filters('sph_editor_check', 'forum topic profileedit');
	if (!empty(SP()->rewrites->pageData['pageview']) && strpos($editorPage, SP()->rewrites->pageData['pageview']) !== false && !$supportOnly) {
		do_action('sph_load_editor', SP()->core->forumData['editor']);
	}
}

/**
 * This function will load an alternative mobile phone page template.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_load_mobile_template() {
	if (isset(SP()->core->forumData['mobile-display'])) SP()->core->forumData['display']['pagetitle']['notitle'] = SP()->core->forumData['mobile-display']['notitle'];
	if (isset(SP()->core->forumData['mobile-display']['usetemplate']) && SP()->core->forumData['mobile-display']['usetemplate']) {
		require_once get_template_directory().'/'.SP()->core->forumData['mobile-display']['pagetemplate'];
		exit();
	}
}

/**
 * This function tries to make sure the forum page title shown by wp_list_pages is correct.
 *
 * @since 6.0
 *
 * @param string $ptext current page title
 *
 * @return string    updated forum page title
 */
function sp_wp_list_pages($ptext) {
	if (!empty(SP()->rewrites->pageData['seotitle'])) {
		$seotitle = SP()->rewrites->pageData['seotitle'];
		$ptext    = str_replace($seotitle, SPPAGETITLE, $ptext);
		$seotitle = html_entity_decode($seotitle, ENT_QUOTES);
		$seotitle = htmlspecialchars($seotitle, ENT_QUOTES, SPCHARSET);
		$ptext    = str_replace($seotitle, SPPAGETITLE, $ptext);
		$seotitle = SP()->saveFilters->title($seotitle);
		$ptext    = str_replace($seotitle, SPPAGETITLE, $ptext);
		$ptext    = str_replace(strtoupper($seotitle), SPPAGETITLE, $ptext);
	} else {
		if (SP()->core->forumData['display']['pagetitle']['banner'] || SP()->core->forumData['display']['pagetitle']['notitle']) {
			$ptext = str_replace(SP()->spPermalinks->get_url().'"></a>', SP()->spPermalinks->get_url().'">'.SPPAGETITLE.'</a>', $ptext);
		}
	}

	return $ptext;
}

/**
 * This function will enqueue the Masonry javascript library if viewing a forum profile page.
 *
 * @since 6.0
 *
 * @param bool $footer   flag indicating to load javascript in footer
 * @param bool $tooltips are tooltips being displayed
 *
 * @return void
 */
function sp_profile_masonry($footer, $tooltips) {
	if (SP()->rewrites->pageData['pageview'] == 'profileshow') {
		wp_enqueue_script('jquery-masonry');
	}
}

/**
 * This function allows display inspector to show on profile popups
 *
 * @since 6.0
 *
 * @param string $out current display text
 *
 * @return string        update display text to include display inspector
 */
function sp_display_inspector_profile_popup($out, $user, $a) {
	sp_display_inspector('pro_profileUser', $user);

	return $out;
}

/**
 * This function allows display inspector to show on profile edit pages
 *
 * @since 6.0
 *
 * @param string $out current display text
 *
 * @return void
 */
function sp_display_inspector_profile_edit() {
	sp_display_inspector('pro_profileUser', SP()->user->profileUser);
}

# = JAVASCRIPT CHECK ==========================

/**
 * This function outputs and warning message if browser javascript is disabled.
 * The forum display requires javascript to be enabled.
 *
 * @since 6.0
 *
 * @return string
 */
function sp_js_check() {
	return '<noscript><div><pre><code>'.SP()->primitives->front_text('This forum requires Javascript to be enabled for posting content').'</code></pre></div></noscript>'."\n";
}
