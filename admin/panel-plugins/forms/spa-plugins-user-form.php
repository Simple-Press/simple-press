<?php
/*
Simple:Press
Admin plugins user form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_plugins_user_form($admin, $save, $form, $reload) {
    if ($form) {
?>
        <script>
			(function(spj, $, undefined) {
				$(document).ready(function() {
					$('#sfpluginsuser').ajaxForm({
						target: '#sfmsgspot',
						success: function() {
							<?php if (!empty($reload)) echo "jQuery('#".esc_attr($reload)."').click();"; ?>
							$('#sfmsgspot').fadeIn();
							$('#sfmsgspot').fadeOut(6000);
						}
					});
				});
			}(window.spj = window.spj || {}, jQuery));
        </script>
<?php
    	spa_paint_options_init();
        $ajaxURL = wp_nonce_url(SPAJAXURL.'plugins-loader&amp;saveform=plugin&amp;func='.$save, 'plugins-loader');
    	echo '<form action="'.esc_attr($ajaxURL).'" method="post" id="sfpluginsuser" name="sfpluginsuser">';
        sp_echo_create_nonce('forum-adminform_userplugin');
    }

    call_user_func($admin);

    if ($form) {
?>
    	<div class="sf-form-submit-bar">
<?php
                echo wp_kses(
            apply_filters('sph_UpdateBar', '<input type="submit" class="sf-button-primary" value="'.SP()->primitives->admin_text("Update").'" />', $reload),
            SP_CORE_ALLOWED_TAGS
        );

?>
    	</div>
        <?php spa_paint_close_tab(); ?>
        </form>

    	<div class="sfform-panel-spacer"></div>
<?php
    }
}
