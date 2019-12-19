<?php
/*
Simple:Press
Admin themes user form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_user_form($admin, $save, $form, $reload) {
	if ($form) {
?>
        <script>
			(function(spj, $, undefined) {
				$(document).ready(function() {
					$('#sfthemesuser').ajaxForm({
						target: '#sfmsgspot',
						success: function() {
	<?php
				if (!empty($reload)) echo "jQuery('#".$reload."').click();";
	?>
							$('#sfmsgspot').fadeIn();
							$('#sfmsgspot').fadeOut(6000);
						}
					});
				});
			}(window.spj = window.spj || {}, jQuery));
        </script>
<?php
		spa_paint_options_init();
		$ajaxURL = wp_nonce_url(SPAJAXURL."themes-loader&amp;saveform=plugin&amp;func=$save", 'themes-loader');
		echo '<form action="'.$ajaxURL.'" method="post" id="sfpluginsuser" name="sfpluginsuser">';
		echo sp_create_nonce('forum-adminform_userplugin');
	}

	call_user_func($admin);

	if ($form) {
?>
    	<div class="sf-form-submit-bar">
    	   <input type="submit" class="sf-button-primary" value="<?php SP()->primitives->admin_etext('Update'); ?>" />
    	</div>
        </form>

    	<div class="sfform-panel-spacer"></div>
<?php
	}
}
