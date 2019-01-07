<?php
/*
Simple:Press
Form Rendering
$LastChangedDate: 2016-02-15 23:30:48 -0600 (Mon, 15 Feb 2016) $
$Rev: 13949 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
# Top level form calls whcih then call the form painting functions
#
# --------------------------------------------------------------------------------------

function sp_inline_login_form($a) {
	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-login.php');
	return sp_render_inline_login_form($a);
}

function sp_inline_search_form($args) {
	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-search.php');
	return sp_render_inline_search_form($args);
}

function sp_add_topic($addTopicForm) {
	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-topic.php');
	return sp_render_add_topic_form($addTopicForm);
}

function sp_add_post($addPostForm) {
	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-post.php');
	return sp_render_add_post_form($addPostForm);
}

function sp_edit_post($editPostForm, $postid, $postcontent) {
	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-post-edit.php');
	return sp_render_edit_post_form($editPostForm, $postid, $postcontent);
}

function sp_setup_editor($tab, $content='') {
	global $spGlobals;

	$out = '';
	$out.= apply_filters('sph_pre_editor_display', '', $spGlobals['editor']);
	$out.= apply_filters('sph_editor_textarea', $out, 'postitem', $content, $spGlobals['editor'], $tab);
	$out.= apply_filters('sph_post_editor_display', '', $spGlobals['editor']);
	return $out;
}

function sp_render_smileys() {
	global $spGlobals;

	$out='';
	# load smiles from sfmeta
	if ($spGlobals['smileys']['smileys']) {
		foreach ($spGlobals['smileys']['smileys'] as $sname => $sinfo) {
			if ($sinfo[2]) {
				if (isset($sinfo[4]) && $sinfo[4]==true) {
					$out.='<br />';
				}
				$out.= '<img class="spSmiley" src="'.esc_url(SFSMILEYS.$sinfo[0]).'" title="'.esc_attr($sname).'" alt="'.esc_attr($sname).'" data-url="'.esc_attr($sinfo[0]).'" data-title="'.esc_attr($sname).'" data-path="'.SFSMILEYS.'" data-code="'.esc_attr($sinfo[1]).'" />';
			}
		}
	}
	return $out;
}
?>