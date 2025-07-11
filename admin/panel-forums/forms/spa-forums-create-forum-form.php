<?php
/*
Simple:Press
Admin Forums Create Forum Form
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

# function to display the create new forum forum.  It is hidden until the create new forum link is clicked
function spa_forums_create_forum_form() {
?>
<script>
   	spj.loadAjaxForm('sfforumnew', 'sfreloadfb');
</script>
<?php
	global $tab;

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=createforum', 'forums-loader');
?>
	<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sfforumnew" name="sfforumnew">
<?php
		sp_echo_create_nonce('forum-adminform_forumnew');
		spa_paint_open_tab(esc_html(SP()->primitives->admin_text('Create New Forum')), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Create New Forum')), 'true', 'create-new-forum');
					# check there are groups before proceeding
					if (SP()->DB->count(SPGROUPS) == 0) {
						echo '<div class="sf-alert-block sf-info">';
						SP()->primitives->admin_etext('There are no groups defined');
						echo esc_html(SP()->primitives->admin_text('Create new group'));
						echo '</div>';
						spa_paint_close_fieldset();
						spa_paint_close_panel();
						spa_paint_close_container();
						spa_paint_close_tab();
                        echo '</form>';
						return;
					}

					# Select the forum type first
					echo "<div class='sf-form-row'>\n";
					echo "<div class='wp-core-ui sp-radio'>";
					echo '<input type="radio" name="forumtype" id="sfradio1" tabindex="'.esc_attr($tab).'" value="1" checked="checked" class="spForumSetOptions" data-target="forum" />'."\n";
					echo '<label for="sfradio1" class="wp-core-ui">'.esc_html(SP()->primitives->admin_text('Standard Forum')).'</label><br>'."\n";
					$tab++;
					# check there are forums before offering subforum creation!
					if (SP()->DB->count(SPFORUMS) != 0) {
						echo '<input type="radio" name="forumtype" id="sfradio2" tabindex="'.esc_attr($tab).'" value="2" class="spForumSetOptions" data-target="subforum" />'."\n";
						echo '<label for="sfradio2" class="wp-core-ui">'.esc_html(SP()->primitives->admin_text('Sub or child forum')).'</label>'."\n";
						$tab++;
					}
					echo '</div><div class="clearboth"></div></div>';

					# Now display the two select box options
					$ajaxURL = wp_nonce_url(SPAJAXURL.'forums', 'forums');
					$target = 'fseq';

					echo '<div id="groupselect" class="sf-dis-block">';
					echo "<div class='sf-form-row'>\n";
					echo "<label>".esc_html(SP()->primitives->admin_text('Select group new forum will belong to'))."</label>\n";
					echo '<select class="spForumSetSequence" tabindex="'.esc_attr($tab).'" name="group_id">';
                    spa_create_group_select(0, 1);
					echo "</select>\n";
					echo '<div class="clearboth"></div>';
					echo '</div>';
					$tab++;
					echo '</div>';

					echo '<div id="forumselect" class="sf-dis-block" style="display:none;">';
					echo "<div class='sf-form-row'>\n";
					echo "<label>".esc_html(SP()->primitives->admin_text('Select forum new subforum will belong to')).":</label>\n";
					echo '<select class="spForumSetSequence" tabindex="'.esc_attr($tab).'" name="forum_id">';
                        echo wp_kses(
                            sp_render_group_forum_select(false, false, false, true),
                            SP_CORE_ALLOWED_TAGS
                        ) ;
					echo "</select>\n";
					echo '<div class="clearboth"></div>';
					echo '</div>';
					$tab++;
					echo '</div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			spa_paint_close_container();
		//	echo '<div class="sfform-panel-spacer"></div>';

       // echo '<div class="sfform-panel-spacer"></div>';
		echo '<div class="sfhidden" id="block1">';

		spa_paint_open_nohead_tab(false);
			spa_paint_open_panel();
				spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Forum Details')), false);
					$target = 'thisforumslug';
					$ajaxURL = wp_nonce_url(SPAJAXURL.'forums', 'forums');

					# forum name and slug
					echo "<div class='sf-form-row'>";
					echo "<label>".esc_html(SP()->primitives->admin_text('Forum Name')).'</label>';
					echo '<input type="text" class="wp-core-ui sp-input-60 spForumSetSlug" tabindex="'.esc_attr($tab).'" name="forum_name" value="" data-url="'.esc_url($ajaxURL).'" data-target="'.esc_attr($target).'" data-type="new" />';
					echo '<div class="clearboth"></div>';
					echo '</div>';
					$tab++;

					echo "<div class='sf-form-row'>\n";
					echo "<label>".esc_html(SP()->primitives->admin_text('Forum slug'))."</label>";
					echo '<input type="text" class="wp-core-ui sp-input-60 spForumSetSlug" tabindex="'.esc_attr($tab).'" name="thisforumslug" id="thisforumslug" value="" disabled="disabled" data-url="'.esc_url($ajaxURL).'" data-target="'.esc_attr($target).'" data-type="new" />';
					echo '<div class="clearboth"></div>';
					echo '</div>';
					$tab++;

					spa_paint_input(esc_html(SP()->primitives->admin_text('Description')), 'forum_desc', '', false, true);

					spa_paint_checkbox(esc_html(SP()->primitives->admin_text('Locked')), 'forum_status', 0);
					spa_paint_checkbox(esc_html(SP()->primitives->admin_text('Disable forum RSS feed so feed will not be generated')), 'forum_private', 0);

                    spa_paint_select_start(esc_html(SP()->primitives->admin_text('Featured Image')), 'feature_image', '');
                    spa_select_icon_dropdown('feature_image', esc_html(SP()->primitives->admin_text('Select Feature Image')), SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/', '', false);
                        spa_paint_select_end('<span class="sf-sublabel sf-sublabel-small">'.esc_html(SP()->primitives->admin_text('Featured images are shown when sharing links on social media. Recommended size 200x200px')).'</span>');

                    echo '<div class="sf-alert-block sf-info">';
                        echo '<p><b>'.esc_html(SP()->primitives->front_text('Custom Icon Ordering')).'</b></br>';
                        echo esc_html(SP()->primitives->front_text('When using custom forum or topic icons and multiple conditions exist, the following precedence is used:')).'</p>';
                        echo '<ul>';
                            echo '<li>'.esc_html(SP()->primitives->front_text('Locked')).'</li>';
                            echo '<li>'.esc_html(SP()->primitives->front_text('Pinned and Unread')).'</li>';
                            echo '<li>'.esc_html(SP()->primitives->front_text('Pinned')).'</li>';
                            echo '<li>'.esc_html(SP()->primitives->front_text('Unread')).'</li>';
                            echo '<li>'.esc_html(SP()->primitives->front_text('Custom')).'</li>';
                            echo '<li>'.esc_html(SP()->primitives->front_text('Theme Default')).'</li>';
                        echo '</ul>';
                    echo '</div>';

					
					$custom_icons = spa_get_custom_icons();
					
					spa_select_iconset_icon_picker( 
							'forum_icon', 
							esc_html(SP()->primitives->admin_text('Custom forum icon')),
							array( 'Custom Icons' => $custom_icons )
							);

					spa_select_iconset_icon_picker( 
							'forum_icon_new', 
							esc_html(SP()->primitives->admin_text('Custom forum icon when new posts')),
							array( 'Custom Icons' => $custom_icons )
							);

					spa_select_iconset_icon_picker( 
							'forum_icon_locked', 
							esc_html(SP()->primitives->admin_text('Custom forum icon when locked')),
							array( 'Custom Icons' => $custom_icons )
							);
					
					spa_select_iconset_icon_picker( 
							'topic_icon', 
							esc_html(SP()->primitives->admin_text('Custom topic icon')),
							array( 'Custom Icons' => $custom_icons )
							);
					
					spa_select_iconset_icon_picker( 
							'topic_icon_new', 
							esc_html(SP()->primitives->admin_text('Custom topic icon when new posts')),
							array( 'Custom Icons' => $custom_icons )
							);

					spa_select_iconset_icon_picker( 
							'topic_icon_locked', 
							esc_html(SP()->primitives->admin_text('Custom topic icon when locked')),
							array( 'Custom Icons' => $custom_icons )
							);
										
					spa_select_iconset_icon_picker( 
							'topic_icon_pinned', 
							esc_html(SP()->primitives->admin_text('Custom topic icon when pinned')),
							array( 'Custom Icons' => $custom_icons )
							);
					
					spa_select_iconset_icon_picker( 
							'topic_icon_pinned_new', 
							esc_html(SP()->primitives->admin_text('Custom topic icon when pinned and new posts')),
							array( 'Custom Icons' => $custom_icons )
							);

					spa_paint_input(esc_html(SP()->primitives->admin_text('Custom meta keywords (SEO option must be enabled)')), 'forum_keywords', '', false, true);
					spa_paint_wide_textarea(esc_html(SP()->primitives->admin_text('Special forum message to be displayed above forums')), 'forum_message', '');
				spa_paint_close_fieldset();

			spa_paint_close_panel();

		spa_paint_tab_right_cell();
			spa_paint_open_panel();
				spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Extended Forum Options')), false);
					# As added by plugins
					do_action('sph_forum_create_forum_options');

				spa_paint_close_fieldset();
			spa_paint_close_panel();

			spa_paint_open_panel();
				spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Add User Group Permissions')), false);
					echo '<div id="block2" class="sfhidden">';
					echo '<div class="sf-alert-block sf-info">'.esc_html(SP()->primitives->admin_text('You can selectively set the permission sets for the forum below. If you want to use the default permissions for the selected group, then do not select anything')).'</div>';

					# Permissions
					$usergroups = spa_get_usergroups_all();
					$roles = sp_get_all_roles();

					foreach ($usergroups as $usergroup) {
						echo '<input type="hidden" name="usergroup_id[]" value="'.esc_attr($usergroup->usergroup_id).'" />';
						spa_paint_select_start(SP()->displayFilters->title($usergroup->usergroup_name), 'role[]', '');
						echo '<option value="-1">'.esc_html(SP()->primitives->admin_text('Select permission set')).'</option>';
						foreach ($roles as $role) {
							echo '<option value="'.esc_attr($role->role_id).'">'.esc_html(SP()->displayFilters->title($role->role_name)).'</option>'."\n";
						}
						spa_paint_select_end();
					}
                    echo '</div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
            <input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Create New Forum'); ?>" />
		</div>
    	<?php spa_paint_close_tab(); ?>
        </div>
		<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
