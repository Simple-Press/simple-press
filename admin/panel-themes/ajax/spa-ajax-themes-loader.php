<?php
/*
Simple:Press Admin
Ajax form loader - themes
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('themes-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-themes/spa-themes-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-themes';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
if (!SP()->auths->current_user_can('SPF Manage Themes')) die();

if (isset($_GET['loadform'])) {
	spa_render_themes_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	switch($_GET['saveform']) {
		case 'theme':
			$msg = spa_save_theme_data();
?>
        	<script>
				(function(spj, $, undefined) {
					$(document).ready(function(){
						$("#sfmsgspot").fadeIn("fast");
						$("#sfmsgspot").html("<?php echo $msg; ?>");
						$("#sfmsgspot").fadeOut(8000);
					});
				}(window.spj = window.spj || {}, jQuery));
        	</script>
<?php
			break;

		case 'mobile':
			$msg = spa_save_theme_mobile_data();
?>
        	<script>
				(function(spj, $, undefined) {
					$(document).ready(function(){
						$("#sfmsgspot").fadeIn("fast");
						$("#sfmsgspot").html("<?php echo $msg; ?>");
						$("#sfmsgspot").fadeOut(8000);
	            	});
				}(window.spj = window.spj || {}, jQuery));
            	</script>
<?php
			break;

		case 'tablet':
			$msg = spa_save_theme_tablet_data();
?>
        	<script>
				(function(spj, $, undefined) {
					$(document).ready(function(){
						$("#sfmsgspot").fadeIn("fast");
						$("#sfmsgspot").html("<?php echo $msg; ?>");
						$("#sfmsgspot").fadeOut(8000);
	            	});
				}(window.spj = window.spj || {}, jQuery));
        	</script>
<?php
			break;

		case 'editor':
			echo spa_save_editor_data();
			break;

		case 'css':
			echo spa_save_css_data();
			break;
	}
	die();
}

die();
