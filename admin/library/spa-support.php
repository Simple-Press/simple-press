<?php
/*
  Simple:Press
  Admin Support Routines
  $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
  $Rev: 15704 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

require_once 'spa-iconsets.php';

function spa_enqueue_color_picker() {
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-color-picker' );
}

add_action( 'admin_enqueue_scripts', 'spa_enqueue_color_picker' );

function spa_get_forums_in_group($groupid) {
	return SP()->DB->table(SPFORUMS, "group_id=$groupid", '', 'forum_seq');
}

function spa_get_group_forums_by_parent($groupid, $parentid) {
	return SP()->DB->table(SPFORUMS, "group_id=$groupid AND parent=$parentid", '', 'forum_seq');
}

function spa_get_forums_all() {
	return SP()->DB->select('SELECT forum_id, forum_name, forum_status, forum_disabled, '.SPGROUPS.'.group_id, group_name
		 FROM '.SPFORUMS.'
		 JOIN '.SPGROUPS.' ON '.SPFORUMS.'.group_id = '.SPGROUPS.'.group_id
		 ORDER BY group_seq, forum_seq');
}

function spa_create_group_select($groupid = 0, $label = false) {
	$groups  = SP()->DB->table(SPGROUPS, '', '', 'group_seq');
	$out     = '';
	$default = '';

	if ($groups) {
		if ($label) {
			$out .= '<option value="">'.SP()->primitives->admin_text('Select forum group:').'</option>';
		}
		foreach ($groups as $group) {
			if ($group->group_id == $groupid) {
				$default = 'selected="selected" ';
			} else {
				$default = null;
			}
			$out .= '<option '.$default.'value="'.$group->group_id.'">'.SP()->displayFilters->title($group->group_name).'</option>'."\n";
			$default = '';
		}
	}

	return $out;
}

function spa_create_forum_select($forumid) {
	$forums = spa_get_forums_all();
	$out    = '';
	if ($forums) {
		foreach ($forums as $forum) {
			if ($forum->forum_id == $forumid) {
				$default = 'selected="selected" ';
			} else {
				$default = '';
			}
			$out .= '<option '.$default.'value="'.$forum->forum_id.'">'.SP()->displayFilters->title($forum->forum_name).'</option>'."\n";
			$default = '';
		}
	}

	return $out;
}

function spa_update_check_option($key) {
	if (isset($_POST[$key])) {
		SP()->options->update($key, true);
	} else {
		SP()->options->update($key, false);
	}
}

function spa_get_usergroups_all($usergroupid = null) {
	$where = '';
	if (!is_null($usergroupid)) $where = "usergroup_id=$usergroupid";

	return SP()->DB->table(SPUSERGROUPS, $where);
}

function spa_get_usergroups_row($usergroup_id) {
	return SP()->DB->table(SPUSERGROUPS, "usergroup_id=$usergroup_id", 'row');
}

function spa_create_usergroup_row($usergroupname, $usergroupdesc, $usergroupbadge, $usergroupjoin, $hide_stats, $usergroupismod, $report_failure = false) {
	# first check to see if user group name exists
	$exists = SP()->DB->table(SPUSERGROUPS, "usergroup_name='$usergroupname'", 'usergroup_id');
	if ($exists) {
		if ($report_failure == true) {
			return false;
		} else {
			return $exists;
		}
	}

	# go on and create the new user group
	$sql = 'INSERT INTO '.SPUSERGROUPS.' (usergroup_name, usergroup_desc, usergroup_badge, usergroup_join, hide_stats, usergroup_is_moderator) ';
	$sql .= "VALUES ('$usergroupname', '$usergroupdesc', '$usergroupbadge', '$usergroupjoin', '$hide_stats', '$usergroupismod')";

	if (SP()->DB->execute($sql)) {
		return SP()->rewrites->pageData['insertid'];
	} else {
		return false;
	}
}

function spa_remove_permission_data($permission_id) {
	return SP()->DB->execute('DELETE FROM '.SPPERMISSIONS." WHERE permission_id=$permission_id");
}

function spa_create_role_row($role_name, $role_desc, $auths, $report_failure = false) {
	# first check to see if rolename exists
	$exists = SP()->DB->table(SPROLES, "role_name='$role_name'", 'role_id');
	if ($exists) {
		if ($report_failure == true) {
			return false;
		} else {
			return $exists;
		}
	}

	# go on and create the new role
	$sql = 'INSERT INTO '.SPROLES.' (role_name, role_desc, role_auths) ';
	$sql .= "VALUES ('$role_name', '$role_desc', '$auths')";

	if (SP()->DB->execute($sql)) {
		return SP()->rewrites->pageData['insertid'];
	} else {
		return false;
	}
}

function spa_get_role_row($role_id) {
	return SP()->DB->table(SPROLES, "role_id=$role_id", 'row');
}

function spa_get_defpermissions($group_id) {
	return SP()->DB->select('SELECT permission_id, '.SPUSERGROUPS.'.usergroup_id, permission_role, usergroup_name
		FROM '.SPDEFPERMISSIONS.'
		JOIN '.SPUSERGROUPS.' ON '.SPDEFPERMISSIONS.'.usergroup_id = '.SPUSERGROUPS.".usergroup_id
		WHERE group_id=$group_id");
}

function spa_get_defpermissions_role($group_id, $usergroup_id) {
	return SP()->DB->table(SPDEFPERMISSIONS, "group_id=$group_id AND usergroup_id=$usergroup_id", 'permission_role');
}

function spa_display_usergroup_select($filter = false, $forum_id = 0, $showSelect = true) {
	$usergroups = spa_get_usergroups_all();
	//if ($showSelect) echo SP()->primitives->admin_text('Select usergroup');
	if ($showSelect) {
		?>
		<label><?php echo SP()->primitives->admin_text('Select usergroup') ?></label>
        <select class='sfacontrol' name='usergroup_id'>
		<?php
	}
	$out = '<option value="-1">'.SP()->primitives->admin_text('Select usergroup').'</option>';
	if ($filter) $perms = sp_get_forum_permissions($forum_id);
	foreach ($usergroups as $usergroup) {
		$disabled = '';
		if ($filter == 1 && $perms) {
			foreach ($perms as $perm) {
				if ($perm->usergroup_id == $usergroup->usergroup_id) {
					$disabled = 'disabled="disabled" ';
					continue;
				}
			}
		}
		$out .= '<option '.$disabled.'value="'.$usergroup->usergroup_id.'">'.SP()->displayFilters->title($usergroup->usergroup_name).'</option>'."\n";
	}
	echo $out;
	if ($showSelect) {
		?>
        </select>
		<?php
	}
}

function spa_display_permission_select($cur_perm = 0, $showSelect = true) {
	?>
	<?php $roles = sp_get_all_roles(); ?>
	<?php if ($showSelect) { ?>
        <select class='sfacontrol' name='role'>
		<?php
	}
	$out = '';
	if ($cur_perm == 0) $out .= '<option value="-1">'.SP()->primitives->admin_text('Select permission set').'</option>';
	foreach ($roles as $role) {
		$selected = '';
		if ($cur_perm == $role->role_id) $selected = 'selected = "selected" ';
		$out .= '<option '.$selected.'value="'.$role->role_id.'">'.SP()->displayFilters->title($role->role_name).'</option>'."\n";
	}
	echo $out;
	if ($showSelect) {
		?>
        </select>
		<?php
	}
}

/**
 * Get all custom icons in a directory
 * 
 * @param string $path
 * @param atring $url_base
 * 
 * @return array
 */
