<?php
/*
Simple:Press
Admin Permissions Edit Permission Form
$LastChangedDate: 2018-08-05 11:33:29 -0500 (Sun, 05 Aug 2018) $
$Rev: 15685 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

# function to display the edit permission set form.  It is hidden until the edit permission set link is clicked
function spa_permissions_edit_permission_form($role_id) {
?>
<script>
   	spj.loadAjaxForm('sfroleedit<?php echo esc_js($role_id);   ?>', 'sfreloadpb');
</script>
<?php
	# Get correct tooltips file
	$lang = spa_get_language_code();
	if (empty($lang)) $lang = 'en';
	$ttpath = SPHELP.'admin/tooltips/admin-permissions-tips-'.$lang.'.php';
	if (file_exists($ttpath) == false) $ttpath = SPHELP.'admin/tooltips/admin-permissions-tips-en.php';
	if (file_exists($ttpath)) require_once $ttpath;

	$role = spa_get_role_row($role_id);

	spa_paint_options_init();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'permissions-loader&amp;saveform=editperm', 'permissions-loader');

?>
	<form action="<?php echo esc_attr($ajaxURL); ?>" method="post" id="sfroleedit<?php echo esc_attr($role->role_id); ?>" name="sfroleedit<?php echo esc_attr($role->role_id); ?>">
<?php
		sp_echo_create_nonce('forum-adminform_roleedit');
		spa_paint_open_tab(SP()->primitives->admin_text('Permissions').' - '.SP()->primitives->admin_text('Manage Permissions'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Edit Permission'), 'true', 'edit-master-permission-set');
				?>
					<input type="hidden" name="role_id" value="<?php echo esc_attr($role->role_id); ?>" />
<?php
					spa_paint_input(SP()->primitives->admin_text('Permission Set Name'), 'role_name', SP()->displayFilters->title($role->role_name), false, true);
					spa_paint_input(SP()->primitives->admin_text('Permission Set Description'), 'role_desc', SP()->displayFilters->title($role->role_desc), false, true);
?>
					<br /><h4><?php SP()->primitives->admin_etext("Permission Set Actions") ?>:</h4>

                    <div class="sf-alert-block sf-info">
                        <p><span class="sf-icon sf-ignore-guest sf-red sf-small"></span>
                            <?php SP()->primitives->admin_etext('Ignored for Guest Users') ?></p>
                        <p><span class="sf-icon sf-requires-enable sf-green sf-small"></span>
                            <?php SP()->primitives->admin_etext('Require enabling to use') ?></p>
                        <p><span class="sf-icon sf-warning sf-yellow sf-small"></span>
                            <?php SP()->primitives->admin_etext('Use with great care') ?></p>
                    </div>
<?php


					sp_build_site_auths_cache();

					$sql = 'SELECT auth_id, auth_name, auth_cat, authcat_name, warning FROM '.SPAUTHS.'
							JOIN '.SPAUTHCATS.' ON '.SPAUTHS.'.auth_cat = '.SPAUTHCATS.'.authcat_id
							WHERE active = 1
							ORDER BY auth_cat, auth_id';
					$authlist = SP()->DB->select($sql);

					$role_auths = maybe_unserialize($role->role_auths);

					$firstitem = true;
					$category = '';
?>
       				<!-- OPEN OUTER CONTAINER DIV -->
					<div class="outershell">
<?php
					foreach ($authlist as $a) {
						if ($category != $a->authcat_name) {
							$category = $a->authcat_name;
							if (!$firstitem) {
?>
								<!-- CLOSE DOWN THE ENDS -->
								</div>
<?php
							}
?>
							<!-- OPEN NEW INNER DIV -->
							<div class="innershell">
							<!-- NEW INNER DETAIL TABLE -->
							<h4 class="sf-mt-15 sf-mb-15"><?php SP()->primitives->admin_etext($category); ?></h4>
<?php
							$firstitem = false;
						}


						$auth_id = $a->auth_id;
						$auth_name = $a->auth_name;
						$authWarn = (empty($a->warning)) ? false : true;
						$tip = ($authWarn) ? " class='permwarning' title='".esc_js(SP()->primitives->admin_text($a->warning))."'" : '';
						$tooltip = $tooltips[$auth_name] ?? '';

						$button = 'b-'.$auth_id;
                        $checked = (isset($role_auths[$auth_id]) && $role_auths[$auth_id]) ? ' checked="checked"' : '' ;

?>
						<div <?php echo esc_attr($tip); ?>>
							<div class="permentry">
								<input type="checkbox" name="<?php echo esc_attr($button); ?>" id="sfR<?php echo esc_attr($role->role_id.$button); ?>"<?php echo esc_attr($checked); ?>  />
                                <label for="sfR<?php echo esc_attr($role->role_id . $button); ?>" class="sflabel">
                                    <img style="float: right; margin-left: 10px; margin-right: 10px; width: 20px;" title="<?php echo esc_attr($tooltip); ?>" src="<?php echo esc_attr(SPADMINIMAGES . 'sp_Information.png');?>" />
                                    <?php SP()->primitives->admin_etext(SP()->core->forumData['auths'][$auth_id]->auth_desc); ?>
                                </label>
<?php
									if (SP()->core->forumData['auths'][$auth_id]->enabling) {
										echo '<img src="'.esc_attr(SPADMINIMAGES.'sp_GlobalPerm.png').'" alt="" style="width:16px;height:16px" title="'.esc_attr(SP()->primitives->admin_text('Requires Enabling')).'" />';
									}
									if (SP()->core->forumData['auths'][$auth_id]->ignored) {
										echo '<img src="'.esc_attr(SPADMINIMAGES.'sp_GuestPerm.png').'" alt="" style="width:16px;height:16px" title="'.esc_attr(SP()->primitives->admin_text('Ignored for Guests')).'" />';
									}
									if ($authWarn) {
										echo '<img src="'.esc_attr(SPADMINIMAGES.'sp_Warning.png').'" alt="" style="width:16px;height:16px" title="'.esc_attr(SP()->primitives->admin_text('Use with Caution')).'" />';
									}
?>
                            </div>
                        </div>
<?php
					}
?>
					<!-- END CONTAINER DIV -->
					</div>
<?php
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_perm_edit_perm_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="sfpermedit<?php echo esc_attr($role->role_id); ?>" name="sfpermedit<?php echo esc_attr($role->role_id); ?>" value="<?php SP()->primitives->admin_etext('Update Permission'); ?>" />
		<input type="button" class="sf-button-primary spCancelForm" data-target="#perm-<?php echo esc_attr($role->role_id); ?>" id="sfpermedit<?php echo esc_attr($role->role_id); ?>" name="editpermcancel<?php echo esc_attr($role->role_id); ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
		</form>
	<?php spa_paint_close_tab(); ?>

	<div class="sfform-panel-spacer"></div>
<?php
}
