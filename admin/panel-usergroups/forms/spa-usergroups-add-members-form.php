<?php
/*
  Simple:Press
  Admin User Groups Add Member Form
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('Access denied - you cannot directly call this file');

function spa_usergroups_add_members_form($usergroup_id) {
    ?>
    <script>
        (function (spj, $, undefined) {
            $(document).ready(function () {
                $('#sfmembernew<?php echo $usergroup_id; ?>').ajaxForm({
                    target: '#sfmsgspot',
                });
            });
        }(window.spj = window.spj || {}, jQuery));
    </script>
    <?php
    spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL . 'usergroups-loader&amp;saveform=addmembers', 'usergroups-loader');
    $url = wp_nonce_url(SPAJAXURL . 'memberships&amp;targetaction=add', 'memberships');
    $target = 'sfmsgspot';
    $smessage = esc_js(SP()->primitives->admin_text('Please Wait - Processing'));
    $emessage = esc_js(SP()->primitives->admin_text('Users added'));
    ?>
    <form action="<?php echo $ajaxURL; ?>" method="post" id="sfmembernew<?php echo $usergroup_id; ?>" name="sfmembernew<?php echo $usergroup_id ?>" onsubmit="spj.addDelMembers('sfmembernew<?php echo $usergroup_id ?>', '<?php echo $url; ?>', '<?php echo $target; ?>', '<?php echo $smessage; ?>', '<?php echo($emessage); ?>', 0, 50, '#amid<?php echo $usergroup_id; ?>');">
        <?php
        spa_paint_open_nohead_tab(true, '');
        echo sp_create_nonce('forum-adminform_membernew');
        ?>
        <div class="sf-panel-body-top">
            <div class="sf-panel-body-top-left sf-mobile-full-width">
                <h4><?php echo SP()->primitives->admin_text('No members selected') ?></h4>
            </div>
            <div class="sf-panel-body-top-right sf-mobile-full-width">
                <p class="search-box-v2 sf-input-group">
                    <input type="search" name="s" value="" placeholder="<?php echo SP()->primitives->admin_text('Search') ?>">
                </p>
                <div class="sf-input-group select-user-group">
                    <div class="sf-form-control sf-select-wrap">
                        <select>
                            <option value=""><?php echo SP()->primitives->admin_text('Select User Group') ?></option>
                            <?php foreach (spa_get_usergroups_all(null) as $usergroup) : ?>
                                <option value="<?php echo $usergroup->usergroup_id ?>"><?php echo SP()->displayFilters->title($usergroup->usergroup_name); ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="sf-input-group-addon">
                        <button class="sf-input-group-btn sf-button-primary"><?php echo SP()->primitives->admin_text('Move') ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="sf-grid-4 sf-users-list">
            <div class="sf-grid-item">
                <input type="checkbox">
                <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                <span class="sf-user-name">Aria Smith</span>
            </div>
            <div class="sf-grid-item">
                <input type="checkbox">
                <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                <span class="sf-user-name">Aria Smith</span>
            </div>
            <div class="sf-grid-item">
                <input type="checkbox">
                <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                <span class="sf-user-name">Aria Smith</span>
            </div>
            <div class="sf-grid-item">
                <input type="checkbox">
                <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                <span class="sf-user-name">Aria Smith</span>
            </div>
            <div class="sf-grid-item">
                <input type="checkbox">
                <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                <span class="sf-user-name">Aria Smith</span>
            </div>
            <div class="sf-grid-item">
                <input type="checkbox">
                <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                <span class="sf-user-name">Aria Smith</span>
            </div>
            <div class="sf-grid-item">
                <input type="checkbox">
                <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                <span class="sf-user-name">Aria Smith</span>
            </div>
            <div class="sf-grid-item">
                <input type="checkbox">
                <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                <span class="sf-user-name">Aria Smith</span>
            </div>
        </div>

        <?php
        /*$sfmemberopts = array();
        $sfmemberopts = SP()->options->get('sfmemberopts');

        if (!isset($sfmemberopts['sfsinglemembership'])) {
            $singleGrp = false;
        } else {
            $singleGrp = $sfmemberopts['sfsinglemembership'];
        }
        $singleOpt = ($singleGrp) ? SP()->primitives->admin_text('On') : SP()->primitives->admin_text('Off');
        $singleMsg = ($singleGrp) ? SP()->primitives->admin_text('Any members moved will be deleted from current user group memberships') : SP()->primitives->admin_text('Any members moved will be retained in current user group memberships');
        ?>
        <input type="hidden" name="usergroup_id" value="<?php echo $usergroup_id; ?>" />
        <p><?php SP()->primitives->admin_etext('Select members to add (use CONTROL for multiple members)'); ?></p>
        <p><br /><?php SP()->primitives->admin_etext('The Option'); ?> <b><?php SP()->primitives->admin_etext('Users are limited to single usergroup membership'); ?></b> <?php echo sprintf(SP()->primitives->admin_text("is turned %s"), $singleOpt); ?></b><br /><?php echo $singleMsg; ?></p>
        <?php
        $from = esc_js(SP()->primitives->admin_text('Eligible Members'));
        $to = esc_js(SP()->primitives->admin_text('Selected Members'));
        $action = 'addug';
        require_once SP_PLUGIN_DIR . '/admin/library/ajax/spa-ajax-multiselect.php';
        ?>
        <div class="clearboth"></div>
        <?php
        do_action('sph_usergroup_add_member_panel');
        ?>
        <span><input type="submit" class="sf-button-primary" id="sfmembernew<?php echo $usergroup_id; ?>" name="sfmembernew<?php echo $usergroup_id; ?>" value="<?php SP()->primitives->admin_etext('Add Members'); ?>" /> <span class="sf-button sfhidden" id='onFinish'></span>
            <input type="button" class="sf-button-primary spCancelForm" data-target="#members-<?php echo $usergroup_id; ?>" id="sfmembernew<?php echo $usergroup_id; ?>" name="addmemberscancel<?php echo $usergroup_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" /></span>
        <br />
        <div class="pbar" id="progressbar"></div>
        <?php */
        spa_paint_close_container();
        spa_paint_close_tab();
        ?>
    </form>
    <?php
}