function spa_get_custom_icons( $path = '', $url_base = '' ) {
	
	if( !$path ) {
		$path = SP_STORE_DIR.'/'.SP()->plugin->storage['custom-icons'];
	}
	
	if( !$url_base ) {
		$url_base = SPCUSTOMURL;
	}
	
	$icons = array();
	
	$images = array_map('basename', glob("{$path}/{*.jpg,*.jpeg,*.png,*.gif,*.svg,*.webp,*.JPG,*.JPEG,*.PNG,*.GIF,*.SVG,*.WEBP}", GLOB_BRACE) );

	sort( $images );
	
	foreach( $images as $image ) {
		$icons[ $image ] = $url_base . $image . "?{$image}";
	}
	
	return array( 'icons' => $icons );
}


/**
 * Adds font icon picker
 * 
 * @param string $name
 * @param string $label
 * @param array $extra_icon_groups
 * @param string $selected
 * @param boolean $show_label
 */
function spa_select_iconset_icon_picker( $name, $label, $extra_icon_groups = array() ,$selected = '', $show_label = true, $css_classes = '' ) {
	
	$iconsets = array_merge( $extra_icon_groups, spa_get_all_active_iconsets() );
	
	global $tab;
	
	$selected_icon = array();
	
	if( !empty( $selected ) && is_array( json_decode( $selected, true ) ) && ( json_last_error() == JSON_ERROR_NONE ) ) {
		
		$ar_icon = json_decode( $selected, true );
		
		$selected_icon['icon']		= isset( $ar_icon['i'] ) ? $ar_icon['i'] : '';
		$selected_icon['color']		= isset( $ar_icon['c'] ) ? $ar_icon['c'] : '';
		
		
		$size_ar = isset( $ar_icon['s'] ) ? spa_iconset_parse_size( $ar_icon['s'] ) : array();
		
		if( !empty( $size_ar ) ) {
			$selected_icon['size']		= isset( $size_ar['size'] )		 ? $size_ar['size']		 : '';
			$selected_icon['size_type'] = isset( $size_ar['size_type'] ) ? $size_ar['size_type'] : '';
		}
	} else {
		$selected_icon['icon']		= $selected;
		$selected_icon['color']		= '';
		$selected_icon['size']		= '';
		$selected_icon['size_type'] = '';
	}
	
	
	if( $show_label ) {
		echo "<div class='sf-form-row ". $css_classes ."'>\n";
		echo "<label class='sp-label-40'>$label</label>\n";
	}
	
	$icon_picker_id = 'icon_picker_' . rand( 111111, 999999 );
	$icon_color_id = $icon_picker_id . '_color';
	
	$icon_size_id = $icon_picker_id . '_size';
	
	echo '<div class="sf-icon-picker-row sf-select-wrap">';
	
	printf( '<input type="hidden" name="%s" value="%s" class="icon_value" />', $name, esc_attr( json_encode( $selected_icon ) ) );
	
	printf( '<select class="wp-core-ui  sp-input-60" tabindex="%s" id="%s">', $tab, $icon_picker_id );
	
	$tab++;
	
	foreach( $iconsets as $iconset_name => $iconset ) {
		echo '<optgroup label="'.$iconset_name.'">';
		
		printf( '<option value=""></option>', $iconset_name );
		
		foreach ( $iconset['icons'] as $icon_id => $icon ) {
			
			$icon_id = is_int( $icon_id ) ? $icon : $icon_id;
			
			$_selected = $selected_icon['icon'] && $selected_icon['icon'] === $icon_id ? ' selected="selected"' : '';
			
			printf( '<option value="%s"%s>%s</option>', $icon, $_selected, $icon_id );
			
		}
		
		echo '</optgroup>';
	}
	
	echo "</select>\n";
	echo '<div class="clearboth"></div>';
	echo '</div>';
	echo '<div class="clearboth"></div>';
	
	if( $show_label ) {
		echo '</div>';
	}
	
	$color_field = sprintf( '<input type="text" class="wp-core-ui font-style-color" value="%s" id="%s" />', isset($selected_icon['color'])? $selected_icon['color'] : '', $icon_color_id );
	
	$size_input_field = sprintf( '<input type="number" placeholder="Size" class="wp-core-ui font-style-size" value="%s" id="%s" />', isset($selected_icon['size']) ? $selected_icon['size'] : '', $icon_size_id );
	
	$size_type_dropdown = spa_iconset_size_type_field( isset($selected_icon['size_type']) ? $selected_icon['size_type'] : '' );
	
	
	
	$font_style_fields = '<div class="font-style-container">\
								<div class="font-color-container">'.$color_field.'</div>\
								<div class="font-size-container">'.$size_input_field.$size_type_dropdown.'</div>\
								<div class="clear wp-clearfix"></div>\
						 </div>';
	
	
	
	?>

	<script>

	jQuery(document).ready(function($) {
		
		var _icon_ins = $('#<?php echo $icon_picker_id; ?>').fontIconPicker({
		        theme: 'fip-bootstrap',
				iconsPerPage: 30,
				allCategoryText : 'From All Libraries',
				iconGenerator: function( icon ) {
					
					// A random number that will be used to break caching.
					break_cache = Math.random();
					break_cache = break_cache.toString();					

					if( icon.match(/\.(jpeg|jpg|gif|png|svg|webp)$/) != null ) {
						// The use of break_cache below uses "&" instead of "?" because the icon variable already has an "?" in it for some reason.  
						// Probably because the iconpicker is trying to break the cache as well - except it uses a fixed string and caches that cache the querystring will not break with that.
						// So we're still going to add in our break_cache var with an "&".
						return '<i class="sf-iconset-icon"><img class="sf-iconset-icon-img" src="' + icon + '&break_cache=' + break_cache + '" /></i>';
					} else {
						return '<i class="'+icon+' sf-iconset-icon"></i>';
					}

				}
		    }).on( 'change', function( e ) {
				spj.UpdateFontIcon( '<?php echo $icon_picker_id; ?>' );
			});
	
		$( '<?php echo $font_style_fields ?>' ).insertBefore( _icon_ins.closest('.sf-icon-picker-row').find('.selector-popup .fip-icons-container') );
		
		
		$( '#<?php echo $icon_picker_id; ?>').closest('.sf-icon-picker-row').find('.font-style-size, .font-style-size_type').on( 'change', function() {
			spj.UpdateFontIcon( '<?php echo $icon_picker_id; ?>' );
		})
			
		$('#<?php echo $icon_color_id; ?>').wpColorPicker({
			change : function(a, b) {
				spj.UpdateFontIcon( '<?php echo $icon_picker_id; ?>' );
			},
			clear :  function(a, b) {
				spj.UpdateFontIcon( '<?php echo $icon_picker_id; ?>', true );
			}
		});
		
                _icon_ins.change();
	});

	</script>
	<?php
	
}

