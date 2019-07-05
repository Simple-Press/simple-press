<?php
/*
Simple:Press
Admin Permissions Add Permission Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');
define('SP_PLUGIN_ICONS', SPADMINIMAGES.'../icons/');
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
		spa_paint_open_tab(SP()->primitives->admin_text('Add New Permission Set'), true);
			spa_paint_open_panel();
      ?><div class="sf-half-panel">
      <div class="sf-half-panel-title"><?php
      spa_paint_open_fieldset(SP()->primitives->admin_text('Permission Set Details'), 'true', 'create-new-permission-set');
      ?></div><div class="sf-half-panel-in"><?php
      spa_paint_input(SP()->primitives->admin_text('Set Name'), "role_name", '', false, true);
			spa_paint_wide_textarea('Set Description', 'role_desc', '','',4);
      spa_paint_select_start(SP()->primitives->admin_text('Clone Existing'), 'role', 'role');
      spa_display_permission_select('', false);
      spa_paint_select_end('<div class="text-small"><small>'.SP()->primitives->admin_text('Select an existing Permission Set to Clone').'</small></div>');
      ?></div><div class="sf-form-submit-bar">
	        <input type="submit" class="sf-button-primary view-non-mobile" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Create New Permission'); ?>" />
	      </div>
        
      </div>
      <div class="sf-half-panel">
        <div class="sf-half-panel-title"><?php
          spa_paint_open_fieldset(SP()->primitives->admin_text('Permission Set Actions'), 'true', 'create-new-permission-set');
      ?>
         </div><div class="sf-half-panel-in">
         <br/>
        <div class="sf-panel-warning view-non-mobile">
          <img src="<?=SPADMINIMAGES?>sp_GuestPerm.png" alt="" style="width:16px;height:16px;vertical-align:top" />
          <small>&nbsp;<?=SP()->primitives->admin_text('Action settings displaying this icon will be ignored for Guest Users')?></small>
        </div>
        <div class="sf-panel-warning view-mobile">
          <img src="<?=SPADMINIMAGES?>sp_GuestPerm.png" alt="" style="width:16px;height:16px;vertical-align:top" />
          <small>&nbsp;<?=SP()->primitives->admin_text('Ignored for Guest Users')?></small>
        </div>
        <div class="sf-panel-warning view-non-mobile">
          <img src="<?=SPADMINIMAGES?>sp_GlobalPerm.png" alt="" style="width:16px;height:16px;vertical-align:top" />
          <small>&nbsp;<?=SP()->primitives->admin_text('Action settings displaying this icon require enabling to use')?></small>
        </div>
        <div class="sf-panel-warning view-mobile">
          <img src="<?=SPADMINIMAGES?>sp_GlobalPerm.png" alt="" style="width:16px;height:16px;vertical-align:top" />
          <small>&nbsp;<?=SP()->primitives->admin_text('Require enabling to use')?></small>
        </div>
        <div class="sf-panel-warning view-non-mobile">
          <img src="<?=SPADMINIMAGES?>sp_Warning.png" alt="" style="width:16px;height:16px;vertical-align:top" />
          <small>&nbsp;<?=SP()->primitives->admin_text('Action settings displaying this icon should be used with great care')?></small>
        </div>
        <div class="sf-panel-warning view-mobile">
          <img src="<?=SPADMINIMAGES?>sp_Warning.png" alt="" style="width:16px;height:16px;vertical-align:top" />
          <small>&nbsp;<?=SP()->primitives->admin_text('Use with great care')?></small>
        </div>
<?php
					
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
					<div class="outershell" style="width: 100%;margin-top: 20px;border: 1px solid #ddd;border-radius: 5px;">
<?php
					foreach ($authlist as $a) {
						if ($category != $a->authcat_name) {
							$category = $a->authcat_name;
							if (!$firstitem) {
?>
								<!-- CLOSE DOWN THE ENDS -->
								</div></div>
<?php
                    		}
?>
							<!-- OPEN NEW INNER DIV -->
							<div class="innershell">
							<!-- NEW INNER DETAIL TABLE -->
              <div class="sp-permition-cat-title">
                <span style="text-align: left;padding-left:5px; "><?php SP()->primitives->admin_etext($category); ?></span>
                <span>
									<span class="sf-icon sf-collapse sf-permition-panel-collapse"></span>
									<span class="sf-icon sf-expand sf-permition-panel-expand"></span>
                </span>
              </div>
              <div class="sp-permition-body">
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
							<div<?php //echo $tip; ?> class="sp-permition-item">
								<span class="permentry<?php echo $warn; ?>" >

								<input type="checkbox" name="<?php echo $button; ?>" id="sf<?php echo $button; ?>"  />
								<label for="sf<?php echo $button; ?>" class="sflabel view-non-mobile">
								
								<?php SP()->primitives->admin_etext(SP()->core->forumData['auths'][$auth_id]->auth_desc); ?></label>
	<?php if ($span == '') { ?>
									<span style="text-align:center;width:32px" class="permentry"></span>
                  <img style="width:16px;height:16px; border: 0 none ;float:right; margin: 3px 3px 0 3px; padding: 0;" class="" title="<?php echo $tooltips[$auth_name]; ?>" src="<?php echo SPADMINIMAGES; ?>sp_Information.png" alt="" />
<?php
                                }
								if ($span == '') {
									if (SP()->core->forumData['auths'][$auth_id]->enabling) {
										echo '<img src="'.SPADMINIMAGES.'sp_GlobalPerm.png" alt="" style="float:right;margin: 3px 3px 0 3px;width:16px;height:16px" title="'.SP()->primitives->admin_text('Requires Enabling').'" />';
									}
									if (SP()->core->forumData['auths'][$auth_id]->ignored) {
										echo '<img src="'.SPADMINIMAGES.'sp_GuestPerm.png" alt="" style="float:right;margin: 3px 3px 0 3px;width:16px;height:16px" title="'.SP()->primitives->admin_text('Ignored for Guests').'" />';
									}
									if ($authWarn) {
										echo '<img src="'.SPADMINIMAGES.'sp_Warning.png" alt="" style="float:right;margin: 3px 3px 0 3px;width:16px;height:16px" title="'.SP()->primitives->admin_text('Use with Caution').'" />';
									}
									echo '</span>';
								} else {
?>
								    </span><span class="permentry" style="width:32px"></span>
                                <?php } ?>
                </span>
							
							</div>
                        <?php } ?>
              
					<!-- END CONTAINER DIV -->
					</div></div><div class="clearboth"></div>
					</div>
          </div>
<?php
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_perm_add_perm_panel');
		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar view-mobile">
		<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Create New Permission'); ?>" />
	</div>
	<?php spa_paint_close_tab(); ?>
		<!-- <input type="submit" class="sf-button-primary view-mobile" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Create New Permission'); ?>" /> -->
	</form>
	<div class="sfform-panel-spacer"></div>
  <script type="text/javascript">
<!--
	
//-->
(function(spj, $, undefined) {
  $(document).ready(function() {
    $('.sf-half-panel .sp-permition-body').hide();
    $('.sf-half-panel .sp-permition-cat-title .sf-permition-panel-collapse').hide();
    $('.sf-half-panel .sp-permition-cat-title .sf-permition-panel-expand').show();
    $('.sf-half-panel .sp-permition-cat-title').on('click', function(e){
      body = $(this).parent().find('.sp-permition-body');
      img1 = $(this).find('.sf-permition-panel-collapse');
      img2 = $(this).find('.sf-permition-panel-expand');
      if(body.css('display') === 'none'){
        body.show();
        img2.hide();
        img1.show();
        $(this).css('background-color', '#FFFFFF');
      }else{
        body.hide();
        img1.hide();
        img2.show();
        $(this).css('background-color', '#F9F9F9');
      }
    });
  });
}(window.spj = window.spj || {}, jQuery));
</script>
<?php
}
