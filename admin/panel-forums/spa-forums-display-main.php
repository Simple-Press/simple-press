<?php
/*
Simple:Press
Admin Forums Main Display
$LastChangedDate: 2016-11-10 08:05:06 -0600 (Thu, 10 Nov 2016) $
$Rev: 14719 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_forums_forums_main() {
	# has SP just been installed?
	if (isset($_POST['install'])) {
		$site = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'troubleshooting&install=1', 'troubleshooting'));
		$target = 'sfmaincontainer';
		?>
		<script type='text/javascript'>
		jQuery(document).ready(function() {
			spjTroubleshooting("<?php echo($site);?>", "<?php echo($target);?>");
		});
		</script>
		<?php
	}

    # check if sample data is to be removed
    if (isset($_POST['spSampleDelDo'])) {
    	# delete sample data
    	$groups = spdb_table(SFGROUPS, 'sample=1');
    	if ($groups) {
    		include_once SF_PLUGIN_DIR.'/admin/panel-forums/support/spa-forums-save.php';
    		foreach ($groups as $group) {
    			spa_delete_sample($group->group_id);
    		}
    		sp_update_option('spSample', false);
    	}
    }

	$groups = spdb_table(SFGROUPS, '', '', 'group_seq');
	if ($groups) {
        if (sp_get_option('spSample')) {
        	echo '<form action="'.SFADMINFORUM.'" method="post" id="spSampleDel" name="spSampleDel">';
        	echo sp_create_nonce('forum-adminform_groupdelete');
        	echo '<input type="submit" class="button-primary" name="spSampleDelDo" id="spSampleDelDo" value="Delete Sample Data" />';
        	echo '</form><br />';
        }

		foreach ($groups as $group) {

			if (empty($group->group_icon)) {
				$icon = SPTHEMEICONSURL.'sp_GroupIcon.png';
			} else {
				$icon = esc_url(SFCUSTOMURL.$group->group_icon);
				if (!file_exists(SFCUSTOMDIR.$group->group_icon)) $icon = SPTHEMEICONSURL.'sp_GroupIcon.png';
			}
			# Group
?>
			<table class="wp-list-table widefat">
				<tr>
					<th style="text-align:center;width:2%" scope="col"><?php spa_etext('ID'); ?></th>
					<th style="text-align:center;width:5%" scope="col"><?php spa_etext('Icon'); ?></th>
					<th style="text-align:center;" scope="col"><?php spa_etext('Forum Group'); ?></th>
				</tr>
			</table>
			<table class="wp-list-table widefat">
				<tr id="grouprow-<?php echo $group->group_id; ?>">
					<td style="text-align:center;width:2%"><?php echo $group->group_id; ?></td>
					<td style="text-align:center;padding:8px 0px;width:5%"><?php echo '<img src="'.$icon.'" alt="" title="'.spa_text('Current group icon').'" />' ?>
					<br />
					<a style="font-size: 30px;font-weight:bold;" title="<?php spa_etext('Collapse/expand forum listing'); ?>" class="spExpandCollapseGroup" data-target="forum-group-<?php echo $group->group_id; ?>">&ndash;</a>

					</td>
					<td>
						<div class="sp-half-row-left">
							<div class='row-title'><strong><?php echo sp_filter_title_display($group->group_name); ?></strong></div><div><?php echo sp_filter_text_display($group->group_desc); ?></div>
<?php
	 						sp_display_item_stats(SFFORUMS, 'group_id', $group->group_id, spa_text('Forums'));
?>
						</div>
						<div class="sp-half-row-right">
<?php
							$base = wp_nonce_url(SPAJAXURL.'forums-loader&amp;id='.$group->group_id, 'forums-loader');
							$target = "group-$group->group_id";
							$image = SFADMINIMAGES;
?>
							<input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Add Permission'); ?>" data-form="groupperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open="" />
							<input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Edit This Group'); ?>" data-form="editgroup" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open="" />
							<input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Delete Group'); ?>" data-form="deletegroup" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open="" />
							<input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Order Forums'); ?>" data-form="ordering" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php $group->group_id; ?>" data-open="" />

						</div>
					</td>
				</tr>

				<tr class="sfinline-form">  <!-- This row will hold ajax forms for the current group -->
				  	<td colspan="3" style="padding: 0">
						<div id="group-<?php echo $group->group_id; ?>">
						</div>
					</td>
				</tr>
			</table>
<?php
			# Forums in group
			$forums = spa_get_forums_in_group($group->group_id);
			if ($forums) {
				# display the current forum information for each forum in table format
?>
				<div id="forum-group-<?php echo $group->group_id; ?>">
				<table class="wp-list-table widefat">
					<tr>
						<th style="text-align:center;width:2%" scope="col"><?php spa_etext('ID'); ?></th>
						<th style="text-align:center;width:5%" scope="col"><?php spa_etext('Icon'); ?></th>
						<th style="text-align:center;" scope="col"><?php echo spa_text('Forums in').' '.$group->group_name; ?></th>
					</tr>
				</table>
				<table class="wp-list-table widefat">
<?php
					spa_paint_group_forums($group->group_id, 0, '', 0);
?>
				</table>
				</div>

				<br /><br />
<?php
			} else {
				echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.spa_text('There are no forums defined in this group').'</div>';
			}
		}
	} else {
		echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.spa_text('There are no groups defined').'<br />';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;'.spa_text('Select').' <b>'.spa_text('Create New Group').'</b> '.spa_text('from the menu on the left to get started').'</div>';
	}
}

function spa_paint_group_forums($groupid, $parent, $parentname, $level) {
	$space = '<img class="subArrow" src="'.SFADMINIMAGES.'sp_SubforumLevel.png" alt="" />';
	$forums = spa_get_group_forums_by_parent($groupid, $parent);
	$noMembers = array();

	if ($forums) {
		$noMembers = spa_forums_check_memberships($forums);

		foreach ($forums as $forum) {
			$subforum = $forum->parent;
			$haschild = '';
			if ($forum->children) {
				$childlist = array(unserialize($forum->children));
				if (count($childlist) > 0) $haschild = $childlist;
			}

			if (empty($forum->forum_icon)) {
				$icon = SPTHEMEICONSURL.'sp_ForumIcon.png';
			} else {
				$icon = esc_url(SFCUSTOMURL.$forum->forum_icon);
				if (!file_exists(SFCUSTOMDIR.$forum->forum_icon)) {
					$icon = SPTHEMEICONSURL.'sp_ForumIcon.png';
				}
			}
			$rowClass = (in_array($forum->forum_id, $noMembers)) ? ' class="spWarningBG"' : '';
?>
			<tr id="forumrow-<?php echo $forum->forum_id; ?>" <?php echo $rowClass; ?>> <!-- display forum information for each forum -->
			<td style="text-align:center;width:2%"><?php echo $forum->forum_id; ?></td>

			<td style="text-align:center;padding:8px 0px;width:5%"><?php echo '<img src="'.$icon.'" alt="" title="'.spa_text('Current forum icon').'" />'; ?>

<?php			if ($haschild) { ?>
					<br /><img class="parentArrow" src="<?php echo SFADMINIMAGES.'sp_HasChild.png'; ?>" alt="" title="<?php spa_etext('Parent Forum'); ?>" />
<?php			} ?>
			</td>

			<td>
				<div class="sp-half-row-left">
					<?php if ($forum->forum_status) echo '<img class="sfalignright" src="'.SFADMINIMAGES.'sp_LockedBig.png" alt="" />'; ?>
<?php
					if ($subforum) { ?>
						<?php if ($forum->forum_disabled) echo '<img class="sfalignright" src="'.SFADMINIMAGES.'sp_NoWrite.png" alt="" title="'.spa_text('Subforum is disabled').'" /> '; ?>
						<?php echo str_repeat($space, ($level - 1));
?>
						<img class="subArrow" src="<?php echo SFADMINIMAGES.'sp_Subforum.png'; ?>" alt="" title="<?php spa_etext('Subforum'); ?>" />
						<div class='row-title'><strong><?php echo sp_filter_title_display($forum->forum_name); ?></strong></div><div>(<?php echo spa_text('Subforum of').': '.$parentname.')'; ?></div><div><?php echo sp_filter_text_display($forum->forum_desc); ?></div>
                    <?php } else { ?>
						<?php if ($forum->forum_disabled) echo '<img class="sfalignright" src="'.SFADMINIMAGES.'sp_NoWrite.png" alt="" title="'.spa_text('Forum is disabled').'" /> '; ?>
						<div class='row-title'><strong><?php echo sp_filter_title_display($forum->forum_name); ?></strong></div><div><?php echo sp_filter_text_display($forum->forum_desc); ?></div>
                    <?php } ?>
<?php
					if (in_array($forum->forum_id, $noMembers)) {
						echo '<p><b>'.spa_text('Warning - There are no usergroups with members that have permission to use this forum').'</b></p>';
					}

				sp_display_item_stats(SFTOPICS, 'forum_id', $forum->forum_id, spa_text('Topics'));
				echo ' | ';
	 			sp_display_item_stats(SFPOSTS, 'forum_id', $forum->forum_id, spa_text('Posts'));
?>
				</div>
				<div class="sp-half-row-right">
<?php
		            $base = wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader');
					$target = "forum-$forum->forum_id";
					$image = SFADMINIMAGES;
?>
					<input id="sfreloadpb<?php echo $forum->forum_id; ?>" type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Forum Permissions'); ?>" data-form="forumperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open="" />
					<input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Edit This Forum'); ?>" data-form="editforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open="" />
					<input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Delete Forum'); ?>" data-form="deleteforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open="" />
        	    	<?php if ($forum->forum_disabled) { ?>
						<input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Enable Forum'); ?>" data-form="enableforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open="" />
		            <?php } else { ?>
        		        <input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Disable Forum'); ?>" data-form="disableforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open="" />
		            <?php } ?>
	        	</div>
			</td>
			</tr>

			<tr class="sfinline-form">  <!-- This row will hold ajax forms for the current forum -->
    			<td colspan="3" style="padding: 0;border-bottom:1px solid #dddddd">
                    <div id="forum-<?php echo $forum->forum_id; ?>"></div>
    			</td>
			</tr>
<?php
			if ($haschild) {
				$newlevel = $level + 1;
				spa_paint_group_forums($groupid, $forum->forum_id, $forum->forum_name, $newlevel);
			}
		}
	}
}

function spa_forums_check_memberships($forums) {
	$value = sp_get_sfmeta('default usergroup', 'sfguests');
	$ugid = spdb_table(SFUSERGROUPS, "usergroup_id={$value[0]['meta_value']}", 'usergroup_id');
	if (empty($ugid)) $ugid = 0;
	$noMembers = array();
	foreach ($forums as $forum) {
		$has_members = false;
		$permissions = sp_get_forum_permissions($forum->forum_id);
		if ($permissions) {
			foreach ($permissions as $permission) {
				$members = spdb_table(SFMEMBERSHIPS, "usergroup_id= $permission->usergroup_id", 'row', '', '1');
				if ($members || $permission->usergroup_id == $ugid) {
					$has_members = true;
					break;
				}
			}
		}
		if (!$has_members) $noMembers[] = $forum->forum_id;
	}
	return $noMembers;
}

?>