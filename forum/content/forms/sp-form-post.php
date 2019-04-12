<?php
/*
Simple:Press
Post Form Rendering
$LastChangedDate: 2017-04-10 11:07:19 -0500 (Mon, 10 Apr 2017) $
$Rev: 15324 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_add_post_form($args) {
	global $tab;

	require_once SP_PLUGIN_DIR.'/forum/content/forms/sp-form-components.php';

	$tab = 1;

	$toolbar = SP()->core->forumData['display']['editor']['toolbar'];

	$defs = array('containerClass'			=> 'spForm',
				  'formClass'				=> 'spForm',
				  'hide'					=> 1,
				  'controlFieldset'			=> 'spEditorFieldset',
				  'legendClass'				=> '',
				  'editorSection'			=> 'spEditorSection',
				  'editorMessage'			=> 'spEditorMessage',
				  'controlInput'			=> 'spControl',
				  'controlSubmit'			=> 'spSubmit',
				  'controlSubmitMobile'		=> 'spicon',
				  'controlOrder'			=> 'cancel|save',
				  'maxTitleLength'			=> 200,
				  'guestUserSection'		=> 'spUserDetails',
				  'topicTitleSection'		=> 'spEditorTitle',
				  'noToolbar'				=> '',
				  'halfLeft'				=> 'spEditorSectionLeft',
				  'halfRight'				=> 'spEditorSectionRight',
				  'sectionHeading'			=> 'spEditorHeading',
				  'optionLabel'				=> 'spLabel',
				  'timeStamp'				=> 'spControl',
				  'failureMessage'			=> '',
				  'toolbarClass'			=> 'spEditorToolbar',
				  'toolbarSubmitClassRight'	=> 'spRight',
				  'mathSection'				=> '',
				  'mathPadding'				=> 'spEditorTitle',
				  'mathLabels'				=> 'spEditorSpam',
				  'labelHeading'			=> SP()->primitives->front_text('Add Reply'),
				  'labelGuestName'			=> SP()->primitives->front_text('Guest name (required)'),
				  'labelGuestEmail'			=> SP()->primitives->front_text('Guest email (required)'),
				  'labelModerateAll'		=> SP()->primitives->front_text('NOTE: new posts are subject to administrator approval before being displayed'),
				  'labelModerateOnce'		=> SP()->primitives->front_text('NOTE: first posts are subject to administrator approval before being displayed'),
				  'labelSmileys'			=> SP()->primitives->front_text('Smileys'),
				  'labelOptions'			=> SP()->primitives->front_text('Options'),
				  'labelOptionLock'			=> SP()->primitives->front_text('Lock this topic'),
				  'labelOptionPin'			=> SP()->primitives->front_text('Pin this post'),
				  'labelOptionTime'			=> SP()->primitives->front_text('Edit post timestamp'),
				  'labelMath'				=> SP()->primitives->front_text('Math Required'),
				  'labelMathSum'			=> SP()->primitives->front_text('What is the sum of'),
				  'labelPostButtonReady'	=> SP()->primitives->front_text('Submit Reply'),
				  'labelPostButtonMath'		=> SP()->primitives->front_text('Do Math To Save'),
				  'labelPostCancel'			=> SP()->primitives->front_text('Cancel'),
				  'tipSmileysButton'		=> SP()->primitives->front_text('Open/Close to Add a Smiley'),
				  'tipOptionsButton'		=> SP()->primitives->front_text('Open/Close to select Posting Options'),
				  'tipSubmitButton'			=> SP()->primitives->front_text('Save the New Post'),
				  'tipCancelButton'			=> SP()->primitives->front_text('Cancel the New Post')
				  );
	$a = wp_parse_args($args, $defs);
	extract($a, EXTR_SKIP);

	# sanitize
	$containerClass			= esc_attr($containerClass);
	$formClass				= esc_attr($formClass);
	$hide					= (int) $hide;
	$controlFieldset		= esc_attr($controlFieldset);
	$legendClass			= esc_attr($legendClass);
	$editorSection			= esc_attr($editorSection);
	$editorMessage			= esc_attr($editorMessage);
	$controlInput			= esc_attr($controlInput);
	$maxTitleLength			= (int) $maxTitleLength;
	$halfLeft				= esc_attr($halfLeft);
	$halfRight				= esc_attr($halfRight);
	$guestUserSection		= esc_attr($guestUserSection);
	$topicTitleSection		= esc_attr($topicTitleSection);
	$failureMessage			= esc_attr($failureMessage);
	$toolbarClass			= esc_attr($toolbarClass);
	$labelHeading			= SP()->displayFilters->title($labelHeading);
	$labelGuestName			= SP()->displayFilters->title($labelGuestName);
	$labelGuestEmail		= SP()->displayFilters->title($labelGuestEmail);
	$labelModerateAll		= SP()->displayFilters->title($labelModerateAll);
	$labelModerateOnce		= SP()->displayFilters->title($labelModerateOnce);

	# Check for a failure package in case this is a redirect
	$f = SP()->cache->get('post');
	if (isset($f['guestname']) ? $guestnameval = esc_attr(stripslashes($f['guestname'])) : $guestnameval = SP()->user->guest_cookie->name);
	if (isset($f['guestemail']) ? $guestemailval = esc_attr(stripslashes($f['guestemail'])) : $guestemailval = SP()->user->guest_cookie->email);
	if (isset($f['postitem']) ? $postitemval = stripslashes($f['postitem']) : $postitemval = '');
	if (isset($f['message']) ? $failmessage = stripslashes($f['message']) : $failmessage = '');
	$captchaValue = SP()->options->get('captcha-value');

	$out = '';

	# Grab above editor message if there is one
	$postmsg = SP()->options->get('sfpostmsg');

	if ($hide ? $hide=' style="display:none;"' : $hide = '');
	$out.= "<div id='spPostForm' class='$containerClass'$hide>\n";

	$out.= "<form class='$formClass' action='".SPAJAXURL."new-post' method='post' id='addpost' name='addpost' data-guest='".SP()->user->thisUser->guest."' data-img='".SP()->theme->paint_file_icon(SPTHEMEICONSURL, 'sp_Success.png')."'>\n";

	$out.= sp_create_nonce('forum-userform_addpost');

	$out.= '<div class="spEditor">'."\n";
	$out = apply_filters('sph_post_editor_top', $out, SP()->forum->view->thisTopic, $a);

	$out.= "<fieldset class='$controlFieldset'>\n";
	$out.= "<legend class='$legendClass'>$labelHeading: ".SP()->forum->view->thisTopic->topic_name."</legend>\n";

	$out.= "<input type='hidden' name='newaction' value='post' />\n";

	$out.= "<input type='hidden' name='forumid' value='".SP()->forum->view->thisTopic->forum_id."' />\n";
	$out.= "<input type='hidden' name='forumslug' value='".SP()->forum->view->thisTopic->forum_slug."' />\n";
	$out.= "<input type='hidden' name='topicid' value='".SP()->forum->view->thisTopic->topic_id."' />\n";
	$out.= "<input type='hidden' name='topicslug' value='".SP()->forum->view->thisTopic->topic_slug."' />\n";
	$out.= "<input type='hidden' name='captcha' value='$captchaValue' />\n";

	# input field that plugins can use
	$out.= "<input type='hidden' id='spEditorCustomValue' name='spEditorCustomValue' value='' />\n";

	# plugins can add before the header
	$out = apply_filters('sph_post_before_editor_header', $out, SP()->forum->view->thisTopic, $a);

	$tout = '';
	$close = false;
	if (!empty($postmsg['sfpostmsgpost']) || SP()->user->thisUser->guest || !SP()->auths->get('bypass_moderation', SP()->forum->view->thisTopic->forum_id) || !SP()->auths->get('bypass_moderation_once', SP()->forum->view->thisTopic->forum_id)) {
		$tout.= "<div class='$editorSection'>\n";
		$close = true;
	}

	# let plugins add stuff at top of editor header
	$tout = apply_filters('sph_post_editor_header_top', $tout, SP()->forum->view->thisTopic, $a);

	if (!empty($postmsg['sfpostmsgpost'])) {
		$tout.= "<div class='$editorMessage'>".SP()->displayFilters->title($postmsg['sfpostmsgtext'])."</div>\n";
		$tout.= sp_InsertBreak('echo=0&spacer=8px');
	}
	if (!empty($postmsg['sfpostmsgpost2'])) {
		$tout.= "<div class='$editorMessage'>".SP()->displayFilters->title($postmsg['sfpostmsgtext2'])."</div>\n";
		$tout.= sp_InsertBreak('echo=0&spacer=8px');
	}

	# create an empty div to allow plugins to add something
	$tout.= '<div id="spEditorCustomDiv"></div>';

	if (SP()->user->thisUser->guest) {
		$tout.= "<div class='$guestUserSection'>\n";
		$tout.= "<div class='$halfLeft'>\n";
		$tout.= "<div class='spEditorTitle'>$labelGuestName:\n";
		$tout.= "<input type='text' tabindex='".$tab++."' class='$controlInput' name='guestname' id='guestname' value='$guestnameval' /></div>\n";
		$tout.= '</div>'."\n";
		$sfguests = SP()->options->get('sfguests');
		if ($sfguests['reqemail']) {
			$tout.= "<div class='$halfRight'>\n";
			$tout.= "<div class='spEditorTitle'>$labelGuestEmail:\n";
			$tout.= "<input type='text' tabindex='".$tab++."' class='$controlInput' name='guestemail' id='guestemail' value='$guestemailval' /></div>\n";
			$tout.= '</div>'."\n";
		}
		$tout.= sp_InsertBreak('echo=0');
		$tout.= "</div>";
	}

	if (!SP()->auths->get('bypass_moderation', SP()->forum->view->thisTopic->forum_id)) {
		$tout.= "<p class='spLabelSmall'>$labelModerateAll</p>\n";
	} elseif (!SP()->auths->get('bypass_moderation_once', SP()->forum->view->thisTopic->forum_id)) {
		$tout.= "<p class='spLabelSmall'>$labelModerateOnce</p>\n";
	}

	# let plugins add stuff at bottom of editor header
	$tout = apply_filters('sph_post_editor_header_bottom', $tout, SP()->forum->view->thisTopic, $a);
	if ($close) $tout.= '</div>'."\n";

	# allow plugins to filter just the header
	$out.= apply_filters('sph_post_editor_header', $tout, SP()->forum->view->thisTopic, $a);

	# Display the selected editor
	$tout = '';
	$tout.= '<div id="spEditorContent">'."\n";
	$tout.= sp_setup_editor(103, $postitemval);
	$tout.= '</div>'."\n";

	# allow plugins to filter the editor content
	$out.= apply_filters('sph_post_editor_content', $tout, SP()->forum->view->thisTopic, $a);

	# define area above toolbar for plugins to add components
	$section = apply_filters('sph_post_editor_above_toolbar', '', SP()->forum->view->thisTopic, $a);
	if (!empty($section)) {
		$tout = '';
		$tout.= '<div class="spEditorSection">';
		$tout.= $section;
		$tout.= '</div>'."\n";
		$out.= apply_filters('sph_post_editor_above_toolbar_end', $tout, SP()->forum->view->thisTopic, $a);
	}

	# DEFINE NEW FAILURE AREA HERE

	# define validation failure notice area
	$out.= sp_InsertBreak('echo=0');
	$out.= "<div id='spPostNotifications' class='$failureMessage'>$failmessage</div>\n";

	# TOOLBAR

	# define toolbar - submit buttons on right, plugin extensions on left
	$toolbarRight = apply_filters('sph_post_editor_toolbar_submit', '', SP()->forum->view->thisTopic, $a, 'toolbar', 0, 'new');
	$toolbarLeft = apply_filters('sph_post_editor_toolbar_buttons', '', SP()->forum->view->thisTopic, $a, 'toolbar', 0, 'new');

	if (!empty($toolbarRight) || !empty($toolbarLeft)) {
		# Submit section
		$tout = '';
		$tout.= "<div class='$editorSection $toolbarClass'>";
		$tout.= $toolbarRight;

	   # toolbar for plugins to add buttons
		$tout.= $toolbarLeft;
		$out.= apply_filters('sph_post_editor_toolbar', $tout, SP()->forum->view->thisTopic, $a, 'toolbar');
		$out.= sp_InsertBreak('echo=0');
		$out.= '</div>'."\n";
   }

	# ALLOW PLUGINS TO ADD AT VARIOUS POINTS

	# let plugins add stuff at top of editor footer
	$tout = '';
	$tout = apply_filters('sph_post_editor_footer_top', $tout, SP()->forum->view->thisTopic, $a);

	# smileys and options
	$tout = apply_filters('sp_post_editor_inline_footer', $tout, SP()->forum->view->thisTopic, $a, 'inline');

	# let plugins add stuff at end of editor footer
	$tout = apply_filters('sph_post_editor_footer_bottom', $tout, SP()->forum->view->thisTopic, $a);

	# plugins can remove or adjust whole footer
	$out.= apply_filters('sph_post_editor_footer', $tout, SP()->forum->view->thisTopic, $a);

	# allow plugins to insert stuff after editor footer
	$out = apply_filters('sph_post_editor_after_footer', $out, SP()->forum->view->thisTopic, $a);

	# START SUBMIT SECTION

	# define submit section of no toolbar in use
	if (!$toolbar) {
		$out.= '<div class="spEditorSubmit">'."\n";
		$out = apply_filters('sph_post_editor_submit_top', $out, SP()->forum->view->thisTopic, $a);

		# let plugins add/remove the controls area
		$tout = apply_filters('sp_post_editor_inline_submit', '', SP()->forum->view->thisTopic, $a, 'inline');

		# let plugins add stuff at end of editor submit bottom
		$out.= apply_filters('sph_post_editor_submit_bottom', $tout, SP()->forum->view->thisTopic, $a);
		$out.= '</div>'."\n";
	}

	# close it up
	$out.= '</fieldset>'."\n";
	$out = apply_filters('sph_post_editor_bottom', $out, SP()->forum->view->thisTopic, $a);
	$out.= '</div>'."\n";

	$out.= '</form>'."\n";
	$out.= '</div>'."\n";

	# let plugins add stuff beneath the editor
	$out = apply_filters('sph_post_editor_beneath', $out, SP()->forum->view->thisTopic, $a);

	return $out;
}
