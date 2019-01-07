<?php
/**
 * Site Support Functions
 * This file loads at site level - all page loads for front end
 *
 *  $LastChangedDate: 2018-08-18 04:27:34 -0500 (Sat, 18 Aug 2018) $
 *  $Rev: 15708 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function redirects Forum RSS Feed requests
 *
 * @since 6.0
 *
 * @return void
 */
function sp_feed() {
	global $wp_query;

	if (is_page() && $wp_query->post->ID == SP()->options->get('sfpage') && !empty(SP()->rewrites->pageData['feed'])) {
		# Fire up forum loader
		(new spcForumLoader)->load();

		#check for old style feed urls - load query args into pageData for new style
		if (isset($_GET['xfeed'])) {
			SP()->rewrites->pageData['feed']      = SP()->filters->str($_GET['xfeed']);
			SP()->rewrites->pageData['feedkey']   = SP()->filters->str($_GET['feedkey']);
			SP()->rewrites->pageData['forumslug'] = SP()->filters->str($_GET['forum']);
			SP()->rewrites->pageData['topicslug'] = SP()->filters->str($_GET['topic']);
		}

		# do we have the clunky group rss feed?
		if (isset($_GET['group'])) SP()->rewrites->pageData['feed'] = 'group';

		# new style rss feed urls
		if (!empty(SP()->rewrites->pageData['feed'])) {
			require_once SP_PLUGIN_DIR.'/forum/feeds/sp-feeds.php';
			exit;
		}
	}
}

/**
 * This function makes WP think are feeds are valid WP feeds so they are left alone.
 *
 * @since 6.0
 *
 * @param array $query current WP query
 *
 * @return array    Updated WP query to handle our forum RSS feeds
 */
function sp_is_feed_check($query) {
	if (!is_admin() && $query->is_main_query() && !empty($query->query_vars['sf_feed'])) {
		$query->set('feed', 'forum');
		$query->is_feed = true;
	}

	return $query;
}

/**
 * This function returns the current forum page canonical permalink.
 *
 * @since 6.0
 *
 * @param string $link   current page permalink
 * @param int    $id     current page ID
 * @param string $sample ?????
 *
 * @return string    updated permalink for forum pages
 */
function sp_get_permalink($link, $id, $sample) {
	if (SP()->isForum) {
		if ($id == SP()->options->get('sfpage') && (SP()->options->get('sfinloop') && in_the_loop())) $link = sp_canonical_url();
	}

	return $link;
}

/**
 * This function loads any forum scripts needed on front end for non-forum pages.
 * Core does not load any, but this allows plugins to do add some.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_load_blog_script() {
	do_action('sph_blog_scripts_start');
	do_action('sph_blog_scripts_end');
}

/**
 * This function loads any required forum support functions on front end for non-forum pages.
 * Core does not load any, but this allows plugins to do add some.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_load_blog_support() {
	global $wp_query;

	# Grab WP post object for use in action
	$wpPost = $wp_query->get_queried_object();
	do_action('sph_blog_support_start', $wpPost);

	do_action('sph_blog_support_end', $wpPost);
}

/**
 * This function displays the spam math form elements on the WP registration form if enabled.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_register_math() {
	$sflogin = SP()->options->get('sflogin');
	if ($sflogin['sfregmath']) {
		$spammath = sp_math_spam_build();
		$uKey     = SP()->options->get('spukey');
		$uKey1    = $uKey.'1';
		$uKey2    = $uKey.'2';

		$out = '<input type="hidden" size="30" name="url" value="" /></p>'."\n";
		$out .= '<label>'.SP()->primitives->front_text('Math Required!').'<br />'."\n";
		$out .= sprintf(SP()->primitives->front_text('What is the sum of: %s %s + %s %s'), '<strong>', $spammath[0], $spammath[1], '</strong>').'</label>'."\n";
		$out .= '<input class="input" type="text" id="'.$uKey1.'" name="'.$uKey1.'" value="" />'."\n";
		$out .= '<input type="hidden" name="'.$uKey2.'" value="'.$spammath[2].'" />'."\n";
		$out .= '<br />';
		echo $out;
	}
}

/**
 * This function checks the WP registration form for any spam math errors if enabled when submitted.
 *
 * @since 6.0
 *
 * @param array $errors current errors list on submitted WP registration form
 *
 * @return array    updated errors list for submitted WP registration form based on spam math form elements
 */
