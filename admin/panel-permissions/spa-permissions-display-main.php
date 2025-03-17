<?php
/*
  Simple:Press
  Admin Permissions Main Display
  $LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
  $Rev: 15488 $
 */

if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}

function spa_permissions_permission_main() {
    spa_paint_open_tab(SP()->primitives->admin_text('Manage Permissions Sets'), true);
    $roles = sp_get_all_roles();
    if ($roles) {
        foreach ($roles as $role) {
            ?>
            <table id="rolerow-<?php echo esc_attr($role->role_id); ?>" class="widefat sf-table-small sf-table-mobile">
                <tr>
                    <td class='row-title'><?php echo esc_attr($role->role_id); ?></td>
                    <td>
                        <div class="sf-title-block sf-title-block-v2">
                            <h4><?php echo esc_attr(SP()->displayFilters->title($role->role_name)); ?></h4>
                            <p><?php echo esc_attr(SP()->displayFilters->title($role->role_desc)); ?></p>
                        </div>
                    </td>
                    <td style="width: 250px;">
                        <?php
                        $base = wp_nonce_url(SPAJAXURL . 'permissions-loader', 'permissions-loader');
                        $target = 'perm-' . $role->role_id;
                        $image = SPADMINIMAGES;
                        $site = wp_nonce_url(SPAJAXURL . "permission-tip&amp;role={$role->role_id}", 'permission-tip');
                        $title = SP()->displayFilters->title($role->role_name);
                        ?>
                        <div class="sf-panel-body-top-right sf-mobile-btns sf-mobile-no-vertical-margin">
                            <input type="button" class="sf-button-secondary spLoadForm" value="<?php SP()->primitives->admin_etext('Edit'); ?>" data-form="editperm" data-url="<?php echo esc_url($base); ?>" data-target="<?php echo esc_attr($target); ?>" data-img="<?php echo esc_url($image); ?>" data-id="<?php echo esc_attr($role->role_id); ?>" data-open="" />
                            <input type="button" class="sf-button-secondary spLoadForm" value="<?php SP()->primitives->admin_etext('Delete'); ?>" data-form="delperm" data-url="<?php echo esc_url($base); ?>" data-target="<?php echo esc_attr($target); ?>" data-img="<?php echo esc_url($image); ?>" data-id="<?php echo esc_attr($role->role_id); ?>" data-open="" />
                            <input type='button' class='sf-button-secondary spOpenDialog' value='<?php SP()->primitives->admin_etext('Usage'); ?>' data-site='<?php echo esc_url($site); ?>' data-label='<?php echo esc_attr($title); ?>' data-width='600' data-height='0' data-align='center' />
                        </div>
                    </td>
                </tr>
                <tr class="sfinline-form"> <!-- This row will hold ajax forms for the current permission set -->
                    <td colspan="3" class="sf-padding-none">
                        <div id="perm-<?php echo esc_attr($role->role_id); ?>">
                        </div>
                    </td>
                </tr>
            </table>
        <?php } ?>
        <br />
        <?php
    } else {
        echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;' . esc_html(SP()->primitives->admin_text('There are no Permission Sets defined.')). '</div>';
    }
    spa_paint_close_container();
    spa_paint_close_tab();
}
