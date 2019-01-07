<?php
/*
Simple:Press
Admin Forums Edit Forum Form
$LastChangedDate: 2017-02-11 09:06:00 -0600 (Sat, 11 Feb 2017) $
$Rev: 15184 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit form information form.  It is hidden until the edit forum link is clicked
function spa_forums_edit_forum_form($forum_id) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfforumedit<?php echo $forum_id; ?>', 'sfreloadfb');
    });
</script>
<?php
	global $spPaths, $tab;

	$forum = spdb_table(SFFORUMS, "forum_id=$forum_id", 'row');

	spa_paint_options_init();

	$ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=editforum', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfforumedit<?php echo $forum->forum_id; ?>" name="sfforumedit<?php echo $forum->forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_forumedit');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Forum Details'), false);
					$subforum = ($forum->parent) ? true : false;
					echo "<input type='hidden' name='cgroup_id' value='$forum->group_id' />";
					echo "<input type='hidden' name='cparent' value='$forum->parent' />";
					echo "<input type='hidden' name='cchildren' value='$forum->children' />";

					if (!$subforum && empty($forum->children)) {
						$mess = sp_text('This is a top-level forum with no sub-forums and on this panel you can change the forum Group it is a member of. If changed it will be moved to the target Forum Group.');
					} elseif (!$subforum && !empty($forum->children)) {
						$mess = sp_text('This is a top level forum with designated sub-forums and on this panel you can change the forum Group it is a member of. If changed it will be moved, along with the sub-forums, to the target Forum Group.');
					} elseif ($subforum && empty($forum->children)) {
						$mess = sp_text('This is a sub-forum and on this panel you can change the forum parent it belongs to. If changed it will be moved to become a sub-forum of the target Forum.');
					} else {
						$mess = sp_text('This is a sub-forum and also a parent to other sub-forums and on this panel you can change the forum parent it belongs to. If changed it will be moved, along with the sub-forums, to the target Forum.');
					}

					echo '<div class="sfoptionerror spaceabove">';
					echo "<p><b>$mess</b></br>";
					echo sp_text('For more flexible Group/Forum ordering and sub-forum promotion and demotion, please use the drag and drop interface on the Order Groups and Forums admin panel from the Forums Menu - or the Order Forums panel at Group level.').'</p>';
					echo '</div>';

					# Top level forum...
					$style = ($subforum) ? ' style="display:none"' : ' style="display:block"';
					echo "<div $style>";
					spa_paint_select_start(spa_text('The group this forum belongs to'), 'group_id', '');
					echo spa_create_group_select($forum->group_id);
					spa_paint_select_end();
					echo '</div>';

					# sub-forum...
					$style = ($subforum) ? ' style="display:block"' : ' style="display:none"';
					echo "<div $style>";
					spa_paint_select_start(spa_text('Parent forum this subforum belongs to'), 'parent', '');
					echo spa_create_forum_select($forum->parent);
					spa_paint_select_end();
					echo '</div>';

					$target = 'cforum_slug';
					$ajaxURL = wp_nonce_url(SPAJAXURL.'forums', 'forums');
					echo '<input type="text" class="wp-core-ui sp-input-60 spForumSetSlug" tabindex="'.$tab.'" name="forum_name" id="forum_name" value="'.esc_attr($forum->forum_name).'" data-url="'.$ajaxURL.'" data-target="'.$target.'" data-type="edit" />';
					echo '<input type="hidden" name="forum_id" value="'.$forum->forum_id.'" />';

					echo "<div class='sp-form-row'>\n";
					echo "<div class='wp-core-ui sflabel sp-label-40'>".spa_text('Forum slug').':</div>';
					echo '<input type="text" class="wp-core-ui sp-input-60" tabindex="'.$tab.'" name="cforum_slug" id="cforum_slug" value="'.esc_attr($forum->forum_slug).'" />';
					echo '<div class="clearboth"></div>';
					echo '</div>';
					$tab++;

					spa_paint_input(spa_text('Description'), 'forum_desc', sp_filter_text_edit($forum->forum_desc), false, true);

				spa_paint_close_fieldset();
			spa_paint_close_panel();

			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Forum Options'), false);
					$target = 'cforum_slug';
					$ajaxURL = wp_nonce_url(SPAJAXURL.'forums', 'forums');

					spa_paint_checkbox(spa_text('Locked'), 'forum_status', $forum->forum_status);
					spa_paint_checkbox(spa_text('Disable forum RSS feed so feed will not be generated'), 'forum_private', $forum->forum_rss_private);

					spa_paint_select_start(sprintf(spa_text('Featured Image for this forum %s(200px x 200px recommended)'), '<br>'), 'feature_image', '');
					spa_select_icon_dropdown('feature_image', spa_text('Select Feature Image'), SF_STORE_DIR.'/'.$spPaths['forum-images'].'/', $forum->feature_image, false);
					spa_paint_select_end();

					echo '<div class="sfoptionerror spaceabove">';
					echo '<p><b>'.sp_text('Custom Icon Ordering').'</b></br>';
					echo sp_text('When using custom forum or topic icons and multiple conditions exist, the following precedence is used:').'</p>';
                    echo sp_text('Locked').'<br />';
                    echo sp_text('Pinned and Unread').'<br />';
                    echo sp_text('Pinned').'<br />';
                    echo sp_text('Unread').'<br />';
                    echo sp_text('Custom').'<br />';
                    echo sp_text('Theme Default').'<br />';
					echo '</div>';

					spa_paint_select_start(spa_text('Custom forum icon'), 'forum_icon', '');
					spa_select_icon_dropdown('forum_icon', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->forum_icon, false);
					spa_paint_select_end();

					spa_paint_select_start(spa_text('Custom forum icon when new posts'), 'forum_icon_new', '');
					spa_select_icon_dropdown('forum_icon_new', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->forum_icon_new, false);
					spa_paint_select_end();

					spa_paint_select_start(spa_text('Custom forum icon when locked'), 'forum_icon_locked', '');
					spa_select_icon_dropdown('forum_icon_locked', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->forum_icon_locked, false);
					spa_paint_select_end();

					spa_paint_select_start(spa_text('Custom topic icon'), 'topic_icon', '');
					spa_select_icon_dropdown('topic_icon', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->topic_icon, false);
					spa_paint_select_end();

					spa_paint_select_start(spa_text('Custom topic icon when new posts'), 'topic_icon_new', '');
					spa_select_icon_dropdown('topic_icon_new', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->topic_icon_new, false);
					spa_paint_select_end();

					spa_paint_select_start(spa_text('Custom topic icon when locked'), 'topic_icon_locked', '');
					spa_select_icon_dropdown('topic_icon_locked', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->topic_icon_locked, false);
					spa_paint_select_end();

					spa_paint_select_start(spa_text('Custom topic icon when pinned'), 'topic_icon_pinned', '');
					spa_select_icon_dropdown('topic_icon_pinned', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->topic_icon_pinned, false);
					spa_paint_select_end();

					spa_paint_select_start(spa_text('Custom topic icon when pinned and new posts'), 'topic_icon_pinned_new', '');
					spa_select_icon_dropdown('topic_icon_pinned_new', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->topic_icon_pinned_new, false);
					spa_paint_select_end();

					spa_paint_input(spa_text('Replacement external RSS URL').'<br />'.spa_text('Default').': <strong>'.sp_build_url($forum->forum_slug, '', 0, 0, 0, 1).'</strong>', 'forum_rss', sp_filter_url_display($forum->forum_rss), false, true);

					spa_paint_input(spa_text('Custom meta keywords (SEO option must be enabled)'), 'forum_keywords', sp_filter_text_edit($forum->keywords), false, true);
					spa_paint_wide_textarea('Special forum message to be displayed above forums', 'forum_message', sp_filter_text_edit($forum->forum_message));
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Extended Forum Options'), false);

					# As added by plugins
					do_action('sph_forum_edit_forum_options', $forum);
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
    		<input type="submit" class="button-primary" id="sfforumedit<?php echo $forum->forum_id; ?>" name="sfforumedit<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Update Forum'); ?>" />
    		<input type="button" class="button-primary spCancelForm" data-target="#forum-<?php echo $forum->forum_id; ?>" id="sfforumedit<?php echo $forum->forum_id; ?>" name="editforumcancel<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
?>