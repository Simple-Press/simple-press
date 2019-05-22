<?php
/*
Simple:Press
Admin Permissions Main Display
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_permissions_permission_main() {
	$roles = sp_get_all_roles();
	if ($roles) {
		# display the permission set roles in table format
?>
		<table class="wp-list-table widefat">
			<tr>
				<th style="text-align:center;width:9%" scope="col"><?php SP()->primitives->admin_etext('ID'); ?></th>
				<th scope="col"><?php SP()->primitives->admin_etext('Name'); ?></th>
				<th scope="col"><?php SP()->primitives->admin_etext('Name'); ?></th>
			</tr>
		</table>
<?php
			foreach ($roles as $role) {
?>
		<table id="rolerow-<?php echo($role->role_id); ?>" class="wp-list-table widefat">
			<tr>
				<td style="width:9%;text-align:center;padding:0;" class='row-title'><?php echo $role->role_id; ?></td>
				<td><span class='row-title'><strong><?php echo SP()->displayFilters->title($role->role_name); ?></strong></span><span><br /><?php echo SP()->displayFilters->title($role->role_desc); ?></span></td>
			</tr>

			<tr>
				<td class='smallLabel'><?php SP()->primitives->admin_etext("Manage Permissions") ?></td>
				<td style="padding:0 0 0 3px;text-align:left;">
<?php
					$base = wp_nonce_url(SPAJAXURL.'permissions-loader', 'permissions-loader');
					$target = 'perm-'.$role->role_id;
					$image = SPADMINIMAGES;
?>
					<input type="button" class="sf-button-secondary spLoadForm" value="<?php echo SP()->primitives->admin_text('Edit Permission'); ?>" data-form="editperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $role->role_id; ?>" data-open="" />
					<input type="button" class="sf-button-secondary spLoadForm" value="<?php echo SP()->primitives->admin_text('Delete Permission'); ?>" data-form="delperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $role->role_id; ?>" data-open="" />

					<?php sp_paint_permission_tip($role->role_id, SP()->displayFilters->title($role->role_name)); ?>

				</td>
			</tr>
			<tr class="sfinline-form"> <!-- This row will hold ajax forms for the current permission set -->
			  	<td colspan="2" style="padding: 0 10px 0 0;">
					<div id="perm-<?php echo $role->role_id; ?>">
					</div>
				</td>
			</tr>
		</table>
<?php	} ?>
		<br />
<?php
	} else {
		echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.SP()->primitives->admin_text('There are no Permission Sets defined.').'</div>';
	}
}

function sp_paint_permission_tip($roleid, $rolename) {
	$site = wp_nonce_url(SPAJAXURL."permission-tip&amp;role=$roleid", 'permission-tip');
	$title = esc_js($rolename);
	echo "<input type='button' class='sf-button-secondary spOpenDialog' value='".SP()->primitives->admin_text('Permission Usage')."' data-site='$site' data-label='$title' data-width='600' data-height='0' data-align='center' />";
}
