<?php
/*
Simple:Press
Admin Plugins
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Admins
global $spStatus;

# Check Whether User Can Manage Plugins
# dont check for admin panels loaded by plugins - the plugins api will do that
$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'plugin-list';
if ($tab != 'plugin') {
    if (!sp_current_user_can('SPF Manage Plugins')) die();
}

include_once SF_PLUGIN_DIR.'/admin/panel-plugins/spa-plugins-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-plugins/support/spa-plugins-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/panel-plugins/support/spa-plugins-save.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';
include_once SPAPI.'sp-api-plugins.php';
include_once SPAPI.'sp-api-themes.php';

if ($spStatus != 'ok') {
    include_once SPLOADINSTALL;
    die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-plugins';
# --------------------------------------------------------------------

# was this individual plugin action or bulk action
# bulk actions use query args action1 and action2 while individual links use action
if (isset($_GET['action1'])) {
    $action = $_GET['action1'];
} elseif (isset($_GET['action2'])) {
    $action = $_GET['action2'];
} else {
    $action = (isset($_GET['action'])) ? $_GET['action'] : '';
}

$title  = (isset($_GET['title'])) ? urldecode(sp_esc_str($_GET['title'])) : '';
$plugin = (isset($_GET['plugin'])) ? sp_esc_str($_GET['plugin']) : '';

if ($action && $action == 'uninstall') {
    $msg = '<h3>'.spa_text('Please confirm that you want to uninstall this plugin?');
    $msg.= '</h3><b>'.spa_text('Please be aware!');
    $msg.= '</b><br><br>'.spa_text('Uninstalling a plugin will also remove ANY components that the plugin created when first activated. This can include folders where items have been stored, database tables, permission settings etc.');
    $msg.= '<br><br><b>'.spa_text('If in doubt - deactivate the plugin instead');
    $msg.= '</b>';
    $msg = apply_filters('sph_uninstall_message', $msg, $plugin);
?>
	<div id="dialog"></div>

    <script type="text/javascript">

	var j = jQuery.noConflict();
	j(document).ready(function() {

		var execute = function() {
			window.location = '<?php echo SFADMINPLUGINS."&plugin=$plugin&action=uninstall_confirmed&sfnonce=".wp_create_nonce('forum-adminform_plugins'); ?>';
		}
		var cancel = function() {
			window.location = '<?php echo SFADMINPLUGINS."&plugin=$plugin&action=uninstall_cancelled&sfnonce=".wp_create_nonce('forum-adminform_plugins'); ?>';
		}

		j('#dialog').html('<?php echo $msg; ?>');
		j('#dialog').dialog({
			modal: true,
			autoOpen: true,
			show: 'fold',
			hide: 'fold',
			width: '500',
			height: 'auto',
			draggable: false,
			resizable: false,
			title: '<?php echo($plugin); ?>',
			closeText: '',
			buttons: {
				"<?php spa_etext('Uninstall'); ?>": execute,
				"<?php spa_etext('Cancel'); ?>": cancel
			}
		});
	});

    </script>
<?php
    die();
}

# was there a plugin action?
if ($action && $action != 'uninstall_cancelled') spa_save_plugin_activation();

spa_panel_header();
spa_render_plugins_panel($tab);
spa_panel_footer();

if ($action) {
	if ($action == 'activate') $msg = $title.' '.spa_text('Plugin').' <strong>'.spa_text('Activated').'</strong>';
	if ($action == 'deactivate') $msg = $title.' '.spa_text('Plugin').' <strong>'.spa_text('Deactivated').'</strong>';
	if ($action == 'uninstall_confirmed') $msg = $title.' '.spa_text('Plugin').' <strong>'.spa_text('Deactivated and Uninstalled').'</strong>';
	if ($action == 'uninstall_cancelled') $msg = spa_text('Plugin uninstall cancelled');
	if ($action == 'delete') $msg = $title.' '.spa_text('Plugin').' <strong>'.spa_text('Deleted').'</strong>';
	$msg = apply_filters('sph_plugin_message', $msg);

    if ($action != 'uninstall_cancelled') {
?>
    	<script type="text/javascript">
        	jQuery(document).ready(function(){
        		jQuery("#sfmsgspot").fadeIn("fast");
        		jQuery("#sfmsgspot").html("<?php echo $msg; ?>");
        		jQuery("#sfmsgspot").fadeOut(8000);
        		window.location = '<?php echo SFADMINPLUGINS; ?>';
        	});
    	</script>
<?php
    }
}
?>