<?php
/*
Simple:Press
New Topic Form Rendering
$LastChangedDate: 2016-07-16 16:19:55 -0500 (Sat, 16 Jul 2016) $
$Rev: 14442 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_add_topic_form($args) {
	global $tab, $spVars, $spGlobals, $spThisForum, $spThisUser, $spGuestCookie;

	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-components.php');

	$tab = 1;

	$toolbar = $spGlobals['display']['editor']['toolbar'];

	$defs = array('tagClass'				=> 'spForm',
                  'hide'					=> 1,
                  'controlFieldset'		    => 'spEditorFieldset',
                  'controlInput'			=> 'spControl',
                  'controlSubmit'			=> 'spSubmit',
                  'controlOrder'			=> 'cancel|save',
                  'maxTitleLength'			=> 200,
                  'labelHeading'			=> sp_text('Add Topic'),
                  'labelGuestName'		    => sp_text('Guest name (required)'),
                  'labelGuestEmail'		    => sp_text('Guest email (required)'),
                  'labelModerateAll'		=> sp_text('NOTE: new posts are subject to administrator approval before being displayed'),
                  'labelModerateOnce'		=> sp_text('NOTE: first posts are subject to administrator approval before being displayed'),
		          'labelTopicName'		    => sp_text('Topic name'),
                  'labelSmileys'			=> sp_text('Smileys'),
                  'labelOptions'			=> sp_text('Options'),
                  'labelOptionLock'		    => sp_text('Lock this topic'),
                  'labelOptionPin'		    => sp_text('Pin this post'),
                  'labelOptionTime'		    => sp_text('Edit post timestamp'),
                  'labelMath'				=> sp_text('Math Required'),
                  'labelMathSum'			=> sp_text('What is the sum of'),
                  'labelPostButtonReady'	=> sp_text('Submit Topic'),
                  'labelPostButtonMath'	    => sp_text('Do Math To Save'),
                  'labelPostCancel'		    => sp_text('Cancel'),
                  'tipSmileysButton'		=> sp_text('Open/Close to Add a Smiley'),
                  'tipOptionsButton'		=> sp_text('Open/Close to select Posting Options'),
                  'tipSubmitButton'		    => sp_text('Save the New Topic'),
                  'tipCancelButton'		    => sp_text('Cancel the New Topic')
				  );
	$a = wp_parse_args($args, $defs);
	extract($a, EXTR_SKIP);

    # sanitize
	$tagClass		        = esc_attr($tagClass);
	$hide			        = (int) $hide;
	$controlFieldset		= esc_attr($controlFieldset);
	$controlInput		    = esc_attr($controlInput);
	$maxTitleLength			= (int) $maxTitleLength;
	$labelHeading		    = sp_filter_title_display($labelHeading);
	$labelGuestName		   = sp_filter_title_display($labelGuestName);
	$labelGuestEmail		= sp_filter_title_display($labelGuestEmail);
	$labelModerateAll		= sp_filter_title_display($labelModerateAll);
	$labelModerateOnce		= sp_filter_title_display($labelModerateOnce);
	$labelTopicName		    = sp_filter_title_display($labelTopicName);

	# Check for a failure package in case this is a redirect
	$f = sp_get_cache('post');
	if (isset($f['guestname']) ? $guestnameval = $f['guestname'] : $guestnameval = $spGuestCookie->guest_name);
	if (isset($f['guestemail']) ? $guestemailval = $f['guestemail'] : $guestemailval = $spGuestCookie->guest_email);
	if (isset($f['newtopicname']) ? $topicnameval = $f['newtopicname'] : $topicnameval = '');
	if (isset($f['postitem']) ? $postitemval = $f['postitem'] : $postitemval = '');
	if (isset($f['message']) ? $failmessage = $f['message'] : $failmessage = '');
	$captchaValue = sp_get_option('captcha-value');

	$out = '';

	# Grab above editor message if there is one
	$postmsg = sp_get_option('sfpostmsg');

	# Grab in-editor message if one
	$inEdMsg = sp_filter_text_display(sp_get_option('sfeditormsg'));

	if ($hide ? $hide=' style="display:none;"' : $hide = '');
	$out.= '<div id="spPostForm"'.$hide.'>'."\n";

	$out.= "<form class='$tagClass' action='".SPAJAXURL."new-topic' method='post' id='addtopic' name='addtopic' data-guest='".$spThisUser->guest."' data-img='".sp_paint_file_icon(SPTHEMEICONSURL, 'sp_Success.png')."'>\n";

	$out.= sp_create_nonce('forum-userform_addtopic');

	$out.= '<div class="spEditor">'."\n";
	$out = apply_filters('sph_topic_editor_top', $out, $spThisForum);

	$out.= "<fieldset class='$controlFieldset'>\n";
	$out.= "<legend>$labelHeading: ".$spThisForum->forum_name."</legend>\n";

	$out.= "<input type='hidden' name='newaction' value='topic' />\n";

	$out.= "<input type='hidden' name='forumid' value='$spThisForum->forum_id' />\n";
	$out.= "<input type='hidden' name='forumslug' value='$spThisForum->forum_slug' />\n";
	$out.= "<input type='hidden' name='captcha' value='$captchaValue' />\n";

	# input field that plugins can use
	$out.= "<input type='hidden' id='spEditorCustomValue' name='spEditorCustomValue' value='' />\n";

    # plugins can add before the header
	$out = apply_filters('sph_topic_before_editor_header', $out, $spThisForum, $a);

    $tout = '';
	$tout.= '<div class="spEditorSection">';

	# let plugins add stuff at top of editor header
	$tout = apply_filters('sph_topic_editor_header_top', $tout, $spThisForum, $a);

	if (!empty($postmsg['sfpostmsgtopic'])) $tout.= '<div class="spEditorMessage">'.sp_filter_text_display($postmsg['sfpostmsgtext']).'</div>'."\n";

	# create an empty div to allow plugins to add something
	$tout.= '<div id="spEditorCustomDiv"></div>';

	if ($spThisUser->guest) {
		$tout.= '<div class="spEditorSectionLeft">'."\n";
		$tout.= "<div class='spEditorTitle'>$labelGuestName:\n";
		$tout.= "<input type='text' tabindex='".$tab++."' class='$controlInput' name='guestname' value='$guestnameval' /></div>\n";
		$tout.= '</div>'."\n";
		$sfguests = sp_get_option('sfguests');
		if ($sfguests['reqemail']) {
			$tout.= '<div class="spEditorSectionRight">'."\n";
			$tout.= "<div class='spEditorTitle'>$labelGuestEmail:\n";
			$tout.= "<input type='text' tabindex='".$tab++."' class='$controlInput' name='guestemail' value='$guestemailval' /></div>\n";
			$tout.= '</div>'."\n";
		}
		$tout.= '<div class="spClear"></div>'."\n";
	}

	if (!sp_get_auth('bypass_moderation', $spThisForum->forum_id)) {
		$tout.= "<p class='spLabelSmall'>$labelModerateAll</p>\n";
	} elseif (!sp_get_auth('bypass_moderation_once', $spThisForum->forum_id)) {
		$tout.= "<p class='spLabelSmall'>$labelModerateOnce</p>\n";
	}

    $tout2 = '';
	$tout2.= "<div class='spEditorTitle'>$labelTopicName: \n";
	$tout2.= "<input id='spTopicTitle' type='text' tabindex='".$tab++."' class='$controlInput' maxlength='$maxTitleLength' name='newtopicname' value='$topicnameval'/>\n";
    $tout2 = apply_filters('sph_topic_editor_name', $tout2, $a);
	$tout2.= '</div>'."\n";
    $tout.= apply_filters('sph_topic_editor_title', $tout2, $spThisForum, $a);

	# let plugins add stuff at bottom of editor header
	$tout = apply_filters('sph_topic_editor_header_bottom', $tout, $spThisForum, $a);
	$tout.= '</div>'."\n";

    # allow plugins to filter just the header
    $out.= apply_filters('sph_topic_editor_header', $tout, $spThisForum, $a);

	# do we have content? Or just add any inline message
	if (empty($postitemval)) $postitemval = $inEdMsg;

	# Display the selected editor
    $tout = '';
	$tout.= '<div id="spEditorContent">'."\n";
	$tout.= sp_setup_editor(103, $postitemval);
	$tout.= '</div>'."\n";

    # allow plugins to filter the editor content
    $out.= apply_filters('sph_topic_editor_content', $tout, $spThisForum, $a);

    # define area above toolbar for plugins to add components
    $section = apply_filters('sph_topic_editor_above_toolbar', '', $spThisForum, $a);
    if (!empty($section)) {
        $tout = '';
    	$tout.= '<div class="spEditorSection">';
        $tout.= $section;
    	$tout.= '</div>'."\n";
        $out.= apply_filters('sph_topic_editor_above_toolbar_end', $tout, $spThisForum, $a);
    }

	# DEFINE NEW FAILURE AREA HERE

	# define validation failure notice area
	$out.= "<div class='spClear'></div>\n";
	$out.= "<div id='spPostNotifications'>$failmessage</div>\n";

	# TOOLBAR

	# define toolbar - submit buttons on right, plugin extensions on left
    $toolbarRight = apply_filters('sph_topic_editor_toolbar_submit', '', $spThisForum, $a, 'toolbar');
    $toolbarLeft = apply_filters('sph_topic_editor_toolbar_buttons', '', $spThisForum, $a, 'toolbar');

	if (!empty($toolbarRight) || !empty($toolbarLeft)) {
		# Submit section
		$tout = '';
		$tout.= '<div class="spEditorSection spEditorToolbar">';
		$tout.= $toolbarRight;

	   # toolbar for plugins to add buttons
        $tout.= $toolbarLeft;
        $out.= apply_filters('sph_topic_editor_toolbar', $tout, $spThisForum, $a, 'toolbar');
		$out.= '<div style="clear:both"></div>';
		$out.= '</div>'."\n";
   }

	# START SMILEYS/OPTIONS

	# let plugins add stuff at top of editor footer
    $tout = '';
	$tout = apply_filters('sph_topic_editor_footer_top', $tout, $spThisForum, $a);

	# smileys and options
	$tout = apply_filters('sp_topic_editor_inline_footer', $tout, $spThisForum, $a, 'inline');

	# let plugins add stuff at end of editor footer
	$tout = apply_filters('sph_topic_editor_footer_bottom', $tout, $spThisForum, $a);

    # plugins can remove or adjust whole footer
	$out.= apply_filters('sph_topic_editor_footer', $tout, $spThisForum, $a);

    # allow plugins to insert stuff after editor footer
	$out = apply_filters('sph_topic_editor_after_footer', $out, $spThisForum, $a);

	# START SUBMIT SECTION

	# define submit section of no toolbar in use
	if (!$toolbar) {
		$out.= '<div class="spEditorSubmit">'."\n";
		$out = apply_filters('sph_topic_editor_submit_top', $out, $spThisForum, $a);

	    # let plugins add/remove the controls area
	    $tout = apply_filters('sp_topic_editor_inline_submit', '', $spThisForum, $a, 'inline');

		# let plugins add stuff at end of editor submit bottom
		$out.= apply_filters('sph_topic_editor_submit_bottom', $tout, $spThisForum, $a);
		$out.= '</div>'."\n";
	}

    # close it up
	$out.= '</fieldset>'."\n";

	$out = apply_filters('sph_topic_editor_bottom', $out, $spThisForum, $a);
	$out.= '</div>'."\n";

	$out.= '</form>'."\n";
	$out.= '</div>'."\n";

	# let plugins add stuff beneath the editor
	$out = apply_filters('sph_topic_editor_beneath', $out, $spThisForum, $a);

	return $out;
}

?>