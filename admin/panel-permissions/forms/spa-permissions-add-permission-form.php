<?php
/*
Simple:Press
Admin Permissions Add Permission Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_permissions_add_permission_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfrolenew', 'sfreloadpb');
    });
</script>
<?php
	# Get correct tooltips file
	$lang = spa_get_language_code();
	if (empty($lang)) $lang = 'en';
	$ttpath = SPHELP.'admin/tooltips/admin-permissions-tips-'.$lang.'.php';
	if (file_exists($ttpath) == false) $ttpath = SPHELP.'admin/tooltips/admin-permissions-tips-en.php';
	if (file_exists($ttpath)) include_once($ttpath);

	global $spGlobals;
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'permissions-loader&amp;saveform=addperm', 'permissions-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfrolenew" name="sfrolenew">
<?php
		echo sp_create_nonce('forum-adminform_rolenew');
		spa_paint_open_tab(spa_text('Permissions')." - ".spa_text('Add New Permission'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Add New Permission'), 'true', 'create-new-permission-set');

					spa_paint_input(spa_text('Permission Set Name'), "role_name", '', false, true);
					spa_paint_input(spa_text('Permission Set Description'), "role_desc", '', false, true);

					spa_paint_select_start(spa_text('Clone Existing Permission Set'), 'role', 'role');
					spa_display_permission_select('', false);
					spa_paint_select_end('<small>('.spa_text('Select an existing Permission Set to Clone.  Any settings below will be ignored.').')</small>');

?>
					<br /><p><strong><?php spa_etext('Permission Set Actions') ?>:</strong></p>
<?php
					echo '<p><img src="'.SFADMINIMAGES.'sp_GuestPerm.png" alt="" style="width:16px;height:16px;vertical-align:top" />';
					echo '<small>&nbsp;'.spa_text('Note: Action settings displaying this icon will be ignored for Guest Users').'</small>';
					echo '&nbsp;&nbsp;&nbsp;<img src="'.SFADMINIMAGES.'sp_GlobalPerm.png" alt="" style="width:16px;height:16px;vertical-align:top" />';
					echo '<small>&nbsp;'.spa_text('Note: Action settings displaying this icon require enabling to use').'</small>';
					echo '&nbsp;&nbsp;&nbsp;<img src="'.SFADMINIMAGES.'sp_Warning.png" alt="" style="width:16px;height:16px;vertical-align:top" />';
					echo '<small>&nbsp;'.spa_text('Note: Action settings displaying this icon should be used with great care').'</small></p>';

					sp_build_site_auths_cache();

					$sql = 'SELECT auth_id, auth_name, auth_cat, authcat_name, warning FROM '.SFAUTHS.'
							JOIN '.SFAUTHCATS.' ON '.SFAUTHS.'.auth_cat = '.SFAUTHCATS.'.authcat_id
							WHERE active = 1
							ORDER BY auth_cat, auth_id';
					$authlist = spdb_select('set', $sql);

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
							<tr><td colspan="2" class="permhead"><?php spa_etext($category); ?></td></tr>
<?php
							$firstitem = false;
						}

						$auth_id = $a->auth_id;
						$auth_name = $a->auth_name;
						$authWarn = (empty($a->warning)) ? false : true;
						$warn = ($authWarn) ? " permwarning" : '';
						$tip = ($authWarn) ? " class='permwarning' title='".esc_js(spa_text($a->warning))."'" : '';

						$button = 'b-'.$auth_id;
						if ($spGlobals['auths'][$auth_id]->ignored || $spGlobals['auths'][$auth_id]->enabling || $authWarn) {
							$span = '';
						} else {
							$span = ' colspan="2" ';
						}

?>
							<tr<?php echo $tip; ?>>
								<td class="permentry<?php echo $warn; ?>">

								<input type="checkbox" name="<?php echo $button; ?>" id="sf<?php echo $button; ?>"  />
								<label for="sf<?php echo $button; ?>" class="sflabel">
								<img style="text-align:top;float: right; border: 0pt none ; margin: -4px 5px 0px 3px; padding: 0;" class="" title="<?php echo $tooltips[$auth_name]; ?>" src="<?php echo SFADMINIMAGES; ?>sp_Information.png" alt="" />
								<?php spa_etext($spGlobals['auths'][$auth_id]->auth_desc); ?></label>
								<?php if ($span == '') { ?>
									<td style="text-align:center;width:32px" class="permentry">
<?php
                                }
								if ($span == '') {
									if ($spGlobals['auths'][$auth_id]->enabling) {
										echo '<img src="'.SFADMINIMAGES.'sp_GlobalPerm.png" alt="" style="width:16px;height:16px" title="'.spa_text('Requires Enabling').'" />';
									}
									if ($spGlobals['auths'][$auth_id]->ignored) {
										echo '<img src="'.SFADMINIMAGES.'sp_GuestPerm.png" alt="" style="width:16px;height:16px" title="'.spa_text('Ignored for Guests').'" />';
									}
									if ($authWarn) {
										echo '<img src="'.SFADMINIMAGES.'sp_Warning.png" alt="" style="width:16px;height:16px" title="'.spa_text('Use with Caution').'" />';
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
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Create New Permission'); ?>" />
	</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
?>