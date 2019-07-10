<?php
/*
Simple:Press
Admin Toolbox Inspector Form
$LastChangedDate: 2018-01-01 15:59:31 -0600 (Mon, 01 Jan 2018) $
$Rev: 15630 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_inspector_form() {
?>
<script>
   	spj.loadAjaxForm('sfinspectorform', '');
</script>
<?php
	$ins = spa_get_inspector_data();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'toolbox-loader&amp;saveform=inspector', 'toolbox-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfinspectorform" name="sfinspector">
	<?php echo sp_create_nonce('forum-adminform_inspector'); ?>
<?php
	spa_paint_options_init();

    #== UNINSTALL Tab ==========================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Toolbox').' - '.*/SP()->primitives->admin_text('Data Inspector'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Data Inspector'), true, 'inspect-data');
				echo '<div class="sf-alert-block sf-info">';
				SP()->primitives->admin_etext('Turning any of these options on will cause the data object being used to populate the relevant view or section to be displayed. You are the only user who will be shown these displays');
				echo '.';
				echo '</div>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Control Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('Forum Loader Class pageData'), 'con_pageData', $ins['con_pageData']);
				spa_paint_checkbox(SP()->primitives->admin_text('Forum Loader Class forumData'), 'con_forumData', $ins['con_forumData']);
				spa_paint_checkbox(SP()->primitives->admin_text('User Class thisUser'), 'con_thisUser', $ins['con_thisUser']);
				spa_paint_checkbox(SP()->primitives->admin_text('Core Loader Class device'), 'con_device', $ins['con_device']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Profile View Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('User Class profileUser'), 'pro_profileUser', $ins['pro_profileUser']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Group View Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class groups'), 'gv_groups', $ins['gv_groups']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisGroup'), 'gv_thisGroup', $ins['gv_thisGroup']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisForum'), 'gv_thisForum', $ins['gv_thisForum']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisForumSubs'), 'gv_thisForumSubs', $ins['gv_thisForumSubs']);
				echo '<hr>';
				spa_paint_checkbox(SP()->primitives->admin_text('View Class Group Query SQL'), 'q_GroupView', $ins['q_GroupView']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class Group Stats Query SQL'), 'q_GroupViewStats', $ins['q_GroupViewStats']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Forum View Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class forums'), 'fv_forums', $ins['fv_forums']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisForum'), 'fv_thisForum', $ins['fv_thisForum']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisForumSubs'), 'fv_thisForumSubs', $ins['fv_thisForumSubs']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisSubForum'), 'fv_thisSubForum', $ins['fv_thisSubForum']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisTopic'), 'fv_thisTopic', $ins['fv_thisTopic']);
				echo '<hr>';
				spa_paint_checkbox(SP()->primitives->admin_text('View Class Forum Query SQL'), 'q_ForumView', $ins['q_ForumView']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Clsss Forum Stats Query SQL'), 'q_ForumViewStats', $ins['q_ForumViewStats']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

	spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Topic View Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class topics'), 'tv_topics', $ins['tv_topics']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisTopic'), 'tv_thisTopic', $ins['tv_thisTopic']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisPist'), 'tv_thisPost', $ins['tv_thisPost']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisPostUser'), 'tv_thisPostUser', $ins['tv_thisPostUser']);
				echo '<hr>';
				spa_paint_checkbox(SP()->primitives->admin_text('View Class Topic Query SQL'), 'q_TopicView', $ins['q_TopicView']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Member View Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class members'), 'mv_members', $ins['mv_members']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisMemberGroup'), 'mv_thisMemberGroup', $ins['mv_thisMemberGroup']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisMember'), 'mv_thisMember', $ins['mv_thisMember']);
				echo '<hr>';
				spa_paint_checkbox(SP()->primitives->admin_text('View Class Members Query SQL'), 'q_MembersView', $ins['q_MembersView']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Topic List View Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class listTopics'), 'tlv_listTopics', $ins['tlv_listTopics']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisListTopic'), 'tlv_thisListTopic', $ins['tlv_thisListTopic']);
				echo '<hr>';
				spa_paint_checkbox(SP()->primitives->admin_text('View Class List Topic Query SQL'), 'q_ListTopicView', $ins['q_ListTopicView']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class List Topic New Query SQL'), 'q_ListTopicViewNew', $ins['q_ListTopicViewNew']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class List Topic First Query SQL'), 'q_ListTopicViewFirst', $ins['q_ListTopicViewFirst']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Post List View Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class listPosts'), 'plv_listPosts', $ins['plv_listPosts']);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisListPost'), 'plv_thisListPost', $ins['plv_thisListPost']);
				echo '<hr>';
				spa_paint_checkbox(SP()->primitives->admin_text('View Class Post List Query SQL'), 'q_ListPostView', $ins['q_ListPostView']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Search View Data'), false);
				spa_paint_checkbox(SP()->primitives->admin_text('View Class thisSearch'), 'sv_search', $ins['sv_search']);
				echo '<hr>';
				spa_paint_checkbox(SP()->primitives->admin_text('View Class Search Query SQL'), 'q_SearchView', $ins['q_SearchView']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_insepctor_panel');
		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Inspector Settings'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
