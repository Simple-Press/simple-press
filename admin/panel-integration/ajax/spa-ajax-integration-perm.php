<?php
/*
Simple:Press Admin
Ajax call for permalink update/integration
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('integration-perm')) die();

# ----------------------------------
# Check Whether User Can Manage Integration
if (!sp_current_user_can('SPF Manage Integration')) die();

if (isset($_GET['item'])) {
	$item = $_GET['item'];
	if ($item == 'upperm') spa_update_permalink_tool();
}

function spa_update_permalink_tool() {
	echo '<strong>&nbsp;'.sp_update_permalink(true).'</strong>';
?>
	<script type="text/javascript">window.location= "<?php echo SFADMININTEGRATION; ?>";</script>
<?php
	die();
}

die();
?>