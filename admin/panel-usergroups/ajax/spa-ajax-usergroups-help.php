<?php
/*
Simple:Press
Admin User Groups Usage Help
$LastChangedDate: 2014-10-20 15:38:39 +0100 (Mon, 20 Oct 2014) $
$Rev: 12009 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

if (!sp_nonce('usergroup-tip')) {
    die();
}

if (!isset($_GET['group'])) {
    die();
}
$groupid = SP()->filters->str($_GET['group']);

$sql = "SELECT forum_name, role_name
		FROM ".SPPERMISSIONS."
		JOIN ".SPFORUMS." ON ".SPPERMISSIONS.".forum_id = ".SPFORUMS.".forum_id
		JOIN ".SPROLES." ON ".SPPERMISSIONS.".permission_role = ".SPROLES.".role_id
		WHERE usergroup_id = ".$groupid."
		ORDER BY forum_name, role_name";
$list = SP()->DB->select($sql);
?>

<div>
    <?php if(empty($list)) : ?>
        <div class="sf-alert-block sf-caution">
            <?php echo SP()->primitives->admin_etext('Not Currently In Use'); ?>
        </div>
    <?php else : ?>
        <table class="widefat sf-table-small sf-table-mobile">
            <thead>
                <tr>
                    <th><?php SP()->primitives->admin_etext('Used for Forum'); ?></th>
                    <th><?php SP()->primitives->admin_etext('With Permission Set'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($list as $usage) : ?>
                    <tr>
                        <td><?php echo($usage->forum_name); ?></td>
                        <td><?php echo($usage->role_name); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<?php

die();
