<?php
/*
Simple:Press
Admin Permissions Usage Help
$LastChangedDate: 2014-10-20 15:38:39 +0100 (Mon, 20 Oct 2014) $
$Rev: 12009 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('permission-tip')) die();

if (!isset($_GET['role'])) die();
$roleid = sp_esc_str($_GET['role']);

$sql = "SELECT forum_name, usergroup_name
		FROM ".SFPERMISSIONS."
		JOIN ".SFFORUMS." ON ".SFPERMISSIONS.".forum_id = ".SFFORUMS.".forum_id
		JOIN ".SFUSERGROUPS." ON ".SFPERMISSIONS.".usergroup_id = ".SFUSERGROUPS.".usergroup_id
		WHERE permission_role = ".$roleid."
		ORDER BY forum_name, usergroup_name";
$list = spdb_select('set', $sql);


echo '<div>';

if(empty($list)) {
	echo '<div class="tipSection">';
	spa_etext('Not Currently In Use');
	echo '</div>';
} else {
?>
	<table class='form-table tipTable'>
		<tr>
			<th><b><?php spa_etext('Used for Forum'); ?></b></th>
			<th><b><?php spa_etext('With User Group'); ?></b></th>
		</tr>
<?php
	$fname = '';
	$firstline = true;
	$data = '';
	foreach($list as $usage) {
		if($usage->forum_name != $fname) {
			if($firstline) {
				$firstline = false;
			} else {
				echo '<td>'.$data.'</td>';
				echo '</tr>';
				$first = false;
				$data = '';
			}
			echo '<tr>';
			$fname = $usage->forum_name;
			echo '<td>'.$usage->forum_name.'</td>';
		}
		$data.= $usage->usergroup_name.'<br />';
	}
	echo '<td>'.$data.'</td>';
	echo '</tr>';

?>
	</table>
<?php
}

echo '</div>';

die();

?>