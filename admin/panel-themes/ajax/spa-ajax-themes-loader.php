<?php
/*
Simple:Press Admin
Ajax form loader - themes
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('themes-loader')) die();

global $spStatus;
if ($spStatus != 'ok') {
	echo $spStatus;
	die();
}

include_once SF_PLUGIN_DIR.'/admin/panel-themes/spa-themes-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-save.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-themes';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
if (!sp_current_user_can('SPF Manage Themes')) die();

if (isset($_GET['loadform'])) {
	spa_render_themes_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	switch($_GET['saveform']) {
		case 'theme':
			$msg = spa_save_theme_data();
?>
        	<script type="text/javascript">
            	jQuery(document).ready(function(){
            		jQuery("#sfmsgspot").fadeIn("fast");
            		jQuery("#sfmsgspot").html("<?php echo $msg; ?>");
            		jQuery("#sfmsgspot").fadeOut(8000);
            	});
        	</script>
<?php
			break;

		case 'mobile':
			$msg = spa_save_theme_mobile_data();
?>
        	<script type="text/javascript">
            	jQuery(document).ready(function(){
            		jQuery("#sfmsgspot").fadeIn("fast");
            		jQuery("#sfmsgspot").html("<?php echo $msg; ?>");
            		jQuery("#sfmsgspot").fadeOut(8000);
            	});
            	</script>
<?php
			break;

		case 'tablet':
			$msg = spa_save_theme_tablet_data();
?>
        	<script type="text/javascript">
            	jQuery(document).ready(function(){
            		jQuery("#sfmsgspot").fadeIn("fast");
            		jQuery("#sfmsgspot").html("<?php echo $msg; ?>");
            		jQuery("#sfmsgspot").fadeOut(8000);
            	});
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
?>