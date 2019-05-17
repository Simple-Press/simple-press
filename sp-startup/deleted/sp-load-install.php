<?php
/*
  Simple:Press
  Installer/Upgrader
  $LastChangedDate: 2018-11-13 22:45:46 -0600 (Tue, 13 Nov 2018) $
  $Rev: 15820 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	INSTALL ROUTER
#	The SP Install Router. Handles all Installs and Updates of SP as needed.
#
# ==========================================================================================
?>
<style>
    .imessage, .zmessage, #debug {
		display: none;
		width: 820px;
		height: auto;
		color: #000000;
		font-weight: bold;
		font-size: 11px;
		font-family: Tahoma, Helvetica, Arial, Verdana;
		margin: 2px 10px;
		padding: 5px;
		border: 2px solid #555555;
		-khtml-border-radius: 5px;
		-webkit-border-radius: 5px;
		border-radius: 5px;
    }

    .updated h3,
    .error h3 {
		border: none;
		font-size: 20px;
		margin: 2px 0 10px;
		line-height: 1.2em;
		min-height: 50px;
    }

    .imessage {
		background-color: #FFF799;
    }

    .zmessage {
		background-color: #A7C1FF;
    }

    .pbar {
		margin: 2px 20px;
		width: 820px;
    }

    #zonecount {
		display:none;
    }

    .stayleft {
		float: left;
		padding-right: 15px;
    }
</style>
<?php
# get current version  and build from database
$current_version = SP()->options->get('sfversion');
$current_build = SP()->options->get('sfbuild');

# check if we are coming back in with post values to install
if (isset($_POST['goinstall'])) {
	sp_go_install();
	return;
}

# check if we are coming back in with post values to upgrade
if (isset($_POST['goupgrade'])) {
	# run the upgrade
	sp_go_upgrade($current_version, $current_build);
	return;
}

# check if we are coming back in with post values to upgrade network
if (isset($_POST['gonetworkupgrade'])) {
	# run the upgrade
	sp_go_network_upgrade($current_version, $current_build);
	return;
}

# downgrading? not good
if (SPBUILD < $current_build || version_compare(SPVERSION, $current_version) == -1) {
	sp_no_downgrade();
	return;
}

# Has the systen been installed?
if (version_compare($current_version, '1.0', '<')) {
	sp_install_required();
	return;
}

# Base already installed - check Version and Build Number
#if (($current_build < SPBUILD) || ($current_version > SPVERSION)) {
if (($current_build < SPBUILD || version_compare($current_version, SPVERSION, '>=') == 1) && SP()->core->status != 'Unallowed 6.0 Upgrade') {
	sp_upgrade_required();
	return;
}

return;

# simple press files are younger than db version - warn you cannot downgrade like that

function sp_no_downgrade() {
	?>
	<div class="wrap"><br />
		<?php
		# Warn you can't downgrade
		?>
		<div class="updated">
			<img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" />
			<h3><?php SP()->primitives->admin_etext('Downgrade Warning'); ?></h3>
			<p><?php SP()->primitives->admin_etext('It appears you are attempting to downgrade your Simple:Press Version. The Build or Version number in the sp-control.php file is lower than the currently installed version in the database.'); ?></p>
			<p><?php SP()->primitives->admin_etext('You must restore your database to this earlier version before you can continue. You cannot simply downgrade Simple:Press files as the database has been upgraded beyond the version you are attempting to downgrade to and may cause irreparable damage to the database.'); ?></p>
		</div>
	</div>
	<?php
}

# set up install

function sp_install_required() {
	?>
	<div class="wrap"><br />
		<?php
		# for multisite, make sure the uploads directory exists
		if (is_multisite()) wp_upload_dir();

		# Check versions
		$bad = sp_version_checks();
		if ($bad != '') {
			echo $bad.'</div>';
			return;
		}
		# Check we can create a folder in wp-content
		$bad = sp_check_folder_creation();
		if ($bad != '') {
			echo $bad.'</div>';
			return;
		}
		# OK - we can contiunue to offer full install
		?>
		<div class="updated">
			<img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" />
			<h3><?php SP()->primitives->admin_etext('Install Simple:Press Version'); ?> <?php echo SPVERSION; ?> - <?php SP()->primitives->admin_etext('Build'); ?> <?php echo SPBUILD; ?></h3>
		</div>
		<form name="sfinstall" method="post" action="<?php echo admin_url('admin.php?page='.SPINSTALLPATH); ?>">
			<div class="notice notice-warning" style="font-size: 14px; padding: 15px;">
				<?php SP()->primitives->admin_etext('If you opt to include the sample data it can be later removed with a simple click of a supplied button'); ?>
				<br />
				<input type="checkbox" checked="checked" id="sample" name="sample" tabindex="1" style="margin-top: 0;" />
				<label class="wp-core-ui" for="sample"><b><?php SP()->primitives->admin_etext('Include some basic Sample Data when performing the forum installation'); ?></b></label>
				<br /><br />
				<?php SP()->primitives->admin_etext('Any user can be a Simple:Press Admin, but at installation, only the WordPress Administrator performing the install is made one'); ?>
				<br />
				<input type="checkbox" checked="checked" id="sample" name="installadmins" tabindex="2" style="margin-top: 0;" />
				<label class="wp-core-ui" for="installadmins"><b><?php SP()->primitives->admin_etext('Make all WordPress Administrators be Simple:Press Admins'); ?></b></label>
				<br /><br />
				<?php SP()->primitives->admin_etext('Simple:Press creates a new WordPress page for the forum display - the default name is Forum'); ?>
				<br />
				<label class="wp-core-ui" for="pagename"><b><?php SP()->primitives->admin_etext('WordPress page name you want the forum to appear on:'); ?></b></label>
				<input type="text" id="pagename" name="pagename" tabindex="3" style="margin-top: 0;vertical-align:sub" value="Forum" />
			</div>
			<input type="submit" class="button-primary" id="sbutton" name="goinstall" value="<?php SP()->primitives->admin_etext('Perform Installation'); ?>" />
		</form>
	</div>
	<?php
}

# set up upgrade

function sp_upgrade_required() {
	?>
	<div class="wrap"><br />
		<?php
		$bad = sp_version_checks();
		if ($bad != '') {
			echo $bad.'</div>';
			return;
		}
		?>
		<div class="updated">
			<img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" />
			<h3><?php echo sprintf(SP()->primitives->admin_text('Upgrade Simple:Press From Version %s to %s'), SP()->options->get('sfversion'), SPVERSION); ?><br />
				(<?php SP()->primitives->admin_etext('Build'); ?> <?php echo SP()->options->get('sfbuild'); ?> <?php SP()->primitives->admin_etext('to'); ?> <?php SP()->primitives->admin_etext('Build'); ?> <?php echo SPBUILD; ?>)</h3>
			<p><?php echo SP()->primitives->admin_text('As with all WordPress related updates we recommend that you backup your site before proceeding with this upgrade.')?></p>				
		</div>
		<hr />
		<?php
		$f = 'update'.str_replace('.', '-', SPVERSION).'.html';
		$path = SP_PLUGIN_DIR.'/sp-startup/install/resources/versions/'.$f;
		if (file_exists($path)) {
			readfile($path);
		}
		?>
		<form name="sfupgrade" method="post" action="<?php echo admin_url('admin.php?page='.SPINSTALLPATH); ?>"><br />
			<?php if (SPVERSION == '5.0.0' && substr(SP()->options->get('sfversion'), 0, 1) != '5') { ?>
				<p><b><input type="checkbox" name="dostorage" id="dostorage" />
						<label for="dostorage"><?php SP()->primitives->admin_etext('Check this box to have the upgrade attempt to convert current storage locations to V5 format (optional)'); ?></label>
						<br /><br /></b></p>
			<?php } ?>

			<input type="submit" class="button-primary" id="sbutton" name="goupgrade" value="<?php SP()->primitives->admin_etext('Perform Upgrade'); ?>" />
			<?php if (is_multisite() && is_super_admin()) { ?>
				<input type="submit" class="button-primary" id="sbutton" name="gonetworkupgrade" value="<?php SP()->primitives->admin_etext('Perform Network Upgrade'); ?>" />
			<?php } ?>
		</form>
	</div>
	<?php
}

# perform install

function sp_go_install() {
	global $current_user;

	add_option('sfInstallID', $current_user->ID); # use wp option table

	$phpfile = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'install&sample='.$_POST['sample'].'&installadmins='.$_POST['installadmins'].'&pagename='.$_POST['pagename'], 'install'));
	$image = SPCOMMONIMAGES.'working.gif';

	# how many users passes at 200 a pop?
	$users = SP()->DB->count(SPUSERS);

	$subphases = ceil($users / 200);
	$nextsubphase = 1;
	?>
	<div class="wrap"><br />
		<div class="updated">
			<img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" />
			<h3><?php SP()->primitives->admin_etext('Simple:Press is being installed'); ?></h3></div>
		<div style="clear: both"></div>
		<br />
		<div class="wrap sfatag">
			<div class="imessage" id="imagezone"></div><br />
			<div class="pbar" id="progressbar"></div><br />
		</div>
		<div style="clear: both"></div>
		<table id="SPLOADINSTALLtable" style="padding:2px;border-spacing:6px;border-collapse:separate;">
			<tr><td><div class="zmessage" id="zone0"><?php SP()->primitives->admin_text('Installing'); ?>...</div></td></tr>
			<tr><td><div class="zmessage" id="zone1"></div></td></tr>
			<tr><td><div class="zmessage" id="zone2"></div></td></tr>
			<tr><td><div class="zmessage" id="zone3"></div></td></tr>
			<tr><td><div class="zmessage" id="zone4"></div></td></tr>
			<tr><td><div class="zmessage" id="zone5"></div></td></tr>
			<tr><td><div class="zmessage" id="zone6"></div></td></tr>
			<tr><td><div class="zmessage" id="zone7"></div></td></tr>
			<tr><td><div class="zmessage" id="zone8"></div></td></tr>
			<tr><td><div class="zmessage" id="zone9"></div></td></tr>
			<tr><td><div class="zmessage" id="zone10"></div></td></tr>
			<tr><td><div class="zmessage" id="zone11"></div></td></tr>
		</table>
		<div class="zmessage" id="errorzone"></div>
		<div id="finishzone"></div>
		<?php
		$pass = 11;
		$curr = 0;
		$messages = esc_js(SP()->primitives->admin_text('Go to Forum Admin')).'@'.esc_js(SP()->primitives->admin_text('Installation is in progress - please wait')).'@'.esc_js(SP()->primitives->admin_text('Installation Completed')).'@'.esc_js(SP()->primitives->admin_text('Installation has been Aborted'));
		$out = '<script>'."\n";
		$out .= '(function(spj, $, undefined) {';
		$out .= 'spj.performInstall("'.$phpfile.'", "'.$pass.'", "'.$curr.'", "'.$subphases.'", "'.$nextsubphase.'", "'.$image.'", "'.$messages.'", "'.SP_FOLDER_NAME.'");'."\n";
		$out .= '}(window.spj = window.spj || {}, jQuery));';
		$out .= '</script>'."\n";
		echo $out;
		?>
	</div>
	<?php
}

# perform upgrade

function sp_go_upgrade($current_version, $current_build) {
	global $current_user;

	if (SPVERSION == '5.0.0') {
		$dostorage = false;
		if (isset($_POST['dostorage'])) $dostorage = true;
		SP()->options->add('V5DoStorage', $dostorage);
	}

	update_option('sfInstallID', $current_user->ID); # use wp option table
	SP()->options->update('sfStartUpgrade', $current_build);

	$phpfile = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'upgrade', 'upgrade')); # since passed to js cant have amp;
	$image = SPCOMMONIMAGES.'working.gif';

	$targetbuild = SPBUILD;
	?>
	<div class="wrap"><br />
		<div class="updated">
			<img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" />
			<h3><?php SP()->primitives->admin_etext('Simple:Press is being upgraded'); ?></h3>
		</div><br />
		<div class="wrap sfatag">
			<div class="imessage" id="imagezone"></div>
		</div><br />
		<div class="pbar" id="progressbar"></div><br />
		<div class="wrap sfatag">
			<div class="zmessage" id="errorzone"></div>
			<div id="finishzone"></div><br />
		</div><br />
		<div id="debug">
			<p><b>Please copy the details below and include them on any support forum question you may have:</b><br /><br /></p>
		</div>
		<?php
		$messages = esc_js(SP()->primitives->admin_text('Go to Forum Admin')).'@'.esc_js(SP()->primitives->admin_text('Upgrade is in progress - please wait')).'@'.esc_js(SP()->primitives->admin_text('Upgrade Completed')).'@'.esc_js(SP()->primitives->admin_text('Upgrade Aborted')).'@'.esc_js(SP()->primitives->admin_text('Go to Forum'));
		$out = '<script>'."\n";
		$out .= '(function(spj, $, undefined) {';
		$out .= 'spj.performUpgrade("'.$phpfile.'", "'.$current_build.'", "'.$targetbuild.'", "'.$current_build.'", "'.$image.'", "'.$messages.'", "'.SP()->spPermalinks->get_url().'", "'.SP_FOLDER_NAME.'");'."\n";
		$out .= '}(window.spj = window.spj || {}, jQuery));';
		$out .= '</script>'."\n";
		echo $out;
		?>
	</div>
	<?php
	# clear any combined css/js cached files
	SP()->plugin->clear_css_cache('all');
	SP()->plugin->clear_css_cache('mobile');
	SP()->plugin->clear_css_cache('tablet');

	SP()->plugin->clear_scripts_cache('desktop');
	SP()->plugin->clear_scripts_cache('mobile');
	SP()->plugin->clear_scripts_cache('tablet');
}

# perform network upgrade

function sp_go_network_upgrade($current_version, $current_build) {
	global $current_user;
	?>
	<div class="wrap"><br />
		<div class="updated">
			<img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" />
			<h3><?php SP()->primitives->admin_etext('Simple:Press is upgrading the Network.'); ?></h3>
		</div><br />
		<div class="wrap sfatag">
			<div class="imessage" id="imagezone"></div>
		</div><br />
		<div class="pbar" id="progressbar"></div><br />
		<div class="wrap sfatag">
			<div class="zmessage" id="errorzone"></div>
			<div id="finishzone"></div><br />
		</div><br />
		<div id="debug">
			<p><b>Please copy the details below and include them on any support forum question you may have:</b><br /><br /></p>
		</div>
	</div>
	<?php
	# get list of network sites
	$sites = get_sites();

	# loop through all blogs and upgrade ones with active simple:press
	foreach ($sites as $site) {
		# switch to network site and see if simple:press is active
		switch_to_blog($site->blog_id);
		global $wpdb;
		$installed = SP()->DB->select('SELECT option_id FROM '.$wpdb->prefix."sfoptions WHERE option_name='sfversion'");
		if ($installed) {
			$phpfile = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'upgrade&sfnetworkid='.$site->blog_id, 'upgrade'));
			$image = SPCOMMONIMAGES.'working.gif';
			$targetbuild = SPBUILD;
			update_option('sfInstallID', $current_user->ID); # use wp option table
			# save the build info
			$out = SP()->primitives->admin_text('Upgrading Network Site ID').': '.$site->blog_id.'<br />';
			SP()->options->update('sfStartUpgrade', $current_build);

			# upgrade the network site
			$messages = esc_js(SP()->primitives->admin_text('Go to Forum Admin')).'@'.esc_js(SP()->primitives->admin_text('Upgrade is in progress - please wait')).'@'.esc_js(SP()->primitives->admin_text('Upgrade Completed')).'@'.esc_js(SP()->primitives->admin_text('Upgrade Aborted')).'@'.esc_js(SP()->primitives->admin_text('Go to Forum'));
			$out .= '<script>'."\n";
			$out .= '(function(spj, $, undefined) {';
			$out .= 'spj.performUpgrade("'.$phpfile.'", "'.$current_build.'", "'.$targetbuild.'", "'.$current_build.'", "'.$image.'", "'.$messages.'", "'.SP()->spPermalinks->get_url().'", "'.SP_FOLDER_NAME.'");'."\n";
			$out .= '}(window.spj = window.spj || {}, jQuery));';
			$out .= '</script>'."\n";
			echo $out;

			# clear any combined css/js cached files
			SP()->plugin->clear_css_cache('all');
			SP()->plugin->clear_css_cache('mobile');
			SP()->plugin->clear_css_cache('tablet');

			SP()->plugin->clear_scripts_cache('desktop');
			SP()->plugin->clear_scripts_cache('mobile');
			SP()->plugin->clear_scripts_cache('tablet');
		}
		restore_current_blog();
	}
}

# Perform version checks prior to install

function sp_version_checks() {
	global $wp_version, $wpdb;

	$message = '';
	$testtable = true;

	$logo = '<div class="error"><img src="'.SPCOMMONIMAGES.'sp-full-logo.png" alt="" title="" /><br /><hr />';

	$xml = sp_load_version_xml(false, false);
	if ($xml) {
		# WordPress version check
		if (sp_version_compare(SP_WP_VER, $wp_version) == false) {
			$message .= $logo;
			$message .= '<h3>'.sprintf(SP()->primitives->admin_text('%s Version %s'), 'WordPress', $wp_version).'</h3>';
			$message .= '<p>'.sprintf(SP()->primitives->admin_text('Your version of %s is not supported by %s %s'), 'WordPress', 'Simple:Press', SPVERSION).'<br />';
			$message .= sprintf(SP()->primitives->admin_text('%s version %s or above is required'), 'WordPress', SP_WP_VER).'</p><br />';
			$logo = '<hr />';
		}

		# MySQL Check
		$mysql_required = (string) $xml->core->mysql_ver;
		if (sp_version_compare($mysql_required, $wpdb->db_version()) == false) {
			$message .= $logo;
			$message .= '<h3>'.sprintf(SP()->primitives->admin_text('%s Version %s'), 'MySQL', $wpdb->db_version()).'</h3>';
			$message .= '<p>'.sprintf(SP()->primitives->admin_text('Your version of %s is not supported by %s %s'), 'MySQL', 'Simple:Press', SPVERSION).'<br />';
			$message .= sprintf(SP()->primitives->admin_text('%s version %s or above is required'), 'MySQL', $mysql_required).'</p><br />';
			$logo = '<hr />';
			$testtable = false;
		}

		# PHP Check
		$php_required = (string) $xml->core->php_ver;
		if (sp_version_compare($php_required, phpversion()) == false) {
			$message .= $logo;
			$message .= '<h3>'.sprintf(SP()->primitives->admin_text('%s Version %s'), 'PHP', phpversion()).'</h3>';
			$message .= '<p>'.sprintf(SP()->primitives->admin_text('Your version of %s is not supported by %s %s'), 'PHP', 'Simple:Press', SPVERSION).'<br />';
			$message .= sprintf(SP()->primitives->admin_text('%s version %s or above is required'), 'PHP', $php_required).'</p><br />';
			$logo = '<hr />';
		}

		# test we can create database tables
		if ($testtable) {
			if (sp_test_table_create() == false) {
				$message .= $logo;
				$message .= '<h3>'.SP()->primitives->admin_text('Database Problem').'</h3>';
				$message .= '<p>'.sprintf(SP()->primitives->admin_text('%s can not Create Tables in your database'), 'Simple:Press').'</p><br />';
			}
		}
	} else {
		echo '<div class="error" style="font-weight:bold;">';
		echo '<span style="font-size:17px">'.SP()->primitives->admin_text('Please Note').'</span><br />';
		echo SP()->primitives->admin_text('We were unable to establish version details to ensure your system meets requirements').'.<br />';
		echo SP()->primitives->admin_text('As a general rule - Simple:Press requires the same minimum versions of PHP and MySQL as WordPress and the most up to date version of WordPress itself').'.</br />';
		echo SP()->primitives->admin_text('If in doubt, please read the');
		echo ' <a href="https://simple-press.com/documentation/installation/installation-information/system-requirements/" target="_blank"> ';
		echo SP()->primitives->admin_text('System Requirements');
		echo '</a> ';
		echo SP()->primitives->admin_text('page in our Online Documentation');
		echo '</div><br />';
	}

	if ($message) $message .= '</div>';
	return $message;
}

function sp_version_compare($need, $got) {
	$need = explode('.', $need);
	$got = explode('.', $got);

	if (isset($need[0]) && intval($need[0]) > intval($got[0])) return false;
	if (isset($need[0]) && intval($need[0]) < intval($got[0])) return true;

	if (isset($need[1]) && intval($need[1]) > intval($got[1])) return false;
	if (isset($need[1]) && intval($need[1]) < intval($got[1])) return true;

	if (isset($need[2]) && intval($need[2]) > intval($got[2])) return false;
	return true;
}

function sp_test_table_create() {
	# make sure we can create database tables
	$sql = '
		CREATE TABLE sfCheckCreate (
			id int(4) NOT NULL,
			item varchar(15) default NULL,
			PRIMARY KEY	 (id)
		) '.SP()->DB->charset();
	SP()->DB->execute($sql);

	$success = SP()->DB->tableExists("sfCheckCreate");
	if ($success == false) {
		return false;
	} else {
		SP()->DB->execute('DROP TABLE sfCheckCreate');
		return true;
	}
}

function sp_check_folder_creation() {
	# Make sure we have write access to the wp-content folder
	$message = '';
	$logo = '<div class="error"><img src="'.SPCOMMONIMAGES.'sp-full-logo.png" alt="" title="" /><br /><hr />';

	if (!is_writable(SP_STORE_DIR)) {
		$message .= $logo;
		$message .= '<h3>'.SP()->primitives->admin_text('Permission Problem').'</h3>';
		$message .= '<p>'.sprintf(SP()->primitives->admin_text('%s can not create sub-folders under wp-content. Please assign correct permissions and re-run the install'), 'Simple:Press').'</p><br />';
	}
	return $message;
}