/**
 * Get selected icon and type
 * 
 * @param string $icon
 * 
 * @return array
 */
function spa_get_selected_icon( $icon, $filter = 'title' ) {
	
	$color = '';
	
	$size = '';
	$size_type = '';
	
	if( $icon ) {
		
		$icon_args = json_decode( html_entity_decode( stripslashes ( $icon ) ), true );
		
		if( $icon_args ) {
			$icon = isset( $icon_args['icon'] ) ? $icon_args['icon'] : '';
			$color = isset( $icon_args['color'] ) ? $icon_args['color'] : '';
			$size = isset( $icon_args['size'] ) ? $icon_args['size'] : '';
			$size = $size && is_numeric( $size ) && $size > 0 ? $size : '';
			
			$default_size_units = spa_iconset_icon_size_units();
			
			$size_type = isset( $icon_args['size_type'] ) ? $icon_args['size_type'] : '';
			
			$size_type = !$size_type || !in_array( $size_type , $default_size_units ) ? 'px' : $size_type;
			
		}
	}
			
	$file = parse_url( $icon, PHP_URL_QUERY );
	$type = 'font';


	if( $file ) {
		$type = 'file';
		$icon = $file;
	}
	
	$icon = call_user_func( array( SP()->saveFilters, $filter ), trim( $icon ) );
	
	
	$value_args = array( 'i' => $icon, 'c' => $color );
	
	if( $size ) {
		$value_args['s'] = $size . $size_type;
	}
	
	$value = addslashes( json_encode( $value_args ) );
	
	$data = array( 'type' => $type, 'icon' => $icon, 'color' => $color, 'value' => $value, 'size' => $size, 'size_type' => $size_type );
	
	return $data;
}
		

