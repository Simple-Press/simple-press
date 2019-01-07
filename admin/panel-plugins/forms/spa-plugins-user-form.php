<?php
/*
Simple:Press
Admin plugins user form
$LastChangedDate: 2016-10-30 16:26:57 -0500 (Sun, 30 Oct 2016) $
$Rev: 14690 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_plugins_user_form($admin, $save, $form, $reload) {
    if ($form) {
?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
            	jQuery('#sfpluginsuser').ajaxForm({
            		target: '#sfmsgspot',
            		success: function() {
            			<?php if (!empty($reload)) echo "jQuery('#".$reload."').click();"; ?>
            			jQuery('#sfmsgspot').fadeIn();
            			jQuery('#sfmsgspot').fadeOut(6000);
            		}
            	});
            });
        </script>
<?php
    	spa_paint_options_init();
        $ajaxURL = wp_nonce_url(SPAJAXURL.'plugins-loader&amp;saveform=plugin&amp;func='.$save, 'plugins-loader');
    	echo '<form action="'.$ajaxURL.'" method="post" id="sfpluginsuser" name="sfpluginsuser">';
    	echo sp_create_nonce('forum-adminform_userplugin');
    }

    call_user_func($admin);

    if ($form) {
?>
    	<div class="sfform-submit-bar">
<?php
			echo apply_filters('sph_UpdateBar', '<input type="submit" class="button-primary" value="'.spa_text("Update").'" />', $reload);
?>
    	</div>
        <?php spa_paint_close_tab(); ?>
        </form>

    	<div class="sfform-panel-spacer"></div>
<?php
    }
}
?>