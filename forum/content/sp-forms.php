<?php
/*
Simple:Press
Form Rendering
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
# Top level form calls whcih then call the form painting functions
#
# --------------------------------------------------------------------------------------

function sp_inline_login_form($a) {
	require_once SP_PLUGIN_DIR.'/forum/content/forms/sp-form-login.php';

	return sp_render_inline_login_form($a);
}

function sp_inline_search_form($args) {
	require_once SP_PLUGIN_DIR.'/forum/content/forms/sp-form-search.php';

	return sp_render_inline_search_form($args);
}

function sp_add_topic($addTopicForm) {
	require_once SP_PLUGIN_DIR.'/forum/content/forms/sp-form-topic.php';

	return sp_render_add_topic_form($addTopicForm);
}

function sp_add_post($addPostForm) {
	require_once SP_PLUGIN_DIR.'/forum/content/forms/sp-form-post.php';

	return sp_render_add_post_form($addPostForm);
}

function sp_edit_post($editPostForm, $postid, $postcontent) {
	require_once SP_PLUGIN_DIR.'/forum/content/forms/sp-form-post-edit.php';

	return sp_render_edit_post_form($editPostForm, $postid, $postcontent);
}

function sp_setup_editor($tab, $content = '') {
	$out = '';
	$out .= apply_filters('sph_pre_editor_display', '', SP()->core->forumData['editor']);
	$out .= apply_filters('sph_editor_textarea', $out, 'postitem', $content, SP()->core->forumData['editor'], $tab);
	$out .= apply_filters('sph_post_editor_display', '', SP()->core->forumData['editor']);

	return $out;
}

function sp_render_smileys() {
	$out = '';
	# load smiles from sfmeta
	$smiley_data = SP()->meta->get_value('smileys', 'smileys');
	if (!empty($smiley_data)) {
		foreach ($smiley_data as $sname => $sinfo) {
			if ($sinfo[2]) {
				if (isset($sinfo[4]) && $sinfo[4] == true) {
					$out .= '<br />';
				}
				$out .= '<img class="spSmiley" src="'.esc_url(SPSMILEYS.$sinfo[0]).'" title="'.esc_attr($sname).'" alt="'.esc_attr($sname).'" data-url="'.esc_attr($sinfo[0]).'" data-title="'.esc_attr($sname).'" data-path="'.SPSMILEYS.'" data-code="'.esc_attr($sinfo[1]).'" />';
			}
		}
	}

	return $out;
}
