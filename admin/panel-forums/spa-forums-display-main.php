<?php
/*
  Simple:Press
  Admin Forums Main Display
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('Access denied - you cannot directly call this file');

function spa_forums_forums_main() {
    ?><div id="sf-tab-forums-main"><?php
    spa_paint_tab_head(SP()->primitives->admin_text('Forums') . ' - ' . SP()->primitives->admin_text('Manage Groups And Forums'));
    # has SP just been installed?
    if (isset($_POST['install'])) {
        $site = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL . 'troubleshooting&install=1', 'troubleshooting'));
        $target = 'sfmaincontainer';
        ?>
            <script>
                (function (spj, $, undefined) {
                    $(document).ready(function () {
                        spj.troubleshooting("<?php echo($site); ?>", "<?php echo($target); ?>");
                    });
                }(window.spj = window.spj || {}, jQuery));
            </script>
            <?php
        }

        # check if sample data is to be removed
        if (isset($_POST['spSampleDelDo'])) {
            # delete sample data
            $groups = SP()->DB->table(SPGROUPS, 'sample=1');
            if ($groups) {
                require_once SP_PLUGIN_DIR . '/admin/panel-forums/support/spa-forums-save.php';
                foreach ($groups as $group) {
                    spa_delete_sample($group->group_id);
                }
                SP()->options->update('spSample', false);
            }
        }

        $groups = SP()->DB->table(SPGROUPS, '', '', 'group_seq');
        if ($groups) {
            //if (SP()->options->get('spSample')) {
            //    echo '<form action="' . SPADMINFORUM . '" method="post" id="spSampleDel" name="spSampleDel">';
            //    echo sp_create_nonce('forum-adminform_groupdelete');
            //    echo '<input type="submit" class="sf-button-primary" name="spSampleDelDo" id="spSampleDelDo" value="Delete Sample Data" />';
            //    echo '</form>';
            //}

            foreach ($groups as $group) {
                spa_paint_open_nohead_tab(true, '');
                if (empty($group->group_icon)) {
                    $icon = SPTHEMEICONSURL . 'sp_GroupIcon.png';
                } else {
                    $icon = esc_url(SPCUSTOMURL . $group->group_icon);
                    if (!file_exists(SPCUSTOMDIR . $group->group_icon))
                        $icon = SPTHEMEICONSURL . 'sp_GroupIcon.png';
                }

                $group_icon_type = 'file';

                if (empty($group->group_icon)) {
                    $icon = SPTHEMEICONSURL . 'sp_GroupIcon.png';
                } else {

                    $group_icon = spa_get_saved_icon($group->group_icon);

                    if ('file' === $group_icon['type']) {
                        $icon = esc_url(SPCUSTOMURL . $group_icon['icon']);
                        if (!file_exists(SPCUSTOMDIR . $group_icon['icon'])) {
                            $icon = SPTHEMEICONSURL . 'sp_GroupIcon.png';
                        }
                    } else {
                        $group_icon_type = 'font';
                        $icon = $group_icon['icon'];
                    }
                }

                # Group
                ?>
                <div id="grouprow-<?php echo $group->group_id; ?>" class="sf-panel-body-top sf-mobile-no-horizontal-padding">
                    <table class="sf-full-width sf-table-mobile">
                        <tr>
                            <td class="sf-v-a-middle">
                                <?php
                                if ('file' === $group_icon_type) {
                                    echo '<img src="' . $icon . '" alt="" title="' . SP()->primitives->admin_text('Current group icon') . '" />';
                                } else {
                                    echo '<i class="' . $icon . '"></i>';
                                }
                                ?>
                                <!--<a title="<?php SP()->primitives->admin_etext('Collapse/expand forum listing'); ?>" class="spExpandCollapseGroup" data-target="forum-group-<?php echo $group->group_id; ?>">&ndash;</a>-->
                            </td>
                            <td class="sf-v-a-middle">
                                <div class="sf-title-block">
                                    <h4><?php echo SP()->displayFilters->title($group->group_name); ?></h4>
                                    <?php echo SP()->displayFilters->text($group->group_desc); ?>
                                    <?php
                                    //sp_display_item_stats(SPFORUMS, 'group_id', $group->group_id, SP()->primitives->admin_text('Forums'));
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="sf-panel-body-top-right sf-mobile-btns">
                                    <?php
                                    $base = wp_nonce_url(SPAJAXURL . 'forums-loader&amp;id=' . $group->group_id, 'forums-loader');
                                    $target = "group-$group->group_id";
                                    $image = SPADMINIMAGES;
                                    ?>
                                    <button class="sf-icon-button spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Add Permission'); ?>" data-form="groupperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open=""><span class="sf-icon sf-blue sf-permissions"></span></button>
                                    <button class="sf-icon-button spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Order Forums'); ?>" data-form="ordering" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php $group->group_id; ?>" data-open=""><span class="sf-icon sf-blue sf-order"></span></button>
                                    <button class="sf-icon-button spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Edit This Group'); ?>" data-form="editgroup" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open=""><span class="sf-icon sf-blue sf-edit"></span></button>
                                    <button class="sf-icon-button spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Delete Group'); ?>" data-form="deletegroup" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open=""><span class="sf-icon sf-blue sf-delete"></span></button>
                                </div>
                            </td>
                        </tr>

                        <tr class="sfinline-form">  <!-- This row will hold ajax forms for the current group -->
                            <td colspan="3" class="sf-padding-none">
                                <div id="group-<?php echo $group->group_id; ?>">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php
                # Forums in group
                $forums = spa_get_forums_in_group($group->group_id);
                if ($forums) {
                    # display the current forum information for each forum in table format
                    ?>
                    <div id="forum-group-<?php echo $group->group_id; ?>">
                        <table class="widefat sf-table-small sf-table-mobile">
                            <?php
                            spa_paint_group_forums($group->group_id, 0, '', 0);
                            ?>
                        </table>
                    </div>
                    <?php
                } else {
                    echo '<div class="sf-alert-block sf-info">' . SP()->primitives->admin_text('There are no forums defined in this group') . '</div>';
                }
                spa_paint_close_container();
                spa_paint_close_tab();
            }
        } else {
            echo '<div class="sfempty">' . SP()->primitives->admin_text('There are no groups defined') . '<br />';
            echo SP()->primitives->admin_text('Select') . ' <b>' . SP()->primitives->admin_text('Create New Group') . '</b> ' . SP()->primitives->admin_text('from the menu on the left to get started') . '</div>';
        }
        ?></div><?php
}

function spa_paint_group_forums($groupid, $parent, $parentname, $level) {
    $space = '<img class="subArrow" src="' . SPADMINIMAGES . 'sp_SubforumLevel.png" alt="" />';
    $forums = spa_get_group_forums_by_parent($groupid, $parent);
    $noMembers = array();

    if ($forums) {
        $noMembers = spa_forums_check_memberships($forums);

        foreach ($forums as $forum) {
            $subforum = $forum->parent;
            $haschild = '';
            if ($forum->children) {
                $childlist = array(unserialize($forum->children));
                if (count($childlist) > 0)
                    $haschild = $childlist;
            }

            $forum_icon_type = 'file';

            if (empty($forum->forum_icon)) {
                $icon = SPTHEMEICONSURL . 'sp_ForumIcon.png';
            } else {


                $forum_icon = spa_get_saved_icon($forum->forum_icon);

                if ('file' === $forum_icon['type']) {
                    $icon = esc_url(SPCUSTOMURL . $forum_icon['icon']);
                    if (!file_exists(SPCUSTOMDIR . $forum_icon['icon'])) {
                        $icon = SPTHEMEICONSURL . 'sp_ForumIcon.png';
                    }
                } else {
                    $forum_icon_type = 'font';
                    $icon = $forum_icon['icon'];
                }
            }
            $rowClass = (in_array($forum->forum_id, $noMembers)) ? ' class="spWarningBG"' : '';
            ?>
            <tr id="forumrow-<?php echo $forum->forum_id; ?>" <?php echo $rowClass; ?>> <!-- display forum information for each forum -->
                <td class="sf-border-none"><?php
                    if ('file' === $forum_icon_type) {
                        echo '<img src="' . $icon . '" alt="" title="' . SP()->primitives->admin_text('Current forum icon') . '" />';
                    } else {
                        echo '<i class="' . $icon . '"></i>';
                    }
                    ?>

                    <?php if ($haschild) { ?>
                        <img class="parentArrow" src="<?php echo SPADMINIMAGES . 'sp_HasChild.png'; ?>" alt="" title="<?php SP()->primitives->admin_etext('Parent Forum'); ?>" />
                    <?php } ?>
                </td>
                <td class="sf-border-none">
                    <?php if ($forum->forum_status) echo '<img class="sfalignright" src="' . SPADMINIMAGES . 'sp_LockedBig.png" alt="" />'; ?>
                    <?php if ($subforum) { ?>
                        <?php if ($forum->forum_disabled) echo '<img class="sfalignright" src="' . SPADMINIMAGES . 'sp_NoWrite.png" alt="" title="' . SP()->primitives->admin_text('Subforum is disabled') . '" /> '; ?>
                        <?php echo str_repeat($space, ($level - 1));
                        ?>
                        <img class="subArrow" src="<?php echo SPADMINIMAGES . 'sp_Subforum.png'; ?>" alt="" title="<?php SP()->primitives->admin_etext('Subforum'); ?>" />
                        <div class="sf-title-block sf-title-block-v2">
                            <h4><?php echo SP()->displayFilters->title($forum->forum_name); ?></h4>
                            <div>(<?php echo SP()->primitives->admin_text('Subforum of') . ': ' . $parentname . ')'; ?></div>
                            <?php echo SP()->displayFilters->text($forum->forum_desc); ?>
                        </div>
                    <?php } else { ?>
                        <?php if ($forum->forum_disabled) echo '<img class="sfalignright" src="' . SPADMINIMAGES . 'sp_NoWrite.png" alt="" title="' . SP()->primitives->admin_text('Forum is disabled') . '" /> '; ?>
                        <div class="sf-title-block sf-title-block-v2">
                            <h4><?php echo SP()->displayFilters->title($forum->forum_name); ?></h4>
                            <?php echo SP()->displayFilters->text($forum->forum_desc); ?>
                        </div>
                    <?php } ?>
                    <?php
                    if (in_array($forum->forum_id, $noMembers)) {
                        echo '<p><b>' . SP()->primitives->admin_text('Warning - There are no usergroups with members that have permission to use this forum') . '</b></p>';
                    }
                    ?>
                </td>
                <td class="sf-border-none sf-mobile-width-by-content sf-mobile-no-vertical-padding">
                    <?php sp_display_item_stats(SPTOPICS, 'forum_id', $forum->forum_id, SP()->primitives->admin_text('Topics')) ?>
                </td>
                <td class="sf-border-none sf-mobile-width-by-content sf-mobile-no-vertical-padding">
                    <?php sp_display_item_stats(SPPOSTS, 'forum_id', $forum->forum_id, SP()->primitives->admin_text('Posts')) ?>
                </td>
                <td class="sf-border-none">
                    <div class="sf-panel-body-top-right sf-mobile-btns sf-mobile-no-vertical-margin">
                        <?php
                        $base = wp_nonce_url(SPAJAXURL . 'forums-loader', 'forums-loader');
                        $target = "forum-$forum->forum_id";
                        $image = SPADMINIMAGES;
                        ?>
                        <button class="sf-icon-button sf-small spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Forum Permissions'); ?>" data-form="forumperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-permissions"></span></button>
                        <?php if ($forum->forum_disabled) { ?>
                            <button class="sf-icon-button sf-small spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Enable Forum'); ?>" data-form="enableforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-forums"></span></button>
                        <?php } else { ?>
                            <button class="sf-icon-button sf-small spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Disable Forum'); ?>" data-form="disableforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-disable-forum"></span></button>
                        <?php } ?>
                        <button class="sf-icon-button sf-small spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Edit This Forum'); ?>" data-form="editforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-edit"></span></button>
                        <button class="sf-icon-button sf-small spStackBtn spLoadForm" title="<?php echo SP()->primitives->admin_text('Delete Forum'); ?>" data-form="deleteforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-delete"></span></button>
                    </div>
                </td>
            </tr>

            <tr class="sfinline-form">  <!-- This row will hold ajax forms for the current forum -->
                <td colspan="5" class="sf-padding-none">
                    <div id="forum-<?php echo $forum->forum_id; ?>"></div>
                </td>
            </tr>
            <?php
            if ($haschild) {
                $newlevel = $level + 1;
                spa_paint_group_forums($groupid, $forum->forum_id, $forum->forum_name, $newlevel);
            }
        }
    }
}

function spa_forums_check_memberships($forums) {
    $value = SP()->meta->get('default usergroup', 'sfguests');
    $ugid = SP()->DB->table(SPUSERGROUPS, "usergroup_id={$value[0]['meta_value']}", 'usergroup_id');
    if (empty($ugid))
        $ugid = 0;
    $noMembers = array();
    foreach ($forums as $forum) {
        $has_members = false;
        $permissions = sp_get_forum_permissions($forum->forum_id);
        if ($permissions) {
            foreach ($permissions as $permission) {
                $members = SP()->DB->table(SPMEMBERSHIPS, "usergroup_id= $permission->usergroup_id", 'row', '', '1');
                if ($members || $permission->usergroup_id == $ugid) {
                    $has_members = true;
                    break;
                }
            }
        }
        if (!$has_members)
            $noMembers[] = $forum->forum_id;
    }
    return $noMembers;
}
