<?php
/*
Simple:Press
Admin User Groups Main Display
$LastChangedDate: 2016-10-23 14:40:24 -0500 (Sun, 23 Oct 2016) $
$Rev: 14666 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_usergroups_usergroup_main() {
	global $spPaths;
	$usergroups = spa_get_usergroups_all(null);
	$defaults = spa_get_mapping_data();

	if ($usergroups) {
?>
			<table class="wp-list-table widefat">
				<tr>
					<th style="text-align:center;width:13%" scope="col"><?php spa_etext('ID'); ?></th>
					<th scope="col"><?php spa_etext('Name'); ?></th>
					<th style="text-align:center;width:8%" scope="col"><?php spa_etext('Moderator'); ?></th>
				</tr>
			</table>
<?php

		foreach ($usergroups as $usergroup) {
			# display the current usergroup information in table format
?>
			<table id="usergrouprow-<?php echo($usergroup->usergroup_id); ?>" class="wp-list-table widefat">
				<tr>
					<td style="width:13%;text-align:center;padding:10px 0;" class='row-title BGhighLight'><?php echo $usergroup->usergroup_id; ?>
					<br>
<?php
					if($usergroup->usergroup_badge) {
						echo "<img src='".SF_STORE_URL.'/'.$spPaths['ranks'].'/'.$usergroup->usergroup_badge."' alt='' style='max-width:80%;' />";
					}
?>
					</td>
<?php
if ($usergroup->usergroup_id == $defaults['sfdefgroup']) {
	$defLabel = '&nbsp;&nbsp;('.spa_text('Default usergroup for new members').')';
} elseif ($usergroup->usergroup_id == $defaults['sfguestsgroup']) {
	$defLabel = '&nbsp;&nbsp;('.spa_text('Default usergroup for guests').')';
} else {
	$defLabel = '';
}
?>
					<td><span class='row-title'><strong><?php echo sp_filter_title_display($usergroup->usergroup_name); ?></strong><?php echo($defLabel); ?></span><span><br /><?php echo sp_filter_title_display($usergroup->usergroup_desc); ?></span>

<?php
					sp_display_item_stats(SFMEMBERSHIPS, 'usergroup_id', $usergroup->usergroup_id, spa_text('Members'));
?>
					</td>
					<td style="width:8%;text-align:center;padding:10px 0;" class='row-title'><?php if ($usergroup->usergroup_is_moderator == 1) echo spa_etext("Yes"); else echo spa_etext("No"); ?></td>
				</tr>

				<tr>
					<td class='smallLabel'><?php spa_etext('Manage Group'); ?></td>
					<td colspan="2" style="padding:0 0 0 3px;text-align:left;">
<?php
                        $base = wp_nonce_url(SPAJAXURL.'usergroups-loader', 'usergroups-loader');
						$target = "usergroup-$usergroup->usergroup_id";
						$image = SFADMINIMAGES;
?>
						<input type="button" class="button-secondary spLoadForm" value="<?php echo spa_text('Edit User Group'); ?>" data-form="editusergroup" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $usergroup->usergroup_id; ?>" data-open="" />
						<input type="button" class="button-secondary spLoadForm" value="<?php echo spa_text('Delete User Group'); ?>" data-form="delusergroup" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $usergroup->usergroup_id; ?>" data-open="" />

						<?php sp_paint_usergroup_tip($usergroup->usergroup_id, sp_filter_title_display($usergroup->usergroup_name)); ?>

					</td>
				</tr>

				<tr class="sfinline-form"> <!-- This row will hold ajax forms for the current user group -->
				  	<td colspan="3" style="padding:0 10px 0 0;">
						<div id="usergroup-<?php echo $usergroup->usergroup_id; ?>">
						</div>
					</td>
				</tr>
				<tr class="sfsubtable sfugrouptable">
					<td class='smallLabel'><?php spa_etext('Manage Users'); ?></td>
					<td colspan="2" style="padding:0 0 0 3px;text-align:left;">
<?php
                        $site = wp_nonce_url(SPAJAXURL."usergroups&amp;ug=$usergroup->usergroup_id", 'usergroups');
						$gif= SFCOMMONIMAGES.'working.gif';
						$text = esc_js(spa_text('Show/Hide'));
?>
						<input type="button" id="show<?php echo $usergroup->usergroup_id; ?>" class="button-secondary spUsergroupShowMembers" value="<?php echo $text; ?>" data-url="<?php echo $site; ?>" data-img="<?php echo $gif; ?>" data-id="<?php echo $usergroup->usergroup_id; ?>" />
<?php
                        $base = wp_nonce_url(SPAJAXURL.'usergroups-loader', 'usergroups-loader');
						$target = "members-$usergroup->usergroup_id";
						$image = SFADMINIMAGES;
?>
						<input type="button" id="add<?php echo $usergroup->usergroup_id; ?>" class="button-secondary spLoadForm" value="<?php spa_etext('Add New'); ?>" data-form="addmembers" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $usergroup->usergroup_id; ?>" data-open="" />
						<input type="button" id="remove<?php echo $usergroup->usergroup_id; ?>" class="button-secondary spLoadForm" value="<?php spa_etext('Move/Delete'); ?>" data-form="delmembers" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $usergroup->usergroup_id; ?>" data-open="" />
					</td>
				</tr>
				<tr class="sfinline-form"> <!-- This row will hold hidden forms for the current user group membership-->
				  	<td colspan="3" style="padding: 0 10px 0 0;">
                        <div id="members-<?php echo $usergroup->usergroup_id; ?>"></div>
					</td>
				</tr>
			</table>
<?php
     	}
	} else {
		echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.spa_text('There are no User Groups defined').'</div>';
	}
?>
    <div class="sfform-panel-spacer"></div>
	<table class="sfmaintable" style="padding:0;border-spacing:0;border-collapse:separate;">
		<tr>
			<th style="text-align:left;padding:10px 20px;" scope="col"><?php spa_etext('Members Not Belonging To Any Usergroup') ?></th>
		</tr>
		<tr class="sfsubtable sfugrouptable">
			<td style="padding:10px 20px;">
<?php
                $site = wp_nonce_url(SPAJAXURL.'usergroups&amp;ug=0', 'usergroups');
				$gif= SFCOMMONIMAGES.'working.gif';
				$text = esc_js(spa_text('Show/Hide Members with No Memberships'));
				?>
				<input type="button" id="show-0" class="button-secondary spUsergroupShowMembers" value="<?php echo $text; ?>" data-url="<?php echo $site; ?>" data-img="<?php echo $gif; ?>" data-id="0" />
			</td>
		</tr>
		<tr class="sfinline-form"> <!-- This row will hold hidden forms for the current user group membership-->
		  	<td>
                <div id="members-0"></div>
			</td>
		</tr>
	</table>
<?php
}

function sp_paint_usergroup_tip($ugid, $ugname) {
	$site = wp_nonce_url(SPAJAXURL."usergroup-tip&amp;group=$ugid", 'usergroup-tip');
	$title = esc_js($ugname);
	echo "<input type='button' class='button-secondary spOpenDialog' value='".spa_text('User Group Usage')."' data-site='$site' data-label='$title' data-width='600' data-height='0' data-align='center' />";
}

?>