function spa_register_error($errors) {
	$sflogin = SP()->options->get('sflogin');
	if ($sflogin['sfregmath']) {
		$spamtest = sp_spamcheck();
		if ($spamtest[0] == true) {
			$errormsg = '<b>'.SP()->primitives->front_text('ERROR').'</b>: '.$spamtest[1];

			if (SP()->isForum == false) {
				$errors->add('Bad Math', $errormsg);
			} else {
				$errors['math_check'] = $errormsg;
			}
		}
	}

	return $errors;
}

/**
 * This function displays a forum unavailable message if the forum status is not "OK".
 *
 * @since 6.0
 *
 * @return string    unavailable message is status not ok, otherwise empty string
 */
function sp_abort_display_forum() {
	# are we awaiting an upgrade - outptut message and bail
	if (SP()->core->status != 'ok') {
		$message = sp_forum_unavailable();
		return $message;
	}

	# let plugins hook in
	$message = apply_filters('sph_alternate_forum_content', '');

	return $message;
}

/**
 * This function builds the forum unavailable message.
 *
 * @since 6.0
 *
 * @return string    unavailable message
 */
function sp_forum_unavailable() {
	global $current_user;

	$out = '';
	$out .= '<div id="spMainContainer">';
	$out .= '<div class="spMessage">';
	$out .= '<p>'.SP()->theme->paint_icon('', SPTHEMEICONSURL, 'sp_Information.png').'</p>';
	$out .= '<p>'.SP()->primitives->front_text('The forum is temporarily unavailable while being upgraded to a new version').'</p>';
	if (SP()->auths->forum_admin($current_user->ID)) $out .= '<a href="'.SPADMINUPGRADE.'">'.SP()->primitives->front_text('Click here to perform the upgrade').'</a>';
	$out .= '</div>';
	$out .= '</div>';

	$out = apply_filters('sph_forum_unavailable', $out);

	return $out;
}

function sp_math_spam_build() {
	$spammath[0] = rand(1, 12);
	$spammath[1] = rand(1, 12);

	# Calculate result
	$result = $spammath[0] + $spammath[1];

	# Add name of the weblog:
	$result .= get_bloginfo('name');
	# Add date:
	$result .= date('j').date('ny');
	# Get MD5 and reverse it
	$enc = strrev(md5($result));
	# Get only a few chars out of the string
	$enc = substr($enc, 26, 1).substr($enc, 10, 1).substr($enc, 23, 1).substr($enc, 3, 1).substr($enc, 19, 1);

	$spammath[2] = $enc;

	return $spammath;
}

function sp_spamcheck() {
	$spamcheck    = array();
	$spamcheck[0] = false;

	# Check dummy input field
	if (array_key_exists('url', $_POST)) {
		if (!empty($_POST['url'])) {
			$spamcheck[0] = true;
			$spamcheck[1] = SP()->primitives->front_text('Form not filled by human hands!');

			return $spamcheck;
		}
	}

	# Check math question
	$uKey    = SP()->options->get('spukey');
	$correct = SP()->filters->str($_POST[$uKey.'2']);
	$test    = SP()->filters->str($_POST[$uKey.'1']);
	$test    = preg_replace('/[^0-9]/', '', $test);

	if ($test == '') {
		$spamcheck[0] = true;
		$spamcheck[1] = SP()->primitives->front_text('No answer was given to the math question');

		return $spamcheck;
	}

	# Add name of the weblog:
	$test .= get_bloginfo('name');
	# Add date:
	$test .= date('j').date('ny');
	# Get MD5 and reverse it
	$enc = strrev(md5($test));
	# Get only a few chars out of the string
	$enc = substr($enc, 26, 1).substr($enc, 10, 1).substr($enc, 23, 1).substr($enc, 3, 1).substr($enc, 19, 1);

	if ($enc != $correct) {
		$spamcheck[0] = true;
		$spamcheck[1] = SP()->primitives->front_text('The answer to the math question was incorrect');

		return $spamcheck;
	}

	return $spamcheck;
}
