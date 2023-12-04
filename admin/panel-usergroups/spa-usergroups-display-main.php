<?php
/*
  Simple:Press
  Admin User Groups Main Display
  $LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
  $Rev: 15488 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('Access denied - you cannot directly call this file');

function spa_usergroups_usergroup_main() {
    $usergroups = spa_get_usergroups_all(null);
    $defaults = spa_get_mapping_data();
    ?>
    <div id="sf-tab-usergroup-main">
        <?php spa_paint_open_tab(SP()->primitives->admin_text('Manage User Groups'), true); ?>
        <?php spa_paint_open_fieldset(SP()->primitives->admin_text('User Groups'), true, 'manage-user-groups'); ?>
        <div class="sf-form-row">
            <?php if ($usergroups) : ?>
                <table id="sf-usergroup-table" class="widefat sf-table-small sf-table-mobile">
                    <thead>
                    <tr>
                        <th><?php echo SP()->primitives->admin_text('Name') ?></th>
                        <th><?php echo SP()->primitives->admin_text('Default for') ?></th>
                        <th><?php echo SP()->primitives->admin_text('Moderator') ?></th>
                        <th class="_sf-narrow"><?php echo SP()->primitives->admin_text('Members') ?></th>
                        <th class="_sf-narrow"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($usergroups as $usergroup) :
                        # display the current usergroup information in table format
                        ?>
                        <tr id="usergrouprow-<?php echo($usergroup->usergroup_id); ?>" class="sf-border-none">
                            <td class="sf-mobile-top-after">
                                <div class="sf-mobile-show sf-title"><?php echo SP()->primitives->admin_text('Group name') ?></div>
                                <div><?php echo SP()->displayFilters->title($usergroup->usergroup_name); ?></div>
                            </td>
                            <td>
                                <?php
                                if ($usergroup->usergroup_id == $defaults['sfdefgroup']) {
                                    $defLabel = SP()->primitives->admin_text('New Members');
                                } elseif ($usergroup->usergroup_id == $defaults['sfguestsgroup']) {
                                    $defLabel = SP()->primitives->admin_text('Guests');
                                } else {
                                    $defLabel = 'Moderators';
                                }
                                ?>
                                <div class="sf-mobile-show sf-title"><?php echo SP()->primitives->admin_text('Default for') ?></div>
                                <div><?php echo($defLabel); ?></div>
                                <?php //sp_display_item_stats(SPMEMBERSHIPS, 'usergroup_id', $usergroup->usergroup_id, SP()->primitives->admin_text('Members')) ?>
                            </td>
                            <td>
                                <div class="sf-mobile-show sf-title"><?php echo SP()->primitives->admin_text('Moderator') ?></div>
                                <div>
                                    <?php
                                    if ($usergroup->usergroup_is_moderator == 1)
                                        echo SP()->primitives->admin_etext("Yes");
                                    else
                                        echo SP()->primitives->admin_etext("No");
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div _style="min-width:370px">
                                    <?php
                                    $base = wp_nonce_url(SPAJAXURL . 'usergroups-loader', 'usergroups-loader');
                                    //$target = "members-$usergroup->usergroup_id";
                                    $target = ".sf-res-usergroup-$usergroup->usergroup_id";
                                    ?>
                                    <div class="sf-mobile-hide">
                                        <input type="button"
                                               id="show<?php echo $usergroup->usergroup_id; ?>"
                                               class="sf-button-secondary sf-button-small spUsergroupShowMembers"
                                               value="<?php echo esc_js(SP()->primitives->admin_text('Show')) ?>"
                                               data-url="<?php echo wp_nonce_url(SPAJAXURL . "usergroups&amp;ug=$usergroup->usergroup_id", 'usergroups') ?>"
                                               data-target="<?php echo $target; ?>"
                                               data-img="<?php echo SPADMINIMAGES . 'sp_WaitBox.gif' ?>"
                                               data-id="<?php echo $usergroup->usergroup_id; ?>"
                                        />
                                        <input type="button"
                                               id="remove<?php echo $usergroup->usergroup_id; ?>"
                                               class="sf-button-secondary sf-button-small spLoadForm"
                                               value="<?php SP()->primitives->admin_etext('Manage'); ?>"
                                               data-form="delmembers"
                                               data-url="<?php echo $base; ?>"
                                               data-target="<?php echo $target; ?>"
                                               data-img="<?php echo SPADMINIMAGES ?>"
                                               data-id="<?php echo $usergroup->usergroup_id; ?>"
                                               data-open=""
                                        />
                                        <input type="button"
                                               id="move<?php echo $usergroup->usergroup_id; ?>"
                                               class="sf-button-secondary sf-button-small spLoadForm"
                                               value="<?php SP()->primitives->admin_etext('Move'); ?>"
                                               data-form="delmembers"
                                               data-url="<?php echo $base; ?>"
                                               data-target="<?php echo $target; ?>"
                                               data-img="<?php echo SPADMINIMAGES ?>"
                                               data-id="<?php echo $usergroup->usergroup_id; ?>"
                                               data-open=""
                                               style="display: none"
                                        />
                                        <input type="button"
                                               id="add<?php echo $usergroup->usergroup_id; ?>"
                                               class="sf-button-secondary sf-button-small spLoadForm"
                                               value="<?php SP()->primitives->admin_etext('Add'); ?>"
                                               data-form="addmembers"
                                               data-url="<?php echo $base; ?>"
                                               data-target="<?php echo $target; ?>"
                                               data-img="<?php echo SPADMINIMAGES ?>"
                                               data-id="<?php echo $usergroup->usergroup_id; ?>"
                                               data-open=""
                                        />
                                    </div>
                                    <div class="sf-mobile-show">
                                        <ul class="sf-list sf-list-v2">
                                            <li class="">
                                                <div class="sf-list-item spLayerToggle spUsergroupShowMembers"
                                                     value="<?php echo esc_js(SP()->primitives->admin_text('Show')) ?>"
                                                     data-url="<?php echo wp_nonce_url(SPAJAXURL . "usergroups&amp;ug=$usergroup->usergroup_id", 'usergroups') ?>"
                                                     data-target=".sf-res-m-show-usergroup-<?php echo $usergroup->usergroup_id; ?>"
                                                     data-img="<?php echo SPADMINIMAGES . 'sp_WaitBox.gif' ?>"
                                                     data-id="<?php echo $usergroup->usergroup_id; ?>"
                                                >
                                                    <span class="sf-item-name"><?php echo SP()->primitives->admin_etext('Show Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <div class="sf-res-m-show-usergroup-<?php echo $usergroup->usergroup_id; ?>"></div>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="sf-list-item spLayerToggle spLoadForm"
                                                     data-form="addmembers"
                                                     data-url="<?php echo $base; ?>"
                                                     data-target=".sf-res-m-add-usergroup-<?php echo $usergroup->usergroup_id; ?>"
                                                     data-img="<?php echo SPADMINIMAGES ?>"
                                                     data-id="<?php echo $usergroup->usergroup_id; ?>"
                                                     data-open=""
                                                >
                                                    <span class="sf-item-name"><?php SP()->primitives->admin_etext('Add Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <div class="sf-res-m-add-usergroup-<?php echo $usergroup->usergroup_id; ?>"></div>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="sf-list-item spLayerToggle spLoadForm"
                                                     data-form="delmembers"
                                                     data-url="<?php echo $base; ?>"
                                                     data-target=".sf-res-m-move-usergroup-<?php echo $usergroup->usergroup_id; ?>"
                                                     data-img="<?php echo SPADMINIMAGES ?>"
                                                     data-id="<?php echo $usergroup->usergroup_id; ?>"
                                                     data-open=""
                                                >
                                                    <span class="sf-item-name"><?php SP()->primitives->admin_etext('Move Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <div class="sf-res-m-move-usergroup-<?php echo $usergroup->usergroup_id; ?>"></div>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="sf-list-item spLayerToggle spLoadForm"
                                                     data-form="delmembers"
                                                     data-url="<?php echo $base; ?>"
                                                     data-target=".sf-res-m-remove-usergroup-<?php echo $usergroup->usergroup_id; ?>"
                                                     data-img="<?php echo SPADMINIMAGES ?>"
                                                     data-id="<?php echo $usergroup->usergroup_id; ?>"
                                                     data-open=""
                                                >
                                                    <span class="sf-item-name"><?php SP()->primitives->admin_etext('Remove Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <div class="sf-res-m-remove-usergroup-<?php echo $usergroup->usergroup_id; ?>"></div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                            <td class="sf-mobile-top">
                                <div class="sf-item-controls sf-mobile-btns">
                                    <button class="sf-icon-button sf-small spOpenDialog"
                                            title='<?php echo SP()->primitives->admin_text('User Group Usage') ?>'
                                            data-site='<?php echo wp_nonce_url(SPAJAXURL . "usergroup-tip&amp;group={$usergroup->usergroup_id}", 'usergroup-tip') ?>'
                                            data-label='<?php echo esc_js(SP()->displayFilters->title($usergroup->usergroup_name)) ?>'
                                            data-width='600'
                                            data-height='0'
                                            data-align='center'
                                    >
                                        <span class="sf-icon sf-about"></span>
                                    </button>
                                    <button class="sf-icon-button sf-small spLoadForm"
                                            title="<?php echo SP()->primitives->admin_text('Edit User Group'); ?>"
                                            data-form="editusergroup"
                                            data-url="<?php echo $base; ?>"
                                            data-target="<?php echo $target; ?>"
                                            data-img="<?php echo SPADMINIMAGES ?>"
                                            data-id="<?php echo $usergroup->usergroup_id; ?>"
                                            data-open=""
                                    >
                                        <span class="sf-icon sf-edit"></span>
                                    </button>
                                    <button class="sf-icon-button sf-small spLoadForm"
                                            title="<?php echo SP()->primitives->admin_text('Delete User Group'); ?>"
                                            data-form="delusergroup"
                                            data-url="<?php echo $base; ?>"
                                            data-target="<?php echo $target; ?>"
                                            data-img="<?php echo SPADMINIMAGES ?>"
                                            data-id="<?php echo $usergroup->usergroup_id; ?>"
                                            data-open=""
                                    >
                                        <span class="sf-icon sf-delete"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="sfinline-form sf-border-none"> <!-- This row will hold ajax forms for the current user group -->
                            <td colspan="5" class="sf-padding-none">
                                <div class="sf-res-usergroup-<?php echo $usergroup->usergroup_id; ?> sf-mobile-hide"></div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            <?php else : ?>
                <?php echo '<div class="sf-alert-block sf-info">' . SP()->primitives->admin_text('There are no User Groups defined') . '</div>'; ?>
            <?php endif ?>
        </div>
        <?php
        spa_paint_close_fieldset();
        spa_paint_close_container();
        spa_paint_close_tab();

        if ($usergroups) {
            spa_members_not_belonging_to_any_usergroup_tab();
        }
        ?>
    </div>
    <?php
}

function spa_members_not_belonging_to_any_usergroup_tab() {
    spa_paint_open_nohead_tab(true, 'sf-filtering sf-mobile-hide');
    //$ajaxURL = wp_nonce_url(SPAJAXURL . 'memberships&amp;targetaction=add&amp;startNum=0&amp;batchNum=50', 'memberships');
    $ajaxURL = wp_nonce_url(SPAJAXURL . 'memberships&amp;targetaction=add', 'memberships');
    $target = 'sfmsgspot';
    $smessage = esc_js(SP()->primitives->admin_text('Please Wait - Processing'));
    $emessage = esc_js(SP()->primitives->admin_text('Users Deleted/Moved'));
    ?>
    <script>
        (function (spj, $, undefined) {
            $(document).ready(function () {
                $('#members_not_belonging_to_any_usergroup').keydown(function(event){

                    // Listen to enter and prevent
                    if (event.keyCode == 13) {
                        // Enter should not submit form
                        event.preventDefault();

                        // Get data from submit
                        let filter = $(this).find('[type="search"]');

                        // Load data in correct html-element
                        $(filter.attr('data-target')).load(
                            filter.attr('data-filter-url')
                            + '&filter=' + encodeURIComponent(filter.val())+ '&rnd='
                            + new Date().getTime()
                        )

                        // Make sure not to continue
                        return false;
                    }
                });

                $('#members_not_belonging_to_any_usergroup').submit(function (e) {
                    e.preventDefault();
                    spj.addDelMembers('members_not_belonging_to_any_usergroup'
                        , ''
                        , '<?php echo $target; ?>'
                        , '<?php echo $smessage; ?>'
                        , '<?php echo $emessage; ?>'
                        , 0
                        , 50
                        , '#dmid0',
                        'move_not_belonging'
                    );

                    $('#sfmsgspot').fadeOut(6000);
                });
            });
        }(window.spj = window.spj || {}, jQuery));
    </script>
    <form action="<?php echo $ajaxURL; ?>" method="get" id="members_not_belonging_to_any_usergroup">
        <?php echo sp_create_nonce('forum-adminform_membernew'); ?>
        <div class="sf-panel">
            <fieldset class="sf-fieldset">
                <div class="sf-panel-body-top">
                    <h4><?php echo SP()->primitives->admin_text('Members Not Belonging To Any Usergroup') ?></h4>
                    <?php echo spa_paint_help('manage-user-groups') ?>
                </div>
                <div class="sf-form-row">
                    Select users using the checkboxes, choose User Group from the dropdown and click the Move button to move users to that group.
                </div>
            </fieldset>
            <div style="display: flex;">
                <div style="flex-basis: 50%">
                    <select name="usergroup_id">
                        <option value=""><?php echo SP()->primitives->admin_text('Select User Group') ?></option>
                        <?php foreach (spa_get_usergroups_all(null) as $usergroup) : ?>
                            <option value="<?php echo $usergroup->usergroup_id ?>"><?php echo SP()->displayFilters->title($usergroup->usergroup_name); ?></option>
                        <?php endforeach ?>
                    </select>
                    <button class="sf-button-primary"><?php echo SP()->primitives->admin_text('Move selected users') ?></button>
                </div>
                <div style="flex-basis: 50%">
                    <p class="search-box" style="">
                        <input type="search" placeholder="<?php echo SP()->primitives->admin_text('Search members') ?>"
                               data-target=".sf-not-belonging-to-any-usergroup"
                               data-filter-url="<?php echo wp_nonce_url(SPAJAXURL . "usergroups&amp;ug_no=1", 'usergroups') ?>"
                        >
                    </p>

                </div>
            </div>
        </div>

        <div class="sf-not-belonging-to-any-usergroup">
            <?php
            ?>
            <?php spa_members_not_belonging_to_any_usergroup("", $_GET['filter'] ?? '') ?>

        </div>
        <span class="_sf-button sf-hidden-important" id='onFinish'></span>
        <div class="pbar" id="progressbar"></div>
    </form>
    <?php
    spa_paint_close_container();
    spa_paint_close_tab();
}
