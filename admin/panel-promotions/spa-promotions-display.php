<?php
/*
Simple:Press
Promotions Panel Rendering
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_promotions_panel($formid) {
?>
	<div class="clearboth"></div>

	<div id="sf-root-wrap" class="wrap sfatag">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
			<?php spa_render_promotions_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_promotions_container($formid) {
	switch ($formid) {
		case 'promotions-1':
			require_once ABSPATH.'wp-admin/includes/admin.php';
			require_once SP_PLUGIN_DIR.'/admin/panel-promotions/forms/spa-promotions1-form.php';
			spa_promotions_1_form();
			break;
	}
}
