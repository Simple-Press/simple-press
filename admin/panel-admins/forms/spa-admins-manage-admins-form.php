<?php
/*
  Simple:Press
  Admin Admins Current Admins Form
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('Access denied - you cannot directly call this file');

function spa_admins_manage_admins_form() {
    ?>
    <script>
        spj.loadAjaxForm('sfupdatecaps', 'sfreloadma');
        spj.loadAjaxForm('sfaddadmins', 'sfreloadma');
    </script>
    <?php
    $adminsexist = false;
    $adminrecords = SP()->core->forumData['forum-admins'];

    # get all the moderators
    $modrecords = array();
    $mods = SP()->DB->table(SPMEMBERS, 'moderator=1');
    if ($mods) {
        foreach ($mods as $mod) {
            $modrecords[$mod->user_id] = $mod->display_name;
        }
    }

    spa_paint_options_init();

    if ($adminrecords || $modrecords) {
        $adminsexist = true;

        $ajaxURL = wp_nonce_url(SPAJAXURL . 'admins-loader&amp;saveform=manageadmin', 'admins-loader');
        ?>
        <form action="<?php echo $ajaxURL; ?>" method="post" id="sfupdatecaps" name="sfupdatecaps">
            <?php echo sp_create_nonce('forum-adminform_sfupdatecaps'); ?>
            <?php
            spa_paint_open_tab(/* SP()->primitives->admin_text('Admins')." - ". */SP()->primitives->admin_text('Manage Admins and Moderators'), true);
            spa_paint_open_panel();
            spa_paint_open_fieldset(SP()->primitives->admin_text('Current Admins and Moderators'), 'true', 'manage-admins');
            for ($x = 1; $x < 3; $x++) {
                $records = ($x == 1) ? $adminrecords : $modrecords;
                if (empty($records))
                    continue;

                foreach ($records as $adminId => $adminName) {
                    $user = new WP_User($adminId);
                    $manage_opts = user_can($user, 'SPF Manage Options');
                    $manage_forums = user_can($user, 'SPF Manage Forums');
                    $manage_ugs = user_can($user, 'SPF Manage User Groups');
                    $manage_perms = user_can($user, 'SPF Manage Permissions');
                    $manage_comps = user_can($user, 'SPF Manage Components');
                    $manage_users = user_can($user, 'SPF Manage Users');
                    $manage_profiles = user_can($user, 'SPF Manage Profiles');
                    $manage_admins = user_can($user, 'SPF Manage Admins');
                    $manage_tools = user_can($user, 'SPF Manage Toolbox');
                    $manage_plugins = user_can($user, 'SPF Manage Plugins');
                    $manage_themes = user_can($user, 'SPF Manage Themes');
                    $manage_integration = user_can($user, 'SPF Manage Integration');

                    $title = ($x == 1) ? SP()->primitives->admin_text('Admin') : SP()->primitives->admin_text('Moderator');
                    spa_paint_open_fieldset($title . ': ' . $adminName, false);

                    echo SP()->primitives->admin_text('ID') . ': ' . $adminId . ' - ' . SP()->primitives->admin_text('Name') . ': <strong>' . $adminName . '</strong>';
                    ?>
                    <input type="hidden" name="uids[]" value="<?php echo $adminId; ?>" />

                    <ul class='sf-float-list'>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Options'), 'manage-opts[' . $adminId . ']', $manage_opts, $adminId); ?>
                            <input type="hidden" name="old-opts[<?php echo $adminId; ?>]" value="<?php echo $manage_opts; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Forums'), 'manage-forums[' . $adminId . ']', $manage_forums, $adminId); ?>
                            <input type="hidden" name="old-forums[<?php echo $adminId; ?>]" value="<?php echo $manage_forums; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage User Groups'), 'manage-ugs[' . $adminId . ']', $manage_ugs, $adminId); ?>
                            <input type="hidden" name="old-ugs[<?php echo $adminId; ?>]" value="<?php echo $manage_ugs; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Permissions'), 'manage-perms[' . $adminId . ']', $manage_perms, $adminId); ?>
                            <input type="hidden" name="old-perms[<?php echo $adminId; ?>]" value="<?php echo $manage_perms; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Components'), 'manage-comps[' . $adminId . ']', $manage_comps, $adminId); ?>
                            <input type="hidden" name="old-comps[<?php echo $adminId; ?>]" value="<?php echo $manage_comps; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Plugins'), 'manage-plugins[' . $adminId . ']', $manage_plugins, $adminId); ?>
                            <input type="hidden" name="old-plugins[<?php echo $adminId; ?>]" value="<?php echo $manage_plugins; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Users'), 'manage-users[' . $adminId . ']', $manage_users, $adminId); ?>
                            <input type="hidden" name="old-users[<?php echo $adminId; ?>]" value="<?php echo $manage_users; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Toolbox'), 'manage-tools[' . $adminId . ']', $manage_tools, $adminId); ?>
                            <input type="hidden" name="old-tools[<?php echo $adminId; ?>]" value="<?php echo $manage_tools; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Profiles'), 'manage-profiles[' . $adminId . ']', $manage_profiles, $adminId); ?>
                            <input type="hidden" name="old-profiles[<?php echo $adminId; ?>]" value="<?php echo $manage_profiles; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Themes'), 'manage-themes[' . $adminId . ']', $manage_themes, $adminId); ?>
                            <input type="hidden" name="old-themes[<?php echo $adminId; ?>]" value="<?php echo $manage_themes; ?>" />
                        </li>
                        <li>
                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Integration'), 'manage-integration[' . $adminId . ']', $manage_integration, $adminId); ?>
                            <input type="hidden" name="old-integration[<?php echo $adminId; ?>]" value="<?php echo $manage_integration; ?>" />
                        </li>
                        <li>
                            <?php
                            if ($adminId == SP()->user->thisUser->ID) {
                                ?>
                                <span class='sf-float-list-label'><?php echo SP()->primitives->admin_text('Manage Admins'); ?></span>
                                <input type="hidden" name="manage-admins[<?php echo $adminId ?>]" value="<?php echo $manage_admins; ?>" />
                                <img src="<?php echo SPADMINIMAGES . 'sp_Locked.png'; ?>" alt="" />
                                <?php
                            } else {
                                spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Admins'), 'manage-admins[' . $adminId . ']', $manage_admins, $adminId);
                            }
                            ?>
                            <input type="hidden" name="old-admins[<?php echo $adminId ?>]" value="<?php echo $manage_admins; ?>" />
                        </li>
                        <?php
                        do_action('sph_admin_caps_list', $user);
                        ?>      						</ul>

                    <div class="clearboth"></div>
                    <?php
                    if ($adminId != SP()->user->thisUser->ID) {
                        echo '<hr />';
                        spa_render_caps_checkbox(SP()->primitives->admin_text('Remove All Capabilities from this') . ' ' . $title, 'remove-admin[' . $adminId . ']', '', $adminId);
                    }
                    spa_paint_close_fieldset();
                }
            }
            spa_paint_close_fieldset();
            spa_paint_close_panel();

            spa_paint_close_container();
        }
        ?>
        <div class="sf-form-submit-bar">
            <input type="submit" class="sf-button-primary" id="savecaps" name="savecaps" value="<?php SP()->primitives->admin_etext('Update Admin Capabilities'); ?>" />
        </div>
        <?php
        spa_paint_close_tab();
        ?>
    </form>
    <?php
    $ajaxURL = wp_nonce_url(SPAJAXURL . 'admins-loader&amp;saveform=addadmin', 'admins-loader');
    ?>
    <form action="<?php echo $ajaxURL; ?>" method="post" id="sfaddadmins" name="sfaddadmins">
        <?php echo sp_create_nonce('forum-adminform_sfaddadmins'); ?>

        <?php
        //spa_paint_open_tab(SP()->primitives->admin_text('Manage Admins') . ' - ' . SP()->primitives->admin_text('Add Admins'), true);
        spa_paint_open_nohead_tab(true);
        //spa_paint_open_panel();
        spa_paint_open_fieldset(SP()->primitives->admin_text('Add New Admins'), false);
        ?>
        <table>
            <tr>
                <th><?php SP()->primitives->admin_etext('Select New Admin Users'); ?></th>
            </tr>
            <tr>
                <td>
                    <p><?php SP()->primitives->admin_etext('Select members to make Admins (use CONTROL for multiple users)'); ?></p>
                    <?php
                    $from = esc_js(SP()->primitives->admin_text('Eligible Members'));
                    $to = esc_js(SP()->primitives->admin_text('Selected Members'));
                    $action = 'addadmin';
                    require_once SP_PLUGIN_DIR . '/admin/library/ajax/spa-ajax-multiselect.php';
                    ?>
                    <div class="clearboth"></div>
                </td>
            </tr>
        </table>

        <p><strong><?php SP()->primitives->admin_etext('Select New Admin Capabilities'); ?></strong></p>

        <ul class='sf-float-list'>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Options'), 'add-opts', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Forums'), 'add-forums', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage User Groups'), 'add-ugs', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Permissions'), 'add-perms', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Components'), 'add-comps', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Users'), 'add-users', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Profiles'), 'add-profiles', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Admins'), 'add-admins', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Toolbox'), 'add-tools', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Plugins'), 'add-plugins', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Themes'), 'add-themes', 0); ?></li>
            <li><?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Integration'), 'add-integration', 0); ?></li>
        <?php do_action('sph_admin_caps_form', $user); ?>
        </ul>
        <?php
        spa_paint_close_fieldset();
        //spa_paint_close_panel();
        //spa_paint_open_panel();

        spa_paint_open_fieldset(SP()->primitives->admin_text('WP Admins but not Forum Admins'), false);
        ?>
        <table class="sfmaintable">
            <tr>
                <th scope="col"></th>
                <th><?php SP()->primitives->admin_etext('User ID'); ?></th>
                <th scope="col"><?php SP()->primitives->admin_etext('Admin Name'); ?></th>
                <th scope="col"></th>
            </tr>
            <?php
            $args = array(
                'role' => 'administrator',
                'fields' => array('ID', 'display_name'),
            );
            $wp_admins = get_users($args);

            $is_users = false;
            foreach ($wp_admins as $admin) {
                if (!SP()->auths->forum_admin($admin->ID)) {
                    echo '<tr>';
                    echo '<td></td>';
                    echo '<td>';
                    echo $admin->ID;
                    echo '</td>';
                    echo '<td>';
                    echo esc_html($admin->display_name);
                    echo '</td>';
                    echo '<td></td>';
                    echo '</tr>';
                    $is_users = true;
                }
            }
            if (!$is_users) {
                echo '<tr>';
                echo '<td></td>';
                echo '<td colspan="2">';
                SP()->primitives->admin_etext('No WP administrators that are not SPF admins were found');
                echo '</td>';
                echo '<td></td>';
                echo '</tr>';
            }
            ?>
        </table>
        <?php
        spa_paint_close_fieldset();
        //spa_paint_close_panel();

        do_action('sph_admins_manage_panel');

        spa_paint_close_container();
        ?>
        <div class="sf-form-submit-bar">
            <input type="submit" class="sf-button-primary" id="savenew" name="savenew" value="<?php SP()->primitives->admin_etext('Add New Admins'); ?>" />
        </div>
        <?php
        spa_paint_close_tab();
        ?>
    </form>
    <?php
}

function spa_render_caps_checkbox($label, $name, $value, $user = 0, $disabled = false) {
    $pos = strpos($name, '[');
    if ($pos)
        $thisid = substr($name, 0, $pos) . $user;
    else
        $thisid = $name . $user;
    echo "<input type='checkbox' name='$name' id='sf-$thisid' ";
    if ($value)
        echo 'checked="checked" ';
    if ($disabled)
        echo 'disabled="disabled" ';
    echo '/>';
    echo "<label for='sf-$thisid'>$label</label>";
}
