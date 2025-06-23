<?php
/*
  Simple:Press
  Admin Admins Current Admins Form
 */

if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}

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
    ?>
    <?php if ($adminrecords || $modrecords) : ?>
        <?php
            $adminsexist = true;
            spa_paint_tab_head(SP()->primitives->admin_text('Manage Admins and Moderators'), true);
            $ajaxURL = wp_nonce_url(SPAJAXURL . 'admins-loader&amp;saveform=manageadmin', 'admins-loader');
        ?>

        <div class='sf-panel-body'>
            <form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sfupdatecaps" name="sfupdatecaps">
                <?php sp_echo_create_nonce('forum-adminform_sfupdatecaps'); ?>
                <div class="sf-panel">
                    <fieldset class="sf-fieldset">
                        <div class="sf-panel-body-top">
                            <h4><?php SP()->primitives->admin_etext('Current Admins and Moderators'); ?></h4>
                            <?php spa_paint_help('manage-admins') ?>
                        </div>
                        <div class="sf-form-row">
                            <?php for ($x = 1; $x < 3; $x++) : ?>
                                <?php
                                $records = ($x === 1) ? $adminrecords : $modrecords;
                                if (empty($records)) {
                                    continue;
                                }
                                ?>
                                <?php foreach ($records as $adminId => $adminName) : ?>
                                    <?php
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

                                    ?>
                                    <div>
                                        <h3> <?php echo esc_html($adminName); ?> (<?php echo esc_html(SP()->primitives->admin_text('ID') . ': ' . $adminId . ')'); ?></h3>
                                        <input type="hidden" name="uids[]" value="<?php echo esc_attr($adminId); ?>" />

                                        <ul class="list-grid sf-ml-10">
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Options'), 'manage-opts[' . esc_attr($adminId) . ']', $manage_opts, $adminId); ?>
                                                <input type="hidden" name="old-opts[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_opts); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Forums'), 'manage-forums[' . esc_attr($adminId) . ']', $manage_forums, $adminId); ?>
                                                <input type="hidden" name="old-forums[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_forums); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage User Groups'), 'manage-ugs[' . esc_attr($adminId) . ']', $manage_ugs, $adminId); ?>
                                                <input type="hidden" name="old-ugs[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_ugs); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Permissions'), 'manage-perms[' . esc_attr($adminId) . ']', $manage_perms, $adminId); ?>
                                                <input type="hidden" name="old-perms[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_perms); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Components'), 'manage-comps[' . esc_attr($adminId) . ']', $manage_comps, $adminId); ?>
                                                <input type="hidden" name="old-comps[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_comps); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Plugins'), 'manage-plugins[' . esc_attr($adminId) . ']', $manage_plugins, $adminId); ?>
                                                <input type="hidden" name="old-plugins[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_plugins); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Users'), 'manage-users[' . esc_attr($adminId) . ']', $manage_users, $adminId); ?>
                                                <input type="hidden" name="old-users[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_users); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Toolbox'), 'manage-tools[' . esc_attr($adminId) . ']', $manage_tools, $adminId); ?>
                                                <input type="hidden" name="old-tools[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_tools); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Profiles'), 'manage-profiles[' . esc_attr($adminId) . ']', $manage_profiles, $adminId); ?>
                                                <input type="hidden" name="old-profiles[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_profiles); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Themes'), 'manage-themes[' . esc_attr($adminId) . ']', $manage_themes, $adminId); ?>
                                                <input type="hidden" name="old-themes[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_themes); ?>" />
                                            </li>
                                            <li>
                                                <?php spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Integration'), 'manage-integration[' . esc_attr($adminId) . ']', $manage_integration, $adminId); ?>
                                                <input type="hidden" name="old-integration[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_integration); ?>" />
                                            </li>
                                            <li>
                                                <?php if ($adminId == SP()->user->thisUser->ID) { ?>
                                                    <?php spa_render_caps_checkbox(
                                                        SP()->primitives->admin_text('Manage Admins'),
                                                        '',
                                                        $manage_admins,
                                                        $adminId,
                                                        true
                                                    ); ?>
                                                    <input type="hidden" name="manage-admins[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_admins); ?>" />
                                                <?php } else {
                                                    spa_render_caps_checkbox(SP()->primitives->admin_text('Manage Admins'), 'manage-admins[' . esc_attr($adminId) . ']', $manage_admins, $adminId);
                                                }
                                                ?>
                                                <input type="hidden" name="old-admins[<?php echo esc_attr($adminId); ?>]" value="<?php echo esc_attr($manage_admins); ?>" />
                                            </li>
                                            <?php do_action('sph_admin_caps_list', $user); ?>
                                        </ul>
                                        <div class="sf-ml-10">
                                            <?php if ($adminId != SP()->user->thisUser->ID) : ?>
                                                <?php echo wp_kses(
                                                    spa_render_caps_checkbox( SP()->primitives->admin_text('Remove All Capabilities from this') . ' ' . $title, 'remove-admin[' .$adminId . ']', '', $adminId),
                                                    array(
                                                        'input' => array(
                                                            'type' => array(),
                                                            'name' => array(),
                                                            'id' => array(),
                                                            'checked' => array(),
                                                            'disabled' => array(),
                                                        ),
                                                        'label' => array(
                                                            'for' => array()
                                                        ),
                                                    )
                                                ); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            <?php endfor; ?>
                        </div>
                    </fieldset>
                </div>
                <div class="sf-form-submit-bar">
                    <input type="submit" class="sf-button-primary" id="savecaps" name="savecaps" value="<?php SP()->primitives->admin_etext('Update Admin Capabilities'); ?>" />
                </div>
            </form>
        </div>

        <?php $ajaxURL = wp_nonce_url(SPAJAXURL . 'admins-loader&amp;saveform=addadmin', 'admins-loader'); ?>

        <div class='sf-panel-body'>
            <form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sfaddadmins" name="sfaddadmins">
                <?php sp_echo_create_nonce('forum-adminform_sfaddadmins'); ?>
                <div class="sf-panel">
                    <fieldset class="sf-fieldset">
                        <div class="sf-panel-body-top">
                            <h4><?php SP()->primitives->admin_etext('Add New Admins'); ?></h4>
                            <?php spa_paint_help('addadmin') ?>
                        </div>
                        <div class="sf-form-row" style="border: 1px solid green;">
                            <?php
                            $action = 'addadmin';
                            require_once SP_PLUGIN_DIR . '/admin/library/ajax/spa-ajax-multiselect.php';
                            ?>

                            <h4><?php SP()->primitives->admin_etext('Select New Admin Capabilities'); ?></h4>

                            <ul class='list-grid sf-ml-10'>
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
                        </div>
                    <?php

                spa_paint_open_fieldset(SP()->primitives->admin_text('WP Admins but not Forum Admins'), false);

                $args = array(
                    'role' => 'administrator',
                    'fields' => array('ID', 'display_name'),
                );
                $wp_admins = get_users($args);

                $out = '';
                foreach ($wp_admins as $admin) {
                    if (!SP()->auths->forum_admin($admin->ID)) {
                        $out .= '<tr>';
                        $out .= '<td>';
                        $out .= $admin->ID;
                        $out .= '</td>';
                        $out .= '<td>';
                        $out .= "<div class='sf-avatar'><img src='" . get_avatar_url($admin->ID) . "' alt='avatar'></div>";
                        $out .= '</td>';
                        $out .= '<td>';
                        $out .= $admin->display_name;
                        $out .= '</td>';
                        $out .= '<td>';
                        $out .= '</td>';
                        $out .= '</tr>';
                    }
                }
                if (!$out) {
                    echo '<div class="sf-alert-block sf-info">';
                    SP()->primitives->admin_etext('No WP administrators that are not SPF admins were found');
                    echo '</div>';
                } else {
                    ?>
                    <table class="widefat sf-table-small sf-table-mobile">
                        <thead>
                            <tr>
                                <th scope="col"><?php SP()->primitives->admin_etext('User ID'); ?></th>
                                <th><?php SP()->primitives->admin_etext('Avatar'); ?></th>
                                <th scope="col"><?php SP()->primitives->admin_etext('Admin Name'); ?></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo wp_kses(
                                $out,
                                array(
                                    array(
                                        'tr' => array(),
                                        'td' => array(),
                                        'div' => array(
                                            'class' => array()
                                        ),
                                    )
                                )
                            ); ?>
                        </tbody>
                    </table>
                    <?php
                }

                do_action('sph_admins_manage_panel');
                ?>
                <div class="sf-form-submit-bar">
                    <input type="submit" class="sf-button-primary" id="savenew" name="savenew" value="<?php SP()->primitives->admin_etext('Add New Admins'); ?>" />
                </div>
            </form>
        </div>
        <?php endif;
    }

function spa_render_caps_checkbox($label, $name, $value, $user = 0, $disabled = false) {
    $pos = strpos($name, '[');
    if ($pos)
        $thisid = substr($name, 0, $pos) . $user;
    else
        $thisid = $name . $user;
    echo "<input type='checkbox' name='" . esc_attr($name) . "' id='sf-" . esc_attr($thisid) . "' ";
    if ($value)
        echo 'checked="checked" ';
    if ($disabled)
        echo 'disabled="disabled" ';
    echo '/>';
    echo "<label for='sf-" . esc_attr($thisid) . "'>" . esc_html($label) . "</label>";
}
