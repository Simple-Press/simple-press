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
		<h1><?php echo SP()->primitives->admin_text('Simple:Press Forums - Specials and Promotions');?></h1>
		<p><?php echo SP()->primitives->admin_text('Simple:Press has premium options that can supercharge your forums - check some of them out below!');?></p>
	</div>
	<div class="spa-promo-row spa-promo-row2">
		<div class="spa-promo-col spa-promo-box">
			<h1><?php echo SP()->primitives->admin_text('Platinum - $199/yr');?></h1>
			<p><?php echo SP()->primitives->admin_text('Over 100 additional features are included in our Simple:Press Forums Platinum Bundle including:');?></p>
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
			<h1><?php echo SP()->primitives->admin_text('Simple:Press Member Manager - Starting at $99/yr');?></h1>
			<p><?php echo SP()->primitives->admin_text('All the features you need to create a full-featured membership site integrated with Simple:Press Forums');?></p>
			<ul>
				<li><?php echo SP()->primitives->admin_text('Beautiful login, account and user profile screens');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Custom registration and user profile forms');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Simple social networking functions');?></p></li>
				<li><?php echo SP()->primitives->admin_text('User directories');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Visual Form builder');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Custom welcome and other account related emails');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Sophisticated role-based user restrictions');?></p></li>
			</ul>
			<p><?php echo SP()->primitives->admin_text('and a whole lot more!');?></p>
			<div class="spa-promo-button"><a href="https://simple-press.com/simplepress-member-manager/"><?php echo SP()->primitives->admin_text('View all features');?></a></div>
		</div>
	</div>
	<div class="spa-promo-row spa-promo-row3">
		<div class="spa-promo-col spa-promo-box">
			<h1><?php echo SP()->primitives->admin_text('Simple:Press Member Subscriptions - Starting at $99/yr');?></h1>
			<p><?php echo SP()->primitives->admin_text('Need to charge users for access to your site or forum?  The Simple:Press Member Subscriptions plugin has you covered!');?></p>
			<ul>
				<li><?php echo SP()->primitives->admin_text('Multiple membership plans - free or paid.');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Content restriction by membership level');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Discount codes');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Stripe and Paypal payment gateways');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Drip content');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Charge for individual posts');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Private pages');?></p></li>
			</ul>
			<p><?php echo SP()->primitives->admin_text('and a whole lot more!');?></p>
			<div class="spa-promo-button"><a href="https://simple-press.com/simplepress-paid-member-subscriptions/"><?php echo SP()->primitives->admin_text('View all features');?></a></div>
		</div>
		<div class="spa-promo-col spa-promo-box">
			<h1><?php echo SP()->primitives->admin_text('Custom Work');?></h1>
			<p><?php echo SP()->primitives->admin_text('Do you need additional functionality that is unique to your workflow?  We have you convered!');?></p>
			<p><?php echo SP()->primitives->admin_text('Here are some examples of things we can handle for you:');?></p>
			<ul>
				<li><?php echo SP()->primitives->admin_text('Create custom themes for your forum or even your overall website');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Develop custom Simple:Press plugins or WordPress plugins');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Create entire websites, especially those that require sophisticated functionality');?></p></li>
				<li><?php echo SP()->primitives->admin_text('Handle all your website support needs under a single monthly contract');?></p></li>
			</ul>
			<p><?php echo SP()->primitives->admin_text('Get the ball rolling by contacting us today!');?></p>
			<div class="spa-promo-button"><a href="https://simple-press.com/contact/"><?php echo SP()->primitives->admin_text('Contact us');?></a></div>
		</div>
	</div>
<?php
}
