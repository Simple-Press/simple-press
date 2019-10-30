<?php
/*
Simple:Press
Promotions 1 Form
$LastChangedDate: 2018-11-13 20:41:56 -0600 (Tue, 13 Nov 2018) $
$Rev: 15817 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_promotions_1_form() {
?>
	<div class="spa-promo-row spa-promo-row1">
		<h1><?php echo SP()->primitives->admin_text('Help and Support Options');?></h1>
		<p><?php echo SP()->primitives->admin_text('Here are three ways to get help and support for your new helpdesk');?></p>
	</div>
	<div class="spa-promo-row spa-promo-row2">
		<div class="spa-promo-col">
			<h3><?php echo SP()->primitives->admin_text('Licensed Users');?></h3>
			<p><?php echo SP()->primitives->admin_text('Users with an active subscription license to one or more of our addons or bundles can open a ticket directly with us');?></p>
		</div>
		<div class="spa-promo-col">
			<h3><?php echo SP()->primitives->admin_text('Unlicensed  and Trial Users');?></h3>
			<p><?php echo SP()->primitives->admin_text('Users without a license can open a ticket on our free community supported WordPress.org forum.');?></p>
		</div>
	</div>
	<div class="spa-promo-row spa-promo-row3">
		<div class="spa-promo-col">
			<h3><?php echo SP()->primitives->admin_text('Do you need help and support for other plugins and themes or do you need emergency help for your site?');?></h3>
			<p><?php echo SP()->primitives->admin_text('If so, check out our partners at ValiusWP.com where you can get unlimited 30 minute website fixes and support for one low price per month.');?></p>
			<p><?php echo SP()->primitives->admin_text('Fast, friendly support for all things WordPress at one low monthly price!');?></p>
			<p><?php echo sprintf( '<a href="%s">Get started with unlimited fixes with a 10 day trial at ValiusWP.com</a>', 'https://valiuswp.com/', 'target="_blank"' ) ; ?></p>
		</div>
	</div>
	<div class="spa-promo-row spa-promo-row4">
		<div class="spa-promo-col">
			<h3><img src="<?php echo SP_PLUGIN_URL?>/sp-startup/install/resources/images/important.png"/></h3>
		</div>
		<div class="spa-promo-col">
			<p><?php echo SP()->primitives->admin_text('Here are three ways to get help and support for your new helpdesk');?></p>
		</div>
	</div>
	<div class="spa-promo-row spa-promo-row5">
		<div class="spa-promo-col">
			<h3><?php echo SP()->primitives->admin_text('Documentation');?></h3>
			<p><?php echo sprintf( 'Documentation links are located in our <a href="%s">About Page</a>', "x", 'target="_blank"' ) ; ?></p>
		</div>
	</div>
<?php
}
