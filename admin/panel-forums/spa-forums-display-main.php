<?php
/*
  Simple:Press
  Admin Forums Main Display
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

function spa_forums_forums_main() { ?>
    <?php
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

        # Fetch all groups
        $groups = SP()->DB->table(SPGROUPS, '', '', 'group_seq');
    ?>
    <div id="sf-tab-forums-main">
        <?php spa_paint_tab_head(SP()->primitives->admin_text('Manage Groups And Forums')); ?>
        <?php if ($groups) : ?>
            <?php foreach ($groups as $group) : ?>
                <?php spa_paint_open_nohead_tab(true, ''); ?>
                <div id="grouprow-<?php echo $group->group_id; ?>" class="spGroupRow">
                    <div class="spGroupRow--container">
                        <div class="spGroupRow--tools">
                            <?php
                            $base = wp_nonce_url(SPAJAXURL . 'forums-loader&amp;id=' . $group->group_id, 'forums-loader');
                            $target = "group-$group->group_id";
                            $image = SPADMINIMAGES;
                            ?>
                            <button class="sf-icon-button spLoadForm" title="<?php echo SP()->primitives->admin_text('Add Permission'); ?>" data-form="groupperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open=""><span class="sf-icon sf-permissions"></span></button>
                            <button class="sf-icon-button spLoadForm" title="<?php echo SP()->primitives->admin_text('Order Forums'); ?>" data-form="ordering" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php $group->group_id; ?>" data-open=""><span class="sf-icon sf-order"></span></button>
                            <button class="sf-icon-button spLoadForm" title="<?php echo SP()->primitives->admin_text('Edit This Group'); ?>" data-form="editgroup" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open=""><span class="sf-icon sf-edit"></span></button>
                            <button class="sf-icon-button spLoadForm" title="<?php echo SP()->primitives->admin_text('Delete Group'); ?>" data-form="deletegroup" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $group->group_id; ?>" data-open=""><span class="sf-icon sf-delete"></span></button>
                        </div>
                        <div class="spGroupRow--information">
                            <div>
                                <h4><?php echo SP()->displayFilters->title($group->group_name); ?></h4>
                                <?php echo SP()->displayFilters->text($group->group_desc); ?>
                            </div>
                        </div>

                        <div>
                            <div id="group-<?php echo $group->group_id; ?>" class="inline-form-container"></div>
                        </div>
                    </div>
                </div>
                <?php
                # Forums in group
                $forums = spa_get_forums_in_group($group->group_id);
                if ($forums) {
                    # display the current forum information for each forum in table format
                    ?>
                    <div id="forum-group-<?php echo $group->group_id; ?>">
                            <?php
                            spa_paint_group_forums($group->group_id, 0, '', 0);
                            ?>
                    </div>
                    <?php
                } else {
                    echo '<div class="sf-alert-block sf-info">' . SP()->primitives->admin_text('There are no forums defined in this group') . '</div>';
                }
                spa_paint_close_tab(); ?>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="sfempty">
                <?php echo SP()->primitives->admin_text('There are no groups defined') ?> <br />
                <?php echo SP()->primitives->admin_text('Select') . ' <b>' . SP()->primitives->admin_text('Create New Group') . '</b> ' . SP()->primitives->admin_text('from the menu on the left to get started'); ?>
            </div>
        <?php endif; ?>
        </div>
    <?php
}

function spa_paint_group_forums($groupid, $parent, $parentname, $level) {
    $forums = spa_get_group_forums_by_parent($groupid, $parent);
    $noMembers = [];

    if ($forums) {
        $noMembers = spa_forums_check_memberships($forums);

        foreach ($forums as $forum) {
            $subforum = $forum->parent;
            $haschild = '';
            if ($forum->children) {
                $childlist = [unserialize($forum->children)];
                if (count($childlist) > 0)
                    $haschild = $childlist;
            }


            # Set default icon and icon type
            $icon = SPTHEMEICONSURL . 'sp_ForumIcon.png';
            $forum_icon_type = 'file';

            # Check if any icons are set
            $forum_icon = spa_get_saved_icon($forum->forum_icon);

            // Filebased icons
            if ('file' === $forum_icon['type'] && $forum_icon['icon'] !== '') {
                $icon = esc_url(SPCUSTOMURL . $forum_icon['icon']);
                if (!file_exists(SPCUSTOMDIR . $forum_icon['icon'])) {
                    $icon = SPTHEMEICONSURL . 'sp_ForumIcon.png';
                }
            }

            // Font awesome and such
            if ('font' === $forum_icon['type']) {
                $forum_icon_type = 'font';
                $icon = $forum_icon['icon'];
            }

            $rowClasses = ['currentLevel' . $level, 'spForumRow'];
            if (in_array($forum->forum_id, $noMembers)) {
                $rowClasses[] = 'spWarningBG';
            }
            ?>
            <div id="forumrow-<?php echo $forum->forum_id; ?>" class="<?php echo implode(' ', $rowClasses) ?>"> <!-- display forum information for each forum -->
                <div class="spForumRow--container">
                    <div class="spForumRow--tools">
                        <?php
                            $base = wp_nonce_url(SPAJAXURL . 'forums-loader', 'forums-loader');
                            $target = "forum-$forum->forum_id";
                            $image = SPADMINIMAGES;
                        ?>
                        <button class="sf-icon-button sf-small spLoadForm" title="<?php echo SP()->primitives->admin_text('Forum Permissions'); ?>" data-form="forumperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-permissions"></span></button>
                        <?php if ($forum->forum_disabled) { ?>
                            <button class="sf-icon-button sf-small spLoadForm" title="<?php echo SP()->primitives->admin_text('Enable Forum'); ?>" data-form="enableforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-forums"></span></button>
                        <?php } else { ?>
                            <button class="sf-icon-button sf-small spLoadForm" title="<?php echo SP()->primitives->admin_text('Disable Forum'); ?>" data-form="disableforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-disable-forum"></span></button>
                        <?php } ?>
                        <button class="sf-icon-button sf-small spLoadForm" title="<?php echo SP()->primitives->admin_text('Edit This Forum'); ?>" data-form="editforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-edit"></span></button>
                        <button class="sf-icon-button sf-small spLoadForm" title="<?php echo SP()->primitives->admin_text('Delete Forum'); ?>" data-form="deleteforum" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open=""><span class="sf-icon sf-blue sf-delete"></span></button>
                    </div>
                    <div class="spForumRow--information">
                        <?php
                           $iconData = '';
                            if ('file' === $forum_icon_type) {
                                $iconData = '<img src="' . $icon . '" alt="" title="' . SP()->primitives->admin_text('Current forum icon') . '" />';
                            } else {
                                $iconData = '<i class="' . $icon . '"></i>';
                            }
                        ?>
                        <?php if ($iconData !== '') : ?>
                            <div class="spForumRow--information--icon">
                                <?php echo $iconData; ?>
                            </div>
                        <?php endif; ?>
                        <div>

                            <h4>
                            <?php if ($forum->forum_status) {
                                echo '<img src="' . SPADMINIMAGES . 'sp_LockedBig.png"  title="' . SP(
                                    )->primitives->admin_text('Forum is locked') . '" />';
                            } ?>

                            <?php if ($forum->forum_disabled) {
                                echo '<img src="' . SPADMINIMAGES . 'sp_NoWrite.png" title="' . SP(
                                    )->primitives->admin_text('Forum is disabled') . '" /> ';
                            } ?>
                            <?php echo SP()->displayFilters->title($forum->forum_name); ?></h4>
                            <?php
                            if (in_array($forum->forum_id, $noMembers)) {
                                echo '<div class="sf-alert-block sf-warning">' . SP()->primitives->admin_text('Warning - There are no usergroups with members that have permission to use this forum') . '</div>';
                            }
                            ?>
                            <div class="spForumRow--stats">
                                <?php sp_display_item_stats(SPTOPICS, 'forum_id', $forum->forum_id, SP()->primitives->admin_text('Topics:')) ?><?php sp_display_item_stats(SPPOSTS, 'forum_id', $forum->forum_id, SP()->primitives->admin_text('Posts:')) ?>
                            </div>

                        </div>
                    </div>
                    <div class="sfinline-form">  <!-- This row will hold ajax forms for the current forum -->
                        <div id="forum-<?php echo $forum->forum_id; ?>"></div>
                    </div>
                </div>
            </div>


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