function spa_select_icon_dropdown($name, $label, $path, $cur, $showSelect = true, $width = 0) {
	# Open folder and get cntents for matching
	$dlist = @opendir($path);
	if (!$dlist) return;

	$files = array();
	while (false !== ($file = readdir($dlist))) {
		if ($file != '.' && $file != '..') {
			$files[] = $file;
		}
	}
	closedir($dlist);
	if (empty($files)) return;
	sort($files);

	$w = '';
	if ($width > 0) $w = 'width:'.$width.'px;';
	if ($showSelect) echo '<select name="'.$name.'" class="sfcontrol sf-vert-align-middle" '.$w.'">';
	if ($cur != '') $label = SP()->primitives->admin_text('Remove');
	echo '<option value="">'.$label.'</option>';

	foreach ($files as $file) {
		$selected = '';
		if ($file == $cur) $selected = ' selected="selected"';
		echo '<option'.$selected.' value="'.esc_attr($file).'">'.esc_html($file).'</option>';
	}
	if ($showSelect) echo '</select>';
}

# 5.2 add new auth categories for grouping of auths
# 6.0 updated for new instals only

function spa_setup_auth_cats() {
	# have the auths tables been created?
	$auths = SP()->DB->tableExists(SPAUTHS);

	# default auths
	SP()->auths->create_cat(SP()->primitives->admin_text('General'), SP()->primitives->admin_text('auth category for general auths'), 1);
	# viewing auths
	SP()->auths->create_cat(SP()->primitives->admin_text('Viewing'), SP()->primitives->admin_text('auth category for viewing auths'), 2);
	# creating auths
	SP()->auths->create_cat(SP()->primitives->admin_text('Creating'), SP()->primitives->admin_text('auth category for creating auths'), 3);
	# editing auths
	SP()->auths->create_cat(SP()->primitives->admin_text('Editing'), SP()->primitives->admin_text('auth category for editing auths'), 4);
	# deleting auths
	SP()->auths->create_cat(SP()->primitives->admin_text('Deleting'), SP()->primitives->admin_text('auth category for deleting auths'), 5);
	# moderation auths
	SP()->auths->create_cat(SP()->primitives->admin_text('Moderation'), SP()->primitives->admin_text('auth category for moderation auths'), 6);
	# tools auths
	SP()->auths->create_cat(SP()->primitives->admin_text('Tools'), SP()->primitives->admin_text('auth category for tools auths'), 7);
	# uploading auths
	SP()->auths->create_cat(SP()->primitives->admin_text('Uploading'), SP()->primitives->admin_text('auth category for uploading auths'), 8);
}

