<?php
/*
  Simple:Press
  Admin User Groups Delete Member Form
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}

function spa_usergroups_delete_members_form($usergroup_id) {
    $usergroup_id = absint($usergroup_id);
    $formId = sprintf('sfmemberdel-%s-%d', esc_attr(uniqid()), $usergroup_id); ?>
<script>
    (function (spj, $, undefined) {
        $(document).ready(function () {
            $('#<?php echo esc_js($formId); ?>').ajaxForm({
                target: '#sfmsgspot',
            });
        });
    }(window.spj = window.spj || {}, jQuery));
</script>
<?php
    spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL . 'usergroups-loader&amp;saveform=delmembers', 'usergroups-loader');

    $url = wp_nonce_url(SPAJAXURL . 'memberships&amp;targetaction=del', 'memberships');
    $target = 'sfmsgspot';
    $smessage = SP()->primitives->admin_text('Please Wait - Processing');
    
    $emessage = SP()->primitives->admin_text('Users Removed/Moved');
?>
<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="<?php echo esc_attr($formId); ?>" name="sfmemberdel<?php echo esc_attr($usergroup_id); ?>"
          onsubmit="spj.addDelMembers(
                      '<?php echo esc_js($formId); ?>',
                      '<?php echo esc_js($url); ?>',
                      '<?php echo esc_js($target); ?>',
                      '<?php echo esc_js($smessage); ?>',
                      '<?php echo esc_js($emessage); ?>',
                      0, 50, '#dmid<?php echo esc_js($usergroup_id); ?>',
					  'move'
                      );">
        <?php
        spa_paint_open_nohead_tab(true, '');
        echo '<input type="hidden" name="'.esc_attr('forum-adminform_memberdel').'" value="'.esc_attr(wp_create_nonce('forum-adminform_memberdel')).'" />';
        ?>
        <input type="hidden" name="usergroupid" value="<?php echo esc_attr($usergroup_id); ?>" />
        <?php
        $from = esc_js(SP()->primitives->admin_text('Current Members'));
        $ug = true;
        $action = 'delug';
        require_once SP_PLUGIN_DIR . '/admin/library/ajax/spa-ajax-multiselect.php';
        ?>
        <?php do_action('sph_usergroup_delete_member_panel'); ?>
        <span class="sf-controls">
            <input type="submit" class="sf-button-primary" name="sfmemberdel<?php echo esc_attr($usergroup_id); ?>" value="<?php echo esc_attr(SP()->primitives->admin_text('Remove from group')); ?>" />
            <input type="button" class="sf-button-primary spCancelForm" data-target="#members-<?php echo esc_attr($usergroup_id); ?>" name="delmemberscancel<?php echo esc_attr($usergroup_id); ?>" value="<?php echo esc_attr(SP()->primitives->admin_text('Cancel')); ?>" />
        </span>
        <span class="sf-button sf-hidden-important" id='onFinish'></span>
        <div class="pbar" id="progressbar"></div>
         <?php
        spa_paint_close_container();
        spa_paint_close_tab();
        ?>
    </form>
    <?php
}
