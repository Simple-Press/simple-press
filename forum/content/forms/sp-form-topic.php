<?php
/*
Simple:Press
New Topic Form Rendering
$LastChangedDate: 2017-04-10 11:07:19 -0500 (Mon, 10 Apr 2017) $
$Rev: 15324 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_add_topic_form($args) {
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
				  'noToolbar'				=> 'panel row-fluid',
				  'halfLeft'				=> 'spEditorSectionLeft',
				  'halfRight'				=> 'spEditorSectionRight',
				  'failureMessage'			=> '',
				  'toolbarClass'			=> 'spEditorToolbar',
				  'toolbarSubmitClassRight'	=> 'spRight',
				  'sectionHeading'			=> 'spEditorHeading',
				  'optionLabel'				=> 'spLabel',
				  'timeStamp'				=> 'spControl',
				  'mathSection'				=> '',
				  'mathPadding'				=> 'spEditorTitle',
				  'mathLabels'				=> 'spEditorSpam',
				  'labelHeading'			=> SP()->primitives->front_text('Add Topic'),
				  'labelGuestName'			=> SP()->primitives->front_text('Guest name (required)'),
				  'labelGuestEmail'			=> SP()->primitives->front_text('Guest email (required)'),
				  'labelModerateAll'		=> SP()->primitives->front_text('NOTE: new posts are subject to administrator approval before being displayed'),
				  'labelModerateOnce'		=> SP()->primitives->front_text('NOTE: first posts are subject to administrator approval before being displayed'),
				  'labelTopicName'			=> SP()->primitives->front_text('Topic name'),
				  'labelSmileys'			=> SP()->primitives->front_text('Smileys'),
				  'labelOptions'			=> SP()->primitives->front_text('Options'),
				  'labelOptionLock'			=> SP()->primitives->front_text('Lock this topic'),
				  'labelOptionPin'			=> SP()->primitives->front_text('Pin this post'),
				  'labelOptionTime'			=> SP()->primitives->front_text('Edit post timestamp'),
				  'labelMath'				=> SP()->primitives->front_text('Math Required'),
				  'labelMathSum'			=> SP()->primitives->front_text('What is the sum of'),
				  'labelPostButtonReady'	=> SP()->primitives->front_text('Submit Topic'),
				  'labelPostButtonMath'		=> SP()->primitives->front_text('Do Math To Save'),
				  'labelPostCancel'			=> SP()->primitives->front_text('Cancel'),
				  'tipSmileysButton'		=> SP()->primitives->front_text('Open/Close to Add a Smiley'),
				  'tipOptionsButton'		=> SP()->primitives->front_text('Open/Close to select Posting Options'),
				  'tipSubmitButton'			=> SP()->primitives->front_text('Save the New Topic'),
				  'tipCancelButton'			=> SP()->primitives->front_text('Cancel the New Topic')
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
	$labelTopicName			= SP()->displayFilters->title($labelTopicName);

	# Check for a failure package in case this is a redirect
	$f = SP()->cache->get('post');
	if (isset($f['guestname']) ? $guestnameval = $f['guestname'] : $guestnameval = SP()->user->guest_cookie->name);
	if (isset($f['guestemail']) ? $guestemailval = $f['guestemail'] : $guestemailval = SP()->user->guest_cookie->email);
	if (isset($f['newtopicname']) ? $topicnameval = $f['newtopicname'] : $topicnameval = '');
	if (isset($f['postitem']) ? $postitemval = $f['postitem'] : $postitemval = '');
	if (isset($f['message']) ? $failmessage = $f['message'] : $failmessage = '');
	$captchaValue = SP()->options->get('captcha-value');

	$out = '';

	# Grab above editor message if there is one
	$postmsg = SP()->options->get('sfpostmsg');

	# Grab in-editor message if one
	$inEdMsg = SP()->displayFilters->text(SP()->options->get('sfeditormsg'));

	if ($hide ? $hide=' style="display:none;"' : $hide = '');
	$out.= "<div id='spPostForm' class='$containerClass'$hide>\n";

	$out.= "<form class='$formClass' action='".SPAJAXURL."new-topic' method='post' id='addtopic' name='addtopic' data-guest='".SP()->user->thisUser->guest."' data-img='".SP()->theme->paint_file_icon(SPTHEMEICONSURL, 'sp_Success.png')."'>\n";

	$out.= sp_create_nonce('forum-userform_addtopic');

	$out.= '<div class="spEditor">'."\n";
	$out = apply_filters('sph_topic_editor_top', $out, SP()->forum->view->thisForum);

	$out.= "<fieldset class='$controlFieldset'>\n";
	$out.= "<legend class='$legendClass'>$labelHeading: ".SP()->forum->view->thisForum->forum_name."</legend>\n";

	$out.= "<input type='hidden' name='newaction' value='topic' />\n";

	$out.= "<input type='hidden' name='forumid' value='".SP()->forum->view->thisForum->forum_id."' />\n";
	$out.= "<input type='hidden' name='forumslug' value='".SP()->forum->view->thisForum->forum_slug."' />\n";
	$out.= "<input type='hidden' name='captcha' value='$captchaValue' />\n";

	# input field that plugins can use
	$out.= "<input type='hidden' id='spEditorCustomValue' name='spEditorCustomValue' value='' />\n";

	# plugins can add before the header
	$out = apply_filters('sph_topic_before_editor_header', $out, SP()->forum->view->thisForum, $a);

	$tout = '';
	$tout.= "<div class='$editorSection'>\n";

	# let plugins add stuff at top of editor header
	$tout = apply_filters('sph_topic_editor_header_top', $tout, SP()->forum->view->thisForum, $a);

	if (!empty($postmsg['sfpostmsgtopic'])) {
		$tout.= "<div class='$editorMessage'>".SP()->displayFilters->title($postmsg['sfpostmsgtext'])."</div>\n";
		$tout.= sp_InsertBreak('echo=0&spacer=8px');
	}
	
	if (!empty($postmsg['sfpostmsgtopic2'])) {
		$tout.= "<div class='$editorMessage'>".SP()->displayFilters->title($postmsg['sfpostmsgtext2'])."</div>\n";
		$tout.= sp_InsertBreak('echo=0&spacer=8px');
	}	

	# create an empty div to allow plugins to add something
	$tout.= '<div id="spEditorCustomDiv"></div>';

	if (SP()->user->thisUser->guest) {
		$tout.= "<div class='$guestUserSection'>\n";
		$tout.= "<div class='$halfLeft'>\n";
		$tout.= "<div class='spEditorTitle'>$labelGuestName:\n";
		$tout.= "<input type='text' tabindex='".$tab++."' class='$controlInput' name='guestname' value='$guestnameval' /></div>\n";
		$tout.= '</div>'."\n";
		$sfguests = SP()->options->get('sfguests');
		if ($sfguests['reqemail']) {
			$tout.= "<div class='$halfRight'>\n";
			$tout.= "<div class='spEditorTitle'>$labelGuestEmail:\n";
			$tout.= "<input type='text' tabindex='".$tab++."' class='$controlInput' name='guestemail' value='$guestemailval' /></div>\n";
			$tout.= '</div>'."\n";
		}
		$tout.= sp_InsertBreak('echo=0');
		$tout.= "</div>";
	}

	if (!SP()->auths->get('bypass_moderation', SP()->forum->view->thisForum->forum_id)) {
		$tout.= "<p class='spLabelSmall'>$labelModerateAll</p>\n";
	} elseif (!SP()->auths->get('bypass_moderation_once', SP()->forum->view->thisForum->forum_id)) {
		$tout.= "<p class='spLabelSmall'>$labelModerateOnce</p>\n";
	}

	$tout2 = '';
	$tout2.= "<div class='$topicTitleSection'>$labelTopicName: \n";
	$tout2.= "<input id='spTopicTitle' type='text' tabindex='".$tab++."' class='$controlInput' maxlength='$maxTitleLength' name='newtopicname' value='$topicnameval'/>\n";
	$tout2 = apply_filters('sph_topic_editor_name', $tout2, $a);
	$tout2.= '</div>'."\n";
	$tout.= apply_filters('sph_topic_editor_title', $tout2, SP()->forum->view->thisForum, $a);

	# let plugins add stuff at bottom of editor header
	$tout = apply_filters('sph_topic_editor_header_bottom', $tout, SP()->forum->view->thisForum, $a);
	$tout.= '</div>'."\n";

	# allow plugins to filter just the header
	$out.= apply_filters('sph_topic_editor_header', $tout, SP()->forum->view->thisForum, $a);

	# do we have content? Or just add any inline message
	if (empty($postitemval)) $postitemval = $inEdMsg;

	# Display the selected editor
	$tout = '';
	$tout.= '<div id="spEditorContent">'."\n";
	$tout.= sp_setup_editor(103, $postitemval);
	$tout.= '</div>'."\n";

	# allow plugins to filter the editor content
	$out.= apply_filters('sph_topic_editor_content', $tout, SP()->forum->view->thisForum, $a);

	# define area above toolbar for plugins to add components
	$section = apply_filters('sph_topic_editor_above_toolbar', '', SP()->forum->view->thisForum, $a);
	if (!empty($section)) {
		$tout = '';
		$tout.= '<div class="spEditorSection">';
		$tout.= $section;
		$tout.= '</div>'."\n";
		$out.= apply_filters('sph_topic_editor_above_toolbar_end', $tout, SP()->forum->view->thisForum, $a);
	}

	# DEFINE NEW FAILURE AREA HERE

	# define validation failure notice area
	$out.= sp_InsertBreak('echo=0');
	$out.= "<div id='spPostNotifications' class='$failureMessage'>$failmessage</div>\n";

	# TOOLBAR

	# define toolbar - submit buttons on right, plugin extensions on left
	$toolbarRight = apply_filters('sph_topic_editor_toolbar_submit', '', SP()->forum->view->thisForum, $a, 'toolbar');
	$toolbarLeft = apply_filters('sph_topic_editor_toolbar_buttons', '', SP()->forum->view->thisForum, $a, 'toolbar');

	if (!empty($toolbarRight) || !empty($toolbarLeft)) {
		# Submit section
		$tout = '';
		$tout.= "<div class='$editorSection $toolbarClass'>";
		$tout.= $toolbarRight;

	   # toolbar for plugins to add buttons
		$tout.= $toolbarLeft;
		$out.= apply_filters('sph_topic_editor_toolbar', $tout, SP()->forum->view->thisForum, $a, 'toolbar');
		$out.= sp_InsertBreak('echo=0');
		$out.= '</div>'."\n";
   }

	# ALLOW PLUGINS TO ADD AT VARIOUS POINTS

	$tout = '';
	$tout = apply_filters('sph_topic_editor_footer_top', $tout, SP()->forum->view->thisForum, $a);

	# let plugins add stuff inline
	$tout = apply_filters('sp_topic_editor_inline_footer', $tout, SP()->forum->view->thisForum, $a, 'inline');

	# let plugins add stuff at end of editor footer
	$tout = apply_filters('sph_topic_editor_footer_bottom', $tout, SP()->forum->view->thisForum, $a);

	# plugins can remove or adjust whole footer
	$out.= apply_filters('sph_topic_editor_footer', $tout, SP()->forum->view->thisForum, $a);

	# allow plugins to insert stuff after editor footer
	$out = apply_filters('sph_topic_editor_after_footer', $out, SP()->forum->view->thisForum, $a);

	# START SUBMIT SECTION

	# define submit section of no toolbar in use
	if (!$toolbar) {
		$out.= '<div class="spEditorSubmit">'."\n";
		$out = apply_filters('sph_topic_editor_submit_top', $out, SP()->forum->view->thisForum, $a);

		# let plugins add/remove the controls area
		$tout = apply_filters('sp_topic_editor_inline_submit', '', SP()->forum->view->thisForum, $a, 'inline');

		# let plugins add stuff at end of editor submit bottom
		$out.= apply_filters('sph_topic_editor_submit_bottom', $tout, SP()->forum->view->thisForum, $a);
		$out.= '</div>'."\n";
	}

	# close it up
	$out.= '</fieldset>'."\n";

	$out = apply_filters('sph_topic_editor_bottom', $out, SP()->forum->view->thisForum, $a);
	$out.= '</div>'."\n";

	$out.= '</form>'."\n";
	$out.= '</div>'."\n";

	# let plugins add stuff beneath the editor
	$out = apply_filters('sph_topic_editor_beneath', $out, SP()->forum->view->thisForum, $a);

	return $out;
}
