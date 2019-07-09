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
                                            <li class="sf-list-item-depth-0 spLayerToggle">
                                                <div class="sf-list-item">
                                                    <span class="sf-item-name"><?php echo esc_js(SP()->primitives->admin_text('Show Members')) ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <?php spa_temp_no_members_selected_form() ?>
                                                </div>
                                            </li>
                                            <li class="sf-list-item-depth-0 spLayerToggle">
                                                <div class="sf-list-item">
                                                    <span class="sf-item-name"><?php SP()->primitives->admin_etext('Add Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <?php spa_temp_no_members_selected_form() ?>
                                                </div>
                                            </li>
                                            <li class="sf-list-item-depth-0 spLayerToggle">
                                                <div class="sf-list-item">
                                                    <span class="sf-item-name"><?php SP()->primitives->admin_etext('Move Members'); ?></span>
                                                    <span class="sf-item-controls">
                                                        <a class="sf-item-edit _spLayerToggle"></a>
                                                    </span>
                                                </div>
                                                <div class="sf-inline-edit sfinline-form">
                                                    <?php spa_temp_no_members_selected_form() ?>
                                                </div>
                                            </li>
                                            <li class="sf-list-item-depth-0 spLayerToggle">
                                                <div class="sf-list-item">
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
                                            data-form="delusergroup" data-url="<?php echo $base; ?>"
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
            echo '<div class="sfempty">' . SP()->primitives->admin_text('There are no User Groups defined') . '</div>';
        }
        spa_paint_close_fieldset();
        spa_paint_close_container();
        spa_paint_close_tab();

        if ($usergroups) {
            ?><div class="sf-mobile-hide"><?php
            spa_members_not_belonging_to_any_usergroup_tab($usergroups);
            ?></div><?php
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

function spa_members_not_belonging_to_any_usergroup_tab($usergroups) {
    spa_paint_open_nohead_tab(true);
    $totalMembers = 300;
    ?>
    <div class="sf-panel-body-top">
        <div class="sf-panel-body-top-left">
            <h4><?php echo SP()->primitives->admin_text('Members Not Belonging To Any Usergroup') ?></h4>
        </div>
        <div class="sf-panel-body-top-right">
            <div class="sf-input-group sf-input-small sf-input-rounded">
                <div class="sf-form-control sf-select-wrap">
                    <select>
                        <option value=""><?php echo SP()->primitives->admin_text('Select User Group') ?></option>
                        <?php foreach ($usergroups as $usergroup) : ?>
                            <option value="<?php echo $usergroup->usergroup_id ?>"><?php echo SP()->displayFilters->title($usergroup->usergroup_name); ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="sf-input-group-addon">
                    <button class="sf-input-group-btn sf-button-primary"><?php echo SP()->primitives->admin_text('Move') ?></button>
                </div>
            </div>
            <p class="search-box">
                <input type="search" name="s" value="">
                <input type="submit" id="search-submit" class="button" value="Search Members"> 
            </p>
            <?php echo spa_paint_help('...', '...') ?>
        </div>
    </div>

    <table class="widefat sf-table-small sf-table-mobile">
        <thead>
            <tr class="sf-v-a-middle" class="sf-narrow">
                <th class="sf-narrow"><input type="checkbox"></th>
                <th>
                    <div class="sf-alphabet">
                        <button class="sf-button sf-active"><?php echo SP()->primitives->admin_text('All') ?></button>
                        <button class="sf-button">0 - 9</button>
                        <button class="sf-button">A</button>
                        <button class="sf-button">B</button>
                        <button class="sf-button">C</button>
                        <button class="sf-button">D</button>
                        <button class="sf-button">E</button>
                        <button class="sf-button">F</button>
                        <button class="sf-button">G</button>
                        <button class="sf-button">H</button>
                        <button class="sf-button">I</button>
                        <button class="sf-button">J</button>
                        <button class="sf-button">K</button>
                        <button class="sf-button">L</button>
                        <button class="sf-button">M</button>
                        <button class="sf-button">N</button>
                        <button class="sf-button">O</button>
                        <button class="sf-button">P</button>
                        <button class="sf-button">Q</button>
                        <button class="sf-button">R</button>
                        <button class="sf-button">S</button>
                        <button class="sf-button">T</button>
                        <button class="sf-button">U</button>
                        <button class="sf-button">V</button>
                        <button class="sf-button">W</button>
                        <button class="sf-button">X</button>
                        <button class="sf-button">Y</button>
                        <button class="sf-button">Z</button>
                    </div>
                </th>
                <th>
                    <div class="sf-pull-right">
                        <?php echo sprintf('%s %d %s', SP()->primitives->admin_text('Total'), $totalMembers, SP()->primitives->admin_text('Members')) ?>
                    </div>
                </th> 
            </tr>
        </thead>
        <tbody>
            <tr class="sp-v-a-middle">
                <td class="sf-narrow"><input type="checkbox"></td>
                <td colspan="2">
                    <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                    <span class="sf-user-name">Aria Smith</span>
                </td>
            </tr>
            <tr class="sp-v-a-middle">
                <td class="sf-narrow"><input type="checkbox"></td>
                <td colspan="2">
                    <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                    <span class="sf-user-name">Aria Smith</span>
                </td>
            </tr>
            <tr class="sp-v-a-middle">
                <td class="sf-narrow"><input type="checkbox"></td>
                <td colspan="2">
                    <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                    <span class="sf-user-name">Aria Smith</span>
                </td>
            </tr>
            <tr class="sp-v-a-middle">
                <td class="sf-narrow"><input type="checkbox"></td>
                <td colspan="2">
                    <div class="sf-avatar "><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                    <span class="sf-user-name">Aria Smith</span>
                </td>
            </tr>
            <tr class="sp-v-a-middle">
                <td class="sf-narrow"><input type="checkbox"></td>
                <td colspan="2">
                    <div class="sf-avatar"><img src="<?php echo SPADMINIMAGES . 'Avatar.png' ?>" alt="avatar"></div>
                    <span class="sf-user-name">Aria Smith</span>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="sf-pagination">
        <span class="sf-pagination-links">
            <a class="sf-first-page" href="#"></a>
            <a class="" href="#">1</a>
            <a class="" href="#">2</a>
            <a class="sf-current-page" href="#">3</a>
            <a class="" href="#">4</a>
            <a class="" href="#">5</a>
            <a class="" href="#">...</a>
            <a class="" href="#">8</a>
            <a class="sf-last-page" href="#"></a>
        </span>
    </div>

    <?php
    spa_paint_close_container();
    spa_paint_close_tab();
}

function spa_temp_no_members_selected_form() {
    ?>
    <form action="https://wp.loc.com/wp-admin/admin-ajax.php?action=usergroups-loader&amp;saveform=addmembers&amp;_wpnonce=af658fc87f" method="post" id="sfmembernew1" name="sfmembernew1" onsubmit="spj.addDelMembers('sfmembernew1', 'https://wp.loc.com/wp-admin/admin-ajax.php?action=memberships&amp;targetaction=add&amp;_wpnonce=02deb3262b', 'sfmsgspot', 'Please Wait - Processing', 'Users added', 0, 50, '#amid1');">
        <div class="sf-panel-body "><div class="sf-full-form"><input type="hidden" name="forum-adminform_membernew" value="65f6b26809">
                <div class="sf-panel-body-top">
                    <div class="sf-panel-body-top-left">
                        <h4>No members selected</h4>
                    </div>
                    <div class="sf-panel-body-top-right">
                        <p class="_search-box sf-input-group">
                            <input type="search" name="s" value="" placeholder="Search">
                        </p>
                        <div class="sf-input-group select-user-group">
                            <div class="sf-form-control sf-select-wrap">
                                <select>
                                    <option value="">Select User Group</option>
                                    <option value="1">Guests</option>
                                    <option value="2">Members</option>
                                    <option value="3">Moderators</option>
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
