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
    ?><div id="sf-tab-usergroup-main"><?php
    spa_paint_open_tab(SP()->primitives->admin_text('Manage User Groups'), true);
    spa_paint_open_fieldset(SP()->primitives->admin_text('User Groups'), true, '...');
    ?>

        <?php
        if ($usergroups) {
            ?>
            <table id="sf-usergroup-table" class="widefat sf-table-small sf-table-mobile">
                <thead>
                    <tr>
                        <th><?php echo SP()->primitives->admin_text('Group name') ?></th>
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
                                    $target = "members-$usergroup->usergroup_id";
                                    ?>
                                    <div class="sf-mobile-hide">
                                        <input type="button" 
                                               id="show<?php echo $usergroup->usergroup_id; ?>"
                                               class="sf-button-secondary sf-button-small spUsergroupShowMembers" 
                                               value="<?php echo esc_js(SP()->primitives->admin_text('Show')) ?>" 
                                               data-url="<?php echo wp_nonce_url(SPAJAXURL . "usergroups&amp;ug=$usergroup->usergroup_id", 'usergroups') ?>"
                                               data-img="<?php echo SPADMINIMAGES . 'sp_WaitBox.gif' ?>"
                                               data-id="<?php echo $usergroup->usergroup_id; ?>"
                                               />
                                        <input type="button"
                                               id="remove<?php echo $usergroup->usergroup_id; ?>"
                                               class="sf-button-secondary sf-button-small spLoadForm"
                                               value="<?php SP()->primitives->admin_etext('Remove'); ?>"
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
                                                <div class="sf-list-item spLayerToggle">
                                                    <span class="sf-item-name"><?php echo SP()->primitives->admin_etext('Show Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <?php spa_temp_no_members_selected_form() ?>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="sf-list-item spLayerToggle">
                                                    <span class="sf-item-name"><?php SP()->primitives->admin_etext('Add Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <?php spa_temp_no_members_selected_form() ?>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="sf-list-item spLayerToggle">
                                                    <span class="sf-item-name"><?php SP()->primitives->admin_etext('Move Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <?php spa_temp_no_members_selected_form() ?>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="sf-list-item spLayerToggle">
                                                    <span class="sf-item-name"><?php SP()->primitives->admin_etext('Remove Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <?php spa_temp_no_members_selected_form() ?>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                            <td class="sf-mobile-top">
                                <div class="sf-item-controls sf-mobile-btns" _style="min-width:150px;">
                                    <?php
                                    $target = "usergroup-$usergroup->usergroup_id";
                                    ?>
                                    <button class="sf-icon-button sf-small spOpenDialog"
                                            title='<?php echo SP()->primitives->admin_text('User Group Usage') ?>' 
                                            data-site='<?php echo wp_nonce_url(SPAJAXURL . "usergroup-tip&amp;group={$usergroup->usergroup_id}", 'usergroup-tip') ?>' 
                                            data-label='<?php echo esc_js(SP()->displayFilters->title($usergroup->usergroup_name)) ?>' 
                                            data-width='600' 
                                            data-height='0' 
                                            data-align='center' 
                                            >
                                        <span class="sf-icon sf-about sf-blue"></span>
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
                                        <span class="sf-icon sf-edit sf-blue"></span>
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
                                        <span class="sf-icon sf-delete sf-blue"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="sfinline-form sf-border-none"> <!-- This row will hold ajax forms for the current user group -->
                            <td colspan="5" class="sf-padding-none">
                                <div id="usergroup-<?php echo $usergroup->usergroup_id; ?>"></div>
                            </td>
                        </tr>
                        <tr class="sfinline-form"> <!-- This row will hold hidden forms for the current user group membership-->
                            <td colspan="5" class="sf-padding-none">
                                <div id="members-<?php echo $usergroup->usergroup_id; ?>"></div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <?php
        } else {
            echo '<div class="sf-alert-block sf-info">' . SP()->primitives->admin_text('There are no User Groups defined') . '</div>';
        }
        spa_paint_close_fieldset();
        spa_paint_close_container();
        spa_paint_close_tab();

        if ($usergroups) {
            ?>
            <div class="sf-mobile-hide">
                <?php spa_members_not_belonging_to_any_usergroup_tab(); ?>
            </div>
            <?php
        }
        ?>



        <!--<div class="sfform-panel-spacer"></div>
        <table class="sfmaintable">
            <tr>
                <th scope="col"><?php SP()->primitives->admin_etext('Members Not Belonging To Any Usergroup') ?></th>
            </tr>
            <tr class="sfsubtable sfugrouptable">
                <td>
                    <input type="button"
                           id="show-0"
                           class="sf-button-secondary spUsergroupShowMembers"
                           value="<?php echo esc_js(SP()->primitives->admin_text('Show/Hide Members with No Memberships')) ?>"
                           data-url="<?php echo wp_nonce_url(SPAJAXURL . 'usergroups&amp;ug=0', 'usergroups') ?>"
                           data-img="<?php echo SPCOMMONIMAGES . 'working.gif' ?>"
                           data-id="0"
                           />
                </td>
            </tr>
            <tr class="sfinline-form"> <!-- This row will hold hidden forms for the current user group membership-->
                <!--<td>
                    <div id="members-0"></div>
                </td>
            </tr>
        </table>-->

    </div>
    <?php
}

function spa_members_not_belonging_to_any_usergroup_tab() {
    spa_paint_open_nohead_tab(true, 'sf-not-belonging-to-any-usergroup');
    spa_members_not_belonging_to_any_usergroup();
    spa_paint_close_container();
    spa_paint_close_tab();
}

function spa_members_not_belonging_to_any_usergroup($filter = '', $pageNum = 1, $maxItemsOnPage = 3) {
    $alphabet = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $members = array();
    $pagination = array();
    $filter = trim($filter);
    if ($pageNum < 1) {
        $pageNum = 1;
    }
    $sql = 'SELECT COUNT(*)
        FROM ' . SPMEMBERS . '
        LEFT JOIN ' . SPMEMBERSHIPS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id
        WHERE ' . SPMEMBERSHIPS . '.usergroup_id IS NULL AND admin=0';

    $countTotal = SP()->DB->select($sql, 'var');

    $offset = $maxItemsOnPage * ($pageNum - 1);

    if ($offset < $countTotal) {
        $sql = 'SELECT ' . SPMEMBERS . '.user_id, display_name
            FROM ' . SPMEMBERS . '
            LEFT JOIN ' . SPMEMBERSHIPS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id
            WHERE ' . SPMEMBERSHIPS . ".usergroup_id IS NULL AND admin=0";
        if (mb_strlen($filter)) {
            $sql .= " AND display_name REGEXP '.*{$filter}.*'";
        }
        $sql .= " ORDER BY display_name LIMIT $offset, $maxItemsOnPage";

        $members = SP()->DB->select($sql);
        if ($members) {
            $countPages = ceil($countTotal / $maxItemsOnPage);
            $pagination = spa_pagination($countPages, $pageNum, 8, 2);
        }
    }
    ?>
    <div class="sf-panel-body-top">
        <div class="sf-panel-body-top-left sf-mobile-full-width">
            <h4><?php echo SP()->primitives->admin_text('Members Not Belonging To Any Usergroup') ?></h4>
        </div>
        <div class="sf-panel-body-top-right sf-mobile-full-width">
            <div class="sf-input-group sf-input-small sf-input-rounded">
                <div class="sf-form-control sf-select-wrap">
                    <select name="usergroup_id">
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
            <p class="search-box">
                <input type="search" name="s" value="<?php echo $filter; ?>" placeholder="<?php echo SP()->primitives->admin_text('Search members') ?>">
                <input type="submit" id="search-submit" class="button" value="Search Members"> 
            </p>
            <?php echo spa_paint_help('...', '...') ?>
        </div>
    </div>
    <?php if ($members): ?> 
        <table class="widefat sf-table-small sf-table-mobile">
            <thead>
                <tr class="sf-v-a-middle" class="sf-narrow">
                    <th class="sf-narrow"><input type="checkbox"></th>
                    <th>
                        <div class="sf-alphabet">
                            <button class="sf-button<?php echo (!mb_strlen($filter)) ? ' sf-active' : '' ?>"><?php echo SP()->primitives->admin_text('All') ?></button>
                            <button class="sf-button<?php echo $filter == '[0-9]' ? ' sf-active' : '' ?>" value="[0-9]">0 - 9</button>
                            <?php foreach ($alphabet as $letter): ?>
                                <button class="sf-button<?php echo strcasecmp($filter, $letter) == 0 ? ' sf-active' : '' ?>" value="<?php echo $letter ?>"><?php echo $letter ?></button>
                            <?php endforeach ?>
                        </div>
                    </th>
                    <th>
                        <div class="sf-pull-right">
                            <?php echo sprintf('%s %d %s', SP()->primitives->admin_text('Total'), $countTotal, SP()->primitives->admin_text('Members')) ?>
                        </div>
                    </th> 
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member) : ?>
                    <tr class="sp-v-a-middle">
                        <td class="sf-narrow"><input type="checkbox" name="dmid[]" value="<?php echo $member->user_id ?>"></td>
                        <td colspan="2">
                            <div class="sf-avatar"><img src="<?php echo get_avatar_url($member->user_id) ?>" alt="avatar"></div>
                            <span class="sf-user-name"><?php echo $member->display_name ?></span>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php if ($pagination): ?>
            <div class="sf-pagination">
                <span class="sf-pagination-links">
                    <a class="sf-first-page" href="javascript:void(0);"
                       ></a>
                       <?php foreach ($pagination['array'] as $n => $v): ?>
                        <a class="" href="javascript:void(0);"
                           ><?php echo $v ?></a>
                    <?php endforeach ?>
                    <a class="sf-last-page" href="javascript:void(0);"
                       ></a>
                </span>
            </div>
        <?php endif ?>
    <?php else: ?>
        <div class="sf-alert-block sf-info"><?php echo SP()->primitives->admin_text('List is empty') ?></div> 
    <?php endif ?>
    <?php
}

////////////////////////////////////////////////////////////////////
function spa_temp_no_members_selected_form() {
    ?>
    <form action="https://wp.loc.com/wp-admin/admin-ajax.php?action=usergroups-loader&amp;saveform=addmembers&amp;_wpnonce=af658fc87f" method="post" id="sfmembernew1" name="sfmembernew1" onsubmit="spj.addDelMembers('sfmembernew1', 'https://wp.loc.com/wp-admin/admin-ajax.php?action=memberships&amp;targetaction=add&amp;_wpnonce=02deb3262b', 'sfmsgspot', 'Please Wait - Processing', 'Users added', 0, 50, '#amid1');">
        <div class="sf-panel-body ">
            <div class="sf-full-form">
                <input type="hidden" name="forum-adminform_membernew" value="65f6b26809">
                <div class="sf-panel-body-top">
                    <div class="sf-panel-body-top-left sf-mobile-full-width">
                        <h4>No members selected</h4>
                    </div>
                    <div class="sf-panel-body-top-right sf-mobile-full-width">
                        <p class="search-box-v2 sf-input-group">
                            <input type="search" name="s" value="" placeholder="Search">
                        </p>
                        <div class="sf-input-group select-user-group">
                            <div class="sf-form-control sf-select-wrap">
                                <select name="usergroup_id">
                                    <option value=""><?php echo SP()->primitives->admin_text('Select User Group') ?></option>
                                    <?php foreach (spa_get_usergroups_all(null) as $usergroup) : ?>
                                        <option value="<?php echo $usergroup->usergroup_id ?>"><?php echo SP()->displayFilters->title($usergroup->usergroup_name); ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="sf-input-group-addon">
                                <button class="sf-input-group-btn sf-button-primary">Move</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sf-grid-4 sf-users-list">
                    <div class="sf-grid-item">
                        <input type="checkbox">
                        <div class="sf-avatar"><img src="https://wp.loc.com/wp-content/plugins/simple-press/admin/resources/images/Avatar.png" alt="avatar"></div>
                        <span class="sf-user-name">Aria Smith</span>
                    </div>
                    <div class="sf-grid-item">
                        <input type="checkbox">
                        <div class="sf-avatar"><img src="https://wp.loc.com/wp-content/plugins/simple-press/admin/resources/images/Avatar.png" alt="avatar"></div>
                        <span class="sf-user-name">Aria Smith</span>
                    </div>
                    <div class="sf-grid-item">
                        <input type="checkbox">
                        <div class="sf-avatar"><img src="https://wp.loc.com/wp-content/plugins/simple-press/admin/resources/images/Avatar.png" alt="avatar"></div>
                        <span class="sf-user-name">Aria Smith</span>
                    </div>
                    <div class="sf-grid-item">
                        <input type="checkbox">
                        <div class="sf-avatar"><img src="https://wp.loc.com/wp-content/plugins/simple-press/admin/resources/images/Avatar.png" alt="avatar"></div>
                        <span class="sf-user-name">Aria Smith</span>
                    </div>
                    <div class="sf-grid-item">
                        <input type="checkbox">
                        <div class="sf-avatar"><img src="https://wp.loc.com/wp-content/plugins/simple-press/admin/resources/images/Avatar.png" alt="avatar"></div>
                        <span class="sf-user-name">Aria Smith</span>
                    </div>
                    <div class="sf-grid-item">
                        <input type="checkbox">
                        <div class="sf-avatar"><img src="https://wp.loc.com/wp-content/plugins/simple-press/admin/resources/images/Avatar.png" alt="avatar"></div>
                        <span class="sf-user-name">Aria Smith</span>
                    </div>
                    <div class="sf-grid-item">
                        <input type="checkbox">
                        <div class="sf-avatar"><img src="https://wp.loc.com/wp-content/plugins/simple-press/admin/resources/images/Avatar.png" alt="avatar"></div>
                        <span class="sf-user-name">Aria Smith</span>
                    </div>
                    <div class="sf-grid-item">
                        <input type="checkbox">
                        <div class="sf-avatar"><img src="https://wp.loc.com/wp-content/plugins/simple-press/admin/resources/images/Avatar.png" alt="avatar"></div>
                        <span class="sf-user-name">Aria Smith</span>
                    </div>
                </div>

            </div></div>    </form>
    <?php
}

function ______________________123_temp() {
    ?>
    <span
        id="_show<?php echo $usergroup->usergroup_id; ?>"
        class="sf-button-secondary sf-button-small spUsergroupShowMembers" 
        data-url="<?php echo wp_nonce_url(SPAJAXURL . "usergroups&amp;ug=$usergroup->usergroup_id", 'usergroups') ?>"
        data-img="<?php echo SPADMINIMAGES . 'sp_WaitBox.gif' ?>"
        data-id="<?php echo $usergroup->usergroup_id; ?>"
        ><?php echo esc_js(SP()->primitives->admin_text('Show Members')) ?>
    </span>
    <button
        id="_add<?php echo $usergroup->usergroup_id; ?>" 
        class="sf-button-secondary sf-button-small spLoadForm"
        data-form="addmembers" 
        data-url="<?php echo $base; ?>" 
        data-target="<?php echo $target; ?>" 
        data-img="<?php echo SPADMINIMAGES ?>"
        data-id="<?php echo $usergroup->usergroup_id; ?>" 
        data-open="" 
        ><?php SP()->primitives->admin_etext('Add Members'); ?>
    </button>
    <button
        id="_move<?php echo $usergroup->usergroup_id; ?>"
        class="sf-button-secondary sf-button-small spLoadForm"
        data-form="delmembers" 
        data-url="<?php echo $base; ?>"
        data-target="<?php echo $target; ?>"
        data-img="<?php echo SPADMINIMAGES ?>"
        data-id="<?php echo $usergroup->usergroup_id; ?>"
        data-open="" 
        ><?php SP()->primitives->admin_etext('Move Members'); ?>
    </button>
    <button
        id="_remove<?php echo $usergroup->usergroup_id; ?>"
        class="sf-button-secondary sf-button-small spLoadForm"
        data-form="delmembers" 
        data-url="<?php echo $base; ?>"
        data-target="<?php echo $target; ?>"
        data-img="<?php echo SPADMINIMAGES ?>"
        data-id="<?php echo $usergroup->usergroup_id; ?>"
        data-open="" 
        ><?php SP()->primitives->admin_etext('Remove Members'); ?>
    </button>
    <?php
}
