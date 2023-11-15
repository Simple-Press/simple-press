<?php
/*
  Simple:Press
  Installer/Upgrader
  $LastChangedDate: 2018-11-13 22:45:46 -0600 (Tue, 13 Nov 2018) $
  $Rev: 15820 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	INSTALL ROUTER
#	The SP Install Router. Handles all Installs and Updates of SP as needed.
#
# ==========================================================================================
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

# Has the system been installed?
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
    <div class="wrap">
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
    <div id="sf-root-wrap" class="wrap">
        <div id="sfmaincontainer" class="sf-installation">
            <?php
            # for multisite, make sure the uploads directory exists
            if (is_multisite())
                wp_upload_dir();

            # Check versions
            $bad = sp_version_checks();
            if ($bad != '') {
                echo $bad . '</div>';
                return;
            }
            # Check we can create a folder in wp-content
            $bad = sp_check_folder_creation();
            if ($bad != '') {
                echo $bad . '</div>';
                return;
            }
            # OK - we can continue to offer full install
            ?>
            <div class="sf-panel-head">
                <div class='sf-buttons'>					
					<?php echo '<a class="sf-button sf-help" target="_blank" href="https://simple-press.com/documentation/installation/new-install/install/">'.SP()->primitives->admin_text('Installation Help').'</a>'; ?>
                </div>
                <h3><?php SP()->primitives->admin_etext('Simple:Press'); ?> <?php echo SPVERSION; ?> <?php SP()->primitives->admin_etext('Installation'); ?></h3>
            </div>
            <form class="sf-panel-body" name="sfinstall" method="post" action="<?php echo admin_url('admin.php?page=' . SPINSTALLPATH); ?>">
                <div class="sf-form-row">
                    <label for="pagename"><?php echo SP()->primitives->admin_etext('Forum Name') ?></label>
                    <input type="text" id="pagename" name="pagename" tabindex="3" placeholder="<?php SP()->primitives->admin_etext('WordPress page name you want the forum to appear on (default is FORUM)'); ?>" />
                    <span class="sf-sublabel sf-sublabel-small">
                        <?php SP()->primitives->admin_etext('Simple:Press creates a new WordPress page for the forum display - the default name is Forum'); ?>
                    </span>
                </div>
                <div class="sf-form-row">
                    <input type="checkbox" checked="checked" id="sample" name="sample" tabindex="1" />
                    <label class="wp-core-ui" for="sample">
                        <?php SP()->primitives->admin_etext('Include some basic Sample Data when performing the forum installation'); ?>
                        <span class="sf-sublabel sf-sublabel-small">
                            <?php SP()->primitives->admin_etext('If you opt to include the sample data it can be removed later with the simple click of a button'); ?>
                        </span>
                    </label>
                </div>
                <div class="sf-form-row">
                    <input type="checkbox" checked="checked" id="installadmins" name="installadmins" tabindex="2"/>
                    <label class="wp-core-ui" for="installadmins">
                        <?php SP()->primitives->admin_etext('Make all WordPress Administrators be Simple:Press Admins'); ?>
                        <span class="sf-sublabel sf-sublabel-small">
                            <?php SP()->primitives->admin_etext('Any user can be a Simple:Press Admin, but at installation, only the WordPress Administrator performing the install is made one'); ?>
                        </span>
                    </label>
                </div>
                <div class="sf-form-row">
                    <button type="submit" class="sf-button-primary" id="sbutton" name="goinstall">
                        <span class="sf-icon sf-install"></span><?php SP()->primitives->admin_etext('Perform Installation'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

# set up upgrade

function sp_upgrade_required() {
    ?>
    <div id="sf-root-wrap" class="wrap">
        <div id="sfmaincontainer" class="sf-installation">
			<?php
			$bad = sp_version_checks();
			if ($bad != '') {
				echo $bad . '</div>';
				return;
			}
			?>

			<div class="sf-panel-body">
				<img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" />
				<h3><?php echo sprintf(SP()->primitives->admin_text('Upgrade Simple:Press From Version %s to %s'), SP()->options->get('sfversion'), SPVERSION); ?>
					(<?php SP()->primitives->admin_etext('Build'); ?> <?php echo SP()->options->get('sfbuild'); ?> <?php SP()->primitives->admin_etext('to'); ?> <?php SP()->primitives->admin_etext('Build'); ?> <?php echo SPBUILD; ?>)
				</h3>
				<p><?php echo SP()->primitives->admin_text('As with all WordPress related updates we recommend that you backup your site before proceeding with this upgrade.') ?></p>				
			</div>
			<hr />
			<?php
			$f = 'update' . str_replace('.', '-', SPVERSION) . '.html';
			$path = SP_PLUGIN_DIR . '/sp-startup/install/resources/versions/' . $f;
			if (file_exists($path)) {
				readfile($path);
			}
			?>
			<form name="sfupgrade" method="post" action="<?php echo admin_url('admin.php?page=' . SPINSTALLPATH); ?>">
				<?php if (SPVERSION == '5.0.0' && substr(SP()->options->get('sfversion'), 0, 1) != '5') { ?>
					<p><b><input type="checkbox" name="dostorage" id="dostorage" />
							<label for="dostorage"><?php SP()->primitives->admin_etext('Check this box to have the upgrade attempt to convert current storage locations to V5 format (optional)'); ?></label>
						</b></p>
				<?php } ?>

				<input type="submit" class="sf-button-primary" id="sbutton" name="goupgrade" value="<?php SP()->primitives->admin_etext('Perform Upgrade'); ?>" />
				<?php if (is_multisite() && is_super_admin()) { ?>
					<input type="submit" class="sf-button-primary" id="sbutton" name="gonetworkupgrade" value="<?php SP()->primitives->admin_etext('Perform Network Upgrade'); ?>" />
				<?php } ?>
			</form>
		</div>
    </div>
    <?php
}

# perform install

function sp_go_install() {
    global $current_user;

    add_option('sfInstallID', $current_user->ID); # use wp option table

    if (!array_key_exists('sample', $_POST)) {
        $_POST['sample'] = '';
    }

    $phpfile = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL . 'install&sample=' . $_POST['sample'] . '&installadmins=' . $_POST['installadmins'] . '&pagename=' . $_POST['pagename'], 'install'));
    $image = SPCOMMONIMAGES . 'working.gif';

    # how many users passes at 200 a pop?
    $users = SP()->DB->count(SPUSERS);

    $subphases = ceil($users / 200);
    $nextsubphase = 1;
    ?>
    <div id="sf-root-wrap" class="wrap">
        <div id="sfmaincontainer" class="sf-installation">
            <div class="sf-panel-head">
                <h3><?php SP()->primitives->admin_etext('Simple:Press is being installed'); ?></h3>
            </div>
            <div class="sf-panel-body">
                <div class="sf-form-row">
                    <div>
                        <h1 id="installation-header"><?php echo SP()->primitives->admin_text('Installation is in progress - please wait'); ?></h1>
                    </div>
                    <div class="pbar" id="progressbar"></div>
                    <table id="SPLOADINSTALLtable">
                        <tr style="display:none"><td><div class="sf-zmessage" id="zone0"></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone1"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Tables created') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone2"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Permission data built') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone3"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Usergroup data built') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone4"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Creating forum pages') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone5"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Create default forum options') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone6"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Create storage location') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone7"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Create resources') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone8"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Create members data') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone9"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Create admin permissions') ?></div></td></tr>
                        <tr><td><div class="sf-zmessage" id="zone10"><span class="sf-icon sf-waiting"></span><?php echo SP()->primitives->admin_etext('Complete Installation') ?></div></td></tr>
                    </table>
                    <div class="sf-zmessage" id="errorzone"></div>
                    <form name="sfinstalldone" method="post" action="admin.php?page=<?php echo SP_FOLDER_NAME ?>/admin/panel-forums/spa-forums.php">
                        <input type="hidden" name="install" value="1" />
                        <button id="installation-finished" type="submit" class="sf-button-primary sfhidden" disabled name="goforuminstall" ><span class="sf-icon sf-admins"></span><?php echo SP()->primitives->admin_text('Go to Forum Admin') ?></button>
                    </form>
                </div>
            </div>
            <?php
            $pass = 11;
            $curr = 0;
            $messages = esc_js(SP()->primitives->admin_text('Go to Forum Admin')) . '@' .
                    esc_js(SP()->primitives->admin_text('Installation is in progress - please wait')) . '@' .
                    esc_js(SP()->primitives->admin_text('Installation Completed')) . '@' .
                    esc_js(SP()->primitives->admin_text('Installation has been Aborted')
            );
            $out = '<script>' . "\n";
            $out .= '(function(spj, $, undefined) {';
            $out .= 'spj.performInstall("' . $phpfile . '", "' . $pass . '", "' . $curr . '", "' . $subphases . '", "' . $nextsubphase . '", "' . $image . '", "' . $messages . '", "' . SP_FOLDER_NAME . '");' . "\n";
            $out .= '}(window.spj = window.spj || {}, jQuery));';
            $out .= '</script>' . "\n";
            echo $out;
            ?>
        </div>
    </div>
    <?php
}

# perform upgrade

function sp_go_upgrade($current_version, $current_build) {
    global $current_user;

    if (SPVERSION == '5.0.0') {
        $dostorage = false;
        if (isset($_POST['dostorage']))
            $dostorage = true;
        SP()->options->add('V5DoStorage', $dostorage);
    }

    update_option('sfInstallID', $current_user->ID); # use wp option table
    SP()->options->update('sfStartUpgrade', $current_build);

    $phpfile = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL . 'upgrade', 'upgrade')); # since passed to js cant have amp;
    $image = SPCOMMONIMAGES . 'working.gif';

    $targetbuild = SPBUILD;
    ?>
    <div id="sf-root-wrap" class="wrap">
		<div id="sfmaincontainer" class="sf-installation">
			<div class="sf-panel-head">
				<!-- <img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" /> -->
				<h3><?php SP()->primitives->admin_etext('Simple:Press is being upgraded to version '); echo SPVERSION ; echo ' / build #'; echo SPBUILD ?></h3>
			</div>
			<div class="sf-panel-body">
                <div class="pbar" id="progressbar"></div>
			</div>
			<div class="sf-zmessage" id="errorzone"></div>
			<div id="finishzone"></div>
			<div id="imagezone"></div>
			<div id="debug">
				<br />
				<p><b>If there are any messages shown below, please copy and include them on any support forum question you may have.</b></p>
			</div>
			<?php
			$messages = esc_js(SP()->primitives->admin_text('Go to Forum Admin')) . '@' . esc_js(SP()->primitives->admin_text('Upgrade is in progress - please wait')) . '@' . esc_js(SP()->primitives->admin_text('Upgrade Completed - please upgrade your plugins and themes to the latest versions!')) . '@' . esc_js(SP()->primitives->admin_text('Upgrade Aborted')) . '@' . esc_js(SP()->primitives->admin_text('Go to Forum'));
			$out = '<script>' . "\n";
			$out .= '(function(spj, $, undefined) {';
			$out .= 'spj.performUpgrade("' . $phpfile . '", "' . $current_build . '", "' . $targetbuild . '", "' . $current_build . '", "' . $image . '", "' . $messages . '", "' . SP()->spPermalinks->get_url() . '", "' . SP_FOLDER_NAME . '");' . "\n";
			$out .= '}(window.spj = window.spj || {}, jQuery));';
			$out .= '</script>' . "\n";
			echo $out;
			?>
		</div>
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
    <div class="wrap">
        <div class="updated">
            <img class="stayleft" src="<?php echo SPCOMMONIMAGES; ?>sp-mini-logo.png" alt="" title="" />
            <h3><?php SP()->primitives->admin_etext('Simple:Press is upgrading the Network.'); ?></h3>
        </div>
        <div id="sf-root-wrap" class="wrap">
            <div class="imessage" id="imagezone"></div>
        </div>
        <div class="pbar" id="progressbar"></div>
        <div id="sf-root-wrap" class="wrap">
            <div class="sf-zmessage" id="errorzone"></div>
            <div id="finishzone"></div>
        </div>
        <div id="debug">
            <p><b>Please copy the details below and include them on any support forum question you may have:</b></p>
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
        $installed = SP()->DB->select('SELECT option_id FROM ' . $wpdb->prefix . "sfoptions WHERE option_name='sfversion'");
        if ($installed) {
            $phpfile = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL . 'upgrade&sfnetworkid=' . $site->blog_id, 'upgrade'));
            $image = SPCOMMONIMAGES . 'working.gif';
            $targetbuild = SPBUILD;
            update_option('sfInstallID', $current_user->ID); # use wp option table
            # save the build info
            $out = SP()->primitives->admin_text('Upgrading Network Site ID') . ': ' . $site->blog_id . '';
            SP()->options->update('sfStartUpgrade', $current_build);

            # upgrade the network site
            $messages = esc_js(SP()->primitives->admin_text('Go to Forum Admin')) . '@' . esc_js(SP()->primitives->admin_text('Upgrade is in progress - please wait')) . '@' . esc_js(SP()->primitives->admin_text('Upgrade Completed - please upgrade your plugins and themes to the latest versions!')) . '@' . esc_js(SP()->primitives->admin_text('Upgrade Aborted')) . '@' . esc_js(SP()->primitives->admin_text('Go to Forum'));
            $out .= '<script>' . "\n";
            $out .= '(function(spj, $, undefined) {';
            $out .= 'spj.performUpgrade("' . $phpfile . '", "' . $current_build . '", "' . $targetbuild . '", "' . $current_build . '", "' . $image . '", "' . $messages . '", "' . SP()->spPermalinks->get_url() . '", "' . SP_FOLDER_NAME . '");' . "\n";
            $out .= '}(window.spj = window.spj || {}, jQuery));';
            $out .= '</script>' . "\n";
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

    $logo = '<div class="error"><img src="' . SPCOMMONIMAGES . 'sp-full-logo.png" alt="" title="" /><hr />';

    $xml = sp_load_version_xml(false, false);
    if ($xml) {
        # WordPress version check
        if (sp_version_compare(SP_WP_VER, $wp_version) == false) {
            $message .= $logo;
            $message .= '<h3>' . sprintf(SP()->primitives->admin_text('%s Version %s'), 'WordPress', $wp_version) . '</h3>';
            $message .= '<p>' . sprintf(SP()->primitives->admin_text('Your version of %s is not supported by %s %s'), 'WordPress', 'Simple:Press', SPVERSION) . '';
            $message .= sprintf(SP()->primitives->admin_text('%s version %s or above is required'), 'WordPress', SP_WP_VER) . '</p>';
            $logo = '<hr />';
        }

        # MySQL Check
        $mysql_required = (string) $xml->core->mysql_ver;
        if (sp_version_compare($mysql_required, $wpdb->db_version()) == false) {
            $message .= $logo;
            $message .= '<h3>' . sprintf(SP()->primitives->admin_text('%s Version %s'), 'MySQL', $wpdb->db_version()) . '</h3>';
            $message .= '<p>' . sprintf(SP()->primitives->admin_text('Your version of %s is not supported by %s %s'), 'MySQL', 'Simple:Press', SPVERSION) . '';
            $message .= sprintf(SP()->primitives->admin_text('%s version %s or above is required'), 'MySQL', $mysql_required) . '</p>';
            $logo = '<hr />';
            $testtable = false;
        }

        # PHP Check
        $php_required = (string) $xml->core->php_ver;
        if (sp_version_compare($php_required, phpversion()) == false) {
            $message .= $logo;
            $message .= '<h3>' . sprintf(SP()->primitives->admin_text('%s Version %s'), 'PHP', phpversion()) . '</h3>';
            $message .= '<p>' . sprintf(SP()->primitives->admin_text('Your version of %s is not supported by %s %s'), 'PHP', 'Simple:Press', SPVERSION) . '';
            $message .= sprintf(SP()->primitives->admin_text('%s version %s or above is required'), 'PHP', $php_required) . '</p>';
            $logo = '<hr />';
        }

        # test we can create database tables
        if ($testtable) {
            if (sp_test_table_create() == false) {
                $message .= $logo;
                $message .= '<h3>' . SP()->primitives->admin_text('Database Problem') . '</h3>';
                $message .= '<p>' . sprintf(SP()->primitives->admin_text('%s can not Create Tables in your database'), 'Simple:Press') . '</p>';
            }
        }
    } else {
        echo '<div class="error">';
        echo '<span>' . SP()->primitives->admin_text('Please Note') . '</span>';
        echo SP()->primitives->admin_text('We were unable to establish version details to ensure your system meets requirements') . '.';
        echo SP()->primitives->admin_text('As a general rule - Simple:Press requires the same minimum versions of PHP and MySQL as WordPress and the most up to date version of WordPress itself') . '.</br />';
        echo SP()->primitives->admin_text('If in doubt, please read the');
        echo ' <a href="https://simple-press.com/documentation/installation/installation-information/system-requirements/" target="_blank"> ';
        echo SP()->primitives->admin_text('System Requirements');
        echo '</a> ';
        echo SP()->primitives->admin_text('page in our Online Documentation');
        echo '</div>';
    }

    if ($message)
        $message .= '</div>';
    return $message;
}

function sp_version_compare($need, $got) {
    $need = explode('.', $need);
    $got = explode('.', $got);

    if (isset($need[0]) && intval($need[0]) > intval($got[0]))
        return false;
    if (isset($need[0]) && intval($need[0]) < intval($got[0]))
        return true;

    if (isset($need[1]) && intval($need[1]) > intval($got[1]))
        return false;
    if (isset($need[1]) && intval($need[1]) < intval($got[1]))
        return true;

    if (isset($need[2]) && intval($need[2]) > intval($got[2]))
        return false;
    return true;
}

function sp_test_table_create() {
    # make sure we can create database tables
    $sql = '
		CREATE TABLE sfCheckCreate (
			id int(4) NOT NULL,
			item varchar(15) default NULL,
			PRIMARY KEY	 (id)
		) ' . SP()->DB->charset();
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
    $logo = '<div class="error"><img src="' . SPCOMMONIMAGES . 'sp-full-logo.png" alt="" title="" /><hr />';

    if (!is_writable(SP_STORE_DIR)) {
        $message .= $logo;
        $message .= '<h3>' . SP()->primitives->admin_text('Permission Problem') . '</h3>';
        $message .= '<p>' . sprintf(SP()->primitives->admin_text('%s can not create sub-folders under wp-content. Please assign correct permissions and re-run the install'), 'Simple:Press') . '</p>';
    }
    return $message;
}
