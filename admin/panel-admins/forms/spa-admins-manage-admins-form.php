<?php
/*
Simple:Press
Admin Admins Current Admins Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_admins_manage_admins_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfupdatecaps', 'sfreloadma');
    	spjAjaxForm('sfaddadmins', 'sfreloadma');
    });
</script>
<?php
	global $spThisUser, $spGlobals;

	$adminsexist = false;
	$adminrecords = $spGlobals['forum-admins'];

	# get all the moderators
	$modrecords = array();
	$mods = spdb_table(SFMEMBERS, 'moderator=1');
	if ($mods) {
		foreach ($mods as $mod) {
			$modrecords[$mod->user_id] = $mod->display_name;
		}
	}

	spa_paint_options_init();

	if ($adminrecords || $modrecords) {
		$adminsexist = true;

        $ajaxURL = wp_nonce_url(SPAJAXURL.'admins-loader&amp;saveform=manageadmin', 'admins-loader');
		?>
		<form action="<?php echo $ajaxURL; ?>" method="post" id="sfupdatecaps" name="sfupdatecaps">
		<?php echo sp_create_nonce('forum-adminform_sfupdatecaps'); ?>
<?php
		spa_paint_open_tab(spa_text('Admins')." - ".spa_text('Manage Admins and Moderators'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Current Admins and Moderators'), 'true', 'manage-admins');
					for ($x = 1; $x < 3; $x++) {
						$records = ($x == 1) ? $adminrecords : $modrecords;
						if (empty($records)) continue;

						foreach ($records as $adminId => $adminName) {
							$user = new WP_User($adminId);
							$manage_opts = $user->has_cap('SPF Manage Options') ? 1 : 0;
							$manage_forums = $user->has_cap('SPF Manage Forums') ? 1 : 0;
							$manage_ugs = $user->has_cap('SPF Manage User Groups') ? 1 : 0;
							$manage_perms = $user->has_cap('SPF Manage Permissions') ? 1 : 0;
							$manage_comps = $user->has_cap('SPF Manage Components') ? 1 : 0;
							$manage_users = $user->has_cap('SPF Manage Users') ? 1 : 0;
							$manage_profiles = $user->has_cap('SPF Manage Profiles') ? 1 : 0;
							$manage_admins = $user->has_cap('SPF Manage Admins') ? 1 : 0;
							$manage_tools = $user->has_cap('SPF Manage Toolbox') ? 1 : 0;
							$manage_plugins = $user->has_cap('SPF Manage Plugins') ? 1 : 0;
							$manage_themes = $user->has_cap('SPF Manage Themes') ? 1 : 0;
							$manage_integration = $user->has_cap('SPF Manage Integration') ? 1 : 0;

							$title = ($x == 1) ? spa_text('Admin') : spa_text('Moderator');
							spa_paint_open_fieldset($title.': '.$adminName, false);

								echo spa_text('ID').': '.$adminId.' - '.spa_text('Name').': <strong>'.$adminName.'</strong>';
?>
								<input type="hidden" name="uids[]" value="<?php echo $adminId; ?>" />

								<ul class='floatList'>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Options'), 'manage-opts['.$adminId.']', $manage_opts, $adminId); ?>
										<input type="hidden" name="old-opts[<?php echo $adminId; ?>]" value="<?php echo $manage_opts; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Forums'), 'manage-forums['.$adminId.']', $manage_forums, $adminId); ?>
										<input type="hidden" name="old-forums[<?php echo $adminId; ?>]" value="<?php echo $manage_forums; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage User Groups'), 'manage-ugs['.$adminId.']', $manage_ugs, $adminId); ?>
										<input type="hidden" name="old-ugs[<?php echo $adminId; ?>]" value="<?php echo $manage_ugs; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Permissions'), 'manage-perms['.$adminId.']', $manage_perms, $adminId); ?>
										<input type="hidden" name="old-perms[<?php echo $adminId; ?>]" value="<?php echo $manage_perms; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Components'), 'manage-comps['.$adminId.']', $manage_comps, $adminId); ?>
										<input type="hidden" name="old-comps[<?php echo $adminId; ?>]" value="<?php echo $manage_comps; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Plugins'), 'manage-plugins['.$adminId.']', $manage_plugins, $adminId); ?>
										<input type="hidden" name="old-plugins[<?php echo $adminId; ?>]" value="<?php echo $manage_plugins; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Users'), 'manage-users['.$adminId.']', $manage_users, $adminId); ?>
										<input type="hidden" name="old-users[<?php echo $adminId; ?>]" value="<?php echo $manage_users; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Toolbox'), 'manage-tools['.$adminId.']', $manage_tools, $adminId); ?>
										<input type="hidden" name="old-tools[<?php echo $adminId; ?>]" value="<?php echo $manage_tools; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Profiles'), 'manage-profiles['.$adminId.']', $manage_profiles, $adminId); ?>
										<input type="hidden" name="old-profiles[<?php echo $adminId; ?>]" value="<?php echo $manage_profiles; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Themes'), 'manage-themes['.$adminId.']', $manage_themes, $adminId); ?>
										<input type="hidden" name="old-themes[<?php echo $adminId; ?>]" value="<?php echo $manage_themes; ?>" />
									</li>
									<li>
										<?php spa_render_caps_checkbox(spa_text('Manage Integration'), 'manage-integration['.$adminId.']', $manage_integration, $adminId); ?>
										<input type="hidden" name="old-integration[<?php echo $adminId; ?>]" value="<?php echo $manage_integration; ?>" />
									</li>
									<li>
<?php
										if ($adminId == $spThisUser->ID) {
?>
                                            <span class='floatListLabel'><?php echo spa_text('Manage Admins'); ?></span>
											<input type="hidden" name="manage-admins[<?php echo $adminId ?>]" value="<?php echo $manage_admins; ?>" />
											<img src="<?php echo SFADMINIMAGES.'sp_Locked.png'; ?>" alt="" style="vertical-align:middle;padding:0 0 0 10px;margin:3px 0;" />
<?php
										} else {
											spa_render_caps_checkbox(spa_text('Manage Admins'), 'manage-admins['.$adminId.']', $manage_admins, $adminId);
										}
?>
										<input type="hidden" name="old-admins[<?php echo $adminId ?>]" value="<?php echo $manage_admins; ?>" />
									</li>
<?php
								    do_action('sph_admin_caps_list', $user);
?>      						</ul>

								<div class="clearboth"></div>
<?php
								if ($adminId != $spThisUser->ID) {
									echo '<hr />';
									spa_render_caps_checkbox(spa_text('Remove All Capabilities from this').' '.$title, 'remove-admin['.$adminId.']', '', $adminId);
								}
							spa_paint_close_fieldset();
						}
					}
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_close_container();
	}
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="savecaps" name="savecaps" value="<?php spa_etext('Update Admin Capabilities'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
    $ajaxURL = wp_nonce_url(SPAJAXURL.'admins-loader&amp;saveform=addadmin', 'admins-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfaddadmins" name="sfaddadmins">
	<?php echo sp_create_nonce('forum-adminform_sfaddadmins'); ?>

<?php
	spa_paint_open_tab(spa_text('Manage Admins').' - '.spa_text('Add Admins'), true);

	spa_paint_open_panel();
	spa_paint_open_fieldset(spa_text('Add New Admins'), false);
?>
	<table style="text-align:center;padding:0;border-spacing:0;border-collapse:separate;" class="forum-table">
		<tr>
			<th style="text-align:center"><?php spa_etext('Select New Admin Users'); ?></th>
		</tr>
		<tr>
			<td style="text-align:center">
				<p style="text-align:center"><?php spa_etext('Select members to make Admins (use CONTROL for multiple users)'); ?></p>
<?php
            	$from = esc_js(spa_text('Eligible Members'));
            	$to = esc_js(spa_text('Selected Members'));
                $action = 'addadmin';
            	include_once SF_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-multiselect.php';
?>
				<div class="clearboth"></div>
			</td>
		</tr>
	</table>

	<p><strong><?php spa_etext('Select New Admin Capabilities'); ?></strong></p>

	<ul class='floatList'>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Options'), 'add-opts', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Forums'), 'add-forums', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage User Groups'), 'add-ugs', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Permissions'), 'add-perms', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Components'), 'add-comps', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Users'), 'add-users', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Profiles'), 'add-profiles', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Admins'), 'add-admins', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Toolbox'), 'add-tools', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Plugins'), 'add-plugins', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Themes'), 'add-themes', 0); ?></li>
		<li><?php spa_render_caps_checkbox(spa_text('Manage Integration'), 'add-integration', 0); ?></li>
		<?php do_action('sph_admin_caps_form', $user); ?>
	</ul>
<?php
		spa_paint_close_fieldset();
		spa_paint_close_panel();
	spa_paint_open_panel();

	spa_paint_open_fieldset(spa_text('WP Admins but not Forum Admins'), false);
?>
	<table style="text-align:center;width:auto;padding:0;border-spacing:0;border-collapse:separate;" class="sfmaintable">
		<tr>
			<th style="text-align:center;width:30px" scope="col"></th>
			<th style="text-align:center"><?php spa_etext('User ID'); ?></th>
			<th style="text-align:center" scope="col"><?php spa_etext('Admin Name'); ?></th>
			<th style="text-align:center;width:30px" scope="col"></th>
		</tr>
<?php
        $args = array(
            'role'      => 'administrator',
            'fields'    => array('ID', 'display_name'),
        );
		$wp_admins = get_users($args);

		$is_users = false;
		foreach ($wp_admins as $admin) {
			if (!sp_is_forum_admin($admin->ID)) {
				echo '<tr>';
				echo '<td></td>';
				echo '<td style="text-align:center">';
				echo $admin->ID;
				echo '</td>';
				echo '<td>';
				echo esc_html($admin->display_name);
				echo '</td>';
				echo '<td></td>';
				echo '</tr>';
				$is_users = true;
			}
		}
		if (!$is_users) {
			echo '<tr>';
			echo '<td></td>';
			echo '<td colspan="2">';
			spa_etext('No WP administrators that are not SPF admins were found');
			echo '</td>';
			echo '<td></td>';
			echo '</tr>';
		}
?>
	</table>
<?php
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_admins_manage_panel');

		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="savenew" name="savenew" value="<?php spa_etext('Add New Admins'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}

function spa_render_caps_checkbox($label, $name, $value, $user=0, $disabled=false) {
	$pos = strpos($name, '[');
	if ($pos) $thisid = substr($name, 0, $pos).$user; else $thisid = $name.$user;
	echo "<input type='checkbox' name='$name' id='sf-$thisid' ";
	if ($value) echo 'checked="checked" ';
	if ($disabled) echo 'disabled="disabled" ';
	echo '/>';
	echo "<label for='sf-$thisid'>$label</label>";
}
?>