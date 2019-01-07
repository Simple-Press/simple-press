<?php
/*
Simple:Press
Edit Post Form Rendering
$LastChangedDate: 2017-04-10 11:07:19 -0500 (Mon, 10 Apr 2017) $
$Rev: 15324 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_edit_post_form($args, $postid, $postcontent) {
	global $tab;

	$tab = 1;

	$toolbar = SP()->core->forumData['display']['editor']['toolbar'];

	$defs = array('containerClass'			=> 'spForm',
				  'formClass'				=> 'spForm',
				  'controlFieldset'			=> 'spEditorFieldset',
				  'legendClass'				=> '',
				  'editorSection'			=> 'spEditorSection',
				  'controlInput'			=> 'spControl',
				  'controlSubmit'			=> 'spSubmit',
				  'controlSubmitMobile'		=> 'spicon',
				  'controlOrder'			=> 'cancel|save',
				  'toolbarClass'			=> 'spEditorToolbar',
				  'toolbarSubmitClassRight'	=> 'spRight',
				  'toolbarSubmit'			=> 'spEditorSubmit',
				  'noToolbar'				=> '',
				  'halfLeft'				=> 'spEditorSectionLeft',
				  'halfRight'				=> 'spEditorSectionRight',
				  'sectionHeading'			=> 'spEditorHeading',
				  'optionLabel'				=> 'spLabel',
				  'timeStamp'				=> 'spControl',
				  'labelHeading'			=> SP()->primitives->front_text('Edit Post'),
				  'labelSmileys'			=> SP()->primitives->front_text('Smileys'),
				  'labelOptions'			=> SP()->primitives->front_text('Options'),
				  'labelOptionTime'			=> SP()->primitives->front_text('Edit post timestamp'),
				  'labelPostButton'			=> SP()->primitives->front_text('Save Edited Post'),
				  'labelPostCancel'			=> SP()->primitives->front_text('Cancel'),
				  'tipSmileysButton'		=> SP()->primitives->front_text('Open/Close to Add a Smiley'),
				  'tipOptionsButton'		=> SP()->primitives->front_text('Open/Close to select Posting Options'),
				  'tipSubmitButton'			=> SP()->primitives->front_text('Save the Edited Post'),
				  'tipCancelButton'			=> SP()->primitives->front_text('Cancel the Post Edits')
				  );
	$a = wp_parse_args($args, $defs);
	extract($a, EXTR_SKIP);

	# sanitize
	$containerClass			= esc_attr($containerClass);
	$formClass				= esc_attr($formClass);
	$controlFieldset		= esc_attr($controlFieldset);
	$legendClass			= esc_attr($legendClass);
	$editorSection			= esc_attr($editorSection);
	$controlInput			= esc_attr($controlInput);
	$toolbarClass			= esc_attr($toolbarClass);
	$toolbarSubmit			= esc_attr($toolbarSubmit);	
	$labelHeading			= SP()->displayFilters->title($labelHeading);

	require_once SP_PLUGIN_DIR.'/forum/content/forms/sp-form-components.php';

	$captchaValue = SP()->options->get('captcha-value');

	$out = '';

	$out.= "<div id='spPostForm' class='$containerClass'>\n";
	$out.= "<form class='$formClass' action='".SP()->spPermalinks->build_url(SP()->forum->view->thisTopic->forum_slug, SP()->forum->view->thisTopic->topic_slug, SP()->forum->view->thisTopic->display_page, $postid)."' method='post' id='editpostform' name='editpostform'>\n";

	$out.= "<input type='hidden' name='forumid' value='".SP()->forum->view->thisTopic->forum_id."' />\n";
	$out.= "<input type='hidden' name='forumslug' value='".SP()->forum->view->thisTopic->forum_slug."' />\n";
	$out.= "<input type='hidden' name='topicid' value='".SP()->forum->view->thisTopic->topic_id."' />\n";
	$out.= "<input type='hidden' name='topicslug' value='".SP()->forum->view->thisTopic->topic_slug."' />\n";
	$out.= "<input type='hidden' name='pid' value='$postid' />\n";
	$out.= "<input type='hidden' name='captcha' value='$captchaValue' />\n";

	$out.= "<div class='spEditor'>\n";
	$out = apply_filters('sph_post_edit_top', $out, $postid, $a);

	$out.= "<fieldset class='$controlFieldset'>\n";
	$out.= "<legend class='$legendClass'>$labelHeading: </legend>\n";

	# Display the selected editor
	$tout = '';
	$tout.= '<div id="spEditorContent">'."\n";
	$tout.= sp_setup_editor(1, str_replace('&', '&amp;', $postcontent));
	$tout.= '</div>'."\n";
	$out.= apply_filters('sph_post_editor_content', $tout, SP()->forum->view->thisTopic, $postid, $a);

	# allow plugins to insert stuff before editor footer
	$out = apply_filters('sph_post_before_editor_footer', $out, SP()->forum->view->thisTopic, $postid, $a);

	# define area above toolbar for plugins to add components
	$section = apply_filters('sph_post_editor_edit_above_toolbar', '', SP()->forum->view->thisTopic, $a);
	if (!empty($section)) {
		$tout = '';
		$tout.= "<div class='$editorSection'>\n";
		$tout.= $section;
		$tout.= '</div>'."\n";
		$out.= apply_filters('sph_post_editor_edit_above_toolbar_end', $tout, SP()->forum->view->thisTopic, $a);
	}

	# DEFINE NEW FAILURE AREA HERE

	# define validation failure notice area
	$out.= sp_InsertBreak('echo=0');
	$out.= "<div id='spPostNotifications'></div>\n";

	# TOOLBAR

	# define toolbar - submit buttons on right, plugin extensions on left
	$toolbarRight = apply_filters('sph_post_editor_edit_toolbar_submit', '', SP()->forum->view->thisTopic, $a, 'toolbar', $postid, 'edit');
	$toolbarLeft = apply_filters('sph_post_editor_toolbar_buttons', '', SP()->forum->view->thisTopic, $a, 'toolbar', $postid, 'edit');

	if (!empty($toolbarRight) || !empty($toolbarLeft)) {
		# Submit section
		$tout = '';
		$tout.= "<div class='$toolbarClass'>";
		$tout.= $toolbarRight;

	   # toolbar for plugins to add buttons
		$tout.= $toolbarLeft;
		$out.= apply_filters('sph_post_editor_toolbar', $tout, SP()->forum->view->thisTopic, $a, 'toolbar');
		$out.= sp_InsertBreak('echo=0');
		$out.= '</div>'."\n";
   }

	# let plugins add stuff at top of editor footer
	$tout = '';
	$tout = apply_filters('sph_post_edit_footer_top', $tout, SP()->forum->view->thisTopic, $postid, $a);

	# smileys and options
	$tout = apply_filters('sp_post_editor_inline_footer', $tout, SP()->forum->view->thisTopic, $a, 'inline');

	# let plugins add stuff at top of editor footer
	$tout = apply_filters('sph_post_edit_footer_bottom', $tout, $postid, $a);

	# plugins can remove or adjust whole footer
	$out.= apply_filters('sph_post_editor_footer', $tout, SP()->forum->view->thisTopic, $a);

	# allow plugins to insert stuff after editor footer
	$out = apply_filters('sph_post_after_editor_footer', $out, SP()->forum->view->thisTopic, $a);

	# START SUBMIT SECTION

	# define submit section of no toolbar in use
	if (!$toolbar) {
		$out.= "<div class='$toolbarSubmit'>\n";
		$out = apply_filters('sph_post_edit_submit_top', $out, SP()->forum->view->thisTopic, $a);

		# let plugins add/remove the controls area
		$tout = apply_filters('sp_post_editor_edit_inline_submit', '', SP()->forum->view->thisTopic, $a, 'inline');

		# let plugins add stuff at end of editor submit bottom
		$out.= apply_filters('sph_post_edit_submit_bottom', $tout, SP()->forum->view->thisTopic, $a);
		$out.= '</div>'."\n";
	}

	$out.= '</fieldset>'."\n";

	$out = apply_filters('sph_post_edit_bottom', $out, $postid, $a);
	$out.= '</div>'."\n";
	$out.= '</form>'."\n";
	$out.= '</div>'."\n";

	# let plugins add stuff beneath the editor
	$out = apply_filters('sph_post_editor_beneath', $out, SP()->forum->view->thisTopic, $a);

	return $out;
}
