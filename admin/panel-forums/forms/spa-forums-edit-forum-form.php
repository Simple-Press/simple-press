<?php
/*
Simple:Press
Admin Forums Edit Forum Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit form information form.  It is hidden until the edit forum link is clicked
function spa_forums_edit_forum_form($forum_id) {
?>
<script>
   	spj.loadAjaxForm('sfforumedit<?php echo $forum_id; ?>', 'sfreloadfb');
</script>
<?php
	global $tab;

	$forum = SP()->DB->table(SPFORUMS, "forum_id=$forum_id", 'row');

	spa_paint_options_init();

	$ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=editforum', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfforumedit<?php echo $forum->forum_id; ?>" name="sfforumedit<?php echo $forum->forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_forumedit');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), true);
			//spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Forum Details'), false);
					$subforum = ($forum->parent) ? true : false;
					echo "<input type='hidden' name='cgroup_id' value='$forum->group_id' />";
					echo "<input type='hidden' name='cparent' value='$forum->parent' />";
					echo "<input type='hidden' name='cchildren' value='$forum->children' />";

					if (!$subforum && empty($forum->children)) {
						$mess = SP()->primitives->front_text('This is a top-level forum with no sub-forums and on this panel you can change the forum Group it is a member of. If changed it will be moved to the target Forum Group.');
					} elseif (!$subforum && !empty($forum->children)) {
						$mess = SP()->primitives->front_text('This is a top level forum with designated sub-forums and on this panel you can change the forum Group it is a member of. If changed it will be moved, along with the sub-forums, to the target Forum Group.');
					} elseif ($subforum && empty($forum->children)) {
						$mess = SP()->primitives->front_text('This is a sub-forum and on this panel you can change the forum parent it belongs to. If changed it will be moved to become a sub-forum of the target Forum.');
					} else {
						$mess = SP()->primitives->front_text('This is a sub-forum and also a parent to other sub-forums and on this panel you can change the forum parent it belongs to. If changed it will be moved, along with the sub-forums, to the target Forum.');
					}

					echo '<div class="sf-alert-block sf-info">';
					echo "<p><b>$mess</b></br>";
					echo SP()->primitives->front_text('For more flexible Group/Forum ordering and sub-forum promotion and demotion, please use the drag and drop interface on the Order Groups and Forums admin panel from the Forums Menu - or the Order Forums panel at Group level.').'</p>';
					echo '</div>';

					# Top level forum...
					$class = ($subforum) ? ' sf-dis-none' : ' sf-dis-block';
					echo "<div class='sf-form-row $class'>";
					spa_paint_select_start(SP()->primitives->admin_text('The group this forum belongs to'), 'group_id', '');
					echo spa_create_group_select($forum->group_id);
					spa_paint_select_end();
					echo '</div>';

					# sub-forum...
					$class = ($subforum) ? ' sf-dis-block' : ' sf-dis-none';
					echo "<div class='sf-form-row $class'>";
					spa_paint_select_start(SP()->primitives->admin_text('Parent forum this subforum belongs to'), 'parent', '');
					echo spa_create_forum_select($forum->parent);
					spa_paint_select_end();
					echo '</div>';

					$target = 'cforum_slug';
					$ajaxURL = wp_nonce_url(SPAJAXURL.'forums', 'forums');
					echo '<div class="sf-form-row"><input type="text" class="wp-core-ui sp-input-60 spForumSetSlug" tabindex="'.$tab.'" name="forum_name" id="forum_name" value="'.esc_attr($forum->forum_name).'" data-url="'.$ajaxURL.'" data-target="'.$target.'" data-type="edit" />';
					echo '<input type="hidden" name="forum_id" value="'.$forum->forum_id.'" /></div>';

					echo "<div class='sf-form-row'>\n";
					echo "<label>".SP()->primitives->admin_text('Forum slug').'</label>';
					echo '<input type="text" class="wp-core-ui sp-input-60" tabindex="'.$tab.'" name="cforum_slug" id="cforum_slug" value="'.esc_attr($forum->forum_slug).'" />';
					echo '<div class="clearboth"></div>';
					echo '</div>';
					$tab++;

					spa_paint_input(SP()->primitives->admin_text('Description'), 'forum_desc', SP()->editFilters->text($forum->forum_desc), false, true);

				spa_paint_close_fieldset();
			//spa_paint_close_panel();

			//spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Forum Options'), false);
					$target = 'cforum_slug';
					$ajaxURL = wp_nonce_url(SPAJAXURL.'forums', 'forums');

					spa_paint_checkbox(SP()->primitives->admin_text('Locked'), 'forum_status', $forum->forum_status);
					spa_paint_checkbox(SP()->primitives->admin_text('Disable forum RSS feed so feed will not be generated'), 'forum_private', $forum->forum_rss_private);

					spa_paint_select_start(sprintf(SP()->primitives->admin_text('Featured Image for this forum %s(200px x 200px recommended)'), '<br>'), 'feature_image', '');
					spa_select_icon_dropdown('feature_image', SP()->primitives->admin_text('Select Feature Image'), SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/', $forum->feature_image, false);
					spa_paint_select_end();

					echo '<div class="sf-alert-block sf-info">';
					echo '<p><b>'.SP()->primitives->front_text('Custom Icon Ordering').'</b></br>';
					echo SP()->primitives->front_text('When using custom forum or topic icons and multiple conditions exist, the following precedence is used:').'</p>';
                    echo SP()->primitives->front_text('Locked').'<br />';
                    echo SP()->primitives->front_text('Pinned and Unread').'<br />';
                    echo SP()->primitives->front_text('Pinned').'<br />';
                    echo SP()->primitives->front_text('Unread').'<br />';
                    echo SP()->primitives->front_text('Custom').'<br />';
                    echo SP()->primitives->front_text('Theme Default').'<br />';
					echo '</div>';
					
					$custom_icons =  spa_get_custom_icons();
					
					spa_select_iconset_icon_picker(
							'forum_icon', 
							SP()->primitives->admin_text('Custom forum icon'), 
							array( 'Custom Icons' => $custom_icons ),
							$forum->forum_icon
							);
					
					spa_select_iconset_icon_picker(
							'forum_icon_new', 
							SP()->primitives->admin_text('Custom forum icon when new posts'), 
							array( 'Custom Icons' => $custom_icons ),
							$forum->forum_icon_new
							);
					
					spa_select_iconset_icon_picker(
							'forum_icon_locked', 
							SP()->primitives->admin_text('Custom forum icon when locked'), 
							array( 'Custom Icons' => $custom_icons ),
							$forum->forum_icon_locked
							);
					
					spa_select_iconset_icon_picker(
							'topic_icon', 
							SP()->primitives->admin_text('Custom topic icon'), 
							array( 'Custom Icons' => $custom_icons ),
							$forum->topic_icon
							);

					spa_select_iconset_icon_picker(
							'topic_icon_new', 
							SP()->primitives->admin_text('Custom topic icon when new posts'), 
							array( 'Custom Icons' => $custom_icons ),
							$forum->topic_icon_new
							);

					spa_select_iconset_icon_picker(
							'topic_icon_locked', 
							SP()->primitives->admin_text('Custom topic icon when locked'), 
							array( 'Custom Icons' => $custom_icons ),
							$forum->topic_icon_locked
							);
					
					spa_select_iconset_icon_picker(
							'topic_icon_pinned', 
							SP()->primitives->admin_text('Custom topic icon when pinned'), 
							array( 'Custom Icons' => $custom_icons ),
							$forum->topic_icon_pinned
							);
					
					spa_select_iconset_icon_picker(
							'topic_icon_pinned_new', 
							SP()->primitives->admin_text('Custom topic icon when pinned and new posts'), 
							array( 'Custom Icons' => $custom_icons ),
							$forum->topic_icon_pinned_new
							);

					spa_paint_input(SP()->primitives->admin_text('Replacement external RSS URL').'<br />'.SP()->primitives->admin_text('Default').': <strong>'.SP()->spPermalinks->build_url($forum->forum_slug, '', 0, 0, 0, 1).'</strong>', 'forum_rss', SP()->displayFilters->url($forum->forum_rss), false, true);

					spa_paint_input(SP()->primitives->admin_text('Custom meta keywords (SEO option must be enabled)'), 'forum_keywords', SP()->editFilters->text($forum->keywords), false, true);
					spa_paint_wide_textarea('Special forum message to be displayed above forums', 'forum_message', SP()->editFilters->text($forum->forum_message));
				spa_paint_close_fieldset();
			//spa_paint_close_panel();

			//spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Extended Forum Options'), false);

					# As added by plugins
					do_action('sph_forum_edit_forum_options', $forum);
				spa_paint_close_fieldset();
			//spa_paint_close_panel();
			spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
    		<input type="submit" class="sf-button-primary" id="sfforumedit<?php echo $forum->forum_id; ?>" name="sfforumedit<?php echo $forum->forum_id; ?>" value="<?php SP()->primitives->admin_etext('Update Forum'); ?>" />
    		<input type="button" class="sf-button-primary spCancelForm" data-target="#forum-<?php echo $forum->forum_id; ?>" id="sfforumedit<?php echo $forum->forum_id; ?>" name="editforumcancel<?php echo $forum->forum_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
