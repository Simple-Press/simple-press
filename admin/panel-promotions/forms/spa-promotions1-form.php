<?php
/*
Simple:Press
Promotions 1 Form
$LastChangedDate: 2018-11-13 20:41:56 -0600 (Tue, 13 Nov 2018) $
$Rev: 15817 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_promotions_1_form() {
	
	spa_paint_open_tab('Simple:Press Forums - Specials and Promotions');
		
	spa_paint_close_tab();
?>
	<div class="spa-promo-row spa-promo-row1">
		<p><?php echo SP()->primitives->admin_text('Simple:Press has premium options that can supercharge your forums - check some of them out below!');?></p>
	</div>
	<div class="spa-promo-row spa-promo-row2">
		<div class="spa-promo-col spa-promo-box">
			<h1><?php echo SP()->primitives->admin_text('Simple:Press Forums Platinum Bundle - $199/yr');?></h1>
			<p><?php echo SP()->primitives->admin_text_noesc('Over 100 additional features are included in our <b>Simple:Press Forums Platinum Bundle</b> including:');?></p>
			<ul>
				<li><?php echo SP()->primitives->admin_text('Enhanced charts and statistics');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Private messaging');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Advertising module');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Custom status for topics');?></p></li>
				<li><?php echo SP()->primitives->admin_text('WooCommerce integration');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Push notifications via SMS, Pushbullet, Slack and more');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Threaded discussions');?></p></li>
			</ul>
			<p><?php echo SP()->primitives->admin_text('and a whole lot more!');?></p>
			<div class="spa-promo-button"><a href="https://simple-press.com/simple-press-forums/sp-forums-pricing/"><?php echo SP()->primitives->admin_text('View all pricing options');?></a></div>
		</div>
		<div class="spa-promo-col spa-promo-box">
			<h1><?php echo SP()->primitives->admin_text('WPCloudDeploy - Starting at $299/yr');?></h1>
			<p><?php echo SP()->primitives->admin_text('Deploy your own high-speed WordPress servers at any of the major cloud providers.');?></p>
			<ul>
				<li><?php echo SP()->primitives->admin_text('Unlimited Servers');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Unlimited Sites');?></p></li>
				<li><?php echo SP()->primitives->admin_text('White Label Ready');?></p></li>
				<li><?php echo SP()->primitives->admin_text('MicroCRM for Agencies');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Multisite Ready');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Open Source');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Developer Friendly');?></p></li>
			</ul>
			<p><?php echo SP()->primitives->admin_text('and a whole lot more!');?></p>
			<div class="spa-promo-button"><a href="https://wpclouddeploy.com/"><?php echo SP()->primitives->admin_text('View all features');?></a></div>
		</div>
	</div>
<?php
}
