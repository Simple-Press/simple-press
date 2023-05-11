<?php
/*
Simple:Press
Admin Forums Global Permission Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

# function to display the add global permission set form.
# It is hidden until user clicks the add global permission set link
function spa_forums_global_perm_form(): void {
?>
<script>
   	spj.loadAjaxForm('sfnewglobalpermission', 'sfreloadfb');
</script>
<?php
	spa_paint_options_init();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=globalperm', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfnewglobalpermission" name="sfnewglobalpermission">
<?php
		echo sp_create_nonce('forum-adminform_globalpermissionnew');
		spa_paint_open_tab(SP()->primitives->admin_text('Add Global Permission Set'), true);
		?>

            <div class="sf-panel">
                <fieldset class="sf-fieldset">
                    <div class="sf-panel-body-top">
                        <h4><?php echo SP()->primitives->admin_text('Add a User Group Permission Set to All Forums') ?></h4>
                        <?php echo spa_paint_help('add-a-user-group-permission-set-to-all-forums') ?>
                    </div>
                    <?php
                    echo '<div class="sf-alert-block sf-caution">'.SP()->primitives->admin_text('Caution:  Any current permission sets for the selected usergroup for ANY forum may be overwritten').'</div>';
                    spa_paint_select_start(SP()->primitives->admin_text('Select usergroup'), 'usergroup_id', '');
                    spa_display_usergroup_select(false, 0, false);
                    spa_paint_select_end();

                    spa_paint_select_start(SP()->primitives->admin_text('Select permission set'), 'role', '');
                    spa_display_permission_select(false, 0);
                    spa_paint_select_end();
                    ?>
               </fieldset>
            </div>
            <?php
			do_action('sph_forums_global_perm_panel');
?>
		<div class="sf-form-submit-bar">
            <input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Add Global Permission'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
<?php
}
