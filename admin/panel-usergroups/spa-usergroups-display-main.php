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
    spa_paint_open_tab(SP()->primitives->admin_text('Manage User Groups'), true);
    spa_paint_open_fieldset(SP()->primitives->admin_text('User Groups'), true, '...');
    ?>

    <?php
    if ($usergroups) {
        ?>
        <table id="usergrouprow-<?php echo($usergroup->usergroup_id); ?>" class="widefat sf-table-small sf-table-mobile">
            <thead>
                <tr>
                    <th><?php echo SP()->primitives->admin_text('Group name') ?></th>
                    <th><?php echo SP()->primitives->admin_text('Default for') ?></th>
                    <th><?php echo SP()->primitives->admin_text('Moderator') ?></th>
                    <th><?php echo SP()->primitives->admin_text('Members') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($usergroups as $usergroup) :
                    # display the current usergroup information in table format
                    ?>
                    <tr class="sf-border-none">
                        <td><?php echo SP()->displayFilters->title($usergroup->usergroup_name); ?></td>
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
                            <span>
                                <?php echo($defLabel); ?>
                            </span>
                            <?php //sp_display_item_stats(SPMEMBERSHIPS, 'usergroup_id', $usergroup->usergroup_id, SP()->primitives->admin_text('Members')) ?>
                        </td>
                        <td>
                            <?php
                            if ($usergroup->usergroup_is_moderator == 1)
                                echo SP()->primitives->admin_etext("Yes");
                            else
                                echo SP()->primitives->admin_etext("No");
                            ?>
                        </td>
                        <td>
                            <?php
                            $base = wp_nonce_url(SPAJAXURL . 'usergroups-loader', 'usergroups-loader');
                            $target = "members-$usergroup->usergroup_id";
                            ?>
                            <input type="button" 
                                   id="show<?php echo $usergroup->usergroup_id; ?>"
                                   class="sf-button-secondary spUsergroupShowMembers" 
                                   value="<?php echo esc_js(SP()->primitives->admin_text('Show')) ?>" 
                                   data-url="<?php echo wp_nonce_url(SPAJAXURL . "usergroups&amp;ug=$usergroup->usergroup_id", 'usergroups') ?>"
                                   data-img="<?php echo SPADMINIMAGES ?>"
                                   data-id="<?php echo $usergroup->usergroup_id; ?>"
                                   />
                            <input type="button"
                                   id="remove<?php echo $usergroup->usergroup_id; ?>"
                                   class="sf-button-secondary spLoadForm"
                                   value="<?php SP()->primitives->admin_etext('Remove/Move'); ?>"
                                   data-form="delmembers" 
                                   data-url="<?php echo $base; ?>"
                                   data-target="<?php echo $target; ?>"
                                   data-img="<?php echo SPADMINIMAGES ?>"
                                   data-id="<?php echo $usergroup->usergroup_id; ?>"
                                   data-open="" 
                                   />
                            <input type="button"
                                   id="add<?php echo $usergroup->usergroup_id; ?>" 
                                   class="sf-button-secondary spLoadForm" 
                                   value="<?php SP()->primitives->admin_etext('Add'); ?>" 
                                   data-form="addmembers" 
                                   data-url="<?php echo $base; ?>" 
                                   data-target="<?php echo $target; ?>" 
                                   data-img="<?php echo SPADMINIMAGES ?>"
                                   data-id="<?php echo $usergroup->usergroup_id; ?>" 
                                   data-open="" 
                                   />
                        </td>
                        <td>
                            <div class="sf-item-controls sf-mobile-btns sf-mobile-stack-btns">
                                <?php
                                $base = wp_nonce_url(SPAJAXURL . 'usergroups-loader', 'usergroups-loader');
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
    ?>
    <div class="sfform-panel-spacer"></div>
    <table class="sfmaintable">
        <tr>
            <th scope="col"><?php SP()->primitives->admin_etext('Members Not Belonging To Any Usergroup') ?></th>
        </tr>
        <tr class="sfsubtable sfugrouptable">
            <td>
                <?php
                $site = wp_nonce_url(SPAJAXURL . 'usergroups&amp;ug=0', 'usergroups');
                $gif = SPCOMMONIMAGES . 'working.gif';
                $text = esc_js(SP()->primitives->admin_text('Show/Hide Members with No Memberships'));
                ?>
                <input type="button" id="show-0" class="sf-button-secondary spUsergroupShowMembers" value="<?php echo $text; ?>" data-url="<?php echo $site; ?>" data-img="<?php echo $gif; ?>" data-id="0" />
            </td>
        </tr>
        <tr class="sfinline-form"> <!-- This row will hold hidden forms for the current user group membership-->
            <td>
                <div id="members-0"></div>
            </td>
        </tr>
    </table>
    <?php
}

//function sp_paint_usergroup_tip($ugid, $ugname) {
//    $site = wp_nonce_url(SPAJAXURL . "usergroup-tip&amp;group=$ugid", 'usergroup-tip');
//    $title = esc_js($ugname);
//    echo "<input type='button' class='sf-button-secondary spOpenDialog' value='" . SP()->primitives->admin_text('User Group Usage') . "' data-site='$site' data-label='$title' data-width='600' data-height='0' data-align='center' />";
//}
