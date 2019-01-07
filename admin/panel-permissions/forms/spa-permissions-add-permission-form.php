<?php
/*
Simple:Press
Admin Permissions Add Permission Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_permissions_add_permission_form() {
?>
<script>
   	spj.loadAjaxForm('sfrolenew', 'sfreloadpb');
</script>
<?php
	# Get correct tooltips file
	$lang = spa_get_language_code();
	if (empty($lang)) $lang = 'en';
	$ttpath = SPHELP.'admin/tooltips/admin-permissions-tips-'.$lang.'.php';
	if (file_exists($ttpath) == false) $ttpath = SPHELP.'admin/tooltips/admin-permissions-tips-en.php';
	if (file_exists($ttpath)) require_once $ttpath;

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'permissions-loader&amp;saveform=addperm', 'permissions-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfrolenew" name="sfrolenew">
<?php
		echo sp_create_nonce('forum-adminform_rolenew');
		spa_paint_open_tab(SP()->primitives->admin_text('Permissions')." - ".SP()->primitives->admin_text('Add New Permission'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Add New Permission'), 'true', 'create-new-permission-set');

					spa_paint_input(SP()->primitives->admin_text('Permission Set Name'), "role_name", '', false, true);
					spa_paint_input(SP()->primitives->admin_text('Permission Set Description'), "role_desc", '', false, true);

					spa_paint_select_start(SP()->primitives->admin_text('Clone Existing Permission Set'), 'role', 'role');
					spa_display_permission_select('', false);
					spa_paint_select_end('<small>('.SP()->primitives->admin_text('Select an existing Permission Set to Clone.  Any settings below will be ignored.').')</small>');

?>
					<br /><p><strong><?php SP()->primitives->admin_etext('Permission Set Actions') ?>:</strong></p>
<?php
					echo '<p><img src="'.SPADMINIMAGES.'sp_GuestPerm.png" alt="" style="width:16px;height:16px;vertical-align:top" />';
					echo '<small>&nbsp;'.SP()->primitives->admin_text('Note: Action settings displaying this icon will be ignored for Guest Users').'</small>';
					echo '&nbsp;&nbsp;&nbsp;<img src="'.SPADMINIMAGES.'sp_GlobalPerm.png" alt="" style="width:16px;height:16px;vertical-align:top" />';
					echo '<small>&nbsp;'.SP()->primitives->admin_text('Note: Action settings displaying this icon require enabling to use').'</small>';
					echo '&nbsp;&nbsp;&nbsp;<img src="'.SPADMINIMAGES.'sp_Warning.png" alt="" style="width:16px;height:16px;vertical-align:top" />';
					echo '<small>&nbsp;'.SP()->primitives->admin_text('Note: Action settings displaying this icon should be used with great care').'</small></p>';

					sp_build_site_auths_cache();

					$sql = 'SELECT auth_id, auth_name, auth_cat, authcat_name, warning FROM '.SPAUTHS.'
							JOIN '.SPAUTHCATS.' ON '.SPAUTHS.'.auth_cat = '.SPAUTHCATS.'.authcat_id
							WHERE active = 1
							ORDER BY auth_cat, auth_id';
					$authlist = SP()->DB->select($sql);

					$firstitem = true;
					$category = '';
?>
					<!-- OPEN OUTER CONTAINER DIV -->
					<div class="outershell" style="width: 100%;">
<?php
					foreach ($authlist as $a) {
						if ($category != $a->authcat_name) {
							$category = $a->authcat_name;
							if (!$firstitem) {
?>
								<!-- CLOSE DOWN THE ENDS -->
								</table></div>
<?php
                    		}
?>
							<!-- OPEN NEW INNER DIV -->
							<div class="innershell">
							<!-- NEW INNER DETAIL TABLE -->
							<table style="width:100%;border:0">
							<tr><td colspan="2" class="permhead"><?php SP()->primitives->admin_etext($category); ?></td></tr>
<?php
							$firstitem = false;
						}

						$auth_id = $a->auth_id;
						$auth_name = $a->auth_name;
						$authWarn = (empty($a->warning)) ? false : true;
						$warn = ($authWarn) ? " permwarning" : '';
						$tip = ($authWarn) ? " class='permwarning' title='".esc_js(SP()->primitives->admin_text($a->warning))."'" : '';

						$button = 'b-'.$auth_id;
						if (SP()->core->forumData['auths'][$auth_id]->ignored || SP()->core->forumData['auths'][$auth_id]->enabling || $authWarn) {
							$span = '';
						} else {
							$span = ' colspan="2" ';
						}

?>
							<tr<?php echo $tip; ?>>
								<td class="permentry<?php echo $warn; ?>">

								<input type="checkbox" name="<?php echo $button; ?>" id="sf<?php echo $button; ?>"  />
								<label for="sf<?php echo $button; ?>" class="sflabel">
								<img style="text-align:top;float: right; border: 0 none ; margin: -4px 5px 0 3px; padding: 0;" class="" title="<?php echo $tooltips[$auth_name]; ?>" src="<?php echo SPADMINIMAGES; ?>sp_Information.png" alt="" />
								<?php SP()->primitives->admin_etext(SP()->core->forumData['auths'][$auth_id]->auth_desc); ?></label>
								<?php if ($span == '') { ?>
									<td style="text-align:center;width:32px" class="permentry">
<?php
                                }
								if ($span == '') {
									if (SP()->core->forumData['auths'][$auth_id]->enabling) {
										echo '<img src="'.SPADMINIMAGES.'sp_GlobalPerm.png" alt="" style="width:16px;height:16px" title="'.SP()->primitives->admin_text('Requires Enabling').'" />';
									}
									if (SP()->core->forumData['auths'][$auth_id]->ignored) {
										echo '<img src="'.SPADMINIMAGES.'sp_GuestPerm.png" alt="" style="width:16px;height:16px" title="'.SP()->primitives->admin_text('Ignored for Guests').'" />';
									}
									if ($authWarn) {
										echo '<img src="'.SPADMINIMAGES.'sp_Warning.png" alt="" style="width:16px;height:16px" title="'.SP()->primitives->admin_text('Use with Caution').'" />';
									}
									echo '</td>';
								} else {
?>
								    </td><td class="permentry" style="width:32px"></td>
                                <?php } ?>
							</tr>
                        <?php } ?>
					<!-- END CONTAINER DIV -->
					</table></div><div class="clearboth"></div>
					</div>
<?php
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_perm_add_perm_panel');
		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Create New Permission'); ?>" />
	</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
