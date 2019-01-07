<?php
/*
Simple:Press
Edit Post Form Rendering
$LastChangedDate: 2016-07-16 16:19:55 -0500 (Sat, 16 Jul 2016) $
$Rev: 14442 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_edit_post_form($args, $postid, $postcontent) {
	global $tab, $spVars, $spThisUser, $spThisTopic, $spGlobals;

	$tab = 1;

	$defs = array('tagClass'				=> 'spForm',
                  'hide'					=> 1,
                  'controlFieldset'		    => 'spEditorFieldset',
                  'controlInput'			=> 'spControl',
                  'controlSubmit'			=> 'spSubmit',
                  'controlOrder'			=> 'cancel|save',
                  'labelHeading'			=> sp_text('Edit Post'),
                  'labelSmileys'			=> sp_text('Smileys'),
                  'labelOptions'			=> sp_text('Options'),
                  'labelOptionTime'		    => sp_text('Edit post timestamp'),
                  'labelPostButton'	        => sp_text('Save Edited Post'),
                  'labelPostCancel'		    => sp_text('Cancel'),
                  'tipSmileysButton'		=> sp_text('Open/Close to Add a Smiley'),
                  'tipOptionsButton'		=> sp_text('Open/Close to select Posting Options'),
                  'tipSubmitButton'		    => sp_text('Save the Edited Post'),
                  'tipCancelButton'		    => sp_text('Cancel the Post Edits')
				  );
	$a = wp_parse_args($args, $defs);
	extract($a, EXTR_SKIP);

    # sanitize
	$tagClass		        = esc_attr($tagClass);
	$hide			        = (int) $hide;
	$controlFieldset		= esc_attr($controlFieldset);
	$labelHeading		    = sp_filter_title_display($labelHeading);

	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-components.php');

	$toolbar = $spGlobals['display']['editor']['toolbar'];
	$captchaValue = sp_get_option('captcha-value');

	$out = '';

	$out.= "<div id='spPostForm'>\n";
	$out.= "<form class='$tagClass' action='".sp_build_url($spThisTopic->forum_slug, $spThisTopic->topic_slug, $spThisTopic->display_page, $postid)."' method='post' id='editpostform' name='editpostform'>\n";

	$out.= "<input type='hidden' name='forumid' value='$spThisTopic->forum_id' />\n";
	$out.= "<input type='hidden' name='forumslug' value='$spThisTopic->forum_slug' />\n";
	$out.= "<input type='hidden' name='topicid' value='$spThisTopic->topic_id' />\n";
	$out.= "<input type='hidden' name='topicslug' value='$spThisTopic->topic_slug' />\n";
	$out.= "<input type='hidden' name='pid' value='$postid' />\n";
	$out.= "<input type='hidden' name='captcha' value='$captchaValue' />\n";

	$out.= "<div class='spEditor'>\n";
	$out = apply_filters('sph_post_edit_top', $out, $postid, $a);

	$out.= "<fieldset class='$controlFieldset'>\n";
	$out.= "<legend>$labelHeading</legend>\n";

	# Display the selected editor
    $tout = '';
	$tout.= '<div id="spEditorContent">'."\n";
	$tout.= sp_setup_editor(1, str_replace('&', '&amp;', $postcontent));
	$tout.= '</div>'."\n";
    $out.= apply_filters('sph_post_editor_content', $tout, $spThisTopic, $postid, $a);

    # allow plugins to insert stuff before editor footer
	$out = apply_filters('sph_post_before_editor_footer', $out, $spThisTopic, $postid, $a);

	# define area above toolbar for plugins to add components
    $section = apply_filters('sph_post_editor_edit_above_toolbar', '', $spThisTopic, $a);
    if (!empty($section)) {
        $tout = '';
    	$tout.= '<div class="spEditorSection">';
        $tout.= $section;
    	$tout.= '</div>'."\n";
        $out.= apply_filters('sph_post_editor_edit_above_toolbar_end', $tout, $spThisTopic, $a);
    }

	# DEFINE NEW FAILURE AREA HERE

	# define validation failure notice area
	$out.= "<div class='spClear'></div>\n";
	$out.= "<div id='spPostNotifications'></div>\n";

	# TOOLBAR

	# define toolbar - submit buttons on right, plugin extensions on left
    $toolbarRight = apply_filters('sph_post_editor_edit_toolbar_submit', '', $spThisTopic, $a, 'toolbar', $postid, 'edit');
    $toolbarLeft = apply_filters('sph_post_editor_toolbar_buttons', '', $spThisTopic, $a, 'toolbar', $postid, 'edit');

	if (!empty($toolbarRight) || !empty($toolbarLeft)) {
		# Submit section
		$tout = '';
		$tout.= '<div class="spEditorSection spEditorToolbar">';
		$tout.= $toolbarRight;

	   # toolbar for plugins to add buttons
        $tout.= $toolbarLeft;
        $out.= apply_filters('sph_post_editor_toolbar', $tout, $spThisTopic, $a, 'toolbar');
		$out.= '<div style="clear:both"></div>';
		$out.= '</div>'."\n";
   }

	# let plugins add stuff at top of editor footer
    $tout = '';
	$tout = apply_filters('sph_post_edit_footer_top', $tout, $spThisTopic, $postid, $a);

	# smileys and options
	$tout = apply_filters('sp_post_editor_inline_footer', $tout, $spThisTopic, $a, 'inline');

	# let plugins add stuff at top of editor footer
	$tout = apply_filters('sph_post_edit_footer_bottom', $tout, $postid, $a);

    # plugins can remove or adjust whole footer
	$out.= apply_filters('sph_post_editor_footer', $tout, $spThisTopic, $a);

    # allow plugins to insert stuff after editor footer
	$out = apply_filters('sph_post_after_editor_footer', $out, $spThisTopic, $a);

	# START SUBMIT SECTION

	# define submit section of no toolbar in use
	if (!$toolbar) {
		$out.= '<div class="spEditorSubmit">'."\n";
		$out = apply_filters('sph_post_edit_submit_top', $out, $spThisTopic, $a);

	    # let plugins add/remove the controls area
	    $tout = apply_filters('sp_post_editor_edit_inline_submit', '', $spThisTopic, $a, 'inline');

		# let plugins add stuff at end of editor submit bottom
		$out.= apply_filters('sph_post_edit_submit_bottom', $tout, $spThisTopic, $a);
		$out.= '</div>'."\n";
	}

	$out.= '</fieldset>'."\n";

	$out = apply_filters('sph_post_edit_bottom', $out, $postid, $a);
	$out.= '</div>'."\n";
	$out.= '</form>'."\n";
	$out.= '</div>'."\n";

	# let plugins add stuff beneath the editor
	$out = apply_filters('sph_post_editor_beneath', $out, $spThisTopic, $a);

	return $out;
}

?>