function spa_setup_auths() {
	# create the auths
	SP()->auths->add('view_forum', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view a forum')), 1, 0, 0, 0, 2, '');
	SP()->auths->add('view_forum_lists', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view a list of forums only')), 1, 0, 0, 0, 2, '');
	SP()->auths->add('view_forum_topic_lists', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view a list of forums and list of topics only')), 1, 0, 0, 0, 2, '');
	SP()->auths->add('view_admin_posts', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view posts by an administrator')), 1, 0, 0, 0, 2, '');
	SP()->auths->add('view_own_admin_posts', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view only own posts and admin/mod posts')), 1, 1, 0, 1, 2, '');
	SP()->auths->add('view_email', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view email and IP addresses of members')), 1, 1, 0, 0, 2, '');
	SP()->auths->add('view_profiles', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view profiles of members')), 1, 0, 0, 0, 2, '');
	SP()->auths->add('view_members_list', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view the members lists')), 1, 0, 0, 0, 2, '');
	SP()->auths->add('view_links', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view links within posts')), 1, 0, 0, 0, 2, '');
	SP()->auths->add('start_topics', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can start new topics in a forum')), 1, 0, 0, 0, 3, '');
	SP()->auths->add('reply_topics', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can reply to existing topics in a forum')), 1, 0, 0, 0, 3, '');
	SP()->auths->add('reply_own_topics', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can only reply to own topics')), 1, 1, 0, 1, 3, '');
	SP()->auths->add('bypass_flood_control', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can bypass wait time between posts')), 1, 0, 0, 0, 3, '');
	SP()->auths->add('use_spoilers', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can use spoilers in posts in posts')), 1, 0, 0, 0, 3, '');
	SP()->auths->add('use_signatures', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can attach a signature to posts')), 1, 1, 0, 0, 3, '');
	SP()->auths->add('create_links', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can create links in posts')), 1, 0, 0, 0, 3, '');
	SP()->auths->add('can_use_smileys', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can use smileys in posts')), 1, 0, 0, 0, 3, '');
	SP()->auths->add('can_use_iframes', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can use iframes in posts')), 1, 1, 0, 0, 3, SP()->primitives->admin_text('*** WARNING *** The use of iframes is dangerous. Allowing users to create iframes enables them to launch a potential security threat against your website. Enabling iframes requires your trust in your users. Turn on with care.'));
	SP()->auths->add('can_use_object_tag', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can use OBJECT and EMBED tags in posts')), 1, 1, 0, 0, 3, SP()->primitives->admin_text('*** WARNING *** The use of the OBJECT and EMBEG tags is dangerous. Allowing users to embed objects enables them to launch a potential security threat against your website. Enabling the OBJECT and EMBED tags requires your trust in your users. Turn on with care.'));
	SP()->auths->add('edit_own_topic_titles', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can edit own topic titles')), 1, 1, 0, 0, 4, '');
	SP()->auths->add('edit_any_topic_titles', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can edit any topic title')), 1, 1, 0, 0, 4, '');
	SP()->auths->add('edit_own_posts_for_time', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can edit own posts for time period')), 1, 1, 0, 0, 4, '');
	SP()->auths->add('edit_own_posts_forever', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can edit own posts forever')), 1, 1, 0, 0, 4, '');
	SP()->auths->add('edit_own_posts_reply', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can edit own posts until there has been a reply')), 1, 1, 0, 0, 4, '');
	SP()->auths->add('edit_any_post', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can edit any post')), 1, 1, 0, 0, 4, '');
	SP()->auths->add('delete_topics', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can delete topics in forum')), 1, 1, 0, 0, 5, '');
	SP()->auths->add('delete_own_posts', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can delete own posts')), 1, 1, 0, 0, 5, '');
	SP()->auths->add('delete_any_post', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can delete any post')), 1, 1, 0, 0, 5, '');
	SP()->auths->add('bypass_math_question', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can bypass the math question')), 1, 0, 0, 0, 6, '');
	SP()->auths->add('bypass_moderation', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can bypass all post moderation')), 1, 0, 0, 0, 6, '');
	SP()->auths->add('bypass_moderation_once', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can bypass first post moderation')), 1, 0, 0, 0, 6, '');
	SP()->auths->add('moderate_posts', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can moderate pending posts')), 1, 1, 0, 0, 6, '');
	SP()->auths->add('pin_topics', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can pin topics in a forum')), 1, 0, 0, 0, 7, '');
	SP()->auths->add('move_topics', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can move topics from a forum')), 1, 0, 0, 0, 7, '');
	SP()->auths->add('move_posts', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can move posts from a topic')), 1, 0, 0, 0, 7, '');
	SP()->auths->add('lock_topics', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can lock topics in a forum')), 1, 0, 0, 0, 7, '');
	SP()->auths->add('pin_posts', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can pin posts within a topic')), 1, 0, 0, 0, 7, '');
	SP()->auths->add('reassign_posts', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can reassign posts to a different user')), 1, 0, 0, 0, 7, '');
	SP()->auths->add('upload_avatars', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can upload avatars')), 1, 1, 1, 0, 8, '');
	SP()->auths->add('can_view_images', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view images in posts')), 1, 0, 0, 0, 2, '');
	SP()->auths->add('can_view_media', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view media in posts')), 1, 0, 0, 0, 2, '');
}

function spa_setup_permissions() {
	# Create default role data

	$role_name   = 'No Access';
	$role_desc   = 'Permission with no access to any Forum features';
	$new_actions = 'a:40:{i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;i:12;i:0;i:13;i:0;i:14;i:0;i:15;i:0;i:16;i:0;i:17;i:0;i:18;i:0;i:19;i:0;i:20;i:0;i:21;i:0;i:22;i:0;i:23;i:0;i:24;i:0;i:25;i:0;i:26;i:0;i:27;i:0;i:28;i:0;i:29;i:0;i:30;i:0;i:31;i:0;i:32;i:0;i:33;i:0;i:34;i:0;i:35;i:0;i:36;i:0;i:37;i:0;i:38;i:0;i:39;i:0;i:40;i:0;}';
	spa_create_role_row($role_name, $role_desc, $new_actions);

	$role_name   = 'Read Only Access';
	$role_desc   = 'Permission with access to only view the Forum';
	$new_actions = 'a:40:{i:1;i:1;i:2;i:0;i:3;i:0;i:4;i:1;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:1;i:10;i:0;i:11;i:0;i:12;i:0;i:13;i:0;i:14;i:1;i:15;i:0;i:16;i:0;i:17;i:0;i:18;i:0;i:19;i:0;i:20;i:0;i:21;i:0;i:22;i:0;i:23;i:0;i:24;i:0;i:25;i:0;i:26;i:0;i:27;i:0;i:28;i:0;i:29;i:0;i:30;i:0;i:31;i:0;i:32;i:0;i:33;i:0;i:34;i:0;i:35;i:0;i:36;i:0;i:37;i:0;i:38;i:0;i:39;i:1;i:40;i:1;}';
	spa_create_role_row($role_name, $role_desc, $new_actions);

	$role_name   = 'Limited Access';
	$role_desc   = 'Permission with access to reply and start topics but with limited features';
	$new_actions = 'a:40:{i:1;i:1;i:2;i:0;i:3;i:0;i:4;i:1;i:5;i:0;i:6;i:0;i:7;i:1;i:8;i:1;i:9;i:1;i:10;i:1;i:11;i:1;i:12;i:0;i:13;i:0;i:14;i:1;i:15;i:0;i:16;i:1;i:17;i:1;i:18;i:0;i:19;i:0;i:20;i:0;i:21;i:0;i:22;i:0;i:23;i:0;i:24;i:0;i:25;i:0;i:26;i:0;i:27;i:0;i:28;i:0;i:29;i:0;i:30;i:0;i:31;i:0;i:32;i:0;i:33;i:0;i:34;i:0;i:35;i:0;i:36;i:0;i:37;i:0;i:38;i:1;i:39;i:1;i:40;i:1;}';
	spa_create_role_row($role_name, $role_desc, $new_actions);

	$role_name   = 'Standard Access';
	$role_desc   = 'Permission with access to reply and start topics with advanced features such as signatures';
	$new_actions = 'a:40:{i:1;i:1;i:2;i:0;i:3;i:0;i:4;i:1;i:5;i:0;i:6;i:0;i:7;i:1;i:8;i:1;i:9;i:1;i:10;i:1;i:11;i:1;i:12;i:0;i:13;i:0;i:14;i:1;i:15;i:1;i:16;i:1;i:17;i:1;i:18;i:0;i:19;i:0;i:20;i:0;i:21;i:0;i:22;i:0;i:23;i:1;i:24;i:0;i:25;i:0;i:26;i:0;i:27;i:0;i:28;i:0;i:29;i:1;i:30;i:1;i:31;i:0;i:32;i:0;i:33;i:0;i:34;i:0;i:35;i:0;i:36;i:0;i:37;i:0;i:38;i:1;i:39;i:1;i:40;i:1;}';
	spa_create_role_row($role_name, $role_desc, $new_actions);

	$role_name   = 'Full Access';
	$role_desc   = 'Permission with Standard Access features and math question bypass';
	$new_actions = 'a:40:{i:1;i:1;i:2;i:0;i:3;i:0;i:4;i:1;i:5;i:0;i:6;i:0;i:7;i:1;i:8;i:1;i:9;i:1;i:10;i:1;i:11;i:1;i:12;i:0;i:13;i:1;i:14;i:1;i:15;i:1;i:16;i:1;i:17;i:1;i:18;i:0;i:19;i:1;i:20;i:0;i:21;i:0;i:22;i:1;i:23;i:1;i:24;i:0;i:25;i:0;i:26;i:0;i:27;i:0;i:28;i:1;i:29;i:1;i:30;i:1;i:31;i:0;i:32;i:0;i:33;i:0;i:34;i:0;i:35;i:0;i:36;i:0;i:37;i:0;i:38;i:1;i:39;i:1;i:40;i:1;}';
	spa_create_role_row($role_name, $role_desc, $new_actions);

	$role_name   = 'Moderator Access';
	$role_desc   = 'Permission with access to all Forum features';
	$new_actions = 'a:40:{i:1;i:1;i:2;i:0;i:3;i:0;i:4;i:1;i:5;i:0;i:6;i:1;i:7;i:1;i:8;i:1;i:9;i:1;i:10;i:1;i:11;i:1;i:12;i:0;i:13;i:1;i:14;i:1;i:15;i:1;i:16;i:1;i:17;i:1;i:18;i:0;i:19;i:1;i:20;i:1;i:21;i:0;i:22;i:1;i:23;i:1;i:24;i:1;i:25;i:1;i:26;i:0;i:27;i:1;i:28;i:1;i:29;i:1;i:30;i:1;i:31;i:1;i:32;i:1;i:33;i:1;i:34;i:1;i:35;i:1;i:36;i:1;i:37;i:1;i:38;i:1;i:39;i:1;i:40;i:1;}';
	spa_create_role_row($role_name, $role_desc, $new_actions);
}

# 5.0 set up stuff for new profile tabs

function spa_new_profile_setup() {
	# set up tabs and menus
	SP()->profile->add_tab('Profile');
	SP()->profile->add_menu('Profile', 'Overview', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-overview.php');
	SP()->profile->add_menu('Profile', 'Edit Profile', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-profile.php');
	SP()->profile->add_menu('Profile', 'Edit Identities', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-identities.php');
	SP()->profile->add_menu('Profile', 'Edit Avatar', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-avatar.php');
	SP()->profile->add_menu('Profile', 'Edit Signature', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-signature.php', 0, 1, 'use_signatures');
	SP()->profile->add_menu('Profile', 'Edit Photos', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-photos.php');
	SP()->profile->add_menu('Profile', 'Account Settings', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-account.php');

	SP()->profile->add_tab('Options');
	SP()->profile->add_menu('Options', 'Edit Global Options', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-global-options.php');
	SP()->profile->add_menu('Options', 'Edit Posting Options', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-posting-options.php');
	SP()->profile->add_menu('Options', 'Edit Display Options', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-display-options.php');

	SP()->profile->add_tab('Usergroups');
	SP()->profile->add_menu('Usergroups', 'Show Memberships', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-memberships.php');

	SP()->profile->add_tab('Permissions');
	SP()->profile->add_menu('Permissions', 'Show Permissions', SP_PLUGIN_DIR.'/forum/profile/forms/sp-form-permissions.php');

	# overview message
	$spProfile = SP()->options->get('sfprofile');
	if (empty($spProfile['sfprofiletext'])) {
		$spProfile['sfprofiletext'] = 'Welcome to the User Profile Overview Panel. From here you can view and update your profile and options as well as view your Usergroup Memberships and Permissions.';
		SP()->options->update('sfprofile', $spProfile);
	}
}

# 5.5.6

function sp_add_caps() {
	global $wp_roles;
	if (class_exists('WP_Roles') && !isset($wp_roles)) $wp_roles = new WP_Roles();

	$wp_roles->add_cap('administrator', 'SPF Manage Options', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Forums', false);
	$wp_roles->add_cap('administrator', 'SPF Manage User Groups', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Permissions', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Components', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Admins', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Users', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Profiles', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Toolbox', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Plugins', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Themes', false);
	$wp_roles->add_cap('administrator', 'SPF Manage Integration', false);
}

# 5.5.3 - get and display simple stats for admin items

function sp_display_item_stats($table, $key, $value, $label) {
	$c = SP()->DB->count($table, "$key = $value");
        ?>
    <span class="stats--key"><?php echo $label ?></span>
    <span class="stats--value"><?php echo $c ?></span>
        <?php
}

function spa_build_forum_permalink_slugs() {
    # grab all the forums
	$query        = new stdClass();
	$query->type  = 'set';
	$query->table = SPFORUMS;
	$forums       = SP()->DB->select($query);

	if ($forums) {
		foreach ($forums as $forum) {
		    # get base slug for this forum
			$slugs     = array($forum->forum_slug);
			$parent_id = $forum->parent;

			# add in any ancestor forums
			while (!empty($parent_id)) {
				# get acncestor forum
				$query        = new stdClass();
				$query->table = SPFORUMS;
				$query->where = "forum_id=$parent_id";
				$query->type  = 'row';
				$parent       = SP()->DB->select($query);
				$parent_id    = $parent->parent;

				# add in the ancestor forum slug
				$slugs[] = $parent->forum_slug;
			}

			# update the forum permalink slug with all ancestors and its slug
			$query         = new stdClass;
			$query->table  = SP_PREFIX.'sfforums';
			$query->fields = array('permalink_slug');
			$slug          = implode('/', array_reverse($slugs));
			$query->data   = array($slug);
			$query->where  = "forum_id=$forum->forum_id";
			$result        = SP()->DB->update($query);
		}
	}
}

function _spa_pagination($countPages, $currentPageNum, $paginationLength = 8, $ellipsisLength = 2) {
    $pagination = array();
    if ($countPages > 1) {
        $maxPaginationLength = $paginationLength;
        if ($countPages <= $paginationLength) {
            $paginationLength = $countPages;
            $from = 1;
        } else {
            $c = floor($paginationLength / 2) - 1;
            if ($currentPageNum <= $c) {
                $from = 1;
            } elseif ($currentPageNum + $c >= $countPages) {
                $from = $countPages - $paginationLength + 1;
            } else {
                $from = $currentPageNum - $c;
            }
        }

        $arr = array_keys(array_fill($from, $paginationLength, ''));

        if (count($arr) == $paginationLength && $ellipsisLength) {
            $pagination['array'] = array();
            foreach ($arr as $k => $pageNumber) {
                if ($currentPageNum + $ellipsisLength < $countPages && $paginationLength - $ellipsisLength < $k + 2 && $paginationLength - 1 > $k) {
                    if ($paginationLength - 2 == $k) {
                        $pagination['array'][$pageNumber] = '...';
                    }
                } else {
                    $pagination['array'][$pageNumber] = $pageNumber;
                }
            }
        } else {
            foreach ($arr as $pageNumber) {
                $pagination['array'][$pageNumber] = $pageNumber;
            }
        }
        if (count($pagination) > $maxPaginationLength - $ellipsisLength) {
            $pagination = array_slice($pagination, -($paginationLength - $ellipsisLength + 1));
        }
        if (count($pagination['array']) > 1) {
            $pagination['first'] = 1;
            $pagination['last'] = $countPages;
        } else {
            $pagination = array();
        }
    }
    return $pagination;
}

function spa_pagination($countPages, $currentPageNum, $paginationLength = 7) {
    $pagination = array();

    if ($countPages <= 1) {
        return $pagination;
    }

    $from = max(1, min($countPages - $paginationLength + 1, $currentPageNum - 2));

    $arr = range($from, min($countPages, $from + $paginationLength - 1));

    $pagination = array_combine($arr, $arr);

    return $pagination;
}


/**
 * Print pagination
 * 
 * @param array $link_args
 * @param int $countPages
 * @param int $currentPageNum
 * @param int $paginationLength
 * @param int $ellipsisLength
 */
function spa_print_pagination( $link_args, $countPages, $currentPageNum, $paginationLength = 8, $ellipsisLength = 2 ) {
	
	$pagination     = spa_pagination( $countPages, $currentPageNum, $paginationLength, $ellipsisLength ); ?>
	
	
	<?php if ( $pagination ): 
		
		
		$load_type = isset( $link_args['load_type'] ) ? $link_args['load_type'] : 'ajax';
		
	
		$url = $link_args['url'];
		$nonce_action = $link_args['nonce_action'] ? $link_args['nonce_action'] : '';
		$url_data_param = isset( $link_args['url_data_param'] ) ? $link_args['url_data_param'] : 'url';
	
		$target = $link_args['target'] ? $link_args['target'] : '.sf-full-form';
		$callback = $link_args['callback'] ? $link_args['callback'] : '';
		$gif = $link_args['gif'] ? $link_args['gif'] : SPADMINIMAGES . 'sp_WaitBox.gif';
		$link_callback = $link_args['link_callback'] ? $link_args['link_callback'] : '';
		
		
		
		$anchor_tag_attrs = array();
		
		$anchor_tag_attrs['href'] = '';
		if( $load_type === 'ajax' ) {
			$anchor_tag_attrs['target']		= 'data-target="'.$target.'"';
			$anchor_tag_attrs['after_cb']	= 'data-after_cb="'.$callback.'"';
			$anchor_tag_attrs['img']		= 'data-img="'.$gif.'"';
		}
		
		$anchor_ajax_class = $load_type === 'ajax' ? 'spLoadAjax' : '';
		
		?>
        <div class="sf-pagination">
            <span class="sf-pagination-links">
					
				<?php
				$a_url = wp_nonce_url( str_replace( '{page_num}', '1', $url ), $nonce_action );
				$href = $load_type === 'ajax' ? 'javascript:void(0);' : $a_url;
				
				?>
                <a class="sf-first-page <?php echo $anchor_ajax_class; ?>" 
				   <?php echo implode( ' ', $anchor_tag_attrs ); ?> 
				   data-url="<?php echo $a_url; ?>" 
				   href="<?php echo $href; ?>"
                ></a>
                   <?php foreach ( $pagination as $n => $v ): 
					   
						$a_url = wp_nonce_url( str_replace( '{page_num}', $n, $url ), $nonce_action );
						$href = $load_type === 'ajax' ? 'javascript:void(0);' : $a_url;
					   ?>
                       <a class="<?php echo $anchor_ajax_class; ?><?php echo $currentPageNum == $n ? ' sf-current-page' : '' ?>" 
                          <?php echo implode( ' ', $anchor_tag_attrs ); ?>
                          data-url="<?php echo $a_url; ?>" 
						  href="<?php echo $href; ?>"
                       ><?php echo $v ?></a>
                   <?php endforeach;
				   
					$a_url = wp_nonce_url( str_replace( '{page_num}', $countPages, $url ), $nonce_action );
					$href = $load_type === 'ajax' ? 'javascript:void(0);' : $a_url;
				   
				   ?>
                <a class="sf-last-page <?php echo $anchor_ajax_class; ?>" 
                   <?php echo implode( ' ', $anchor_tag_attrs ); ?>
					data-url="<?php echo $a_url; ?>" 
					href="<?php echo $href; ?>"
                ></a>
            </span>
        </div>
	<?php endif;
}


/**
 * Return list groups related to a user
 * 
 * @global object $wpdb
 * @param int $user_id
 * 
 * @return array
 */
function spa_user_groups_list( $user_id ) {
	global $wpdb;
	
	$sql = "SELECT m.usergroup_id, ug.usergroup_name FROM " . SPMEMBERSHIPS . " m
			LEFT JOIN " . SPUSERGROUPS . " ug ON ug.usergroup_id = m.usergroup_id
			WHERE m.user_id = %d";
	
	
	$results =  $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );
	
	return $results;
}